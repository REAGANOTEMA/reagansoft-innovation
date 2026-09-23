<?php
/**
 * Protected project file download.
 * Files are never served directly from the web root — access is always
 * verified here against the current user's role and project ownership.
 */
require __DIR__ . '/config/config.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$st = db()->prepare('SELECT * FROM project_files WHERE id = ? LIMIT 1');
$st->execute([$id]);
$file = $st->fetch();

if (!$file) {
    http_response_code(404);
    exit('File not found.');
}

// Load the project to enforce ownership / role checks.
$st = db()->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
$st->execute([(int)$file['project_id']]);
$project = $st->fetch();

if (!$project || !user_can_access_project($project)) {
    http_response_code(403);
    exit('You are not authorised to download this file.');
}

$abs = realpath(UPLOAD_DIR . $file['file_path']);
$uploadRoot = realpath(UPLOAD_DIR);
if ($abs === false || $uploadRoot === false || strncmp($abs, $uploadRoot, strlen($uploadRoot)) !== 0) {
    http_response_code(404);
    exit('File not found.');
}
if (!is_file($abs)) {
    http_response_code(404);
    exit('File not found.');
}

audit('file_downloaded', 'project_files', (int)$file['id'], 'Downloaded ' . $file['original_name']);

$fname = str_replace(['"', "\r", "\n", "\\"], '_', $file['original_name']);
header('Content-Type: ' . (preg_match('#^[a-z]+/[a-z0-9.+-]+$#i', $file['mime_type']) ? $file['mime_type'] : 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . $fname . '"');
header('Content-Length: ' . (string)filesize($abs));
header('X-Content-Type-Options: nosniff');
while (ob_get_level()) { ob_end_clean(); }
readfile($abs);
exit;