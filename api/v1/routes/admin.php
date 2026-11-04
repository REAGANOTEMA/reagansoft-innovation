<?php
/**
 * Administration console endpoints — authenticated staff/admin roles.
 * Admins additionally receive destructive/configuration permissions.
 */

declare(strict_types=1);

function handle_admin(string $method, array $seg): bool
{
    $user = api_require_staff();
    $what = $seg[1] ?? '';
    $id = $seg[2] ?? null;

    if ($what === 'overview' && $method === 'GET') {
        admin_overview();
    }
    if ($what === 'projects') {
        if ($method === 'GET' && $id === null) admin_projects_list();
        if ($method === 'GET' && $id !== null) admin_project_detail($id);
        if ($method === 'POST' && $id === null) admin_project_create($user);
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_project_update($user, $id);
        if ($method === 'DELETE' && $id !== null) admin_project_delete($user, $id);
    }
    if ($what === 'clients') {
        if ($method === 'GET' && $id === null) admin_clients_list();
        if ($method === 'GET' && $id !== null) admin_client_detail($id);
        if ($method === 'POST' && $id === null) admin_client_create();
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_client_update($id);
        if ($method === 'DELETE' && $id !== null) admin_client_deactivate($id);
    }
    if ($what === 'enquiries') {
        if ($method === 'GET') admin_enquiries_list();
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_enquiry_update($id);
        if ($method === 'DELETE' && $id !== null) admin_enquiry_delete($id);
    }
    if ($what === 'services') {
        if ($method === 'GET') admin_services_list();
        if ($method === 'POST' && $id === null) admin_service_create();
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_service_update($id);
        if ($method === 'DELETE' && $id !== null) admin_service_delete($id);
    }
    if ($what === 'invoices') {
        if ($method === 'GET' && $id === null) admin_invoices_list();
        if ($method === 'GET' && $id !== null) admin_invoice_detail($id);
        if ($method === 'POST' && $id === null) admin_invoice_create($user);
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_invoice_update($id);
        if ($method === 'DELETE' && $id !== null) admin_invoice_delete($user, $id);
    }
    if ($what === 'quotations') {
        if ($method === 'GET') admin_quotations_list();
        if ($method === 'POST' && $id === null) admin_quotation_create($user);
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_quotation_update($id);
        if ($method === 'DELETE' && $id !== null) admin_quotation_delete($user, $id);
    }
    if ($what === 'payments') {
        if ($method === 'GET') admin_payments_list();
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_payment_update($user, $id);
    }
    if ($what === 'tasks') {
        if ($method === 'GET') admin_tasks_list();
        if ($method === 'POST' && $id === null) admin_task_create($user);
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_task_update($user, $id);
        if ($method === 'DELETE' && $id !== null) admin_task_delete($user, $id);
    }
    if ($what === 'users') {
        if ($method === 'GET' && $id === null) admin_users_list();
        if ($method === 'POST' && $id === null) admin_user_create();
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_user_update($id);
        if ($method === 'DELETE' && $id !== null) admin_user_deactivate($id);
    }
    if ($what === 'expenses') {
        if ($method === 'GET') admin_expenses_list();
        if ($method === 'POST' && $id === null) admin_expense_create($user);
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true) && $id !== null) admin_expense_update($id);
        if ($method === 'DELETE' && $id !== null) admin_expense_delete($id);
    }
    if ($what === 'reports' && $method === 'GET') {
        admin_reports();
    }
    if ($what === 'settings') {
        if ($method === 'GET') admin_settings_get();
        if (in_array($method, ['PATCH', 'PUT', 'POST'], true)) admin_settings_update();
    }
    if ($what === 'audit' && $method === 'GET') {
        admin_audit($user);
    }
    if ($what === 'notifications' && $method === 'GET') {
        admin_notifications($user);
    }
    if ($what === 'notifications' && $method === 'POST') {
        api_require_csrf();
        db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([(int)$user['id']]);
        api_ok(['unread' => 0]);
    }
    if ($what === 'staff' && $method === 'GET') {
        $rows = db()->query("SELECT id, full_name, email, role FROM users WHERE role IN ('admin','staff') AND active = 1 ORDER BY full_name")->fetchAll();
        api_ok($rows);
    }
    return false;
}

/* ------------------------------------------------------------------ */
/* Dashboard                                                          */
/* ------------------------------------------------------------------ */

function admin_overview(): never
{
    $pdo = db();
    $stats = [
        'clients' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='client' AND active=1")->fetchColumn(),
        'new_requests' => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status IN ('NEW','REVIEWING')")->fetchColumn(),
        'active_projects' => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status NOT IN ('COMPLETED','CANCELLED')")->fetchColumn(),
        'completed_projects' => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status='COMPLETED'")->fetchColumn(),
        'pending_quotes' => (int)$pdo->query("SELECT COUNT(*) FROM quotations WHERE status='sent'")->fetchColumn(),
        'pending_payments' => (int)$pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn(),
        'unread_enquiries' => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
    ];
    $out = $pdo->query("SELECT COALESCE(SUM(total - amount_paid),0) s, COUNT(*) c FROM invoices WHERE status IN ('sent','partially_paid','overdue')")->fetch();
    $stats['outstanding_value'] = (float)$out['s'];
    $stats['outstanding_invoices'] = (int)$out['c'];
    $stats['revenue'] = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed'")->fetchColumn();
    $stats['expenses'] = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM expenses")->fetchColumn();

    $recentProjects = $pdo->query(
        "SELECT p.id, p.ref_no, p.title, p.status, p.progress, p.priority, p.submitted_at, p.updated_at,
                u.full_name AS client_name, u.company, s.name AS service
         FROM projects p JOIN users u ON u.id = p.client_id LEFT JOIN services s ON s.id = p.service_id
         ORDER BY p.submitted_at DESC LIMIT 8"
    )->fetchAll();

    $pendingInvoices = $pdo->query(
        "SELECT i.id, i.invoice_no, i.status, i.total, i.amount_paid, i.due_date, u.full_name
         FROM invoices i JOIN users u ON u.id = i.client_id
         WHERE i.status IN ('sent','partially_paid','overdue') ORDER BY i.due_date ASC LIMIT 7"
    )->fetchAll();

    $recentLogs = $pdo->query(
        "SELECT l.*, u.full_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id
         ORDER BY l.created_at DESC LIMIT 8"
    )->fetchAll();

    $recentPayments = $pdo->query(
        "SELECT pay.*, i.invoice_no, u.full_name FROM payments pay
         JOIN invoices i ON i.id = pay.invoice_id JOIN users u ON u.id = i.client_id
         ORDER BY pay.created_at DESC LIMIT 6"
    )->fetchAll();

    $monthly = $pdo->query(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, SUM(amount) total
         FROM payments WHERE status='confirmed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
         GROUP BY ym ORDER BY ym"
    )->fetchAll();

    api_ok([
        'stats' => $stats,
        'recent_projects' => $recentProjects,
        'pending_invoices' => $pendingInvoices,
        'recent_activity' => $recentLogs,
        'recent_payments' => $recentPayments,
        'monthly_revenue' => $monthly,
    ]);
}

/* ------------------------------------------------------------------ */
/* Projects                                                           */
/* ------------------------------------------------------------------ */

function admin_projects_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $status = trim((string)($_GET['status'] ?? ''));
    $assigned = trim((string)($_GET['assigned_to'] ?? ''));
    $search = trim((string)($_GET['q'] ?? ''));

    $where = ' WHERE 1=1';
    $params = [];
    if ($status !== '' && in_array($status, project_statuses(), true)) {
        $where .= ' AND p.status = ?';
        $params[] = $status;
    }
    if ($assigned !== '' && ctype_digit($assigned)) {
        $where .= ' AND p.assigned_to = ?';
        $params[] = (int)$assigned;
    }
    if ($search !== '') {
        $where .= ' AND (p.title LIKE ? OR p.ref_no LIKE ? OR u.full_name LIKE ? OR u.company LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }

    $count = $pdo->prepare('SELECT COUNT(*) FROM projects p JOIN users u ON u.id = p.client_id' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 15);

    $sql = "SELECT p.*, u.full_name AS client_name, u.company, u.email AS client_email,
                   s.name AS service, a.full_name AS assignee
            FROM projects p JOIN users u ON u.id = p.client_id
            LEFT JOIN services s ON s.id = p.service_id
            LEFT JOIN users a ON a.id = p.assigned_to" . $where .
           ' ORDER BY p.submitted_at DESC, p.id DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);

    $counts = $pdo->query(
        "SELECT status, COUNT(*) c FROM projects GROUP BY status"
    )->fetchAll();

    api_ok($st->fetchAll(), $pg + ['status_counts' => $counts]);
}

function admin_find_project(string $id): array
{
    if (!ctype_digit($id)) {
        throw new ApiException('Invalid project.', 400);
    }
    $st = db()->prepare(
        "SELECT p.*, u.full_name AS client_name, u.company, u.email AS client_email, u.phone AS client_phone,
                s.name AS service, a.full_name AS assignee
         FROM projects p JOIN users u ON u.id = p.client_id
         LEFT JOIN services s ON s.id = p.service_id
         LEFT JOIN users a ON a.id = p.assigned_to WHERE p.id = ? LIMIT 1"
    );
    $st->execute([(int)$id]);
    $row = $st->fetch();
    if (!$row) {
        throw new ApiException('Project not found.', 404);
    }
    return $row;
}

function admin_project_detail(string $id): never
{
    $project = admin_find_project($id);
    $pid = (int)$project['id'];
    $pdo = db();

    $tasks = $pdo->prepare('SELECT t.*, u.full_name AS assignee FROM project_tasks t LEFT JOIN users u ON u.id = t.assigned_to WHERE t.project_id = ? ORDER BY t.id');
    $tasks->execute([$pid]);

    $files = $pdo->prepare('SELECT f.*, u.full_name AS uploader FROM project_files f LEFT JOIN users u ON u.id = f.uploader_id WHERE f.project_id = ? ORDER BY f.created_at DESC');
    $files->execute([$pid]);
    $fileRows = array_map(static function ($f) {
        $f['url'] = app_url('download.php?id=' . (int)$f['id']);
        unset($f['file_path'], $f['stored_name']);
        return $f;
    }, $files->fetchAll());

    $messages = $pdo->prepare('SELECT m.*, u.full_name AS sender FROM project_messages m LEFT JOIN users u ON u.id = m.sender_id WHERE m.project_id = ? ORDER BY m.created_at');
    $messages->execute([$pid]);

    $invoices = $pdo->prepare('SELECT * FROM invoices WHERE project_id = ? ORDER BY id DESC');
    $invoices->execute([$pid]);
    $quotes = $pdo->prepare('SELECT * FROM quotations WHERE project_id = ? ORDER BY id DESC');
    $quotes->execute([$pid]);

    api_ok([
        'project' => $project,
        'tasks' => $tasks->fetchAll(),
        'files' => $fileRows,
        'messages' => $messages->fetchAll(),
        'invoices' => $invoices->fetchAll(),
        'quotations' => $quotes->fetchAll(),
        'milestones' => project_milestones(),
        'milestone_index' => milestone_index((string)$project['status']),
    ]);
}

function admin_project_create(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $clientId = api_int($data, 'client_id');
    if (!$clientId) {
        throw new ApiException('Choose a client.', 422, ['client_id' => 'Client is required.']);
    }
    $st = db()->prepare("SELECT id FROM users WHERE id = ? AND role = 'client' LIMIT 1");
    $st->execute([$clientId]);
    if (!$st->fetchColumn()) {
        throw new ApiException('Client not found.', 404);
    }

    $title = api_string($data, 'title', 'Title', 190, true);
    $description = api_string($data, 'description', 'Description', 8000, true);
    $requirements = api_string($data, 'requirements', 'Requirements', 8000);
    $serviceId = api_int($data, 'service_id');
    $assignedTo = api_int($data, 'assigned_to');
    $budget = api_decimal($data, 'budget', 0.0);
    $priority = api_in_enum(api_string($data, 'priority', 'Priority', 20) ?: 'normal', ['low', 'normal', 'high', 'urgent'], 'priority');
    $status = api_in_enum(api_string($data, 'status', 'Status', 30) ?: 'NEW', project_statuses(), 'status');
    $progress = max(0, min(100, (int)api_int($data, 'progress', 0)));
    $deadline = api_date($data, 'deadline');
    $website = api_string($data, 'website_url', 'Website', 255);
    $deliverables = api_string($data, 'deliverables', 'Deliverables', 190);
    $cover = api_string($data, 'cover_art', 'Cover art', 40);

    $pdo = db();
    $ref = next_project_ref($pdo);
    $pdo->prepare(
        'INSERT INTO projects (ref_no, client_id, service_id, assigned_to, title, description, requirements,
            website_url, deliverables, cover_art, budget, priority, status, progress, deadline)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        $ref, $clientId, $serviceId, $assignedTo, $title, $description, $requirements ?: null,
        $website ?: null, $deliverables ?: null, $cover ?: null, $budget > 0 ? $budget : null,
        $priority, $status, $progress, $deadline,
    ]);
    $pid = (int)$pdo->lastInsertId();
    audit('project_created', 'projects', $pid, 'Created ' . $ref . ' for client #' . $clientId);
    notify($clientId, 'Project created', 'Project ' . $ref . ' has been created for you.', 'project', $pid);
    api_ok(admin_find_project((string)$pid), null, 201);
}

function admin_project_update(array $user, string $id): never
{
    api_require_csrf();
    $project = admin_find_project($id);
    $data = api_body();
    $fields = [];
    $params = [];

    $map = [
        'title' => ['title', 'Title', 190, true],
        'description' => ['description', 'Description', 8000, false],
        'requirements' => ['requirements', 'Requirements', 8000, false],
        'website_url' => ['website_url', 'Website', 255, false],
        'deliverables' => ['deliverables', 'Deliverables', 190, false],
        'cover_art' => ['cover_art', 'Cover art', 40, false],
    ];
    foreach ($map as $key => [$col, $label, $max, $required]) {
        if (array_key_exists($key, $data)) {
            $value = api_string($data, $key, $label, $max, $required);
            $fields[] = $col . ' = ?';
            $params[] = $value === '' ? null : $value;
        }
    }
    if (array_key_exists('service_id', $data)) {
        $fields[] = 'service_id = ?';
        $params[] = api_int($data, 'service_id');
    }
    if (array_key_exists('assigned_to', $data)) {
        $fields[] = 'assigned_to = ?';
        $params[] = api_int($data, 'assigned_to');
    }
    if (array_key_exists('budget', $data)) {
        $fields[] = 'budget = ?';
        $params[] = api_decimal($data, 'budget', 0.0);
    }
    if (array_key_exists('priority', $data)) {
        $fields[] = 'priority = ?';
        $params[] = api_in_enum(api_string($data, 'priority', 'Priority', 20), ['low', 'normal', 'high', 'urgent'], 'priority');
    }
    if (array_key_exists('status', $data)) {
        $status = api_in_enum(api_string($data, 'status', 'Status', 30), project_statuses(), 'status');
        $fields[] = 'status = ?';
        $params[] = $status;
        if ($status === 'COMPLETED') {
            $fields[] = 'completed_at = COALESCE(completed_at, NOW())';
            $fields[] = 'progress = 100';
        }
        if ($status === 'IN_PROGRESS') {
            $fields[] = 'started_at = COALESCE(started_at, NOW())';
        }
        if ($status !== (string)$project['status']) {
            notify((int)$project['client_id'], 'Project updated', $project['ref_no'] . ' is now ' . str_replace('_', ' ', strtolower($status)) . '.', 'project', (int)$project['id']);
        }
    }
    if (array_key_exists('progress', $data)) {
        $fields[] = 'progress = ?';
        $params[] = max(0, min(100, (int)api_int($data, 'progress', 0)));
    }
    if (array_key_exists('deadline', $data)) {
        $fields[] = 'deadline = ?';
        $params[] = api_date($data, 'deadline');
    }
    if (!$fields) {
        throw new ApiException('No changes were supplied.', 422);
    }

    $params[] = (int)$project['id'];
    db()->prepare('UPDATE projects SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    audit('project_updated', 'projects', (int)$project['id'], 'Updated ' . $project['ref_no']);
    api_ok(admin_find_project($id));
}

function admin_project_delete(array $user, string $id): never
{
    api_require_admin();
    api_require_csrf();
    $project = admin_find_project($id);
    db()->prepare("UPDATE projects SET status = 'CANCELLED' WHERE id = ?")->execute([(int)$project['id']]);
    audit('project_cancelled', 'projects', (int)$project['id'], 'Cancelled ' . $project['ref_no']);
    notify((int)$project['client_id'], 'Project cancelled', $project['ref_no'] . ' has been cancelled.', 'warning', (int)$project['id']);
    api_ok(['cancelled' => true]);
}

/* ------------------------------------------------------------------ */
/* Clients                                                            */
/* ------------------------------------------------------------------ */

function admin_clients_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $search = trim((string)($_GET['q'] ?? ''));
    $where = " WHERE u.role = 'client'";
    $params = [];
    if ($search !== '') {
        $where .= ' AND (u.full_name LIKE ? OR u.email LIKE ? OR u.company LIKE ? OR u.phone LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }
    $count = $pdo->prepare('SELECT COUNT(*) FROM users u' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 15);
    $sql = "SELECT u.id, u.full_name, u.email, u.phone, u.company, u.city, u.country, u.active,
                   u.created_at, u.last_login_at,
                   (SELECT COUNT(*) FROM projects p WHERE p.client_id = u.id) AS project_count,
                   (SELECT COALESCE(SUM(total - amount_paid),0) FROM invoices i WHERE i.client_id = u.id AND i.status IN ('sent','partially_paid','overdue')) AS outstanding
            FROM users u" . $where . ' ORDER BY u.created_at DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    api_ok($st->fetchAll(), $pg);
}

function admin_client_detail(string $id): never
{
    if (!ctype_digit($id)) throw new ApiException('Invalid client.', 400);
    $st = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'client' LIMIT 1");
    $st->execute([(int)$id]);
    $client = $st->fetch();
    if (!$client) throw new ApiException('Client not found.', 404);
    unset($client['password_hash']);
    $projects = db()->prepare('SELECT id, ref_no, title, status, progress, submitted_at FROM projects WHERE client_id = ? ORDER BY id DESC');
    $projects->execute([(int)$id]);
    $invoices = db()->prepare('SELECT id, invoice_no, total, amount_paid, status, due_date FROM invoices WHERE client_id = ? ORDER BY id DESC');
    $invoices->execute([(int)$id]);
    api_ok(['client' => $client, 'projects' => $projects->fetchAll(), 'invoices' => $invoices->fetchAll()]);
}

function admin_client_create(): never
{
    api_require_csrf();
    $data = api_body();
    $name = api_string($data, 'full_name', 'Full name', 150, true);
    $email = strtolower(api_string($data, 'email', 'Email', 190, true));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ApiException('Invalid email address.', 422, ['email' => 'Invalid email.']);
    $phone = api_string($data, 'phone', 'Phone', 40);
    $company = api_string($data, 'company', 'Company', 190);
    $password = (string)($data['password'] ?? '');
    if ($password === '') $password = bin2hex(random_bytes(6)) . 'Aa1!';
    if (strlen($password) < 8) throw new ApiException('Password must be at least 8 characters.', 422);

    $st = db()->prepare('SELECT id FROM users WHERE email = ?');
    $st->execute([$email]);
    if ($st->fetch()) throw new ApiException('An account with this email already exists.', 409);

    db()->prepare("INSERT INTO users (full_name, email, phone, company, password_hash, role, active) VALUES (?,?,?,?,?, 'client', 1)")
        ->execute([$name, $email, $phone ?: null, $company ?: null, password_hash($password, PASSWORD_DEFAULT)]);
    $id = (int)db()->lastInsertId();
    audit('client_created', 'users', $id, 'Admin created client ' . $name);
    notify($id, 'Welcome to Reagansoft Innovation', 'Your client account has been created. You can now track projects and invoices.', 'success');
    api_ok(['id' => $id, 'temporary_password' => (isset($data['password']) && $data['password'] !== '') ? null : $password], null, 201);
}

function admin_client_update(string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid client.', 400);
    $data = api_body();
    $fields = [];
    $params = [];
    foreach (['full_name' => 150, 'phone' => 40, 'company' => 190, 'address' => 255, 'city' => 90, 'country' => 80] as $key => $max) {
        if (array_key_exists($key, $data)) {
            $fields[] = $key . ' = ?';
            $params[] = api_string($data, $key, ucfirst($key), $max) ?: null;
        }
    }
    if (array_key_exists('active', $data)) {
        $fields[] = 'active = ?';
        $params[] = api_bool($data, 'active') ? 1 : 0;
    }
    if (array_key_exists('password', $data) && (string)$data['password'] !== '') {
        if (strlen((string)$data['password']) < 8) throw new ApiException('Password must be at least 8 characters.', 422);
        $fields[] = 'password_hash = ?';
        $params[] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
    }
    if (!$fields) throw new ApiException('No changes were supplied.', 422);
    $params[] = (int)$id;
    db()->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ? AND role = 'client'")->execute($params);
    audit('client_updated', 'users', (int)$id, 'Updated client #' . $id);
    api_ok(['updated' => true]);
}

function admin_client_deactivate(string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid client.', 400);
    db()->prepare("UPDATE users SET active = 0 WHERE id = ? AND role = 'client'")->execute([(int)$id]);
    audit('client_deactivated', 'users', (int)$id, 'Deactivated client #' . $id);
    api_ok(['deactivated' => true]);
}

/* ------------------------------------------------------------------ */
/* Enquiries                                                          */
/* ------------------------------------------------------------------ */

function admin_enquiries_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $filter = trim((string)($_GET['filter'] ?? ''));
    $where = ' WHERE 1=1';
    if ($filter === 'unread') $where .= ' AND is_read = 0';
    if ($filter === 'read') $where .= ' AND is_read = 1';
    $pg = api_pagination((int)$pdo->query('SELECT COUNT(*) FROM contact_messages' . $where)->fetchColumn(), $page, 15);
    $st = $pdo->query('SELECT * FROM contact_messages' . $where . ' ORDER BY created_at DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset']);
    api_ok($st->fetchAll(), $pg);
}

function admin_enquiry_update(string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid enquiry.', 400);
    $data = api_body();
    db()->prepare('UPDATE contact_messages SET is_read = ? WHERE id = ?')
        ->execute([api_bool($data, 'is_read', true) ? 1 : 0, (int)$id]);
    api_ok(['updated' => true]);
}

function admin_enquiry_delete(string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid enquiry.', 400);
    db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([(int)$id]);
    audit('enquiry_deleted', 'contact_messages', (int)$id, 'Deleted enquiry');
    api_ok(['deleted' => true]);
}

/* ------------------------------------------------------------------ */
/* Services                                                           */
/* ------------------------------------------------------------------ */

function admin_services_list(): never
{
    $rows = db()->query('SELECT * FROM services ORDER BY sort_order, name')->fetchAll();
    api_ok($rows);
}

function admin_service_payload(): array
{
    $data = api_body();
    $name = api_string($data, 'name', 'Name', 150, true);
    $slug = strtolower(api_string($data, 'slug', 'Slug', 160));
    if ($slug === '') {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)) ?: '', '-');
    }
    if ($slug === '') throw new ApiException('A valid slug is required.', 422);
    $type = api_in_enum(api_string($data, 'pricing_type', 'Pricing type', 30) ?: 'starting_from', ['range', 'starting_from', 'contact_for_quote'], 'pricing type');
    $features = $data['features'] ?? '';
    if (is_array($features)) $features = implode("\n", array_map('strval', $features));
    return [
        'name' => $name,
        'slug' => $slug,
        'description' => api_string($data, 'description', 'Description', 5000, true),
        'price_min' => api_decimal($data, 'price_min', 0),
        'price_max' => array_key_exists('price_max', $data) && $data['price_max'] !== '' && $data['price_max'] !== null ? api_decimal($data, 'price_max', 0) : null,
        'currency' => api_string($data, 'currency', 'Currency', 8) ?: 'UGX',
        'pricing_type' => $type,
        'price_note' => api_string($data, 'price_note', 'Price note', 120),
        'features' => trim((string)$features),
        'delivery_days' => api_int($data, 'delivery_days'),
        'icon' => api_string($data, 'icon', 'Icon', 60) ?: 'code-s',
        'status' => api_in_enum(api_string($data, 'status', 'Status', 20) ?: 'active', ['active', 'inactive'], 'status'),
        'sort_order' => api_int($data, 'sort_order', 0),
    ];
}

function admin_service_create(): never
{
    api_require_csrf();
    $p = admin_service_payload();
    try {
        db()->prepare(
            'INSERT INTO services (name, slug, description, price_min, price_max, currency, pricing_type, price_note, features, delivery_days, icon, status, sort_order)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $p['name'], $p['slug'], $p['description'], $p['price_min'], $p['price_max'], $p['currency'],
            $p['pricing_type'], $p['price_note'], $p['features'], $p['delivery_days'], $p['icon'], $p['status'], $p['sort_order'],
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') throw new ApiException('That slug is already in use.', 409);
        throw $e;
    }
    $id = (int)db()->lastInsertId();
    audit('service_created', 'services', $id, 'Created service ' . $p['name']);
    api_ok(['id' => $id], null, 201);
}

function admin_service_update(string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid service.', 400);
    $p = admin_service_payload();
    try {
        db()->prepare(
            'UPDATE services SET name=?, slug=?, description=?, price_min=?, price_max=?, currency=?,
             pricing_type=?, price_note=?, features=?, delivery_days=?, icon=?, status=?, sort_order=? WHERE id=?'
        )->execute([
            $p['name'], $p['slug'], $p['description'], $p['price_min'], $p['price_max'], $p['currency'],
            $p['pricing_type'], $p['price_note'], $p['features'], $p['delivery_days'], $p['icon'], $p['status'], $p['sort_order'], (int)$id,
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') throw new ApiException('That slug is already in use.', 409);
        throw $e;
    }
    audit('service_updated', 'services', (int)$id, 'Updated service ' . $p['name']);
    api_ok(['updated' => true]);
}

function admin_service_delete(string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid service.', 400);
    db()->prepare("UPDATE services SET status = 'inactive' WHERE id = ?")->execute([(int)$id]);
    audit('service_deactivated', 'services', (int)$id, 'Deactivated service #' . $id);
    api_ok(['deactivated' => true]);
}

/* ------------------------------------------------------------------ */
/* Invoices                                                           */
/* ------------------------------------------------------------------ */

function admin_invoices_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $status = trim((string)($_GET['status'] ?? ''));
    $search = trim((string)($_GET['q'] ?? ''));
    $where = ' WHERE 1=1';
    $params = [];
    if ($status !== '') { $where .= ' AND i.status = ?'; $params[] = $status; }
    if ($search !== '') {
        $where .= ' AND (i.invoice_no LIKE ? OR u.full_name LIKE ? OR p.title LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like);
    }
    $count = $pdo->prepare('SELECT COUNT(*) FROM invoices i JOIN users u ON u.id = i.client_id JOIN projects p ON p.id = i.project_id' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 15);
    $sql = "SELECT i.*, u.full_name AS client_name, p.title AS project, p.ref_no
            FROM invoices i JOIN users u ON u.id = i.client_id JOIN projects p ON p.id = i.project_id" . $where .
           ' ORDER BY i.created_at DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $summary = $pdo->query("SELECT
        COALESCE(SUM(CASE WHEN status IN ('sent','partially_paid','overdue') THEN total - amount_paid ELSE 0 END),0) outstanding,
        COALESCE(SUM(CASE WHEN status='paid' THEN total ELSE 0 END),0) paid_total,
        COUNT(*) total_count FROM invoices")->fetch();
    api_ok($st->fetchAll(), $pg + ['summary' => $summary]);
}

function admin_invoice_detail(string $id): never
{
    if (!ctype_digit($id)) throw new ApiException('Invalid invoice.', 400);
    $st = db()->prepare('SELECT i.*, u.full_name AS client_name, p.title AS project FROM invoices i JOIN users u ON u.id=i.client_id JOIN projects p ON p.id=i.project_id WHERE i.id=?');
    $st->execute([(int)$id]);
    $inv = $st->fetch();
    if (!$inv) throw new ApiException('Invoice not found.', 404);
    $items = db()->prepare('SELECT * FROM invoice_items WHERE invoice_id = ?');
    $items->execute([(int)$id]);
    $payments = db()->prepare('SELECT * FROM payments WHERE invoice_id = ? ORDER BY created_at DESC');
    $payments->execute([(int)$id]);
    $inv['items'] = $items->fetchAll();
    $inv['payments'] = $payments->fetchAll();
    api_ok($inv);
}

function admin_invoice_create(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $projectId = api_int($data, 'project_id');
    if (!$projectId) throw new ApiException('Choose a project.', 422);
    $st = db()->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([$projectId]);
    $project = $st->fetch();
    if (!$project) throw new ApiException('Project not found.', 404);

    $items = $data['items'] ?? [];
    if (!is_array($items) || !$items) {
        $items = [['description' => api_string($data, 'description', 'Description', 255, true), 'quantity' => 1, 'unit_price' => api_decimal($data, 'amount', 0)]];
    }
    $subtotal = 0.0;
    $clean = [];
    foreach ($items as $item) {
        $desc = trim((string)($item['description'] ?? ''));
        $qty = max(0, (float)($item['quantity'] ?? 1));
        $price = max(0, (float)($item['unit_price'] ?? 0));
        if ($desc === '') continue;
        $subtotal += $qty * $price;
        $clean[] = ['description' => $desc, 'quantity' => $qty, 'unit_price' => $price];
    }
    if (!$clean) throw new ApiException('Add at least one invoice line.', 422);
    $discount = api_decimal($data, 'discount', 0);
    $taxPercent = api_decimal($data, 'tax_percent', (float)settings('tax_percent', '0'));
    $taxable = max(0, $subtotal - $discount);
    $total = round($taxable + ($taxable * $taxPercent / 100), 2);
    $dueDays = (int)settings('invoices_due_days', '14');
    $status = api_in_enum(api_string($data, 'status', 'Status', 20) ?: 'sent', ['draft', 'sent'], 'status');
    $invoiceNo = next_doc_no(db(), 'invoices', 'invoice_no', 'INV-' . date('Y') . '-');

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'INSERT INTO invoices (invoice_no, project_id, client_id, due_date, subtotal, discount, tax_percent, total, amount_paid, status, is_deposit, notes)
             VALUES (?,?,?,DATE_ADD(CURDATE(), INTERVAL ? DAY),?,?,?,?,0,?,0,?)'
        )->execute([$invoiceNo, $projectId, (int)$project['client_id'], $dueDays, $subtotal, $discount, $taxPercent, $total, $status, api_string($data, 'notes', 'Notes', 2000) ?: null]);
        $invId = (int)$pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) VALUES (?,?,?,?)');
        foreach ($clean as $line) {
            $ins->execute([$invId, $line['description'], $line['quantity'], $line['unit_price']]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    audit('invoice_created', 'invoices', $invId, 'Created ' . $invoiceNo . ' for ' . $project['ref_no']);
    notify((int)$project['client_id'], 'New invoice', 'Invoice ' . $invoiceNo . ' for ' . money($total, settings('currency')) . ' has been issued.', 'invoice', $projectId);
    api_ok(['id' => $invId, 'invoice_no' => $invoiceNo, 'total' => $total], null, 201);
}

function admin_invoice_update(string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid invoice.', 400);
    $data = api_body();
    $fields = [];
    $params = [];
    if (array_key_exists('status', $data)) {
        $status = api_in_enum(api_string($data, 'status', 'Status', 20), ['draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled'], 'status');
        $fields[] = 'status = ?';
        $params[] = $status;
    }
    if (array_key_exists('due_date', $data)) { $fields[] = 'due_date = ?'; $params[] = api_date($data, 'due_date'); }
    if (array_key_exists('notes', $data)) { $fields[] = 'notes = ?'; $params[] = api_string($data, 'notes', 'Notes', 2000) ?: null; }
    if (!$fields) throw new ApiException('No changes were supplied.', 422);
    $params[] = (int)$id;
    db()->prepare('UPDATE invoices SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    audit('invoice_updated', 'invoices', (int)$id, 'Updated invoice #' . $id);
    api_ok(['updated' => true]);
}

function admin_invoice_delete(array $user, string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid invoice.', 400);
    db()->prepare("UPDATE invoices SET status = 'cancelled' WHERE id = ?")->execute([(int)$id]);
    audit('invoice_cancelled', 'invoices', (int)$id, 'Cancelled invoice #' . $id);
    api_ok(['cancelled' => true]);
}

/* ------------------------------------------------------------------ */
/* Quotations                                                         */
/* ------------------------------------------------------------------ */

function admin_quotations_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $status = trim((string)($_GET['status'] ?? ''));
    $where = ' WHERE 1=1';
    $params = [];
    if ($status !== '') { $where .= ' AND q.status = ?'; $params[] = $status; }
    $count = $pdo->prepare('SELECT COUNT(*) FROM quotations q' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 15);
    $sql = "SELECT q.*, u.full_name AS client_name, p.title AS project, p.ref_no
            FROM quotations q JOIN users u ON u.id=q.client_id JOIN projects p ON p.id=q.project_id" . $where .
           ' ORDER BY q.created_at DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();
    foreach ($rows as &$row) {
        $items = $pdo->prepare('SELECT * FROM quotation_items WHERE quotation_id = ?');
        $items->execute([(int)$row['id']]);
        $row['items'] = $items->fetchAll();
    }
    unset($row);
    api_ok($rows, $pg);
}

function admin_quotation_create(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $projectId = api_int($data, 'project_id');
    if (!$projectId) throw new ApiException('Choose a project.', 422);
    $st = db()->prepare('SELECT * FROM projects WHERE id = ?');
    $st->execute([$projectId]);
    $project = $st->fetch();
    if (!$project) throw new ApiException('Project not found.', 404);

    $items = $data['items'] ?? [];
    if (!is_array($items) || !$items) $items = [['description' => api_string($data, 'description', 'Description', 255, true), 'quantity' => 1, 'unit_price' => api_decimal($data, 'amount', 0)]];
    $subtotal = 0.0;
    $clean = [];
    foreach ($items as $item) {
        $desc = trim((string)($item['description'] ?? ''));
        $qty = max(0, (float)($item['quantity'] ?? 1));
        $price = max(0, (float)($item['unit_price'] ?? 0));
        if ($desc === '') continue;
        $subtotal += $qty * $price;
        $clean[] = [$desc, $qty, $price];
    }
    if (!$clean) throw new ApiException('Add at least one quotation line.', 422);
    $discount = api_decimal($data, 'discount', 0);
    $taxPercent = api_decimal($data, 'tax_percent', (float)settings('tax_percent', '0'));
    $taxable = max(0, $subtotal - $discount);
    $total = round($taxable + ($taxable * $taxPercent / 100), 2);
    $quoteNo = next_doc_no(db(), 'quotations', 'quotation_no', 'QT-' . date('Y') . '-');
    $status = api_in_enum(api_string($data, 'status', 'Status', 20) ?: 'draft', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'], 'status');

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare(
            'INSERT INTO quotations (quotation_no, project_id, client_id, issued_on, expiry_date, subtotal, discount, tax_percent, total, status, notes, terms)
             VALUES (?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 14 DAY),?,?,?,?,?,?,?)'
        )->execute([$quoteNo, $projectId, (int)$project['client_id'], $subtotal, $discount, $taxPercent, $total, $status, api_string($data, 'notes', 'Notes', 2000) ?: null, api_string($data, 'terms', 'Terms', 3000) ?: null]);
        $qid = (int)$pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO quotation_items (quotation_id, description, quantity, unit_price) VALUES (?,?,?,?)');
        foreach ($clean as [$desc, $qty, $price]) $ins->execute([$qid, $desc, $qty, $price]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    audit('quotation_created', 'quotations', $qid, 'Created ' . $quoteNo);
    notify((int)$project['client_id'], 'New quotation', 'Quotation ' . $quoteNo . ' for ' . money($total, settings('currency')) . ' is ready.', 'quotation', $projectId);
    api_ok(['id' => $qid, 'quotation_no' => $quoteNo, 'total' => $total], null, 201);
}

function admin_quotation_update(string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid quotation.', 400);
    $data = api_body();
    $fields = [];
    $params = [];
    if (array_key_exists('status', $data)) {
        $fields[] = 'status = ?';
        $params[] = api_in_enum(api_string($data, 'status', 'Status', 20), ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'], 'status');
    }
    if (array_key_exists('notes', $data)) { $fields[] = 'notes = ?'; $params[] = api_string($data, 'notes', 'Notes', 2000) ?: null; }
    if (array_key_exists('terms', $data)) { $fields[] = 'terms = ?'; $params[] = api_string($data, 'terms', 'Terms', 3000) ?: null; }
    if (array_key_exists('expiry_date', $data)) { $fields[] = 'expiry_date = ?'; $params[] = api_date($data, 'expiry_date'); }
    if (!$fields) throw new ApiException('No changes were supplied.', 422);
    $params[] = (int)$id;
    db()->prepare('UPDATE quotations SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    audit('quotation_updated', 'quotations', (int)$id, 'Updated quotation #' . $id);
    api_ok(['updated' => true]);
}

function admin_quotation_delete(array $user, string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid quotation.', 400);
    db()->prepare("UPDATE quotations SET status = 'cancelled' WHERE id = ?")->execute([(int)$id]);
    audit('quotation_cancelled', 'quotations', (int)$id, 'Cancelled quotation #' . $id);
    api_ok(['cancelled' => true]);
}

/* ------------------------------------------------------------------ */
/* Payments                                                           */
/* ------------------------------------------------------------------ */

function admin_payments_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $status = trim((string)($_GET['status'] ?? ''));
    $where = ' WHERE 1=1';
    $params = [];
    if ($status !== '') { $where .= ' AND pay.status = ?'; $params[] = $status; }
    $count = $pdo->prepare('SELECT COUNT(*) FROM payments pay' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 15);
    $sql = "SELECT pay.*, i.invoice_no, i.total AS invoice_total, i.amount_paid, i.status AS invoice_status,
                   u.full_name AS client_name, p.title AS project, p.ref_no
            FROM payments pay JOIN invoices i ON i.id = pay.invoice_id
            JOIN users u ON u.id = i.client_id JOIN projects p ON p.id = i.project_id" . $where .
           ' ORDER BY pay.created_at DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    api_ok($st->fetchAll(), $pg);
}

function admin_payment_update(array $user, string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid payment.', 400);
    $data = api_body();
    $action = api_in_enum(api_string($data, 'action', 'Action', 20), ['confirm', 'reject'], 'action');

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT pay.*, i.id AS inv_id, i.total, i.amount_paid, i.status AS invoice_status, i.client_id, i.project_id, i.invoice_no
                             FROM payments pay JOIN invoices i ON i.id = pay.invoice_id WHERE pay.id = ? FOR UPDATE');
        $st->execute([(int)$id]);
        $payment = $st->fetch();
        if (!$payment) throw new ApiException('Payment not found.', 404);
        if ($payment['status'] !== 'pending') throw new ApiException('This payment has already been processed.', 409);

        if ($action === 'confirm') {
            $pdo->prepare("UPDATE payments SET status='confirmed', confirmed_at=NOW(), received_by=? WHERE id=?")
                ->execute([(int)$user['id'], (int)$id]);
            $newPaid = (float)$payment['amount_paid'] + (float)$payment['amount'];
            $newStatus = $newPaid >= (float)$payment['total'] ? 'paid' : 'partially_paid';
            $pdo->prepare('UPDATE invoices SET amount_paid = ?, status = ? WHERE id = ?')
                ->execute([$newPaid, $newStatus, (int)$payment['inv_id']]);
            notify((int)$payment['client_id'], 'Payment confirmed', 'We confirmed ' . money((float)$payment['amount'], settings('currency')) . ' for ' . $payment['invoice_no'] . '. Thank you.', 'success', (int)$payment['project_id']);
            audit('payment_confirmed', 'payments', (int)$id, 'Confirmed ' . money((float)$payment['amount'], settings('currency')) . ' on ' . $payment['invoice_no']);
        } else {
            $pdo->prepare("UPDATE payments SET status='rejected', confirmed_at=NOW(), received_by=? WHERE id=?")
                ->execute([(int)$user['id'], (int)$id]);
            notify((int)$payment['client_id'], 'Payment not confirmed', 'We could not confirm your payment for ' . $payment['invoice_no'] . '. Please contact support with your reference.', 'warning', (int)$payment['project_id']);
            audit('payment_rejected', 'payments', (int)$id, 'Rejected payment on ' . $payment['invoice_no']);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    api_ok(['updated' => true, 'action' => $action]);
}

/* ------------------------------------------------------------------ */
/* Tasks                                                              */
/* ------------------------------------------------------------------ */

function admin_tasks_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $status = trim((string)($_GET['status'] ?? ''));
    $projectId = api_int($_GET, 'project_id');
    $where = ' WHERE 1=1';
    $params = [];
    if ($status !== '') { $where .= ' AND t.status = ?'; $params[] = $status; }
    if ($projectId) { $where .= ' AND t.project_id = ?'; $params[] = $projectId; }
    $count = $pdo->prepare('SELECT COUNT(*) FROM project_tasks t' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 20);
    $sql = "SELECT t.*, p.title AS project, p.ref_no, u.full_name AS assignee
            FROM project_tasks t JOIN projects p ON p.id=t.project_id LEFT JOIN users u ON u.id=t.assigned_to" . $where .
           ' ORDER BY t.due_date IS NULL, t.due_date, t.id DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    api_ok($st->fetchAll(), $pg);
}

function admin_task_create(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $projectId = api_int($data, 'project_id');
    if (!$projectId) throw new ApiException('Choose a project.', 422);
    $st = db()->prepare('SELECT id FROM projects WHERE id = ?');
    $st->execute([$projectId]);
    if (!$st->fetchColumn()) throw new ApiException('Project not found.', 404);
    $title = api_string($data, 'title', 'Task title', 190, true);
    $status = api_in_enum(api_string($data, 'status', 'Status', 20) ?: 'TODO', ['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'], 'status');
    $priority = api_in_enum(api_string($data, 'priority', 'Priority', 20) ?: 'medium', ['low', 'medium', 'high'], 'priority');
    db()->prepare(
        'INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
         VALUES (?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        $projectId, api_int($data, 'assigned_to'), $title, api_string($data, 'description', 'Description', 3000) ?: null,
        $status, max(0, min(100, (int)api_int($data, 'progress', 0))), $priority,
        api_date($data, 'start_date'), api_date($data, 'due_date'), (int)$user['id'],
    ]);
    $id = (int)db()->lastInsertId();
    audit('task_created', 'project_tasks', $id, 'Created task ' . $title);
    api_ok(['id' => $id], null, 201);
}

function admin_task_update(array $user, string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid task.', 400);
    $data = api_body();
    $fields = [];
    $params = [];
    foreach (['title' => 190, 'description' => 3000] as $key => $max) {
        if (array_key_exists($key, $data)) { $fields[] = $key . ' = ?'; $params[] = api_string($data, $key, ucfirst($key), $max) ?: null; }
    }
    if (array_key_exists('status', $data)) { $fields[] = 'status = ?'; $params[] = api_in_enum(api_string($data, 'status', 'Status', 20), ['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'], 'status'); }
    if (array_key_exists('priority', $data)) { $fields[] = 'priority = ?'; $params[] = api_in_enum(api_string($data, 'priority', 'Priority', 20), ['low', 'medium', 'high'], 'priority'); }
    if (array_key_exists('progress', $data)) { $fields[] = 'progress = ?'; $params[] = max(0, min(100, (int)api_int($data, 'progress', 0))); }
    if (array_key_exists('assigned_to', $data)) { $fields[] = 'assigned_to = ?'; $params[] = api_int($data, 'assigned_to'); }
    if (array_key_exists('due_date', $data)) { $fields[] = 'due_date = ?'; $params[] = api_date($data, 'due_date'); }
    if (!$fields) throw new ApiException('No changes were supplied.', 422);
    $params[] = (int)$id;
    db()->prepare('UPDATE project_tasks SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    audit('task_updated', 'project_tasks', (int)$id, 'Updated task #' . $id);
    api_ok(['updated' => true]);
}

function admin_task_delete(array $user, string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid task.', 400);
    db()->prepare('DELETE FROM project_tasks WHERE id = ?')->execute([(int)$id]);
    audit('task_deleted', 'project_tasks', (int)$id, 'Deleted task #' . $id);
    api_ok(['deleted' => true]);
}

/* ------------------------------------------------------------------ */
/* Users (admin only for writes)                                      */
/* ------------------------------------------------------------------ */

function admin_users_list(): never
{
    api_require_admin();
    $rows = db()->query(
        "SELECT id, full_name, email, phone, role, company, active, last_login_at, created_at
         FROM users ORDER BY role, full_name"
    )->fetchAll();
    api_ok($rows);
}

function admin_user_create(): never
{
    api_require_admin();
    api_require_csrf();
    $data = api_body();
    $name = api_string($data, 'full_name', 'Full name', 150, true);
    $email = strtolower(api_string($data, 'email', 'Email', 190, true));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new ApiException('Invalid email address.', 422, ['email' => 'Invalid email.']);
    $role = api_in_enum(api_string($data, 'role', 'Role', 20) ?: 'staff', ['admin', 'staff', 'client'], 'role');
    $password = (string)($data['password'] ?? '');
    if (strlen($password) < 8) throw new ApiException('Password must be at least 8 characters.', 422);
    $st = db()->prepare('SELECT id FROM users WHERE email = ?');
    $st->execute([$email]);
    if ($st->fetch()) throw new ApiException('An account with this email already exists.', 409);
    db()->prepare('INSERT INTO users (full_name, email, phone, role, password_hash, active) VALUES (?,?,?,?,?,1)')
        ->execute([$name, $email, api_string($data, 'phone', 'Phone', 40) ?: null, $role, password_hash($password, PASSWORD_DEFAULT)]);
    $id = (int)db()->lastInsertId();
    audit('user_created', 'users', $id, 'Created ' . $role . ' ' . $name);
    api_ok(['id' => $id], null, 201);
}

function admin_user_update(string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid user.', 400);
    $data = api_body();
    $fields = [];
    $params = [];
    foreach (['full_name' => 150, 'phone' => 40] as $key => $max) {
        if (array_key_exists($key, $data)) { $fields[] = $key . ' = ?'; $params[] = api_string($data, $key, ucfirst($key), $max) ?: null; }
    }
    if (array_key_exists('role', $data)) { $fields[] = 'role = ?'; $params[] = api_in_enum(api_string($data, 'role', 'Role', 20), ['admin', 'staff', 'client'], 'role'); }
    if (array_key_exists('active', $data)) { $fields[] = 'active = ?'; $params[] = api_bool($data, 'active') ? 1 : 0; }
    if (array_key_exists('password', $data) && (string)$data['password'] !== '') {
        if (strlen((string)$data['password']) < 8) throw new ApiException('Password must be at least 8 characters.', 422);
        $fields[] = 'password_hash = ?';
        $params[] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
    }
    if (!$fields) throw new ApiException('No changes were supplied.', 422);
    $params[] = (int)$id;
    db()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    audit('user_updated', 'users', (int)$id, 'Updated user #' . $id);
    api_ok(['updated' => true]);
}

function admin_user_deactivate(string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid user.', 400);
    if ((int)$id === (int)(api_user()['id'] ?? 0)) throw new ApiException('You cannot deactivate your own account.', 422);
    db()->prepare('UPDATE users SET active = 0 WHERE id = ?')->execute([(int)$id]);
    audit('user_deactivated', 'users', (int)$id, 'Deactivated user #' . $id);
    api_ok(['deactivated' => true]);
}

/* ------------------------------------------------------------------ */
/* Expenses                                                           */
/* ------------------------------------------------------------------ */

function admin_expenses_list(): never
{
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $category = trim((string)($_GET['category'] ?? ''));
    $where = ' WHERE 1=1';
    $params = [];
    if ($category !== '') { $where .= ' AND e.category = ?'; $params[] = $category; }
    $count = $pdo->prepare('SELECT COUNT(*) FROM expenses e' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 15);
    $sql = "SELECT e.*, p.title AS project, u.full_name AS recorder
            FROM expenses e LEFT JOIN projects p ON p.id=e.project_id LEFT JOIN users u ON u.id=e.recorded_by" . $where .
           ' ORDER BY e.expense_date DESC, e.id DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $total = (float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM expenses')->fetchColumn();
    api_ok($st->fetchAll(), $pg + ['total' => $total]);
}

function admin_expense_create(array $user): never
{
    api_require_csrf();
    $data = api_body();
    $category = api_string($data, 'category', 'Category', 80, true);
    $description = api_string($data, 'description', 'Description', 255, true);
    $amount = api_decimal($data, 'amount', 0);
    if ($amount <= 0) throw new ApiException('Enter an amount greater than zero.', 422, ['amount' => 'Required.']);
    $date = api_date($data, 'expense_date') ?: date('Y-m-d');
    db()->prepare(
        'INSERT INTO expenses (category, description, amount, currency, expense_date, project_id, recorded_by, reference, notes)
         VALUES (?,?,?,?,?,?,?,?,?)'
    )->execute([
        $category, $description, $amount, settings('currency', 'UGX'), $date,
        api_int($data, 'project_id'), (int)$user['id'],
        api_string($data, 'reference', 'Reference', 120) ?: null,
        api_string($data, 'notes', 'Notes', 2000) ?: null,
    ]);
    $id = (int)db()->lastInsertId();
    audit('expense_created', 'expenses', $id, $description . ' ' . money($amount, settings('currency')));
    api_ok(['id' => $id], null, 201);
}

function admin_expense_update(string $id): never
{
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid expense.', 400);
    $data = api_body();
    $fields = [];
    $params = [];
    foreach (['category' => 80, 'description' => 255, 'reference' => 120, 'notes' => 2000] as $key => $max) {
        if (array_key_exists($key, $data)) { $fields[] = $key . ' = ?'; $params[] = api_string($data, $key, ucfirst($key), $max) ?: null; }
    }
    if (array_key_exists('amount', $data)) { $fields[] = 'amount = ?'; $params[] = api_decimal($data, 'amount', 0); }
    if (array_key_exists('expense_date', $data)) { $fields[] = 'expense_date = ?'; $params[] = api_date($data, 'expense_date'); }
    if (array_key_exists('project_id', $data)) { $fields[] = 'project_id = ?'; $params[] = api_int($data, 'project_id'); }
    if (!$fields) throw new ApiException('No changes were supplied.', 422);
    $params[] = (int)$id;
    db()->prepare('UPDATE expenses SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    audit('expense_updated', 'expenses', (int)$id, 'Updated expense #' . $id);
    api_ok(['updated' => true]);
}

function admin_expense_delete(string $id): never
{
    api_require_admin();
    api_require_csrf();
    if (!ctype_digit($id)) throw new ApiException('Invalid expense.', 400);
    db()->prepare('DELETE FROM expenses WHERE id = ?')->execute([(int)$id]);
    audit('expense_deleted', 'expenses', (int)$id, 'Deleted expense #' . $id);
    api_ok(['deleted' => true]);
}

/* ------------------------------------------------------------------ */
/* Reports / settings / audit                                          */
/* ------------------------------------------------------------------ */

function admin_reports(): never
{
    $pdo = db();
    $revenue = $pdo->query(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, SUM(amount) total
         FROM payments WHERE status='confirmed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
         GROUP BY ym ORDER BY ym"
    )->fetchAll();
    $expenses = $pdo->query(
        "SELECT DATE_FORMAT(expense_date, '%Y-%m') ym, SUM(amount) total
         FROM expenses WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
         GROUP BY ym ORDER BY ym"
    )->fetchAll();
    $projectStatus = $pdo->query('SELECT status, COUNT(*) c FROM projects GROUP BY status')->fetchAll();
    $topClients = $pdo->query(
        "SELECT u.full_name, u.company, COALESCE(SUM(pay.amount),0) total
         FROM payments pay JOIN invoices i ON i.id=pay.invoice_id JOIN users u ON u.id=i.client_id
         WHERE pay.status='confirmed' GROUP BY u.id ORDER BY total DESC LIMIT 8"
    )->fetchAll();
    $invoiceSummary = $pdo->query(
        "SELECT status, COUNT(*) c, COALESCE(SUM(total),0) total, COALESCE(SUM(amount_paid),0) paid
         FROM invoices GROUP BY status"
    )->fetchAll();
    $expenseByCategory = $pdo->query(
        'SELECT category, SUM(amount) total FROM expenses GROUP BY category ORDER BY total DESC'
    )->fetchAll();

    api_ok([
        'revenue_by_month' => $revenue,
        'expenses_by_month' => $expenses,
        'projects_by_status' => $projectStatus,
        'top_clients' => $topClients,
        'invoice_summary' => $invoiceSummary,
        'expenses_by_category' => $expenseByCategory,
        'totals' => [
            'revenue' => (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed'")->fetchColumn(),
            'expenses' => (float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM expenses')->fetchColumn(),
            'outstanding' => (float)$pdo->query("SELECT COALESCE(SUM(total-amount_paid),0) FROM invoices WHERE status IN ('sent','partially_paid','overdue')")->fetchColumn(),
        ],
    ]);
}

function admin_settings_get(): never
{
    api_require_admin();
    $rows = db()->query('SELECT setting_key, setting_value FROM settings ORDER BY setting_key')->fetchAll();
    $out = [];
    foreach ($rows as $row) $out[$row['setting_key']] = $row['setting_value'];
    api_ok($out);
}

function admin_settings_update(): never
{
    api_require_admin();
    api_require_csrf();
    $data = api_body();
    $allowed = [
        'company_name', 'company_tagline', 'company_about', 'company_email', 'company_phone',
        'company_whatsapp', 'company_address', 'company_founder', 'company_hours', 'company_map_url',
        'company_fb', 'company_linkedin', 'company_x', 'currency', 'project_deposit',
        'registration_open', 'seo_keywords', 'site_announcement', 'support_email',
        'newsletter_enabled', 'payment_mtn_number', 'payment_airtel_number',
        'payment_bank_details', 'payment_gateway_status', 'invoices_due_days', 'tax_percent',
    ];
    $st = db()->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?,?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $updated = [];
    foreach ($allowed as $key) {
        if (array_key_exists($key, $data)) {
            $value = is_scalar($data[$key]) ? (string)$data[$key] : '';
            $st->execute([$key, $value]);
            $updated[] = $key;
        }
    }
    if (!$updated) throw new ApiException('No settings were supplied.', 422);
    audit('settings_updated', 'settings', null, 'Updated: ' . implode(', ', $updated));
    api_ok(['updated' => $updated]);
}

function admin_audit(array $user): never
{
    api_require_admin();
    $pdo = db();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $search = trim((string)($_GET['q'] ?? ''));
    $where = ' WHERE 1=1';
    $params = [];
    if ($search !== '') {
        $where .= ' AND (l.action LIKE ? OR l.details LIKE ? OR u.full_name LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like);
    }
    $count = $pdo->prepare('SELECT COUNT(*) FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id' . $where);
    $count->execute($params);
    $pg = api_pagination((int)$count->fetchColumn(), $page, 25);
    $sql = 'SELECT l.*, u.full_name FROM activity_logs l LEFT JOIN users u ON u.id=l.user_id' . $where .
           ' ORDER BY l.created_at DESC LIMIT ' . $pg['per_page'] . ' OFFSET ' . $pg['offset'];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    api_ok($st->fetchAll(), $pg);
}

function admin_notifications(array $user): never
{
    $st = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
    $st->execute([(int)$user['id']]);
    api_ok([
        'notifications' => $st->fetchAll(),
        'unread' => unread_notifications((int)$user['id']),
    ]);
}
