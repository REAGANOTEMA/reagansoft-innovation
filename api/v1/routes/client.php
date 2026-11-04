<?php
/**
 * Client portal endpoints — authenticated client role.
 */

declare(strict_types=1);

function handle_client(string $method, array $seg): bool
{
    $user = api_require_role('client');
    $what = $seg[1] ?? '';

    if ($what === 'overview' && $method === 'GET') {
        client_overview($user);
    }
    if ($what === 'projects' && $method === 'GET') {
        if (isset($seg[2]) && $seg[2] === 'create') {
            return false;
        }
        if (isset($seg[2])) {
            if (($seg[3] ?? '') === 'files') {
                return false;
            }
            if (($seg[3] ?? '') === 'messages') {
                return false;
            }
            client_project_detail($user, $seg[2]);
        }
        client_projects($user);
    }
    if ($what === 'projects' && $method === 'POST' && !isset($seg[2])) {
        client_create_project($user);
    }
    if ($what === 'projects' && isset($seg[2]) && isset($seg[3])) {
        if ($seg[3] === 'files' && $method === 'POST') {
            client_upload_file($user, $seg[2]);
        }
        if ($seg[3] === 'messages' && $method === 'POST') {
            client_send_message($user, $seg[2]);
        }
    }
    if ($what === 'invoices' && $method === 'GET') {
        client_invoices($user);
    }
    if ($what === 'payments' && $method === 'POST') {
        client_submit_payment($user);
    }
    if ($what === 'notifications' && $method === 'GET') {
        client_notifications($user);
    }
    if ($what === 'notifications' && $method === 'POST') {
        client_read_notifications($user);
    }
    if ($what === 'billing' && $method === 'GET') {
        client_billing($user);
    }
    return false;
}

function client_projects_query(int $uid): string
{
    return "SELECT p.*, s.name AS service
            FROM projects p LEFT JOIN services s ON s.id = p.service_id
            WHERE p.client_id = " . $uid;
}

function client_overview(array $user): never
{
    $uid = (int)$user['id'];
    $pdo = db();

    $projects = $pdo->query(client_projects_query($uid) . ' ORDER BY p.updated_at DESC, p.id DESC')->fetchAll();
    $active = array_values(array_filter($projects, static fn($p) => !in_array($p['status'], ['COMPLETED', 'CANCELLED'], true)));
    $pending = array_values(array_filter($projects, static fn($p) => in_array($p['status'], ['NEW', 'REVIEWING', 'QUOTATION'], true)));
    $completed = array_values(array_filter($projects, static fn($p) => $p['status'] === 'COMPLETED'));

    $st = $pdo->prepare("SELECT * FROM invoices WHERE client_id = ? AND status IN ('sent','partially_paid','overdue') ORDER BY due_date");
    $st->execute([$uid]);
    $outstanding = $st->fetchAll();

    $st = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 6');
    $st->execute([$uid]);
    $notifs = $st->fetchAll();

    $stats = [
        'active_projects' => count($active),
        'pending_requests' => count($pending),
        'completed_projects' => count($completed),
        'outstanding_invoices' => count($outstanding),
        'outstanding_value' => array_sum(array_map(static fn($i) => max(0, (float)$i['total'] - (float)$i['amount_paid']), $outstanding)),
        'unread_notifications' => unread_notifications($uid),
    ];

    api_ok([
        'user' => api_user_public($user),
        'stats' => $stats,
        'projects' => array_slice($projects, 0, 5),
        'invoices' => $outstanding,
        'notifications' => $notifs,
    ]);
}

function client_projects(array $user): never
{
    $uid = (int)$user['id'];
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $status = trim((string)($_GET['status'] ?? ''));
    $search = trim((string)($_GET['q'] ?? ''));

    $where = ' WHERE p.client_id = ?';
    $params = [$uid];
    if ($status !== '' && in_array($status, project_statuses(), true)) {
        $where .= ' AND p.status = ?';
        $params[] = $status;
    }
    if ($search !== '') {
        $where .= ' AND (p.title LIKE ? OR p.ref_no LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $count = $pdo->prepare('SELECT COUNT(*) FROM projects p' . $where);
    $count->execute($params);
    $total = (int)$count->fetchColumn();
    $pg = api_pagination($total, $page, 12);

    $sql = "SELECT p.*, s.name AS service FROM projects p
            LEFT JOIN services s ON s.id = p.service_id" . $where .
           ' ORDER BY p.updated_at DESC, p.id DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);

    api_ok($st->fetchAll(), $pg);
}

function client_find_project(int $uid, string $id): array
{
    if (!ctype_digit($id)) {
        throw new ApiException('Invalid project.', 400);
    }
    $st = db()->prepare(
        "SELECT p.*, s.name AS service FROM projects p
         LEFT JOIN services s ON s.id = p.service_id
         WHERE p.id = ? AND p.client_id = ? LIMIT 1"
    );
    $st->execute([(int)$id, $uid]);
    $project = $st->fetch();
    if (!$project) {
        throw new ApiException('Project not found.', 404);
    }
    return $project;
}

function client_project_detail(array $user, string $id): never
{
    $uid = (int)$user['id'];
    $project = client_find_project($uid, $id);
    $pid = (int)$project['id'];
    $pdo = db();

    $st = $pdo->prepare('SELECT * FROM project_tasks WHERE project_id = ? ORDER BY id');
    $st->execute([$pid]);
    $tasks = $st->fetchAll();

    $st = $pdo->prepare(
        'SELECT f.*, u.full_name AS uploader FROM project_files f
         LEFT JOIN users u ON u.id = f.uploader_id WHERE f.project_id = ? ORDER BY f.created_at DESC'
    );
    $st->execute([$pid]);
    $files = array_map(static function ($f) {
        $f['url'] = app_url('download.php?id=' . (int)$f['id']);
        unset($f['file_path'], $f['stored_name']);
        return $f;
    }, $st->fetchAll());

    $st = $pdo->prepare(
        'SELECT m.*, u.full_name AS sender FROM project_messages m
         LEFT JOIN users u ON u.id = m.sender_id
         WHERE m.project_id = ? AND m.is_internal = 0 ORDER BY m.created_at'
    );
    $st->execute([$pid]);
    $messages = $st->fetchAll();

    $st = $pdo->prepare('SELECT * FROM invoices WHERE project_id = ? ORDER BY id DESC');
    $st->execute([$pid]);
    $invoices = $st->fetchAll();
    foreach ($invoices as &$invoice) {
        $items = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
        $items->execute([(int)$invoice['id']]);
        $invoice['items'] = $items->fetchAll();
    }
    unset($invoice);

    $st = $pdo->prepare('SELECT * FROM quotations WHERE project_id = ? ORDER BY id DESC');
    $st->execute([$pid]);
    $quotations = $st->fetchAll();
    foreach ($quotations as &$quote) {
        $items = $pdo->prepare('SELECT * FROM quotation_items WHERE quotation_id = ?');
        $items->execute([(int)$quote['id']]);
        $quote['items'] = $items->fetchAll();
    }
    unset($quote);

    api_ok([
        'project' => $project,
        'tasks' => $tasks,
        'files' => $files,
        'messages' => $messages,
        'invoices' => $invoices,
        'quotations' => $quotations,
        'milestones' => project_milestones(),
        'milestone_index' => milestone_index((string)$project['status']),
    ]);
}

function client_create_project(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $title = api_string($data, 'title', 'Project title', 190, true);
    $description = api_string($data, 'description', 'Description', 8000, true);
    $requirements = api_string($data, 'requirements', 'Requirements', 8000);
    $priority = api_in_enum(api_string($data, 'priority', 'Priority', 20) ?: 'normal', ['low', 'normal', 'high', 'urgent'], 'priority');
    $budget = api_decimal($data, 'budget', 0.0);
    $serviceId = api_int($data, 'service_id');

    if ($serviceId !== null) {
        $st = db()->prepare("SELECT id FROM services WHERE id = ? AND status = 'active' LIMIT 1");
        $st->execute([$serviceId]);
        if (!$st->fetchColumn()) {
            throw new ApiException('The selected service is not available.', 422, ['service_id' => 'Unknown service.']);
        }
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $ref = next_project_ref($pdo);
        $pdo->prepare(
            'INSERT INTO projects (ref_no, client_id, service_id, title, description, requirements, budget, priority, status, progress)
             VALUES (?,?,?,?,?,?,?,?, \'NEW\', 0)'
        )->execute([
            $ref, (int)$user['id'], $serviceId, $title, $description,
            $requirements ?: null, $budget > 0 ? $budget : null, $priority,
        ]);
        $pid = (int)$pdo->lastInsertId();
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        log_error('client create project: ' . $e->getMessage());
        throw new ApiException('We could not submit your request. Please try again.', 503);
    }

    audit('project_requested', 'projects', $pid, 'New request ' . $ref . ': ' . $title);
    notify_staff('New project request', $user['full_name'] . ' submitted ' . $ref . ' — ' . $title, 'project', $pid);
    notify((int)$user['id'], 'Request received', 'Your request ' . $ref . ' has been received and is being reviewed.', 'success', $pid);

    $st = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([$pid]);
    api_ok($st->fetch(), null, 201);
}

function client_upload_file(array $user, string $id): never
{
    api_require_csrf();
    $project = client_find_project((int)$user['id'], $id);
    if (!isset($_FILES['file'])) {
        throw new ApiException('Choose a file to upload.', 422);
    }
    $upload = upload_file($_FILES['file']);
    if (!$upload['ok']) {
        throw new ApiException($upload['error'], 422);
    }
    $pdo = db();
    $pdo->prepare(
        'INSERT INTO project_files (project_id, uploader_id, original_name, stored_name, file_path, mime_type, size_bytes)
         VALUES (?,?,?,?,?,?,?)'
    )->execute([
        (int)$project['id'], (int)$user['id'], $upload['name'], $upload['stored'],
        $upload['path'], $upload['mime'], $upload['size'],
    ]);
    $fid = (int)$pdo->lastInsertId();
    audit('file_uploaded', 'project_files', $fid, $upload['name']);
    notify_staff('New project file', $user['full_name'] . ' uploaded ' . $upload['name'], 'project', (int)$project['id']);
    api_ok([
        'id' => $fid,
        'original_name' => $upload['name'],
        'size_bytes' => $upload['size'],
        'mime_type' => $upload['mime'],
        'url' => app_url('download.php?id=' . $fid),
    ], null, 201);
}

function client_send_message(array $user, string $id): never
{
    api_require_csrf();
    $project = client_find_project((int)$user['id'], $id);
    $data = api_body();
    $message = api_string($data, 'message', 'Message', 5000, true);
    $pdo = db();
    $pdo->prepare(
        'INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal) VALUES (?,?,?,?,0)'
    )->execute([(int)$project['id'], (int)$user['id'], 'client', $message]);
    $mid = (int)$pdo->lastInsertId();
    audit('message_sent', 'project_messages', $mid, 'Client message on ' . $project['ref_no']);
    notify_staff('New client message', $user['full_name'] . ' sent a message on ' . $project['ref_no'], 'project', (int)$project['id']);
    $st = $pdo->prepare('SELECT * FROM project_messages WHERE id = ?');
    $st->execute([$mid]);
    api_ok($st->fetch(), null, 201);
}

function client_invoices(array $user): never
{
    $st = db()->prepare(
        "SELECT i.*, p.title AS project, p.ref_no
         FROM invoices i JOIN projects p ON p.id = i.project_id
         WHERE i.client_id = ? ORDER BY i.created_at DESC"
    );
    $st->execute([(int)$user['id']]);
    $invoices = $st->fetchAll();
    foreach ($invoices as &$invoice) {
        $items = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
        $items->execute([(int)$invoice['id']]);
        $invoice['items'] = $items->fetchAll();
    }
    unset($invoice);
    api_ok($invoices);
}

function client_submit_payment(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $invoiceId = api_int($data, 'invoice_id');
    if (!$invoiceId) {
        throw new ApiException('Choose an invoice to pay.', 422, ['invoice_id' => 'Invoice is required.']);
    }

    $st = db()->prepare('SELECT * FROM invoices WHERE id = ? AND client_id = ? LIMIT 1');
    $st->execute([$invoiceId, (int)$user['id']]);
    $invoice = $st->fetch();
    if (!$invoice) {
        throw new ApiException('Invoice not found.', 404);
    }
    if (in_array($invoice['status'], ['paid', 'cancelled'], true)) {
        throw new ApiException('This invoice is already closed.', 409);
    }

    $missing = billing_missing($user);
    if ($missing) {
        throw new ApiException('Complete your billing details before submitting a payment.', 422, [
            'billing' => array_map(static fn($m) => $m['label'], $missing),
        ]);
    }

    $balance = max(0, (float)$invoice['total'] - (float)$invoice['amount_paid']);
    $amount = api_decimal($data, 'amount', $balance);
    if ($amount <= 0) {
        throw new ApiException('Enter the amount you paid.', 422, ['amount' => 'Amount must be greater than zero.']);
    }
    if ($amount > $balance) {
        throw new ApiException('The amount exceeds the invoice balance of ' . money($balance, settings('currency')) . '.', 422, ['amount' => 'Too high.']);
    }
    $method = api_in_enum(
        api_string($data, 'method', 'Payment method', 30) ?: 'mtn_momo',
        ['mtn_momo', 'airtel_money', 'bank', 'cash', 'other'],
        'payment method'
    );
    $reference = api_string($data, 'reference', 'Transaction reference', 120);
    $notes = api_string($data, 'notes', 'Notes', 2000);
    if ($method !== 'cash' && $reference === '') {
        throw new ApiException('Enter the mobile money or bank transaction reference.', 422, ['reference' => 'Reference is required.']);
    }

    $pdo = db();
    $pdo->prepare(
        'INSERT INTO payments (invoice_id, amount, method, reference, status, notes) VALUES (?,?,?,?, \'pending\', ?)'
    )->execute([$invoiceId, $amount, $method, $reference ?: null, $notes ?: null]);
    $payId = (int)$pdo->lastInsertId();

    audit('payment_submitted', 'payments', $payId, 'Payment of ' . money($amount, settings('currency')) . ' for ' . $invoice['invoice_no']);
    notify_staff('Payment awaiting confirmation', $user['full_name'] . ' submitted ' . money($amount, settings('currency')) . ' for ' . $invoice['invoice_no'], 'invoice', (int)$invoice['project_id']);
    notify((int)$user['id'], 'Payment received', 'Your payment for ' . $invoice['invoice_no'] . ' is awaiting confirmation.', 'info', (int)$invoice['project_id']);

    api_ok(['id' => $payId, 'status' => 'pending', 'message' => 'Payment submitted. Our team will confirm it shortly.'], null, 201);
}

function client_notifications(array $user): never
{
    $st = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
    $st->execute([(int)$user['id']]);
    api_ok([
        'notifications' => $st->fetchAll(),
        'unread' => unread_notifications((int)$user['id']),
    ]);
}

function client_read_notifications(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $id = api_int($data, 'id');
    if ($id) {
        $st = db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $st->execute([$id, (int)$user['id']]);
    } else {
        db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([(int)$user['id']]);
    }
    api_ok(['unread' => unread_notifications((int)$user['id'])]);
}

function client_billing(array $user): never
{
    $requirements = [];
    foreach (billing_requirements($user) as $item) {
        $requirements[] = [
            'key' => $item['key'],
            'group' => $item['group'] ?? '',
            'type' => $item['type'] ?? 'text',
            'label' => $item['label'],
            'value' => $item['value'] ?? '',
            'required' => (bool)($item['required'] ?? false),
            'placeholder' => $item['placeholder'] ?? '',
            'why' => $item['why'] ?? '',
            'hint' => $item['hint'] ?? '',
            'options' => $item['options'] ?? [],
        ];
    }
    api_ok([
        'requirements' => $requirements,
        'missing' => billing_missing($user),
        'terms_accepted' => billing_terms_accepted($user),
        'countries' => billing_countries(),
        'schema_ready' => billing_schema_ready(),
        'user' => api_user_public($user),
    ]);
}
