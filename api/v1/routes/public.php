<?php
/**
 * Public website endpoints — no authentication required.
 */

declare(strict_types=1);

function handle_public(string $method, array $seg): bool
{
    $what = $seg[1] ?? '';

    if ($what === 'site' && $method === 'GET') {
        public_site();
    }
    if ($what === 'services' && $method === 'GET') {
        if (isset($seg[2])) {
            public_service_detail($seg[2]);
        }
        public_services();
    }
    if ($what === 'portfolio' && $method === 'GET') {
        if (isset($seg[2])) {
            public_portfolio_detail($seg[2]);
        }
        public_portfolio();
    }
    if ($what === 'contact' && $method === 'POST') {
        public_contact();
    }
    if ($what === 'newsletter' && $method === 'POST') {
        public_newsletter();
    }
    if ($what === 'session' && $method === 'GET') {
        api_ok([
            'user' => api_user(),
            'csrf' => api_csrf_token(),
        ]);
    }
    return false;
}

function public_site(): never
{
    $pdo = db();

    $services = $pdo->query(
        "SELECT * FROM services WHERE status = 'active' ORDER BY sort_order, name"
    )->fetchAll();

    $portfolio = $pdo->query(
        "SELECT p.id, p.ref_no, p.title, p.description, p.deliverables, p.cover_art,
                p.website_url, p.completed_at, p.progress, s.name AS service, s.slug AS service_slug
         FROM projects p
         LEFT JOIN services s ON s.id = p.service_id
         WHERE p.status = 'COMPLETED'
         ORDER BY p.completed_at DESC, p.id DESC
         LIMIT 12"
    )->fetchAll();

    $stats = [
        'projects' => (int)$pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
        'completed' => (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'COMPLETED'")->fetchColumn(),
        'clients' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client' AND active = 1")->fetchColumn(),
        'services' => count($services),
    ];

    api_ok([
        'settings' => api_public_settings(),
        'services' => array_map('api_public_service', $services),
        'portfolio' => $portfolio,
        'stats' => $stats,
        'csrf' => api_csrf_token(),
        'user' => api_user(),
        'server_time' => date('c'),
    ]);
}

function public_services(): never
{
    $rows = db()->query(
        "SELECT * FROM services WHERE status = 'active' ORDER BY sort_order, name"
    )->fetchAll();
    api_ok(array_map('api_public_service', $rows));
}

function public_service_detail(string $key): never
{
    $pdo = db();
    if (ctype_digit($key)) {
        $st = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 'active' LIMIT 1");
        $st->execute([(int)$key]);
    } else {
        $st = $pdo->prepare("SELECT * FROM services WHERE slug = ? AND status = 'active' LIMIT 1");
        $st->execute([$key]);
    }
    $row = $st->fetch();
    if (!$row) {
        throw new ApiException('Service not found.', 404);
    }
    $related = $pdo->query(
        "SELECT * FROM services WHERE status = 'active' AND id <> " . (int)$row['id'] . " ORDER BY sort_order LIMIT 3"
    )->fetchAll();
    api_ok([
        'service' => api_public_service($row),
        'related' => array_map('api_public_service', $related),
    ]);
}

function public_portfolio(): never
{
    $pdo = db();
    $category = trim((string)($_GET['category'] ?? ''));
    $sql = "SELECT p.id, p.ref_no, p.title, p.description, p.deliverables, p.cover_art,
                   p.website_url, p.completed_at, p.progress, p.status,
                   s.name AS service, s.slug AS service_slug
            FROM projects p LEFT JOIN services s ON s.id = p.service_id
            WHERE p.status = 'COMPLETED'";
    $params = [];
    if ($category !== '') {
        $sql .= ' AND (s.slug = ? OR p.cover_art = ?)';
        $params = [$category, $category];
    }
    $sql .= ' ORDER BY p.completed_at DESC, p.id DESC';
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();

    $cats = $pdo->query(
        "SELECT DISTINCT s.slug, s.name FROM projects p JOIN services s ON s.id = p.service_id
         WHERE p.status = 'COMPLETED' ORDER BY s.name"
    )->fetchAll();

    api_ok(['projects' => $rows, 'categories' => $cats]);
}

function public_portfolio_detail(string $id): never
{
    if (!ctype_digit($id)) {
        throw new ApiException('Invalid project reference.', 400);
    }
    $st = db()->prepare(
        "SELECT p.id, p.ref_no, p.title, p.description, p.requirements, p.deliverables,
                p.cover_art, p.website_url, p.completed_at, p.started_at, p.progress,
                p.status, s.name AS service, s.slug AS service_slug
         FROM projects p LEFT JOIN services s ON s.id = p.service_id
         WHERE p.id = ? AND p.status = 'COMPLETED' LIMIT 1"
    );
    $st->execute([(int)$id]);
    $row = $st->fetch();
    if (!$row) {
        throw new ApiException('Project not found.', 404);
    }
    api_ok($row);
}

function public_contact(): never
{
    api_method('POST');
    api_require_csrf();

    $data = api_body();
    if (!empty($data['company_website'])) {
        api_ok(['received' => true, 'message' => 'Thank you. Your message has been received.']);
    }

    $name = api_string($data, 'full_name', 'Full name', 150, true);
    $email = api_string($data, 'email', 'Email', 190, true);
    $phone = api_string($data, 'phone', 'Phone', 40, true);
    $company = api_string($data, 'company', 'Company', 190);
    $subject = api_string($data, 'subject', 'Subject', 190);
    $message = api_string($data, 'message', 'Message', 5000, true);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ApiException('Please enter a valid email address.', 422, ['email' => 'Invalid email address.']);
    }
    if (mb_strlen($message) < 10) {
        throw new ApiException('Please write a little more about your project.', 422, ['message' => 'At least 10 characters.']);
    }

    try {
        db()->prepare(
            'INSERT INTO contact_messages (full_name, email, phone, company, subject, message, ip_address)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([$name, $email, $phone, $company ?: null, $subject ?: null, $message, $_SERVER['REMOTE_ADDR'] ?? null]);
        $id = (int)db()->lastInsertId();
        audit('contact_message_sent', 'contact_messages', $id, 'Message from ' . $name);
        notify_staff('New website enquiry', $name . ' sent a message about ' . ($subject ?: 'a project'), 'info');
    } catch (Throwable $e) {
        log_error('API contact insert: ' . $e->getMessage());
        throw new ApiException('We could not save your message right now. Please try again shortly.', 503);
    }

    api_ok(['received' => true, 'message' => 'Thank you. Our team will respond shortly.'], null, 201);
}

function public_newsletter(): never
{
    api_method('POST');
    api_require_csrf();
    $data = api_body();
    $email = strtolower(api_string($data, 'email', 'Email', 190, true));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ApiException('Please enter a valid email address.', 422, ['email' => 'Invalid email address.']);
    }
    $name = api_string($data, 'full_name', 'Name', 150);
    db()->prepare(
        "INSERT INTO newsletter_subscribers (email, full_name, source) VALUES (?,?,?)
         ON DUPLICATE KEY UPDATE is_active = 1, full_name = COALESCE(NULLIF(VALUES(full_name), ''), full_name)"
    )->execute([$email, $name ?: null, 'website']);
    api_ok(['subscribed' => true, 'message' => 'You are subscribed to Reagansoft insights.']);
}
