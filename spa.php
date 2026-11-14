<?php
declare(strict_types=1);

/**
 * React SPA shell.
 *
 * Serves the Vite build from /app with the install path injected as meta
 * tags, so the same build works at the domain root or in a subfolder such
 * as /reagansoft-innovation.
 */

$root = __DIR__;
$build = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'index.html';

if (!is_file($build)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "The application has not been built yet.\n\nRun: cd frontend && npm install && npm run build\n";
    exit;
}

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/spa.php');
$base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($base === '.' || $base === '/') {
    $base = '';
}

$assetBase = $base . '/assets/img';
$apiBase = $base . '/api/v1';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$forwardedProto = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
if (in_array($forwardedProto, ['http', 'https'], true)) {
    $scheme = $forwardedProto;
}
$host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'reagansoftinnovation.com'));
$origin = $scheme . '://' . $host;
$absoluteAssetBase = $origin . $assetBase;
$currentPath = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?');
$canonicalUrl = $origin . $currentPath;

$html = (string) file_get_contents($build);

$inject = implode("\n    ", [
    '<base href="' . htmlspecialchars($base . '/app/', ENT_QUOTES, 'UTF-8') . '">',
    '<meta name="rsi-base" content="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '">',
    '<meta name="rsi-api-base" content="' . htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8') . '">',
    '<meta name="rsi-asset-base" content="' . htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') . '">',
    '<link rel="icon" type="image/png" sizes="32x32" href="' . htmlspecialchars($assetBase . '/favicon-32.png', ENT_QUOTES, 'UTF-8') . '">',
    '<link rel="icon" type="image/png" sizes="16x16" href="' . htmlspecialchars($assetBase . '/favicon-16.png', ENT_QUOTES, 'UTF-8') . '">',
    '<link rel="apple-touch-icon" sizes="180x180" href="' . htmlspecialchars($assetBase . '/apple-180.png', ENT_QUOTES, 'UTF-8') . '">',
    '<link rel="manifest" href="' . htmlspecialchars($assetBase . '/site.webmanifest', ENT_QUOTES, 'UTF-8') . '">',
    '<meta property="og:url" content="' . htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') . '">',
    '<meta property="og:image" content="' . htmlspecialchars($absoluteAssetBase . '/og-image.jpg', ENT_QUOTES, 'UTF-8') . '">',
    '<meta property="og:image:width" content="1200">',
    '<meta property="og:image:height" content="630">',
    '<meta name="twitter:card" content="summary_large_image">',
    '<meta name="twitter:image" content="' . htmlspecialchars($absoluteAssetBase . '/og-image.jpg', ENT_QUOTES, 'UTF-8') . '">',
    '<meta name="apple-mobile-web-app-capable" content="yes">',
    '<meta name="apple-mobile-web-app-status-bar-style" content="default">',
    '<meta name="apple-mobile-web-app-title" content="Reagansoft">',
    '<meta name="mobile-web-app-capable" content="yes">',
]);

$html = preg_replace('/<head>/i', "<head>\n    " . $inject . "\n", $html, 1) ?? $html;

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
echo $html;

