<?php
/**
 * Reagan Soft Innovation Limited — REST API helpers (v1).
 *
 * Shared by every API route. Uses the existing application configuration,
 * PDO connection, session, CSRF tokens, audit trail and upload validation.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

final class ApiException extends RuntimeException
{
    public int $status;
    public array $errors;

    public function __construct(string $message, int $status = 400, array $errors = [])
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errors = $errors;
    }
}

function api_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function api_ok(mixed $data = null, ?array $meta = null, int $status = 200): never
{
    $payload = ['ok' => true, 'data' => $data];
    if ($meta !== null) {
        $payload['meta'] = $meta;
    }
    api_response($payload, $status);
}

function api_error(string $message, int $status = 400, array $errors = []): never
{
    api_response(['ok' => false, 'error' => $message, 'errors' => $errors], $status);
}

function api_method(string ...$allowed): string
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $override = strtoupper((string)($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? ''));
    if ($override !== '') {
        $method = $override;
    }
    if (!in_array($method, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        throw new ApiException('Method not allowed.', 405);
    }
    return $method;
}

/** Raw request body decoded from JSON, or $_POST for form/multipart requests. */
function api_body(): array
{
    static $body = null;
    if ($body !== null) {
        return $body;
    }
    $type = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($type, 'application/json')) {
        $raw = file_get_contents('php://input');
        $decoded = $raw === '' ? [] : json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new ApiException('The request body is not valid JSON.', 400);
        }
        $body = $decoded;
    } else {
        $body = $_POST;
    }
    return $body;
}

function api_scalar(array $data, string $key, mixed $default = null): mixed
{
    return array_key_exists($key, $data) ? $data[$key] : $default;
}

function api_string(array $data, string $key, string $label, int $max, bool $required = false, string $default = ''): string
{
    $value = trim((string)api_scalar($data, $key, $default));
    if ($required && $value === '') {
        throw new ApiException($label . ' is required.', 422, [$key => $label . ' is required.']);
    }
    if ($max > 0 && mb_strlen($value) > $max) {
        throw new ApiException($label . ' is too long.', 422, [$key => 'Maximum ' . $max . ' characters.']);
    }
    return $value;
}

function api_int(array $data, string $key, ?int $default = null): ?int
{
    $value = api_scalar($data, $key, null);
    if ($value === null || $value === '') {
        return $default;
    }
    if (!is_numeric($value)) {
        throw new ApiException('Invalid number for ' . $key . '.', 422, [$key => 'Must be a number.']);
    }
    return (int)$value;
}

function api_decimal(array $data, string $key, float $default = 0.0): float
{
    $value = api_scalar($data, $key, null);
    if ($value === null || $value === '') {
        return $default;
    }
    if (!is_numeric($value)) {
        throw new ApiException('Invalid amount for ' . $key . '.', 422, [$key => 'Must be a number.']);
    }
    return round((float)$value, 2);
}

function api_date(array $data, string $key): ?string
{
    $value = trim((string)api_scalar($data, $key, ''));
    if ($value === '') {
        return null;
    }
    $ts = strtotime($value);
    if ($ts === false) {
        throw new ApiException('Invalid date for ' . $key . '.', 422, [$key => 'Use YYYY-MM-DD.']);
    }
    return date('Y-m-d', $ts);
}

function api_bool(array $data, string $key, bool $default = false): bool
{
    $value = api_scalar($data, $key, $default);
    return in_array($value, [true, 1, '1', 'true', 'yes', 'on'], true);
}

function api_in_enum(string $value, array $allowed, string $label): string
{
    if (!in_array($value, $allowed, true)) {
        throw new ApiException('Invalid ' . $label . '.', 422, ['value' => 'Invalid ' . $label . '.']);
    }
    return $value;
}

function api_user(): ?array
{
    return current_user();
}

function api_require_login(): array
{
    $user = api_user();
    if ($user === null) {
        throw new ApiException('Please sign in to continue.', 401);
    }
    return $user;
}

function api_require_role(string ...$roles): array
{
    $user = api_require_login();
    if (!in_array((string)$user['role'], $roles, true)) {
        throw new ApiException('You are not authorised to perform this action.', 403);
    }
    return $user;
}

function api_require_staff(): array
{
    return api_require_role('admin', 'staff');
}

function api_require_admin(): array
{
    return api_require_role('admin');
}

function api_csrf_token(): string
{
    return csrf_token();
}

function api_require_csrf(): void
{
    $provided = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? ''));
    $expected = (string)($_SESSION['csrf'] ?? '');
    if ($provided === '' || $expected === '' || !hash_equals($expected, $provided)) {
        audit('csrf_rejected', null, null, 'API request with an invalid security token');
        throw new ApiException('Your security token expired. Refresh the page and try again.', 419);
    }
}

function api_rate_limit_login(string $email): void
{
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip !== '') {
        $st = db()->prepare(
            "SELECT COUNT(*) FROM activity_logs
             WHERE action = 'login_failed' AND ip_address = ? AND created_at > (NOW() - INTERVAL 15 MINUTE)"
        );
        $st->execute([$ip]);
        if ((int)$st->fetchColumn() >= 25) {
            throw new ApiException('Too many sign-in attempts from this network. Try again in 15 minutes.', 429);
        }
    }
}

function api_apply_login_audit(string $action, string $details): void
{
    try {
        db()->prepare(
            'INSERT INTO activity_logs (user_id, action, entity_type, details, ip_address) VALUES (?,?,?,?,?)'
        )->execute([null, $action, 'auth', $details, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) {
        log_error('api login audit: ' . $e->getMessage());
    }
}

function api_pagination(int $total, int $page, int $perPage): array
{
    $totalPages = max(1, (int)ceil($total / max(1, $perPage)));
    $page = max(1, min($totalPages, $page));
    return [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'offset' => ($page - 1) * $perPage,
    ];
}

function api_public_settings(): array
{
    $keys = [
        'company_name', 'company_tagline', 'company_about', 'company_email',
        'company_phone', 'company_phone_alt', 'company_whatsapp', 'company_address', 'company_founder',
        'company_hours', 'company_map_url', 'company_fb', 'company_linkedin',
        'company_x', 'currency', 'project_deposit', 'registration_open',
        'seo_keywords', 'site_announcement', 'support_email', 'newsletter_enabled',
    ];
    $out = [];
    foreach ($keys as $key) {
        $out[$key] = settings($key, '');
    }
    return $out;
}

function api_public_service(array $row): array
{
    $features = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string)($row['features'] ?? '')))));
    return [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'description' => $row['description'],
        'price_min' => (float)$row['price_min'],
        'price_max' => $row['price_max'] !== null ? (float)$row['price_max'] : null,
        'currency' => $row['currency'],
        'pricing_type' => $row['pricing_type'],
        'price_note' => $row['price_note'],
        'price_display' => service_price_display($row),
        'price_short' => service_price_short($row),
        'features' => $features,
        'delivery_days' => $row['delivery_days'] !== null ? (int)$row['delivery_days'] : null,
        'icon' => $row['icon'],
    ];
}

function api_project_statuses(): array
{
    return project_statuses();
}

/**
 * Normalises a SQL row for JSON: casts numeric fields that the frontend
 * expects as numbers and leaves everything else as text.
 */
function api_row(array $row, array $ints = [], array $floats = []): array
{
    foreach ($ints as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null) {
            $row[$key] = (int)$row[$key];
        }
    }
    foreach ($floats as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null) {
            $row[$key] = (float)$row[$key];
        }
    }
    return $row;
}

function api_notify(int $userId, string $title, string $message, string $type = 'info', ?int $projectId = null): void
{
    notify($userId, $title, $message, $type, $projectId);
}
