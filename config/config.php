<?php
/**
 * Reagan Soft Innovation Limited — application bootstrap.
 *
 * Every page loads this file first. It sets up the secure session,
 * security headers, error handling, database connection and the
 * shared helper library.
 */

declare(strict_types=1);

/* ------------------------------------------------------------------
 * Error handling (log technical errors, show friendly messages)
 * ------------------------------------------------------------------ */
define('APP_DEBUG', !empty(getenv('RSI_DEBUG')));

ini_set('display_errors', APP_DEBUG ? '1' : '0');
error_reporting(E_ALL);

define('STORAGE_DIR', __DIR__ . '/../storage');
define('LOG_DIR', STORAGE_DIR . '/logs');
define('APP_LOG_FILE', LOG_DIR . '/app.log');

if (!is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0775, true);
}

set_exception_handler(function (Throwable $e): void {
    log_error((string)$e);
    http_response_code(500);
    if (APP_DEBUG) {
        echo '<pre>' . htmlspecialchars((string)$e, ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo '<h1>Something went wrong</h1><p>Please try again or contact support.</p>';
    }
    exit;
});

function log_error(string $message): void {
    @file_put_contents(APP_LOG_FILE, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    @file_put_contents(APP_LOG_FILE, '  IP: ' . ($_SERVER['REMOTE_ADDR'] ?? '') . '  URI: ' . ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL, FILE_APPEND);
}

/* ------------------------------------------------------------------
 * Timezone
 * ------------------------------------------------------------------ */
date_default_timezone_set('Africa/Kampala');

/* ------------------------------------------------------------------
 * Secure session
 * ------------------------------------------------------------------ */
if (session_status() === PHP_SESSION_NONE) {
    session_name('rsi_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => rsi_request_scheme() === 'https',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ------------------------------------------------------------------
 * Security headers (applied on every page)
 * ------------------------------------------------------------------ */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()');
// Google Fonts (Space Grotesk / Inter / JetBrains Mono) are fetched from
// fonts.googleapis.com (stylesheets) and fonts.gstatic.com (font files).
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data:; font-src 'self' https://fonts.gstatic.com; connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
header('X-XSS-Protection: 1; mode=block');

/* ------------------------------------------------------------------
 * Base URL detection
 * ------------------------------------------------------------------
 * APP_URL is used for every link, stylesheet, script and image, so it
 * must always match the origin the page was actually requested on.
 * A hardcoded http:// base breaks as soon as the site is opened over
 * HTTPS: the browser reports every asset as mixed content and then
 * blocks it because the CSP compares the URL against 'self', which is
 * the HTTPS origin. The scheme, host and install path are therefore
 * derived from the request unless RSI_APP_URL says otherwise.
 * ------------------------------------------------------------------ */
function rsi_request_scheme(): string {
    $https = $_SERVER['HTTPS'] ?? '';
    if ($https !== '' && strtolower((string)$https) !== 'off') {
        return 'https';
    }
    if (strtolower(trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0])) === 'https') {
        return 'https';
    }
    return ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) ? 'https' : 'http';
}

function rsi_request_host(): string {
    $candidates = [(string)($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''), (string)($_SERVER['HTTP_HOST'] ?? '')];
    foreach ($candidates as $candidate) {
        $host = trim(explode(',', $candidate)[0]);
        // host, optional port, optional bracketed IPv6 literal — nothing else
        if ($host !== '' && preg_match('/^\[?[A-Za-z0-9._-]+\]?(?::[0-9]{1,5})?$/', $host) === 1) {
            return $host;
        }
    }
    return 'localhost';
}

/**
 * Install path below the document root: '' when the app sits in the web
 * root, '/reagansoft-innovation' when it sits in a sub-folder. Derived
 * from the filesystem, so it is correct on any host without config.
 */
function rsi_base_path(): string {
    $docRoot = rtrim(str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $appDir  = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    if ($docRoot === '' || $appDir === '' || stripos($appDir . '/', $docRoot . '/') !== 0) {
        return '';
    }
    return substr($appDir, strlen($docRoot));
}

/* ------------------------------------------------------------------
 * Application constants (override via environment in production)
 * ------------------------------------------------------------------ */
define('APP_NAME', 'Reagan Soft Innovation Limited');
define('APP_SHORT', 'RSI');
define('APP_URL', rtrim(rsi_env('RSI_APP_URL') ?: rsi_request_scheme() . '://' . rsi_request_host() . rsi_base_path(), '/'));
define('APP_FOUNDER', 'Reagan Otema');
define('APP_LOCATION', 'Jinja, Uganda');
define('APP_VERSION', '2.0.0');

/* ------------------------------------------------------------------
 * Database configuration
 * ------------------------------------------------------------------
 * ONE database is used for the whole application, named
 *
 *      reagansoft_clients
 *
 * The matching admin store
 *
 *      reagansoft_admin
 *
 * mirrors the same schema and data on the host account alongside
 * it. It is created and kept in step by install.php; set
 * RSI_DB_MIRROR to '' to skip it.
 *
 * Defaults below use the host-account credentials. To override for
 * another environment either:
 *
 *  1. edit the lines below, or
 *  2. set these environment variables on the server:
 *       RSI_DB_HOST  RSI_DB_NAME  RSI_DB_USER  RSI_DB_PASS  RSI_DB_MIRROR
 * ------------------------------------------------------------------ */
/**
 * Reads an environment variable, treating "set but empty" as a real
 * value rather than as "not set".
 *
 * The mirror database is the reason this exists. With the usual
 * `getenv('RSI_DB_MIRROR') ?: 'reagansoft_admin'`, setting the variable
 * to an empty string — the documented way to switch the mirror off on a
 * single-database host — was falsy, so PHP fell back to the default and
 * the mirror stayed switched on. rsi_env() returns '' for an empty
 * string, and DB_MIRROR's default is then never reached.
 *
 * @return string|null null only when the variable is genuinely absent
 */
function rsi_env(string $name): ?string
{
    $v = getenv($name);
    if ($v === false) { return null; }   // not set at all -> use the default
    return $v;                          // set, even to '' -> honour it
}

define('DB_HOST', rsi_env('RSI_DB_HOST') ?: 'localhost');
define('DB_NAME', rsi_env('RSI_DB_NAME') ?: 'reagansoft_clients');
define('DB_MIRROR', rsi_env('RSI_DB_MIRROR') ?? 'reagansoft_admin');
define('DB_USER', rsi_env('RSI_DB_USER') ?: 'reagansoft_reagansoft');
define('DB_PASS', rsi_env('RSI_DB_PASS') ?: 'Lovely2God');

define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024);        // 10 MB
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_WEB_PREFIX', '/uploads/');
define('LOGIN_MAX_ATTEMPTS', 6);
define('LOGIN_LOCK_MINUTES', 15);

/* ------------------------------------------------------------------
 * Database connection (PDO, exceptions, real prepared statements)
 * ------------------------------------------------------------------
 * The session time zone is pinned to East Africa Time (UTC+3, no
 * daylight saving) so NOW(), CURDATE() and every TIMESTAMP column
 * agree with PHP's Africa/Kampala clock. Without it a hosted MySQL
 * running in UTC silently disagrees with PHP by three hours, which
 * shows up as wrong timestamps, due dates and reports.
 * ------------------------------------------------------------------ */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                    PDO::ATTR_TIMEOUT            => 5,                       // fail fast, never hang the site
                    // EAT (UTC+3, no DST) so NOW()/CURDATE()/TIMESTAMP agree with PHP,
                    // and the same utf8mb4_unicode_ci collation the tables were built with.
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+03:00', NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]
            );
        } catch (PDOException $e) {
            db_connection_failed($e);
        }
    }
    return $pdo;
}

/**
 * True when the connection is alive. Used by the health check and
 * before a long export so a dead connection is reported as such
 * instead of as a random query error.
 */
function db_is_connected(): bool {
    try {
        db()->query('SELECT 1');
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * A dead database must never show a raw PDO message to a visitor.
 * Log the real reason, then show a short, honest maintenance notice.
 */
function db_connection_failed(Throwable $e): never {
    $reason = $e instanceof PDOException ? $e->getMessage() : 'unknown error';
    log_error('DB connection failed (' . DB_USER . '@' . DB_HOST . '/' . DB_NAME . '): ' . $reason);
    if (!headers_sent()) {
        http_response_code(503);
        header('Retry-After: 300');
    }
    $detail = APP_DEBUG
        ? '<pre style="text-align:left;white-space:pre-wrap;background:#0b1b2b;color:#cfe3f7;padding:16px;border-radius:10px;overflow:auto">'
          . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . '</pre>'
        : '<p style="opacity:.85">Our system is momentarily offline while we restore the connection. Please try again in a few minutes.</p>';
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<title>Temporarily offline · ' . APP_NAME . '</title>'
       . '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0b1b2b;color:#eaf4ff;'
       . 'font-family:"Segoe UI",system-ui,-apple-system,sans-serif;text-align:center;padding:24px}'
       . '.card{max-width:520px}h1{font-size:26px;margin:0 0 10px}p{line-height:1.6;margin:0 0 8px}'
       . 'a{color:#6fc8ff}</style></head><body><div class="card">'
       . '<h1>We are briefly offline</h1>' . $detail
       . '<p><a href="">Reload this page</a></p></div></body></html>';
    exit;
}

/* ------------------------------------------------------------------
 * Core helpers
 * ------------------------------------------------------------------ */
function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    $provided = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($provided) || !hash_equals($_SESSION['csrf'] ?? '', $provided)) {
        log_error('CSRF verification failed');
        http_response_code(419);
        exit('Security token expired. Please go back, refresh the page and try again.');
    }
}

function flash(string $key, ?string $value = null): ?string {
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function flash_errors(): array {
    $errors = $_SESSION['flash']['errors'] ?? [];
    unset($_SESSION['flash']['errors']);
    return is_array($errors) ? $errors : [];
}

function set_errors(array $errors): void {
    $_SESSION['flash']['errors'] = $errors;
}

function old(string $key, string $default = ''): string {
    return e($_SESSION['old'][$key] ?? $default);
}

function keep_old(array $fields = []): void {
    foreach ($fields as $f) {
        $_SESSION['old'][$f] = (string)($_POST[$f] ?? '');
    }
}

function clear_old(): void {
    unset($_SESSION['old']);
}

/**
 * Absolute URL for a path inside the app, always on the origin the
 * current request came in on, so assets never trip mixed content or
 * the 'self' source in the Content Security Policy.
 */
function app_url(string $path = ''): string {
    return APP_URL . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

/* ------------------------------------------------------------------
 * Authentication / authorization
 * ------------------------------------------------------------------ */
function current_user(): ?array {
    static $user = false;
    if ($user === false) {
        if (empty($_SESSION['user_id'])) {
            $user = null;
        } else {
            $st = db()->prepare('SELECT * FROM users WHERE id = ? AND active = 1 LIMIT 1');
            $st->execute([(int)$_SESSION['user_id']]);
            $user = $st->fetch() ?: null;
        }
    }
    return $user;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function user_role(): ?string {
    $u = current_user();
    return $u['role'] ?? null;
}

function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '';
        flash('error', 'Please sign in to continue.');
        redirect(app_url('login.php'));
    }
}

function require_role(string $role): void {
    require_login();
    if (user_role() !== $role) {
        http_response_code(403);
        exit('You are not authorised to view this page.');
    }
}

function require_client(): void {
    require_role('client');
}

function require_staff(): void {
    require_login();
    if (!in_array(user_role(), ['admin', 'staff'], true)) {
        http_response_code(403);
        exit('Staff access required.');
    }
}

function require_admin(): void {
    require_role('admin');
}

function can_manage_projects(): bool {
    return in_array(user_role(), ['admin', 'staff'], true);
}

function can_use_admin_console(): bool {
    return in_array(user_role(), ['admin', 'staff'], true);
}

/* ------------------------------------------------------------------
 * Shared library
 * ------------------------------------------------------------------ */
require_once __DIR__ . '/../inc/functions.php';