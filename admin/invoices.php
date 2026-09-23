<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$me = current_user();
$st = $_GET['status'] ?? '';
$q  = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'record_payment') {
        $invId = (int)($_POST['invoice_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? 'other';
        if (!in_array($method, ['mtn_momo', 'airtel_money', 'bank', 'cash', 'other'], true)) $method = 'other';
        $reference = trim($_POST['reference'] ?? '') ?: null;
        $decision = $_POST['payment_status'] ?? 'confirmed';
        $inv = $pdo->prepare('SELECT i.*, p.client_id, p.id AS project_id FROM invoices i JOIN projects p ON p.id = i.project_id WHERE i.id = ?');
        $inv->execute([$invId]);
        $inv = $inv->fetch();
        if ($inv && $amount > 0) {
            if ($decision === 'pending') {
                $pdo->prepare("INSERT INTO payments (invoice_id, amount, method, reference, status, received_by, notes) VALUES (?,?,?,?, 'pending', ?, 'Recorded by staff awaiting confirmation')")
                    ->execute([$invId, $amount, $method, $reference, (int)$me['id']]);
                audit('payment_recorded', 'invoice', $invId, 'Pending payment of ' . $amount . ' on ' . $inv['invoice_no']);
                flash('success', 'Pending payment recorded for ' . $inv['invoice_no'] . '.');
            } else {
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO payments (invoice_id, amount, method, reference, status, received_by, notes, confirmed_at) VALUES (?,?,?,?, 'confirmed', ?, ?, NOW())")
                    ->execute([$invId, $amount, $method, $reference, (int)$me['id'], 'Confirmed by ' . $me['full_name']]);
                $newPaid = (float)$inv['amount_paid'] + $amount;
                $newStatus = $newPaid >= (float)$inv['total'] ? 'paid' : 'partially_paid';
                $pdo->prepare('UPDATE invoices SET amount_paid = ?, status = ? WHERE id = ?')->execute([$newPaid, $newStatus, $invId]);
                $pdo->commit();
                audit('payment_confirmed', 'invoice', $invId, 'Payment of ' . $amount . ' confirmed on ' . $inv['invoice_no']);
                notify((int)$inv['client_id'], 'Payment confirmed', 'Payment of ' . money($amount, settings('currency')) . ' confirmed on ' . $inv['invoice_no'] . '.', 'invoice', (int)$inv['project_id']);
                flash('success', 'Payment confirmed on ' . $inv['invoice_no'] . '. Invoice is now ' . str_replace('_', ' ', $newStatus) . '.');
            }
        } else {
            set_errors(['Payment could not be recorded.']);
        }
        redirect(app_url('admin/invoices.php'));
    }

    if ($action === 'decide_payment') {
        $payId = (int)($_POST['payment_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        $p = $pdo->query('SELECT p.*, i.invoice_no, i.total, i.amount_paid, i.id AS invoice_id FROM payments p JOIN invoices i ON i.id = p.invoice_id WHERE p.id = ' . $payId)->fetch();
        if ($p) {
            if ($decision === 'confirm' && $p['status'] === 'pending') {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE payments SET status = 'confirmed', confirmed_at = NOW(), notes = CONCAT(COALESCE(notes,''), ' Confirmed by ' , ?) WHERE id = ?")->execute([$me['full_name'], $payId]);
                $newPaid = (float)$p['amount_paid'] + (float)$p['amount'];
                $newStatus = $newPaid >= (float)$p['total'] ? 'paid' : 'partially_paid';
                $pdo->prepare('UPDATE invoices SET amount_paid = ?, status = ? WHERE id = ?')->execute([$newPaid, $newStatus, $p['invoice_id']]);
                $pdo->commit();
                audit('payment_confirmed', 'invoice', $p['invoice_id'], 'Payment of ' . $p['amount'] . ' confirmed on ' . $p['invoice_no']);
                flash('success', 'Payment confirmed. ' . $p['invoice_no'] . ' is now ' . str_replace('_', ' ', $newStatus) . '.');
            } elseif ($decision === 'reject' && $p['status'] === 'pending') {
                $pdo->prepare("UPDATE payments SET status = 'rejected' WHERE id = ?")->execute([$payId]);
                audit('payment_rejected', 'invoice', $p['invoice_id'], 'Payment of ' . $p['amount'] . ' rejected on ' . $p['invoice_no']);
                flash('success', 'Payment rejected.');
            } else {
                set_errors(['Unsupported payment action.']);
            }
        }
        redirect(app_url('admin/invoices.php'));
    }

    if ($action === 'inv_status') {
        $invId = (int)($_POST['invoice_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['draft', 'sent', 'cancelled'], true)) {
            $pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?')->execute([$status, $invId]);
            audit('invoice_updated', 'invoice', $invId, 'Invoice marked ' . $status);
            flash('success', 'Invoice updated.');
        }
        redirect(app_url('admin/invoices.php'));
    }
}

$where = ['1=1'];
$params = [];
if (in_array($st, ['draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled'], true)) {
    $where[] = 'i.status = ?'; $params[] = $st;
}
if ($q !== '') {
    $where[] = '(i.invoice_no LIKE ? OR u.full_name LIKE ? OR u.company LIKE ? OR p.title LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) c FROM invoices i JOIN projects p ON p.id = i.project_id JOIN users u ON u.id = i.client_id WHERE $whereSql");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT i.*, u.full_name AS client_name, u.company, p.title AS project_title, p.ref_no AS project_ref, p.id AS project_id
        FROM invoices i
        JOIN projects p ON p.id = i.project_id
        JOIN users u ON u.id = i.client_id
        WHERE $whereSql
        ORDER BY i.created_at DESC
        LIMIT $perPage OFFSET {$pg['offset']}";
$stm = $pdo->prepare($sql); $stm->execute($params);
$invoices = $stm->fetchAll();

$pendingPays = $pdo->query("SELECT pay.*, i.invoice_no, u.full_name AS client_name FROM payments pay JOIN invoices i ON i.id = pay.invoice_id JOIN projects p ON p.id = i.project_id JOIN users u ON u.id = i.client_id WHERE pay.status = 'pending' ORDER BY pay.created_at ASC")->fetchAll();

$base = 'invoices.php?status=' . urlencode($st) . '&q=' . urlencode($q);
$currency = settings('currency');

dashboard_head(['title' => 'Invoices', 'active' => 'invoices', 'crumb' => 'Invoices']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Billing</span>
    <h2>Invoices &amp; payments</h2>
    <p class="sub">Track issued invoices, record payments and confirm client payments.</p>
  </div>
</div>

<?php render_alerts(); ?>

<?php if ($pendingPays): ?>
  <section class="panel">
    <div class="panel-head"><h3><?= icon('bell') ?> Pending payment confirmations</h3><span class="muted small"><?= count($pendingPays) ?> awaiting action</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Invoice</th><th>Client</th><th>Method</th><th>Reference</th><th>Amount</th><th>Submitted</th><th>Decide</th></tr></thead>
        <tbody>
          <?php foreach ($pendingPays as $pp): ?>
            <tr>
              <td class="small"><?= e($pp['invoice_no']) ?></td>
              <td class="small"><?= e($pp['client_name']) ?></td>
              <td class="small"><?= e(ucwords(str_replace('_', ' ', $pp['method']))) ?></td>
              <td class="small"><?= e($pp['reference'] ?? '—') ?></td>
              <td><b><?= money($pp['amount'], $currency) ?></b></td>
              <td class="small"><?= time_ago($pp['created_at']) ?></td>
              <td style="min-width:160px">
                <div style="display:flex;gap:6px">
                  <form method="post" data-confirm="Confirm this payment?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="decide_payment">
                    <input type="hidden" name="payment_id" value="<?= (int)$pp['id'] ?>">
                    <input type="hidden" name="decision" value="confirm">
                    <button class="btn btn-primary btn-sm" type="submit">Confirm</button>
                  </form>
                  <form method="post" data-confirm="Reject this payment?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="decide_payment">
                    <input type="hidden" name="payment_id" value="<?= (int)$pp['id'] ?>">
                    <input type="hidden" name="decision" value="reject">
                    <button class="btn btn-ghost btn-sm" type="submit">Reject</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

<form class="toolbar" method="get" action="invoices.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:360px">
    <label class="visually-hidden" for="q">Search invoices</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search invoice, client, project…" value="<?= e($q) ?>">
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="status">Status</label>
    <select class="select" id="status" name="status">
      <option value="">All statuses</option>
      <?php foreach (['draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled'] as $s): ?><option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $s))) ?></option><?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="invoices.php">Reset</a>
</form>

<section class="panel">
  <?php if ($invoices): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Invoice</th><th>Project / Client</th><th>Total</th><th>Paid</th><th>Outstanding</th><th>Due</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($invoices as $i): $outstanding = (float)$i['total'] - (float)$i['amount_paid']; ?>
            <tr>
              <td><a class="row-title" href="<?= app_url('admin/project.php?id=' . (int)$i['project_id'] . '&tab=invoice') ?>"><?= e($i['invoice_no']) ?></a><div class="row-sub"><?= fmt_date($i['created_at']) ?></div></td>
              <td><span class="row-title"><?= e($i['client_name']) ?></span><div class="row-sub"><?= e(truncate($i['project_title'], 34)) ?></div></td>
              <td><b><?= money($i['total'], $currency) ?></b></td>
              <td class="small"><?= money($i['amount_paid'], $currency) ?></td>
              <td class="small"><?= money($outstanding, $currency) ?></td>
              <td class="small"><?= $i['due_date'] ? fmt_date($i['due_date']) : '—' ?></td>
              <td><?= status_badge($i['status']) ?></td>
              <td style="min-width:170px">
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                  <button class="btn btn-ghost btn-sm" type="button" data-modal-open="pay-modal-<?= (int)$i['id'] ?>"><?= icon('money') ?> Pay</button>
                  <?php if ($i['status'] !== 'cancelled'): ?>
                    <form method="post" data-confirm="Cancel this invoice?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="inv_status">
                      <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
                      <input type="hidden" name="status" value="cancelled">
                      <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <div class="modal-backdrop" id="pay-modal-<?= (int)$i['id'] ?>">
              <div class="modal">
                <div class="modal-head"><h3>Record payment — <?= e($i['invoice_no']) ?></h3><button type="button" class="modal-close" data-modal-close="pay-modal-<?= (int)$i['id'] ?>" aria-label="Close"><?= icon('close') ?></button></div>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="record_payment">
                  <input type="hidden" name="invoice_id" value="<?= (int)$i['id'] ?>">
                  <div class="form-row">
                    <div class="field"><label for="amount_<?= (int)$i['id'] ?>">Amount (<?= e($currency) ?>) *</label><input class="input" id="amount_<?= (int)$i['id'] ?>" name="amount" type="number" min="1" step="100" required value="<?= e((string)($outstanding > 0 ? $outstanding : $i['total'])) ?>"></div>
                    <div class="field"><label for="method_<?= (int)$i['id'] ?>">Method</label>
                      <select class="select" id="method_<?= (int)$i['id'] ?>" name="method">
                        <?php foreach (['mtn_momo' => 'MTN MoMo', 'airtel_money' => 'Airtel Money', 'bank' => 'Bank transfer', 'cash' => 'Cash', 'other' => 'Other'] as $k => $v): ?><option value="<?= $k ?>" <?= $k === 'other' ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="field"><label for="reference_<?= (int)$i['id'] ?>">Reference</label><input class="input" id="reference_<?= (int)$i['id'] ?>" name="reference" maxlength="120" placeholder="Transaction ID or note"></div>
                  <div class="field"><label for="pstatus_<?= (int)$i['id'] ?>">Status</label>
                    <select class="select" id="pstatus_<?= (int)$i['id'] ?>" name="payment_status">
                      <option value="confirmed">Confirmed (update invoice)</option>
                      <option value="pending">Pending confirmation</option>
                    </select>
                  </div>
                  <div class="modal-foot"><button type="button" class="btn btn-ghost" data-modal-close="pay-modal-<?= (int)$i['id'] ?>">Cancel</button><button class="btn btn-primary" type="submit"><?= icon('check') ?> Save payment</button></div>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php pagination_links($pg['totalPages'], $pg['page'], $base); ?>
  <?php else: ?>
    <div class="empty"><?= icon('money') ?><h4>No invoices found</h4><p><?= ($q || $st) ? 'No invoices match your filters.' : 'Create invoices from inside a project to get started.' ?></p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>