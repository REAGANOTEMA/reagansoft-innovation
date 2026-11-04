<?php
/**
 * Reagan Soft Innovation Limited — REST API v1 front controller.
 *
 * Request:
 *   GET  /api/v1/public/site
 *   POST /api/v1/auth/login
 *   GET  /api/v1/admin/overview
 *
 * Responses always use:
 *   { "ok": true,  "data": ..., "meta": ... }
 *   { "ok": false, "error": "...", "errors": { ... } }
 *
 * Authentication uses the existing secure PHP session. Mutating requests
 * must send X-CSRF-Token (returned by /auth/session and /public/site).
 */

declare(strict_types=1);

require_once __DIR__ . '/lib.php';

set_exception_handler(function (Throwable $e): void {
    if ($e instanceof ApiException) {
        api_error($e->getMessage(), $e->status, $e->errors);
    }
    log_error('API unhandled: ' . (string)$e);
    $detail = APP_DEBUG ? $e->getMessage() : 'An unexpected server error occurred.';
    api_error($detail, 500);
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function api_route(): string
{
    if (!empty($_GET['route'])) {
        $route = (string)$_GET['route'];
    } else {
        $path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $base = (string)(parse_url(APP_URL, PHP_URL_PATH) ?: '');
        $prefix = rtrim($base, '/') . '/api/v1';
        if (str_starts_with($path, $prefix)) {
            $route = substr($path, strlen($prefix));
        } elseif (isset($_SERVER['PATH_INFO'])) {
            $route = (string)$_SERVER['PATH_INFO'];
        } else {
            $route = '';
        }
    }
    $route = '/' . trim($route, '/');
    $route = preg_replace('#/+#', '/', $route) ?: '/';
    return $route;
}

function api_segments(string $route): array
{
    $parts = array_values(array_filter(explode('/', trim($route, '/')), static fn($p) => $p !== ''));
    return array_map('urldecode', $parts);
}

$route = api_route();
$segments = api_segments($route);
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'OPTIONS') {
    header('Allow: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/routes/public.php';
require_once __DIR__ . '/routes/auth.php';
require_once __DIR__ . '/routes/client.php';
require_once __DIR__ . '/routes/admin.php';

$domain = $segments[0] ?? '';

try {
    $handled = match ($domain) {
        'public' => handle_public($method, $segments),
        'auth' => handle_auth($method, $segments),
        'client' => handle_client($method, $segments),
        'admin' => handle_admin($method, $segments),
        default => false,
    };
    if (!$handled) {
        throw new ApiException('Endpoint not found.', 404);
    }
    throw new ApiException('The route did not produce a response.', 500);
} catch (ApiException $e) {
    api_error($e->getMessage(), $e->status, $e->errors);
} catch (Throwable $e) {
    log_error('API ' . $method . ' ' . $route . ': ' . (string)$e);
    api_error(APP_DEBUG ? $e->getMessage() : 'An unexpected server error occurred.', 500);
}
