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
$base = rtrim(str_replace('/spa.php', '', $scriptName), '/');
if ($base === '.' || $base === '/') {
    $base = '';
}

$assetBase = $base . '/assets/img';
$apiBase = $base . '/api/v1';
$html = (string) file_get_contents($build);

$inject = implode("\n    ", [
    '<base href="' . htmlspecialchars($base . '/app/', ENT_QUOTES, 'UTF-8') . '">',
    '<meta name="rsi-base" content="' . htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '">',
    '<meta name="rsi-api-base" content="' . htmlspecialchars($apiBase, ENT_QUOTES, 'UTF-8') . '">',
    '<meta name="rsi-asset-base" content="' . htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') . '">',
]);

$html = preg_replace('/<head>/i', "<head>\n    " . $inject . "\n", $html, 1) ?? $html;

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
echo $html;
