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
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
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
 * Application constants (override via environment in production)
 * ------------------------------------------------------------------ */
define('APP_NAME', 'Reagan Soft Innovation Limited');
define('APP_SHORT', 'RSI');
define('APP_URL', rtrim((string)getenv('RSI_APP_URL') ?: 'http://localhost/reagansoft-innovation', '/'));
define('APP_FOUNDER', 'Reagan Otema');
define('APP_LOCATION', 'Jinja, Uganda');
define('APP_VERSION', '2.0.0');

/* ------------------------------------------------------------------
 * Database configuration
 * ------------------------------------------------------------------
 * ONE database is used for the whole application, named
 *
 *      reagan_soft_innovation
 *
 * Defaults below are for a local XAMPP install (user `root`, no
 * password). To point the app at a hosted database, either:
 *
 *  1. edit the four lines below, or
 *  2. set these environment variables on the server:
 *       RSI_DB_HOST  RSI_DB_NAME  RSI_DB_USER  RSI_DB_PASS
 * ------------------------------------------------------------------ */
define('DB_HOST', getenv('RSI_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('RSI_DB_NAME') ?: 'reagan_soft_innovation');
define('DB_USER', getenv('RSI_DB_USER') ?: 'root');
define('DB_PASS', getenv('RSI_DB_PASS') ?: '');

define('MAX_UPLOAD_BYTES', 10 * 1024 * 1024);        // 10 MB
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_WEB_PREFIX', '/uploads/');
define('LOGIN_MAX_ATTEMPTS', 6);
define('LOGIN_LOCK_MINUTES', 15);

/* ------------------------------------------------------------------
 * Database connection (PDO, exceptions, real prepared statements)
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
                ]
            );
        } catch (PDOException $e) {
            log_error('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Unable to connect to the database. Please check the installation.');
        }
    }
    return $pdo;
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