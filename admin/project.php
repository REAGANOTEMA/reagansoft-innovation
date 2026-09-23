<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare("SELECT p.*, c.full_name AS client_name, c.company, c.email, c.phone, c.address, s.name AS service, a.full_name AS assigned_name
                     FROM projects p
                     JOIN users c ON c.id = p.client_id
                     LEFT JOIN services s ON s.id = p.service_id
                     LEFT JOIN users a ON a.id = p.assigned_to
                     WHERE p.id = ?");
$st->execute([$id]);
$project = $st->fetch();

if (!$project) {
    http_response_code(404);
    exit('Project not found.');
}

$tab = $_GET['tab'] ?? 'overview';
if (!in_array($tab, ['overview', 'tasks', 'files', 'messages', 'quotation', 'invoice', 'activity'], true)) {
    redirect(app_url('admin/project.php?id=' . $id));
}

$me = current_user();
$isAdmin = user_role() === 'admin';
$return = 'admin/project.php?id=' . $id;

/* ---------- POST actions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $errors = [];

    if ($action === 'update_project') {
        $status   = strtoupper(trim($_POST['status'] ?? ''));
        $priority = $_POST['priority'] ?? 'normal';
        $progress = max(0, min(100, (int)($_POST['progress'] ?? 0)));
        $deadline = trim($_POST['deadline'] ?? '');
        $budget   = trim($_POST['budget'] ?? '');
        $assigned = (int)($_POST['assigned_to'] ?? 0);
        $deadline = ($deadline !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) ? $deadline : null;
        $budgetZ  = ($budget !== '' && (float)$budget > 0) ? (float)$budget : null;

        if (!in_array($status, project_statuses(), true)) {
            set_errors(['Invalid status.']);
            redirect(app_url('admin/projects.php'));
        }
        if (!array_key_exists($priority, project_priority_labels())) $priority = 'normal';
        if ($assigned > 0) {
            $u = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role IN ('admin','staff')");
            $u->execute([$assigned]);
            if (!$u->fetch()) $assigned = null;
        } else { $assigned = null; }

        $oldStatus = $project['status'];
        $pdo->prepare('UPDATE projects SET status = ?, priority = ?, progress = ?, deadline = ?, budget = ?, assigned_to = ? WHERE id = ?')
            ->execute([$status, $priority, $progress, $deadline, $budgetZ, $assigned, $id]);

        if ($status === 'COMPLETED' && $oldStatus !== 'COMPLETED') {
            $pdo->prepare("UPDATE projects SET completed_at = NOW() WHERE id = ?")->execute([$id]);
        }
        if ($status === 'IN_PROGRESS' && !$project['started_at']) {
            $pdo->prepare("UPDATE projects SET started_at = NOW() WHERE id = ?")->execute([$id]);
        }

        if ($status !== $oldStatus) {
            audit('status_changed', 'project', $id, $oldStatus . ' → ' . $status);
            notify((int)$project['client_id'], 'Project status changed', 'Your project ' . $project['ref_no'] . ' is now ' . $status . '.', 'project', $id);
            notify_staff('Project status changed', $project['ref_no'] . ' moved to ' . $status . '.', 'project', $id);
        }
        audit('project_updated', 'project', $id, 'Project details updated');
        flash('success', 'Project updated.');
        redirect(app_url('admin/project.php?id=' . $id . '&tab=overview'));
    }

    if ($action === 'create_task') {
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $assigned = (int)($_POST['assigned_to'] ?? 0);
        $status  = $_POST['status'] ?? 'TODO';
        $priority = $_POST['priority'] ?? 'medium';
        $progress = max(0, min(100, (int)($_POST['progress'] ?? 0)));
        $start = trim($_POST['start_date'] ?? '');
        $due   = trim($_POST['due_date'] ?? '');
        if (!in_array($status, ['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'], true)) $status = 'TODO';
        if (!in_array($priority, ['low', 'medium', 'high'], true)) $priority = 'medium';
        $start = ($start && preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) ? $start : null;
        $due   = ($due && preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) ? $due : null;
        if ($title === '') { set_errors(['Task title is required.']); redirect(app_url($return . '&tab=tasks')); }
        $pdo->prepare(
            'INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by) VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute([$id, $assigned ?: null, $title, $desc ?: null, $status, $progress, $priority, $start, $due, (int)$me['id']]);
        audit('task_created', 'project_task', (int)$pdo->lastInsertId(), 'Task "' . $title . '" added');
        notify((int)$project['client_id'], 'New task added', 'A task was added to ' . $project['ref_no'] . ': ' . $title, 'task', $id);
        flash('success', 'Task created.');
        redirect(app_url($return . '&tab=tasks'));
    }

    if ($action === 'update_task') {
        $tid = (int)($_POST['task_id'] ?? 0);
        $ts = $pdo->prepare('SELECT * FROM project_tasks WHERE id = ? AND project_id = ?');
        $ts->execute([$tid, $id]);
        if ($t = $ts->fetch()) {
            $title = trim($_POST['title'] ?? $t['title']);
            $desc  = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? $t['status'];
            $priority = $_POST['priority'] ?? $t['priority'];
            $progress = max(0, min(100, (int)($_POST['progress'] ?? $t['progress'])));
            if (!in_array($status, ['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'], true)) $status = $t['status'];
            if (!in_array($priority, ['low', 'medium', 'high'], true)) $priority = $t['priority'];
            $assignee = (int)($_POST['assigned_to'] ?? 0) ?: null;
            $due = trim($_POST['due_date'] ?? '');
            $due = ($due && preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) ? $due : null;
            $pdo->prepare('UPDATE project_tasks SET title=?, description=?, status=?, progress=?, priority=?, assigned_to=?, due_date=? WHERE id=?')
                ->execute([$title, $desc ?: null, $status, $progress, $priority, $assignee, $due, $tid]);
            audit('task_updated', 'project_task', $tid, 'Task "' . $title . '" → ' . $status . ' ' . $progress . '%');
            if ((string)$t['status'] !== $status || (int)$t['progress'] !== $progress) {
                notify((int)$project['client_id'], 'Task update', 'Task "' . $title . '" is now ' . $status . ' (' . $progress . '%).', 'task', $id);
            }
            flash('success', 'Task updated.');
        }
        redirect(app_url($return . '&tab=tasks'));
    }

    if ($action === 'delete_task') {
        $tid = (int)($_POST['task_id'] ?? 0);
        $pdo->prepare('DELETE FROM project_tasks WHERE id = ? AND project_id = ?')->execute([$tid, $id]);
        audit('task_deleted', 'project_task', $tid, 'Task #' . $tid . ' removed from project ' . $project['ref_no']);
        flash('success', 'Task deleted.');
        redirect(app_url($return . '&tab=tasks'));
    }

    if ($action === 'send_message') {
        $message = trim($_POST['message'] ?? '');
        $internal = !empty($_POST['is_internal']) ? 1 : 0;
        if ($message !== '' && mb_strlen($message) <= 5000) {
            $role = $me['role'] === 'admin' ? 'admin' : 'staff';
            $pdo->prepare("INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal) VALUES (?,?,?,?,?)")
                ->execute([$id, (int)$me['id'], $role, $message, $internal]);
            audit($internal ? 'internal_note' : 'message_sent', 'project', $id, ($internal ? 'Internal note' : 'Message to client'));
            if (!$internal) {
                notify((int)$project['client_id'], 'New message on ' . $project['ref_no'], truncate($message, 120), 'project', $id);
            }
            flash('success', $internal ? 'Internal note saved (hidden from client).' : 'Message sent to client.');
        } else {
            set_errors(['Message is empty or too long.']);
        }
        redirect(app_url($return . '&tab=messages'));
    }

    if ($action === 'upload_file') {
        if (!empty($_FILES['project_file']['name'])) {
            $res = upload_file($_FILES['project_file']);
            if ($res['ok']) {
                $pdo->prepare('INSERT INTO project_files (project_id, uploader_id, original_name, stored_name, file_path, mime_type, size_bytes) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$id, (int)$me['id'], $res['name'], $res['stored'], $res['path'], $res['mime'], $res['size']]);
                audit('file_uploaded', 'project_files', (int)$pdo->lastInsertId(), 'Uploaded ' . $res['name']);
                notify((int)$project['client_id'], 'New file uploaded', 'A new file was added to ' . $project['ref_no'] . ': ' . $res['name'], 'project', $id);
                flash('success', 'File uploaded.');
            } else {
                set_errors([$res['error']]);
            }
        } else {
            set_errors(['No file selected.']);
        }
        redirect(app_url($return . '&tab=files'));
    }

    if ($action === 'create_quotation') {
        $rawItems = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
        $items = [];
        foreach ($rawItems as $it) {
            if (!is_array($it)) { continue; }
            $description = trim((string)($it['description'] ?? ''));
            if ($description === '') { continue; }
            $items[] = ['description' => $description, 'qty' => (float)($it['qty'] ?? 0), 'price' => (float)($it['price'] ?? 0)];
        }
        $errors = [];
        if (!is_array($items) || count($items) === 0) $errors[] = 'Add at least one quotation item.';
        foreach ($items as $it) {
            if (empty($it['description']) || (float)($it['qty'] ?? 0) <= 0 || (float)($it['price'] ?? 0) < 0) {
                $errors[] = 'Every item needs a description, quantity and unit price.';
                break;
            }
        }
        if ($errors) { set_errors($errors); redirect(app_url($return . '&tab=quotation')); }

        $subtotal = 0;
        foreach ($items as $it) { $subtotal += (float)$it['qty'] * (float)$it['price']; }
        $discount = max(0, (float)($_POST['discount'] ?? 0));
        $taxPct   = max(0, min(100, (float)($_POST['tax_percent'] ?? (float)settings('tax_percent', '0'))));
        $total    = ($subtotal - $discount) * (1 + $taxPct / 100);
        $expiry   = trim($_POST['expiry_date'] ?? '');
        $expiry   = ($expiry && preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry)) ? $expiry : date('Y-m-d', strtotime('+30 days'));

        try {
            $pdo->beginTransaction();
            $no = next_doc_no($pdo, 'quotations', 'quotation_no', 'QT-' . date('Y') . '-');
            $ins = $pdo->prepare('INSERT INTO quotations (quotation_no, project_id, client_id, issued_on, expiry_date, subtotal, discount, tax_percent, total, status, notes, terms) VALUES (?,?,?,CURDATE(),?,?,?,?,?,?,?,?)');
            $ins->execute([$no, $id, (int)$project['client_id'], $expiry, $subtotal, $discount, $taxPct, $total, 'sent', trim($_POST['notes'] ?? '') ?: null, trim($_POST['terms'] ?? '') ?: null]);
            $qid = (int)$pdo->lastInsertId();
            $qins = $pdo->prepare('INSERT INTO quotation_items (quotation_id, description, quantity, unit_price) VALUES (?,?,?,?)');
            foreach ($items as $it) { $qins->execute([$qid, trim($it['description']), (float)$it['qty'], (float)$it['price']]); }
            $pdo->prepare("UPDATE projects SET status = 'QUOTATION' WHERE id = ? AND status IN ('NEW','REVIEWING')")->execute([$id]);
            $pdo->commit();
            audit('quotation_created', 'quotation', $qid, 'Created ' . $no . ' for ' . money($total, settings('currency')));
            notify((int)$project['client_id'], 'Your quotation is ready', 'Quotation ' . $no . ' (' . money($total, settings('currency')) . ') is waiting for your approval.', 'quotation', $id);
            flash('success', 'Quotation ' . $no . ' created and sent to the client.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            log_error('create_quotation: ' . $e->getMessage());
            set_errors(['Could not create the quotation. Please check the database connection.']);
        }
        redirect(app_url($return . '&tab=quotation'));
    }

    if ($action === 'quote_decision') {
        $qid = (int)($_POST['quotation_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';
        if (in_array($decision, ['cancelled', 'accepted', 'rejected'], true)) {
            $pdo->prepare('UPDATE quotations SET status = ? WHERE id = ? AND project_id = ?')->execute([$decision, $qid, $id]);
            audit('quotation_updated', 'quotation', $qid, 'Quotation marked ' . $decision);
            flash('success', 'Quotation updated.');
        }
        redirect(app_url($return . '&tab=quotation'));
    }

    if ($action === 'create_invoice') {
        $rawItems = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
        $items = [];
        foreach ($rawItems as $it) {
            if (!is_array($it)) { continue; }
            $description = trim((string)($it['description'] ?? ''));
            if ($description === '') { continue; }
            $items[] = ['description' => $description, 'qty' => (float)($it['qty'] ?? 0), 'price' => (float)($it['price'] ?? 0)];
        }
        $errors = [];
        if (!is_array($items) || count($items) === 0) $errors[] = 'Add at least one invoice item.';
        foreach ($items as $it) {
            if (empty($it['description']) || (float)($it['qty'] ?? 0) <= 0 || (float)($it['price'] ?? 0) < 0) {
                $errors[] = 'Every item needs a description, quantity and unit price.';
                break;
            }
        }
        if ($errors) { set_errors($errors); redirect(app_url($return . '&tab=invoice')); }

        $subtotal = 0;
        foreach ($items as $it) { $subtotal += (float)$it['qty'] * (float)$it['price']; }
        $discount = max(0, (float)($_POST['discount'] ?? 0));
        $taxPct   = max(0, min(100, (float)($_POST['tax_percent'] ?? (float)settings('tax_percent', '0'))));
        $total    = ($subtotal - $discount) * (1 + $taxPct / 100);
        $days     = max(1, (int)($_POST['due_days'] ?? (int)settings('invoices_due_days', '14')));
        $status   = $_POST['status'] ?? 'sent';
        if (!in_array($status, ['draft', 'sent'], true)) $status = 'sent';

        try {
            $pdo->beginTransaction();
            $no = next_doc_no($pdo, 'invoices', 'invoice_no', 'INV-' . date('Y') . '-');
            $ins = $pdo->prepare('INSERT INTO invoices (invoice_no, project_id, client_id, due_date, subtotal, discount, tax_percent, total, status, notes) VALUES (?,?,?,DATE_ADD(CURDATE(), INTERVAL ? DAY),?,?,?,?,?,?)');
            $ins->execute([$no, $id, (int)$project['client_id'], $days, $subtotal, $discount, $taxPct, $total, $status, trim($_POST['notes'] ?? '') ?: null]);
            $invId = (int)$pdo->lastInsertId();
            $iins = $pdo->prepare('INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) VALUES (?,?,?,?)');
            foreach ($items as $it) { $iins->execute([$invId, trim($it['description']), (float)$it['qty'], (float)$it['price']]); }
            $pdo->commit();
            audit('invoice_created', 'invoice', $invId, 'Created ' . $no . ' for ' . money($total, settings('currency')));
            if ($status === 'sent') {
                notify((int)$project['client_id'], 'Your invoice is ready', 'Invoice ' . $no . ' (' . money($total, settings('currency')) . ') was issued for ' . $project['ref_no'] . '.', 'invoice', $id);
            }
            flash('success', 'Invoice ' . $no . ' created.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            log_error('create_invoice: ' . $e->getMessage());
            set_errors(['Could not create the invoice.']);
        }
        redirect(app_url($return . '&tab=invoice'));
    }

    if ($action === 'record_payment') {
        $invId = (int)($_POST['invoice_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? 'other';
        $reference = trim($_POST['reference'] ?? '');
        $decision = $_POST['payment_status'] ?? 'confirmed';
        if (!in_array($method, ['mtn_momo', 'airtel_money', 'bank', 'cash', 'other'], true)) $method = 'other';
        $ist = $pdo->prepare('SELECT * FROM invoices WHERE id = ? AND project_id = ?');
        $ist->execute([$invId, $id]);
        $inv = $ist->fetch();
        if ($inv && $amount > 0) {
            if ($decision === 'pending') {
                $pdo->prepare("INSERT INTO payments (invoice_id, amount, method, reference, status, received_by, notes) VALUES (?,?,?,?, 'pending', ?, 'Recorded by staff awaiting confirmation')")
                    ->execute([$invId, $amount, $method, $reference ?: null, (int)$me['id']]);
                audit('payment_recorded', 'invoice', $invId, 'Pending payment of ' . $amount);
                flash('success', 'Pending payment recorded for ' . $inv['invoice_no'] . '. Confirmation will finalise it.');
            } else {
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO payments (invoice_id, amount, method, reference, status, received_by, notes, confirmed_at) VALUES (?,?,?,?, 'confirmed', ?, ?, NOW())")
                    ->execute([$invId, $amount, $method, $reference ?: null, (int)$me['id'], 'Confirmed by ' . $me['full_name']]);
                $newPaid = (float)$inv['amount_paid'] + $amount;
                $newStatus = $newPaid >= (float)$inv['total'] ? 'paid' : 'partially_paid';
                $pdo->prepare('UPDATE invoices SET amount_paid = ?, status = ? WHERE id = ?')->execute([$newPaid, $newStatus, $invId]);
                $pdo->commit();
                audit('payment_confirmed', 'invoice', $invId, 'Payment of ' . $amount . ' confirmed on ' . $inv['invoice_no']);
                notify((int)$project['client_id'], 'Payment confirmed', 'Payment of ' . money($amount, settings('currency')) . ' confirmed on ' . $inv['invoice_no'] . '.', 'invoice', $id);
                flash('success', 'Payment confirmed on ' . $inv['invoice_no'] . '. Invoice is now ' . $newStatus . '.');
            }
        } else {
            set_errors(['Payment could not be recorded.']);
        }
        redirect(app_url($return . '&tab=invoice'));
    }

    redirect(app_url($return . '&tab=' . $tab));
}

/* ---------- data ---------- */
$staff = $pdo->query("SELECT id, full_name FROM users WHERE role IN ('admin','staff') AND active = 1 ORDER BY full_name")->fetchAll();
$tasks = $pdo->prepare('SELECT t.*, u.full_name AS assignee FROM project_tasks t LEFT JOIN users u ON u.id = t.assigned_to WHERE t.project_id = ? ORDER BY t.status, t.due_date IS NULL, t.due_date, t.id');
$tasks->execute([$id]); $tasks = $tasks->fetchAll();

$files = $pdo->prepare('SELECT f.*, u.full_name AS uploader FROM project_files f JOIN users u ON u.id = f.uploader_id WHERE f.project_id = ? ORDER BY f.created_at DESC');
$files->execute([$id]); $files = $files->fetchAll();

$messages = $pdo->prepare('SELECT m.*, u.full_name AS sender_name FROM project_messages m JOIN users u ON u.id = m.sender_id WHERE m.project_id = ? ORDER BY m.created_at ASC');
$messages->execute([$id]); $messages = $messages->fetchAll();

$quotations = $pdo->prepare('SELECT * FROM quotations WHERE project_id = ? ORDER BY created_at DESC');
$quotations->execute([$id]); $quotations = $quotations->fetchAll();

$invoices = $pdo->prepare('SELECT * FROM invoices WHERE project_id = ? ORDER BY created_at DESC');
$invoices->execute([$id]); $invoices = $invoices->fetchAll();

$activity = $pdo->prepare("SELECT l.*, u.full_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id WHERE l.entity_type = 'project' AND l.entity_id = ? ORDER BY l.created_at DESC LIMIT 30");
$activity->execute([$id]); $activity = $activity->fetchAll();

$tabQ = urlencode($id);

dashboard_head(['title' => $project['title'], 'active' => 'projects', 'crumb' => 'Project · ' . $project['ref_no']]);
?>
<div class="dash-head">
  <div>
    <a class="small" href="<?= app_url('admin/projects.php') ?>"><?= icon('arrow-l') ?> Back to projects</a>
    <h2><?= e($project['title']) ?></h2>
    <p class="sub"><?= e($project['ref_no']) ?> · <?= e($project['client_name']) ?><?= $project['company'] ? ' — ' . e($project['company']) : '' ?> · <?= e($project['service'] ?? 'General') ?></p>
  </div>
  <div class="actions"><?= status_badge($project['status']) ?> <?= priority_badge($project['priority']) ?></div>
</div>

<?php render_alerts(); ?>

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
      <div class="panel-head"><h3>Project details</h3><a class="btn btn-ghost btn-sm" href="<?= app_url('client/project.php?id=' . $id) ?>" target="_blank" rel="noopener">Client view</a></div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_project">
        <div class="form-row">
          <div class="field"><label for="status">Status</label>
            <select class="select" id="status" name="status">
              <?php foreach (project_statuses() as $s): ?><option value="<?= e($s) ?>" <?= $project['status'] === $s ? 'selected' : '' ?>><?= e(ucwords(strtolower($s))) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label for="priority">Priority</label>
            <select class="select" id="priority" name="priority">
              <?php foreach (project_priority_labels() as $k => $v): ?><option value="<?= e($k) ?>" <?= $project['priority'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="field"><label for="progress">Progress (%)</label><input class="input" id="progress" type="number" name="progress" min="0" max="100" value="<?= (int)$project['progress'] ?>"></div>
          <div class="field"><label for="deadline">Deadline</label><input class="input" id="deadline" type="date" name="deadline" value="<?= e($project['deadline'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="budget">Budget (<?= e(settings('currency')) ?>)</label><input class="input" id="budget" type="number" min="0" step="10000" name="budget" value="<?= e($project['budget'] !== null ? (string)$project['budget'] : '') ?>"></div>
          <div class="field"><label for="assigned_to">Assigned team member</label>
            <select class="select" id="assigned_to" name="assigned_to">
              <option value="0">Unassigned</option>
              <?php foreach ($staff as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (int)$project['assigned_to'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['full_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <button class="btn btn-primary" type="submit"><?= icon('check') ?> Save project</button>
      </form>
    </section>
    <section class="panel">
      <div class="panel-head"><h3>Client</h3></div>
      <div class="kv"><span>Name</span><span><?= e($project['client_name']) ?></span></div>
      <div class="kv"><span>Company</span><span><?= e($project['company'] ?? '—') ?></span></div>
      <div class="kv"><span>Email</span><span><?= e($project['email']) ?></span></div>
      <div class="kv"><span>Phone</span><span><?= e($project['phone']) ?></span></div>
      <div class="kv"><span>Description</span><span style="font-weight:500"><?= e(truncate($project['description'], 140)) ?></span></div>
      <?php if ($project['requirements']): ?><div class="kv"><span>Requirements</span><span style="font-weight:500"><?= e(truncate($project['requirements'], 140)) ?></span></div><?php endif; ?>
      <div class="kv"><span>Submitted</span><span><?= fmt_date($project['submitted_at'], 'd M Y H:i') ?></span></div>
      <div class="kv"><span>Started</span><span><?= fmt_date($project['started_at'], 'd M Y H:i') ?></span></div>
      <div class="kv"><span>Completed</span><span><?= fmt_date($project['completed_at'], 'd M Y H:i') ?></span></div>
    </section>
  </div>
  <section class="panel">
    <div class="panel-head"><h3>Timeline &amp; progress</h3><strong><?= (int)$project['progress'] ?>% overall</strong></div>
    <div class="progress lg" style="margin-bottom:24px"><span style="width:<?= max(0, min(100, (int)$project['progress'])) ?>%"></span></div>
    <div class="grid-2">
      <div><?php render_milestones($project['status']); ?></div>
      <div>
        <h4>Next steps</h4>
        <div class="kv"><span>Status</span><span><?= status_badge($project['status']) ?></span></div>
        <div class="kv"><span>Open tasks</span><span><?= count(array_filter($tasks, fn($t) => $t['status'] !== 'COMPLETED')) ?> of <?= count($tasks) ?></span></div>
        <div class="kv"><span>Files shared</span><span><?= count($files) ?></span></div>
        <div class="kv"><span>Quotations</span><span><?= count($quotations) ?></span></div>
        <div class="kv"><span>Invoices</span><span><?= count($invoices) ?></span></div>
      </div>
    </div>
  </section>
<?php elseif ($tab === 'tasks'): ?>
  <section class="panel">
    <div class="panel-head">
      <h3>Project tasks</h3>
      <button class="btn btn-primary btn-sm" type="button" data-modal-open="task-modal"><?= icon('plus') ?> New task</button>
    </div>
    <?php if ($tasks): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Task</th><th>Assignee</th><th>Status</th><th>Progress</th><th>Priority</th><th>Due</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($tasks as $t): ?>
              <tr>
                <td><div class="row-title"><?= e($t['title']) ?></div><?php if ($t['description']): ?><div class="row-sub"><?= e(truncate($t['description'], 90)) ?></div><?php endif; ?></td>
                <td class="small"><?= e($t['assignee'] ?? 'Unassigned') ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td style="min-width:120px"><div class="progress"><span style="width:<?= max(0, min(100, (int)$t['progress'])) ?>%"></span></div><small class="muted"><?= (int)$t['progress'] ?>%</small></td>
                <td><?= priority_badge($t['priority']) ?></td>
                <td class="muted small"><?= $t['due_date'] ? fmt_date($t['due_date']) : '—' ?></td>
                <td class="actions-cell">
                  <button class="btn btn-ghost btn-sm" type="button" data-modal-open="task-edit-<?= (int)$t['id'] ?>">Edit</button>
                  <form method="post" data-confirm="Delete this task? This cannot be undone.">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_task">
                    <input type="hidden" name="task_id" value="<?= (int)$t['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('list') ?><h4>No tasks yet</h4><p>Create tasks to assign work, track progress and keep the client informed.</p></div>
    <?php endif; ?>
  </section>

  <div class="modal-backdrop" id="task-modal">
    <div class="modal">
      <h3>New task</h3>
      <p class="muted small mb-2">Tasks are visible to the client with their status and progress.</p>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_task">
        <div class="field"><label for="tk_title">Task title *</label><input class="input" id="tk_title" name="title" required maxlength="190"></div>
        <div class="field"><label for="tk_desc">Description</label><textarea class="textarea" id="tk_desc" name="description" style="min-height:70px"></textarea></div>
        <div class="form-row">
          <div class="field"><label for="tk_assigned">Assigned to</label>
            <select class="select" id="tk_assigned" name="assigned_to">
              <option value="0">Unassigned</option>
              <?php foreach ($staff as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['full_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label for="tk_status">Status</label>
            <select class="select" id="tk_status" name="status">
              <?php foreach (['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'] as $ts): ?><option><?= e($ts) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="field"><label for="tk_priority">Priority</label>
            <select class="select" id="tk_priority" name="priority">
              <?php foreach (['low', 'medium', 'high'] as $tp): ?><option><?= e(ucfirst($tp)) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label for="tk_progress">Progress %</label><input class="input" id="tk_progress" type="number" name="progress" min="0" max="100" value="0"></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="tk_start">Start date</label><input class="input" id="tk_start" type="date" name="start_date"></div>
          <div class="field"><label for="tk_due">Due date</label><input class="input" id="tk_due" type="date" name="due_date"></div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-ghost" type="button" data-modal-close="task-modal">Cancel</button>
          <button class="btn btn-primary" type="submit"><?= icon('plus') ?> Create task</button>
        </div>
      </form>
    </div>
  </div>

  <?php foreach ($tasks as $t): ?>
    <div class="modal-backdrop" id="task-edit-<?= (int)$t['id'] ?>">
      <div class="modal">
        <h3>Edit task</h3>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_task">
          <input type="hidden" name="task_id" value="<?= (int)$t['id'] ?>">
          <div class="field"><label for="te_title_<?= (int)$t['id'] ?>">Task title *</label><input class="input" id="te_title_<?= (int)$t['id'] ?>" name="title" required maxlength="190" value="<?= e($t['title']) ?>"></div>
          <div class="field"><label for="te_desc_<?= (int)$t['id'] ?>">Description</label><textarea class="textarea" id="te_desc_<?= (int)$t['id'] ?>" name="description" style="min-height:70px"><?= e($t['description'] ?? '') ?></textarea></div>
          <div class="form-row">
            <div class="field"><label for="te_assigned_<?= (int)$t['id'] ?>">Assigned to</label>
              <select class="select" id="te_assigned_<?= (int)$t['id'] ?>" name="assigned_to">
                <option value="0">Unassigned</option>
                <?php foreach ($staff as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (int)$t['assigned_to'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['full_name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="field"><label for="te_status_<?= (int)$t['id'] ?>">Status</label>
              <select class="select" id="te_status_<?= (int)$t['id'] ?>" name="status">
                <?php foreach (['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'] as $ts): ?><option <?= $t['status'] === $ts ? 'selected' : '' ?>><?= e($ts) ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="field"><label for="te_priority_<?= (int)$t['id'] ?>">Priority</label>
              <select class="select" id="te_priority_<?= (int)$t['id'] ?>" name="priority">
                <?php foreach (['low', 'medium', 'high'] as $tp): ?><option <?= $t['priority'] === $tp ? 'selected' : '' ?>><?= e(ucfirst($tp)) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="field"><label for="te_progress_<?= (int)$t['id'] ?>">Progress %</label><input class="input" id="te_progress_<?= (int)$t['id'] ?>" type="number" name="progress" min="0" max="100" value="<?= (int)$t['progress'] ?>"></div>
          </div>
          <div class="field"><label for="te_due_<?= (int)$t['id'] ?>">Due date</label><input class="input" id="te_due_<?= (int)$t['id'] ?>" type="date" name="due_date" value="<?= e($t['due_date'] ?? '') ?>"></div>
          <div class="modal-actions">
            <button class="btn btn-ghost" type="button" data-modal-close="task-edit-<?= (int)$t['id'] ?>">Cancel</button>
            <button class="btn btn-primary" type="submit"><?= icon('check') ?> Save task</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php elseif ($tab === 'files'): ?>
  <div class="grid-2">
    <section class="panel">
      <div class="panel-head"><h3>Project files</h3></div>
      <?php if ($files): ?>
        <?php foreach ($files as $f): ?>
          <div class="file-card">
            <span class="fc-icon"><?= icon('file') ?></span>
            <div style="flex-grow:1;min-width:0">
              <b><?= e($f['original_name']) ?></b>
              <small><?= pretty_size((int)$f['size_bytes']) ?> · <?= e($f['uploader']) ?> · <?= fmt_date($f['created_at']) ?></small>
            </div>
            <a class="btn btn-ghost btn-sm" href="<?= app_url('download.php?id=' . (int)$f['id']) ?>"><?= icon('download') ?></a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty"><?= icon('file') ?><h4>No files yet</h4><p>Upload supporting files or deliverables for this project.</p></div>
      <?php endif; ?>
    </section>
    <section class="panel">
      <div class="panel-head"><h3>Upload a file</h3></div>
      <p class="muted small">Shared files are visible to the client. Keep internal-only documents as internal messages instead.</p>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload_file">
        <div class="field"><label for="pfile">Choose file</label><input class="input" id="pfile" type="file" name="project_file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.png,.jpg,.jpeg,.gif,.webp,.zip"></div>
        <div class="form-note mb-2">Max 10 MB. Allowed: PDF, Office documents, text, CSV, images, ZIP.</div>
        <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Upload file</button>
      </form>
    </section>
  </div>
<?php elseif ($tab === 'messages'): ?>
  <div class="grid-2">
    <section class="panel">
      <div class="panel-head"><h3>Conversation</h3></div>
      <?php if ($messages): ?>
        <div class="chat">
          <?php $myId = (int)$me['id']; foreach ($messages as $m): ?>
            <div class="msg <?= (int)$m['sender_id'] === $myId ? 'out' : '' ?>">
              <div class="bubble">
                <?php if ($m['is_internal']): ?><span class="internal-flag">Internal only</span><?php endif; ?>
                <?= nl2br(e($m['message'])) ?>
              </div>
              <span class="meta"><?= e($m['sender_name']) ?> (<?= e(ucfirst($m['sender_role'])) ?>) · <?= fmt_date($m['created_at'], 'd M Y H:i') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty"><?= icon('chat') ?><h4>No messages yet</h4><p>Messages and internal notes about this project appear here.</p></div>
      <?php endif; ?>
    </section>
    <section class="panel">
      <div class="panel-head"><h3>Send a message</h3></div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="send_message">
        <div class="field"><label for="msg">Message</label><textarea class="textarea" id="msg" name="message" required maxlength="5000" placeholder="Write a reply to the client or an internal note for the team…"></textarea></div>
        <div class="field">
          <label style="display:flex;gap:9px;align-items:center;font-weight:600;cursor:pointer">
            <input type="checkbox" name="is_internal" value="1" style="width:17px;height:17px"> Internal note — hidden from the client
          </label>
        </div>
        <button class="btn btn-primary" type="submit"><?= icon('send') ?> Send</button>
      </form>
    </section>
  </div>
<?php elseif ($tab === 'quotation'): ?>
  <div class="grid-2">
    <section class="panel">
      <div class="panel-head"><h3>Quotations</h3></div>
      <?php if ($quotations): foreach ($quotations as $q):
        $items = $pdo->prepare('SELECT * FROM quotation_items WHERE quotation_id = ?');
        $items->execute([(int)$q['id']]); $items = $items->fetchAll();
      ?>
        <div class="file-card" style="align-items:flex-start">
          <div style="flex-grow:1">
            <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:6px">
              <b><?= e($q['quotation_no']) ?></b><?= status_badge($q['status']) ?>
            </div>
            <small class="muted">Issued <?= fmt_date($q['issued_on']) ?> · expires <?= fmt_date($q['expiry_date']) ?> · Total: <?= money($q['total'], settings('currency')) ?></small>
            <?php if ($q['notes']): ?><p class="small muted mb-0"><?= e(truncate($q['notes'], 120)) ?></p><?php endif; ?>
          </div>
          <div style="display:flex;gap:6px;flex-wrap:wrap">
            <button class="btn btn-ghost btn-sm" type="button" data-modal-open="quote-view-<?= (int)$q['id'] ?>">View</button>
            <?php if (in_array($q['status'], ['sent', 'draft', 'rejected', 'expired'], true)): ?>
              <form method="post" data-confirm="Mark this quotation as cancelled?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="quote_decision">
                <input type="hidden" name="quotation_id" value="<?= (int)$q['id'] ?>">
                <input type="hidden" name="decision" value="cancelled">
                <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-backdrop" id="quote-view-<?= (int)$q['id'] ?>">
          <div class="modal" style="width:min(640px,100%)">
            <div class="panel-head"><h3><?= e($q['quotation_no']) ?></h3><?= status_badge($q['status']) ?></div>
            <div class="meta-row mb-2"><span>Issued: <?= fmt_date($q['issued_on']) ?></span><span>Expires: <?= fmt_date($q['expiry_date']) ?></span></div>
            <div class="doc-summary mb-2">
              <?php foreach ($items as $it): ?>
                <div class="doc-line"><span><?= e($it['description']) ?> × <?= rtrim(rtrim(number_format((float)$it['quantity'], 2), '0'), '.') ?></span><span><?= money((float)$it['unit_price'] * (float)$it['quantity'], settings('currency')) ?></span></div>
              <?php endforeach; ?>
              <div class="doc-line"><span>Subtotal</span><span><?= money($q['subtotal'], settings('currency')) ?></span></div>
              <?php if ((float)$q['discount'] > 0): ?><div class="doc-line"><span>Discount</span><span>- <?= money($q['discount'], settings('currency')) ?></span></div><?php endif; ?>
              <?php if ((float)$q['tax_percent'] > 0): ?><div class="doc-line"><span>Tax</span><span><?= money($q['total'] - ($q['subtotal'] - $q['discount']), settings('currency')) ?></span></div><?php endif; ?>
              <div class="doc-line total"><span>Total</span><span><?= money($q['total'], settings('currency')) ?></span></div>
            </div>
            <?php if ($q['terms']): ?><p class="muted small"><strong>Terms:</strong> <?= nl2br(e($q['terms'])) ?></p><?php endif; ?>
            <div class="modal-actions"><button class="btn btn-ghost" type="button" data-modal-close="quote-view-<?= (int)$q['id'] ?>">Close</button><button class="btn btn-primary no-print" onclick="window.print()">Print</button></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php else: ?>
        <div class="empty"><?= icon('doc') ?><h4>No quotations</h4><p>Create a quotation to send the client an itemised, approved price.</p></div>
      <?php endif; ?>
    </section>
    <section class="panel">
      <div class="panel-head"><h3>Create quotation</h3></div>
      <form method="post" id="quote-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_quotation">
        <div class="line-items">
          <div class="line-item">
            <input class="input" name="items[d][description]" placeholder="Description" required>
            <input class="input" name="items[d][qty]" type="number" min="1" step="1" value="1" placeholder="Qty" required>
            <input class="input" name="items[d][price]" type="number" min="0" step="100" placeholder="Unit price" required>
            <button class="btn btn-danger btn-sm remove-line" type="button" aria-label="Remove line">×</button>
          </div>
        </div>
        <button class="btn btn-ghost btn-sm mb-2" type="button" data-add-item data-target=".line-items"><?= icon('plus') ?> Add line</button>
        <div class="form-row">
          <div class="field"><label for="q_discount">Discount (<?= e(settings('currency')) ?>)</label><input class="input" id="q_discount" type="number" min="0" step="100" name="discount" value="0"></div>
          <div class="field"><label for="q_tax">Tax (%)</label><input class="input" id="q_tax" type="number" min="0" max="100" step="0.5" name="tax_percent" value="<?= e(settings('tax_percent', '0')) ?>"></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="q_expiry">Expiry date</label><input class="input" id="q_expiry" type="date" name="expiry_date" value="<?= e(date('Y-m-d', strtotime('+30 days'))) ?>"></div>
          <div class="field"><label for="q_terms">Terms (optional)</label><input class="input" id="q_terms" name="terms" maxlength="500" placeholder="e.g. 50% deposit, remainder on delivery"></div>
        </div>
        <div class="field"><label for="q_notes">Notes (optional)</label><textarea class="textarea" id="q_notes" name="notes" style="min-height:70px"></textarea></div>
        <button class="btn btn-primary" type="submit"><?= icon('doc') ?> Create &amp; send quotation</button>
      </form>
    </section>
  </div>
<?php elseif ($tab === 'invoice'): ?>
  <div class="grid-2">
    <section class="panel">
      <div class="panel-head"><h3>Invoices</h3></div>
      <?php if ($invoices): foreach ($invoices as $inv):
        $payments = $pdo->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY created_at DESC");
        $payments->execute([(int)$inv['id']]); $payments = $payments->fetchAll();
        $outstanding = max(0, (float)$inv['total'] - (float)$inv['amount_paid']);
      ?>
        <div class="file-card" style="align-items:flex-start">
          <div style="flex-grow:1;min-width:0">
            <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:6px">
              <b><?= e($inv['invoice_no']) ?></b><?= status_badge($inv['status']) ?>
            </div>
            <small class="muted">Total <?= money($inv['total'], settings('currency')) ?> · paid <?= money($inv['amount_paid'], settings('currency')) ?> · due <?= fmt_date($inv['due_date']) ?></small>
            <?php if ($payments): ?>
              <?php foreach ($payments as $pay): ?>
                <div class="meta-row" style="margin-top:6px;font-size:12.5px">
                  <span><?= status_badge($pay['status']) ?></span>
                  <span><?= money($pay['amount'], settings('currency')) ?></span>
                  <span><?= e(str_replace('_', ' ', $pay['method'])) ?></span>
                  <?php if ($pay['reference']): ?><span>Ref: <?= e($pay['reference']) ?></span><?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($outstanding > 0 && !in_array($inv['status'], ['paid', 'cancelled'], true)): ?>
          <form method="post" class="mt-2" style="border-top:1px dashed var(--line);padding-top:12px">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="record_payment">
            <input type="hidden" name="invoice_id" value="<?= (int)$inv['id'] ?>">
            <div class="form-row">
              <div class="field" style="margin-bottom:8px"><label for="pay_amount_<?= (int)$inv['id'] ?>">Payment amount *</label><input class="input" id="pay_amount_<?= (int)$inv['id'] ?>" type="number" name="amount" min="1" step="100" required value="<?= e((string)$outstanding) ?>"></div>
              <div class="field" style="margin-bottom:8px"><label for="pay_method_<?= (int)$inv['id'] ?>">Method</label>
                <select class="select" id="pay_method_<?= (int)$inv['id'] ?>" name="method">
                  <option value="mtn_momo">MTN Mobile Money</option><option value="airtel_money">Airtel Money</option><option value="bank">Bank transfer</option><option value="cash">Cash</option><option value="other">Other</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="field" style="margin-bottom:8px"><label for="pay_ref_<?= (int)$inv['id'] ?>">Reference</label><input class="input" id="pay_ref_<?= (int)$inv['id'] ?>" name="reference" maxlength="120"></div>
              <div class="field" style="margin-bottom:8px"><label for="pay_status_<?= (int)$inv['id'] ?>">Action</label>
                <select class="select" id="pay_status_<?= (int)$inv['id'] ?>" name="payment_status">
                  <option value="confirmed">Confirm payment received</option>
                  <option value="pending">Record as pending</option>
                </select>
              </div>
            </div>
            <button class="btn btn-dark btn-sm" type="submit"><?= icon('money') ?> Record payment</button>
          </form>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php else: ?>
        <div class="empty"><?= icon('money') ?><h4>No invoices</h4><p>Create an invoice once the client approves the quotation.</p></div>
      <?php endif; ?>
    </section>
    <section class="panel">
      <div class="panel-head"><h3>Create invoice</h3></div>
      <form method="post" id="invoice-form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_invoice">
        <div class="line-items">
          <div class="line-item">
            <input class="input" name="items[d][description]" placeholder="Description" required>
            <input class="input" name="items[d][qty]" type="number" min="1" step="1" value="1" placeholder="Qty" required>
            <input class="input" name="items[d][price]" type="number" min="0" step="100" placeholder="Unit price" required>
            <button class="btn btn-danger btn-sm remove-line" type="button" aria-label="Remove line">×</button>
          </div>
        </div>
        <button class="btn btn-ghost btn-sm mb-2" type="button" data-add-item data-target=".line-items"><?= icon('plus') ?> Add line</button>
        <div class="form-row">
          <div class="field"><label for="iv_discount">Discount (<?= e(settings('currency')) ?>)</label><input class="input" id="iv_discount" type="number" min="0" step="100" name="discount" value="0"></div>
          <div class="field"><label for="iv_tax">Tax (%)</label><input class="input" id="iv_tax" type="number" min="0" max="100" step="0.5" name="tax_percent" value="<?= e(settings('tax_percent', '0')) ?>"></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="iv_days">Due after (days)</label><input class="input" id="iv_days" type="number" min="1" max="365" name="due_days" value="<?= e(settings('invoices_due_days', '14')) ?>"></div>
          <div class="field"><label for="iv_status">Status</label>
            <select class="select" id="iv_status" name="status">
              <option value="sent">Send to client (notify)</option>
              <option value="draft">Save as draft</option>
            </select>
          </div>
        </div>
        <div class="field"><label for="iv_notes">Notes (optional)</label><textarea class="textarea" id="iv_notes" name="notes" style="min-height:70px"></textarea></div>
        <button class="btn btn-primary" type="submit"><?= icon('money') ?> Create invoice</button>
      </form>
    </section>
  </div>
<?php else: ?>
  <section class="panel">
    <div class="panel-head"><h3>Activity history</h3></div>
    <?php if ($activity): ?>
      <div class="timeline">
        <?php foreach ($activity as $log): ?>
          <div class="tl-item done">
            <b><?= e(str_replace('_', ' ', ucfirst($log['action']))) ?></b>
            <span><?= e($log['full_name'] ?? 'Guest') ?> · <?= fmt_date($log['created_at'], 'd M Y H:i') ?> · <?= $log['details'] ? e($log['details']) : '' ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('clock') ?><h4>No activity recorded yet</h4><p>Actions taken on this project will be logged here.</p></div>
    <?php endif; ?>
  </section>
<?php endif; ?>

<template id="line-item-template">
  <div class="line-item">
    <input class="input" name="items[n][description]" placeholder="Description" required>
    <input class="input" name="items[n][qty]" type="number" min="1" step="1" value="1" placeholder="Qty" required>
    <input class="input" name="items[n][price]" type="number" min="0" step="100" placeholder="Unit price" required>
    <button class="btn btn-danger btn-sm remove-line" type="button" aria-label="Remove line">×</button>
  </div>
</template>
<?php dashboard_footer(); ?>