<?php
require __DIR__ . '/../config/config.php';
require_client();

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

// The signed-in client, kept in one place because the payment gate
// below needs it several times. current_user() caches its row, so this
// costs nothing.
$userForGate = current_user();

$st = $pdo->prepare("SELECT p.*, c.full_name AS client_name, c.company, s.name AS service, a.full_name AS assigned_name
                     FROM projects p
                     JOIN users c ON c.id = p.client_id
                     LEFT JOIN services s ON s.id = p.service_id
                     LEFT JOIN users a ON a.id = p.assigned_to
                     WHERE p.id = ?");
$st->execute([$id]);
$project = $st->fetch();

if (!$project || (int)$project['client_id'] !== (int)current_user()['id']) {
    // Another client's project is reported exactly like a missing one, so
    // the URL cannot be used to probe for other people's project ids.
    dashboard_not_found(
        'Project not found',
        'This project does not exist, or it belongs to another account.',
        app_url('client/projects.php'),
        'Back to my projects'
    );
}

$tab = $_GET['tab'] ?? 'overview';
$allowedTabs = ['overview', 'tasks', 'files', 'messages', 'quotation', 'invoice', 'activity'];
if (!in_array($tab, $allowedTabs, true)) {
    redirect(app_url('client/project.php?id=' . $id));
}

/* ---------- actions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'send_message') {
        $message = trim($_POST['message'] ?? '');
        if ($message !== '') {
            if (mb_strlen($message) > 5000) {
                set_errors(['Message is too long (max 5000 characters).']);
                redirect(app_url('client/project.php?id=' . $id . '&tab=messages'));
            }
            $pdo->prepare(
                "INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal) VALUES (?,?,'client',?,0)"
            )->execute([$id, (int)current_user()['id'], $message]);
            audit('message_sent', 'project', $id, 'Client sent a message');
            notify_staff('New message on ' . $project['ref_no'], $project['title'] . ': ' . truncate($message, 120), 'project', $id);
            flash('success', 'Message sent.');
        }
        redirect(app_url('client/project.php?id=' . $id . '&tab=messages'));
    }

    if ($action === 'quotation_decision') {
        $qid = (int)($_POST['quotation_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['accepted', 'rejected'], true)) {
            set_errors(['Invalid action.']);
            redirect(app_url('client/project.php?id=' . $id . '&tab=quotation'));
        }
        $qst = $pdo->prepare('SELECT * FROM quotations WHERE id = ? AND project_id = ? AND client_id = ?');
        $qst->execute([$qid, $id, (int)current_user()['id']]);
        $quote = $qst->fetch();
        if (!$quote) { set_errors(['Quotation not found.']); redirect(app_url('client/project.php?id=' . $id . '&tab=quotation')); }
        if (in_array($quote['status'], ['accepted', 'rejected', 'cancelled', 'expired'], true)) {
            set_errors(['This quotation has already been responded to.']);
            redirect(app_url('client/project.php?id=' . $id . '&tab=quotation'));
        }
        $pdo->prepare('UPDATE quotations SET status = ? WHERE id = ?')->execute([$decision, $qid]);
        audit('quotation_' . $decision, 'quotation', $qid, 'Client ' . $decision . ' ' . $quote['quotation_no']);
        notify_staff('Quotation ' . $decision, ($decision === 'accepted' ? 'Client accepted' : 'Client rejected') . ' quotation ' . $quote['quotation_no'] . ' for ' . $project['ref_no'], 'quotation', $id);
        flash('success', 'Quotation ' . ($decision === 'accepted' ? 'accepted.' : 'declined.'));
        redirect(app_url('client/project.php?id=' . $id . '&tab=quotation'));
    }

    if ($action === 'submit_payment') {
        // The gate. Checked on the server, not just by hiding the form,
        // because the absence of an input is not a control anyone can
        // rely on — this endpoint can be posted to directly.
        billing_require_complete($userForGate, billing_return_here());

        $invId = (int)($_POST['invoice_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? 'other';
        $reference = trim($_POST['reference'] ?? '');
        if (!in_array($method, ['mtn_momo', 'airtel_money', 'bank', 'cash', 'other'], true)) $method = 'other';
        $ist = $pdo->prepare('SELECT * FROM invoices WHERE id = ? AND project_id = ? AND client_id = ?');
        $ist->execute([$invId, $id, (int)current_user()['id']]);
        $inv = $ist->fetch();
        if (!$inv) { set_errors(['Invoice not found.']); redirect(app_url('client/project.php?id=' . $id . '&tab=invoice')); }
        $outstanding = max(0, (float)$inv['total'] - (float)$inv['amount_paid']);
        if ($amount <= 0 || $amount > $outstanding) {
            set_errors(['Payment amount is invalid. Outstanding balance: ' . money($outstanding, settings('currency')) . '.']);
            redirect(app_url('client/project.php?id=' . $id . '&tab=invoice'));
        }
        $pdo->prepare(
            "INSERT INTO payments (invoice_id, amount, method, reference, status, notes) VALUES (?,?,?,?, 'pending', 'Initiated by client')"
        )->execute([$invId, $amount, $method, $reference ?: null]);
        audit('payment_submitted', 'invoice', $invId, 'Client submitted pending payment of ' . $amount . ' ' . settings('currency'));
        notify_staff('Payment submitted', 'A payment of ' . money($amount, settings('currency')) . ' for ' . $inv['invoice_no'] . ' awaits confirmation.', 'invoice', $id);
        flash('success', 'Payment submitted. Our team will confirm it once received.');
        redirect(app_url('client/project.php?id=' . $id . '&tab=invoice'));
    }

    redirect(app_url('client/project.php?id=' . $id));
}

/* ---------- data ---------- */
$tasks = $pdo->prepare('SELECT * FROM project_tasks WHERE project_id = ? ORDER BY status, due_date IS NULL, due_date, id');
$tasks->execute([$id]); $tasks = $tasks->fetchAll();

$files = $pdo->prepare('SELECT f.*, u.full_name AS uploader FROM project_files f JOIN users u ON u.id = f.uploader_id WHERE f.project_id = ? ORDER BY f.created_at DESC');
$files->execute([$id]); $files = $files->fetchAll();

$messages = $pdo->prepare('SELECT m.*, u.full_name AS sender_name FROM project_messages m JOIN users u ON u.id = m.sender_id WHERE m.project_id = ? AND m.is_internal = 0 ORDER BY m.created_at ASC');
$messages->execute([$id]); $messages = $messages->fetchAll();

$quotations = $pdo->prepare('SELECT q.*, (SELECT COUNT(*) FROM quotation_items qi WHERE qi.quotation_id = q.id) AS item_count FROM quotations q WHERE q.project_id = ? ORDER BY q.created_at DESC');
$quotations->execute([$id]); $quotations = $quotations->fetchAll();

$invoices = $pdo->prepare('SELECT i.*, (SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.invoice_id = i.id AND p.status = \'confirmed\') AS confirmed_paid FROM invoices i WHERE i.project_id = ? ORDER BY i.created_at DESC');
$invoices->execute([$id]); $invoices = $invoices->fetchAll();

$activity = $pdo->prepare("SELECT * FROM activity_logs WHERE entity_type = 'project' AND entity_id = ? ORDER BY created_at DESC LIMIT 20");
$activity->execute([$id]); $activity = $activity->fetchAll();

$depositDue = deposit_balance($pdo, $id);
$depositInv = deposit_invoice_for($pdo, $id);

$tabQ = urlencode($id);

dashboard_head(['title' => $project['title'], 'active' => 'projects', 'crumb' => 'Project']);
?>
<div class="dash-head">
  <div>
    <a class="small" href="<?= app_url('client/projects.php') ?>"><?= icon('arrow-l') ?> Back to projects</a>
    <h2><?= e($project['title']) ?></h2>
    <p class="sub"><?= e($project['ref_no']) ?> <?= $project['service'] ? '· ' . e($project['service']) : '' ?> · Submitted <?= fmt_date($project['submitted_at']) ?></p>
  </div>
  <div class="actions no-print">
    <?= status_badge($project['status']) ?> <?= priority_badge($project['priority']) ?>
  </div>
</div>

<?php render_alerts(); ?>

<?php if ($depositInv && $depositDue > 0 && !in_array($depositInv['status'], ['paid', 'cancelled'], true)): ?>
  <?php if (!billing_complete($userForGate)): ?>
    <div class="deposit-callout gated">
      <span class="dc-icon"><?= icon('lock') ?></span>
      <div class="dc-body">
        <b>Confirm your payment details to pay your <?= money($depositDue, settings('currency')) ?> deposit.</b>
        <p>We need a few details about you before we can accept the payment. It takes about a minute, you only do it once, and you will come straight back to this project.</p>
      </div>
      <a class="btn btn-primary btn-sm" href="<?= e(app_url('client/billing.php?next=' . urlencode('/client/project.php?id=' . $id . '&tab=invoice'))) ?>"><?= icon('edit') ?> Enter my details</a>
    </div>
  <?php else: ?>
  <div class="deposit-callout">
    <span class="dc-icon"><?= icon('rocket') ?></span>
    <div class="dc-body">
      <b>Complete your <?= money($depositDue, settings('currency')) ?> project deposit to activate this project.</b>
      <p>Your project stays on hold for the team until the deposit is confirmed. Pay by MTN MoMo, Airtel Money, bank transfer or cash, then submit the payment for confirmation.</p>
    </div>
    <a class="btn btn-primary btn-sm" href="<?= app_url('client/project.php?id=' . $id . '&tab=invoice') ?>"><?= icon('money') ?> Pay deposit</a>
  </div>
  <?php endif; ?>
<?php endif; ?>

<div class="tabs" role="tablist">
  <a class="tab <?= $tab === 'overview' ? 'active' : '' ?>" href="project.php?id=<?= $tabQ ?>"><?= icon('dashboard') ?> Overview</a>
  <a class="tab <?= $tab === 'tasks' ? 'active' : '' ?>" href="project.php?id=<?= $tabQ ?>&tab=tasks"><?= icon('list') ?> Tasks (<?= count($tasks) ?>)</a>
  <a class="tab <?= $tab === 'files' ? 'active' : '' ?>" href="project.php?id=<?= $tabQ ?>&tab=files"><?= icon('file') ?> Files (<?= count($files) ?>)</a>
  <a class="tab <?= $tab === 'messages' ? 'active' : '' ?>" href="project.php?id=<?= $tabQ ?>&tab=messages"><?= icon('chat') ?> Messages</a>
  <a class="tab <?= $tab === 'quotation' ? 'active' : '' ?>" href="project.php?id=<?= $tabQ ?>&tab=quotation"><?= icon('doc') ?> Quotation</a>
  <a class="tab <?= $tab === 'invoice' ? 'active' : '' ?>" href="project.php?id=<?= $tabQ ?>&tab=invoice"><?= icon('money') ?> Invoice</a>
  <a class="tab <?= $tab === 'activity' ? 'active' : '' ?>" href="project.php?id=<?= $tabQ ?>&tab=activity"><?= icon('clock') ?> Activity</a>
</div>

<?php if ($tab === 'overview'): ?>
  <div class="grid-2">
    <section class="panel">
      <div class="panel-head"><h3>About this project</h3></div>
      <p class="mb-1"><strong>Description</strong></p>
      <p><?= nl2br(e($project['description'])) ?></p>
      <?php if ($project['requirements']): ?>
        <p class="mb-1"><strong>Requirements</strong></p>
        <p><?= nl2br(e($project['requirements'])) ?></p>
      <?php endif; ?>
      <div class="kv"><span>Reference number</span><span><?= e($project['ref_no']) ?></span></div>
      <div class="kv"><span>Service</span><span><?= e($project['service'] ?? 'General') ?></span></div>
      <div class="kv"><span>Budget</span><span><?= $project['budget'] !== null ? money($project['budget'], settings('currency')) : 'Not specified' ?></span></div>
      <div class="kv"><span>Priority</span><span><?= e(ucfirst($project['priority'])) ?></span></div>
      <div class="kv"><span>Deadline</span><span><?= $project['deadline'] ? fmt_date($project['deadline']) : 'Not set' ?></span></div>
      <div class="kv"><span>Assigned team member</span><span><?= e($project['assigned_name'] ?? 'To be assigned') ?></span></div>
      <div class="kv"><span>Last updated</span><span><?= fmt_date($project['updated_at'], 'd M Y H:i') ?></span></div>
    </section>
    <section class="panel">
      <div class="panel-head"><h3>Progress</h3><strong><?= (int)$project['progress'] ?>%</strong></div>
      <div class="progress lg"><span style="width:<?= max(0, min(100, (int)$project['progress'])) ?>%"></span></div>
      <h4 class="mt-3">Project timeline</h4>
      <?php render_milestones($project['status']); ?>
      <?php if ($project['status'] === 'WAITING_FOR_CLIENT'):
        $hint = 'We are waiting for information or confirmation from you. Please check your messages.';
      elseif ($project['status'] === 'QUOTATION'):
        $hint = 'A quotation is ready for your review — open the Quotation tab to respond.';
      else:
        $hint = '';
      endif; ?>
      <?php if ($hint): ?><div class="alert info mt-2"><?= e($hint) ?></div><?php endif; ?>
    </section>
  </div>
<?php elseif ($tab === 'tasks'): ?>
  <section class="panel">
    <div class="panel-head"><h3>Project tasks</h3><a class="btn btn-ghost btn-sm" href="project.php?id=<?= $tabQ ?>&tab=overview">Overview</a></div>
    <?php if ($tasks): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Task</th><th>Status</th><th>Progress</th><th>Priority</th><th>Due date</th></tr></thead>
          <tbody>
            <?php foreach ($tasks as $t): ?>
              <tr>
                <td><div class="row-title"><?= e($t['title']) ?></div><?php if ($t['description']): ?><div class="row-sub"><?= e(truncate($t['description'], 100)) ?></div><?php endif; ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td style="min-width:130px"><div class="progress"><span style="width:<?= max(0, min(100, (int)$t['progress'])) ?>%"></span></div><small class="muted"><?= (int)$t['progress'] ?>%</small></td>
                <td><?= priority_badge($t['priority']) ?></td>
                <td class="muted small"><?= $t['due_date'] ? fmt_date($t['due_date']) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('list') ?><h4>No tasks yet</h4><p>The team will add tasks with progress once your project gets underway.</p></div>
    <?php endif; ?>
  </section>
<?php elseif ($tab === 'files'): ?>
  <section class="panel">
    <div class="panel-head"><h3>Project files</h3></div>
    <?php if ($files): ?>
      <?php foreach ($files as $f): ?>
        <div class="file-card">
          <span class="fc-icon"><?= icon('file') ?></span>
          <div style="flex-grow:1;min-width:0">
            <b><?= e($f['original_name']) ?></b>
            <small><?= pretty_size((int)$f['size_bytes']) ?> · uploaded by <?= e($f['uploader']) ?> · <?= fmt_date($f['created_at']) ?></small>
          </div>
          <a class="btn btn-ghost btn-sm" href="<?= app_url('download.php?id=' . (int)$f['id']) ?>"><?= icon('download') ?> Download</a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><?= icon('file') ?><h4>No files yet</h4><p>Supporting documents and deliverables for this project will appear here.</p></div>
    <?php endif; ?>
  </section>
<?php elseif ($tab === 'messages'): ?>
  <div class="grid-2">
    <section class="panel">
      <div class="panel-head"><h3>Conversation with the team</h3></div>
      <?php if ($messages): ?>
        <div class="chat">
          <?php $myId = (int)current_user()['id']; foreach ($messages as $m): ?>
            <div class="msg <?= (int)$m['sender_id'] === $myId ? 'out' : '' ?>">
              <div class="bubble"><?= nl2br(e($m['message'])) ?></div>
              <span class="meta"><?= e($m['sender_name']) ?> · <?= fmt_date($m['created_at'], 'd M Y H:i') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty"><?= icon('chat') ?><h4>No messages yet</h4><p>Messages between you and the RSI team about this project appear here.</p></div>
      <?php endif; ?>
    </section>
    <section class="panel">
      <div class="panel-head"><h3>Send a message</h3></div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="send_message">
        <div class="field"><label for="message">Write your message *</label><textarea class="textarea" id="message" name="message" required maxlength="5000" placeholder="Ask a question, request a change or share useful information about this project…"></textarea></div>
        <button class="btn btn-primary" type="submit"><?= icon('send') ?> Send message</button>
      </form>
    </section>
  </div>
<?php elseif ($tab === 'quotation'): ?>
  <?php if ($quotations): foreach ($quotations as $quote):
    $items = $pdo->prepare('SELECT * FROM quotation_items WHERE quotation_id = ?');
    $items->execute([(int)$quote['id']]); $items = $items->fetchAll();
  ?>
    <section class="panel">
      <div class="panel-head">
        <h3><?= e($quote['quotation_no']) ?></h3>
        <?= status_badge($quote['status']) ?>
      </div>
      <div class="meta-row mb-2">
        <span>Issued: <?= fmt_date($quote['issued_on']) ?></span>
        <span>Expires: <?= fmt_date($quote['expiry_date']) ?></span>
      </div>
      <div class="doc-summary mb-2">
        <?php if ($items): ?>
          <?php foreach ($items as $it): ?>
            <div class="doc-line"><span><?= e($it['description']) ?> × <?= rtrim(rtrim(number_format((float)$it['quantity'], 2), '0'), '.') ?></span><span><?= money((float)$it['unit_price'] * (float)$it['quantity'], settings('currency')) ?></span></div>
          <?php endforeach; ?>
        <?php endif; ?>
        <div class="doc-line"><span>Subtotal</span><span><?= money($quote['subtotal'], settings('currency')) ?></span></div>
        <?php if ((float)$quote['discount'] > 0): ?><div class="doc-line"><span>Discount</span><span>- <?= money($quote['discount'], settings('currency')) ?></span></div><?php endif; ?>
        <?php if ((float)$quote['tax_percent'] > 0): ?><div class="doc-line"><span>Tax (<?= rtrim(rtrim(number_format((float)$quote['tax_percent'], 2), '0'), '.') ?>%)</span><span><?= money((float)$quote['total'] - ((float)$quote['subtotal'] - (float)$quote['discount']), settings('currency')) ?></span></div><?php endif; ?>
        <div class="doc-line total"><span>Total</span><span><?= money($quote['total'], settings('currency')) ?></span></div>
      </div>
      <?php if ($quote['notes']): ?><p class="muted small"><?= nl2br(e($quote['notes'])) ?></p><?php endif; ?>
      <?php if ($quote['terms']): ?><p class="muted small"><strong>Terms:</strong> <?= nl2br(e($quote['terms'])) ?></p><?php endif; ?>
      <?php if (in_array($quote['status'], ['draft', 'sent', 'expired'], true)): ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <?php if ($quote['status'] === 'sent'): ?>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="quotation_decision">
              <input type="hidden" name="quotation_id" value="<?= (int)$quote['id'] ?>">
              <input type="hidden" name="decision" value="accepted">
              <button class="btn btn-primary" type="submit"><?= icon('check') ?> Accept quotation</button>
            </form>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="quotation_decision">
              <input type="hidden" name="quotation_id" value="<?= (int)$quote['id'] ?>">
              <input type="hidden" name="decision" value="rejected">
              <button class="btn btn-danger" type="submit">Decline quotation</button>
            </form>
          <?php else: ?>
            <p class="muted small">This quotation is currently <?= status_badge($quote['status']) ?> and cannot be responded to.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
  <?php else: ?>
    <section class="panel"><div class="empty"><?= icon('doc') ?><h4>No quotation yet</h4><p>Once the team reviews your request, a detailed quotation will appear here for approval.</p></div></section>
  <?php endif; ?>
<?php elseif ($tab === 'invoice'): ?>
  <?php if ($invoices): foreach ($invoices as $inv):
    $items = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
    $items->execute([(int)$inv['id']]); $items = $items->fetchAll();
    $outstanding = max(0, (float)$inv['total'] - (float)$inv['amount_paid']);
  ?>
    <section class="panel">
      <div class="panel-head"><h3><?= e($inv['invoice_no']) ?></h3><?= status_badge($inv['status']) ?></div>
      <div class="meta-row mb-2"><span>Created: <?= fmt_date($inv['created_at'], 'd M Y') ?></span><span>Due: <?= fmt_date($inv['due_date']) ?></span></div>
      <div class="doc-summary mb-2">
        <?php foreach ($items as $it): ?>
          <div class="doc-line"><span><?= e($it['description']) ?> × <?= rtrim(rtrim(number_format((float)$it['quantity'], 2), '0'), '.') ?></span><span><?= money((float)$it['unit_price'] * (float)$it['quantity'], settings('currency')) ?></span></div>
        <?php endforeach; ?>
        <div class="doc-line"><span>Subtotal</span><span><?= money($inv['subtotal'], settings('currency')) ?></span></div>
        <?php if ((float)$inv['discount'] > 0): ?><div class="doc-line"><span>Discount</span><span>- <?= money($inv['discount'], settings('currency')) ?></span></div><?php endif; ?>
        <div class="doc-line total"><span>Total</span><span><?= money($inv['total'], settings('currency')) ?></span></div>
        <div class="doc-line"><span>Paid</span><span><?= money($inv['amount_paid'], settings('currency')) ?></span></div>
        <div class="doc-line"><span>Outstanding</span><span><?= money($outstanding, settings('currency')) ?></span></div>
      </div>
      <?php if ($inv['notes']): ?><p class="muted small"><?= nl2br(e($inv['notes'])) ?></p><?php endif; ?>
      <?php if ($outstanding > 0 && !in_array($inv['status'], ['paid', 'cancelled'], true)): ?>
        <p class="muted small"><strong>How to pay</strong> — send the amount using any option below, then submit the payment form so our team can confirm it.</p>
        <?= payment_instructions_html() ?>
        <?php if (!billing_complete($userForGate)): ?>
          <?php
          billing_lock_notice_html(
              $userForGate,
              billing_return_here(),
              'We cannot accept this payment until we know who is paying. It takes about a minute, and you only do it once.'
          );
          ?>
        <?php else: ?>
          <?php billing_summary_html($userForGate); ?>
          <details class="mt-2" open>
            <summary class="small" style="font-weight:700;cursor:pointer">Submit a payment for confirmation</summary>
            <form method="post" class="mt-2" novalidate>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="submit_payment">
              <input type="hidden" name="invoice_id" value="<?= (int)$inv['id'] ?>">
              <div class="form-row">
                <div class="field"><label for="amount">Amount (<?= e(settings('currency')) ?>) *</label><input class="input" id="amount" type="number" min="1" step="100" name="amount" required max="<?= e((string)$outstanding) ?>" value="<?= e($outstanding > 0 ? (string)$outstanding : '') ?>"><div class="form-note">Outstanding: <?= money($outstanding, settings('currency')) ?></div></div>
                <div class="field"><label for="method">Payment method *</label>
                  <select class="select" id="method" name="method" required>
                    <option value="mtn_momo">MTN Mobile Money</option>
                    <option value="airtel_money">Airtel Money</option>
                    <option value="bank">Bank transfer</option>
                    <option value="cash">Cash</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>
              <div class="field"><label for="reference">Reference / transaction ID</label><input class="input" id="reference" name="reference" maxlength="120" placeholder="e.g. MoMo transaction reference"></div>
              <button class="btn btn-primary" type="submit"><?= icon('money') ?> Submit payment</button>
            </form>
          </details>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
  <?php else: ?>
    <section class="panel"><div class="empty"><?= icon('money') ?><h4>No invoices yet</h4><p>Once your project is quoted and approved, invoices will appear here.</p></div></section>
  <?php endif; ?>
<?php else: ?>
  <section class="panel">
    <div class="panel-head"><h3>Activity history</h3></div>
    <?php if ($activity): ?>
      <div class="timeline">
        <?php foreach ($activity as $log): ?>
          <div class="tl-item done"><b><?= e(str_replace('_', ' ', ucfirst($log['action']))) ?></b><span><?= fmt_date($log['created_at'], 'd M Y H:i') ?> · <?= $log['details'] ? e(str_replace('_', ' ', $log['details'])) : '' ?></span></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('clock') ?><h4>No activity recorded yet</h4><p>Significant events on this project will appear here over time.</p></div>
    <?php endif; ?>
  </section>
<?php endif; ?>
<?php dashboard_footer(); ?>