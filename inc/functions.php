<?php
/**
 * Reagan Soft Innovation Limited — shared helpers.
 * Loaded from config/config.php.
 */

/* ------------------------------------------------------------------
 * Settings
 * ------------------------------------------------------------------ */
function settings(?string $key = null, ?string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = ['company_name' => APP_NAME, 'company_phone' => '+256730314979', 'currency' => 'UGX'];
        try {
            $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $r) {
                $cache[$r['setting_key']] = (string)$r['setting_value'];
            }
        } catch (Throwable $e) {
            log_error('settings() fallback: ' . $e->getMessage());
        }
    }
    if ($key === null) {
        return '';
    }
    return $cache[$key] ?? $default;
}

/* ------------------------------------------------------------------
 * Formatting
 * ------------------------------------------------------------------ */
function money(mixed $amount, string $currency = 'UGX'): string {
    $amount = (float)$amount;
    if ($currency !== '') {
        return $currency . ' ' . number_format($amount, 0);
    }
    return number_format($amount, 0);
}

function price_range(mixed $min, mixed $max, string $currency = 'UGX'): string {
    $min = (float)$min;
    $max = (float)$max;
    if ($max > 0 && $max > $min) {
        return ltrim(money($min, $currency), $currency . ' ') . ' – ' . money($max, $currency);
    }
    return money($min, $currency);
}

function fmt_date(?string $date, string $format = 'd M Y'): string {
    if (!$date) {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '—';
}

function time_ago(?string $datetime): string {
    if (!$datetime) {
        return '—';
    }
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return intdiv($diff, 60) . ' min ago';
    if ($diff < 86400) return intdiv($diff, 3600) . ' hr ago';
    if ($diff < 604800) return intdiv($diff, 86400) . ' day' . (intdiv($diff, 86400) === 1 ? '' : 's') . ' ago';
    return date('d M Y', $ts);
}

function truncate(string $text, int $length = 90): string {
    return mb_strimwidth($text, 0, $length, '…');
}

/* ------------------------------------------------------------------
 * Project reference numbers  (RSI-2026-00001)
 * ------------------------------------------------------------------ */
function next_project_ref(PDO $pdo): string {
    $year = date('Y');
    $pdo->exec("SET @row := 0");
    $st = $pdo->prepare(
        "SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(ref_no,'-',-1) AS UNSIGNED)),0) + 1 AS n
         FROM projects WHERE ref_no LIKE :prefix"
    );
    $prefix = 'RSI-' . $year . '-';
    $st->execute([':prefix' => $prefix . '%']);
    $next = (int)$st->fetch()['n'];
    return $prefix . str_pad((string)$next, 5, '0', STR_PAD_LEFT);
}

function next_doc_no(PDO $pdo, string $table, string $column, string $prefix): string {
    $st = $pdo->prepare("SELECT COUNT(*) + 1 AS n FROM `" . $table . "` WHERE `" . $column . "` LIKE :pfx");
    $pfx = $prefix . '%';
    $st->execute([':pfx' => $pfx]);
    return $prefix . str_pad((string)(int)$st->fetch()['n'], 4, '0', STR_PAD_LEFT);
}

/* ------------------------------------------------------------------
 * Project status
 * ------------------------------------------------------------------ */
function project_statuses(): array {
    return ['NEW', 'REVIEWING', 'QUOTATION', 'APPROVED', 'IN_PROGRESS', 'WAITING_FOR_CLIENT', 'TESTING', 'COMPLETED', 'CANCELLED'];
}

function project_priority_labels(): array {
    return ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'];
}

function status_badge(string $status): string {
    $class = [
        'NEW' => 'blue', 'REVIEWING' => 'sky', 'QUOTATION' => 'violet',
        'APPROVED' => 'cyan', 'IN_PROGRESS' => 'blue', 'WAITING_FOR_CLIENT' => 'amber',
        'TESTING' => 'violet', 'COMPLETED' => 'green', 'CANCELLED' => 'red',
        'TODO' => 'gray', 'REVIEW' => 'violet',
        'draft' => 'gray', 'sent' => 'blue', 'accepted' => 'green', 'rejected' => 'red', 'expired' => 'amber', 'cancelled' => 'red',
        'paid' => 'green', 'partially_paid' => 'amber', 'overdue' => 'red',
        'active' => 'green', 'inactive' => 'red',
        'read' => 'gray', 'unread' => 'blue',
    ];
    $label = ucwords(str_replace('_', ' ', strtolower((string)$status)));
    return '<span class="badge ' . ($class[$status] ?? 'gray') . '">' . e($label) . '</span>';
}

function priority_badge(string $priority): string {
    $map = ['low' => 'gray', 'normal' => 'sky', 'medium' => 'sky', 'high' => 'amber', 'urgent' => 'red'];
    return '<span class="badge ' . ($map[$priority] ?? 'gray') . '">' . e(ucfirst($priority)) . '</span>';
}

/* ------------------------------------------------------------------
 * Notifications
 * ------------------------------------------------------------------ */
function notify(int $userId, string $title, string $message, string $type = 'info', ?int $projectId = null): void {
    db()->prepare(
        'INSERT INTO notifications (user_id, title, message, type, related_project_id) VALUES (?,?,?,?,?)'
    )->execute([$userId, $title, $message, $type, $projectId]);
}

function notify_staff(string $title, string $message, string $type = 'info', ?int $projectId = null): void {
    try {
        $rows = db()->query("SELECT id FROM users WHERE role IN ('admin','staff') AND active = 1")->fetchAll();
        $st = db()->prepare('INSERT INTO notifications (user_id, title, message, type, related_project_id) VALUES (?,?,?,?,?)');
        foreach ($rows as $r) {
            $st->execute([(int)$r['id'], $title, $message, $type, $projectId]);
        }
    } catch (Throwable $e) {
        log_error('notify_staff: ' . $e->getMessage());
    }
}

function unread_notifications(?int $userId = null): int {
    $uid = $userId ?? (int)(current_user()['id'] ?? 0);
    if (!$uid) return 0;
    $st = db()->prepare('SELECT COUNT(*) c FROM notifications WHERE user_id = ? AND is_read = 0');
    $st->execute([$uid]);
    return (int)$st->fetch()['c'];
}

/* ------------------------------------------------------------------
 * Audit log
 * ------------------------------------------------------------------ */
function audit(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void {
    try {
        db()->prepare(
            'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?,?,?,?,?,?)'
        )->execute([
            (int)(current_user()['id'] ?? 0) ?: null,
            $action,
            $entityType,
            $entityId,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        log_error('audit() failed: ' . $e->getMessage());
    }
}

/* ------------------------------------------------------------------
 * Secure file uploads
 * ------------------------------------------------------------------ */
const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'zip'];
const ALLOWED_MIME_GROUPS = [
    'pdf'  => ['application/pdf'],
    'doc'  => ['application/msword'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'xls'  => ['application/vnd.ms-excel'],
    'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    'ppt'  => ['application/vnd.ms-powerpoint'],
    'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
    'txt'  => ['text/plain'],
    'csv'  => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
    'png'  => ['image/png'],
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'gif'  => ['image/gif'],
    'webp' => ['image/webp'],
    'zip'  => ['application/zip', 'application/x-zip-compressed'],
];

function upload_file(array $file): array {
    // Returns ['ok' => bool, 'error' => string, 'path' => rel, 'name' => original, 'stored' => stored, 'mime' => string, 'size' => int]
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'No file selected.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'The file could not be uploaded (error code ' . $file['error'] . ').'];
    }
    if ((int)$file['size'] > MAX_UPLOAD_BYTES) {
        return ['ok' => false, 'error' => 'File is too large. Maximum allowed is ' . (MAX_UPLOAD_BYTES / 1048576) . ' MB.'];
    }
    if ((int)$file['size'] === 0) {
        return ['ok' => false, 'error' => 'The file is empty.'];
    }

    $original = basename((string)$file['name']);
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if ($original === '' || $ext === '' || !in_array($ext, ALLOWED_EXTENSIONS, true)) {
        return ['ok' => false, 'error' => 'File type not allowed. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS) . '.'];
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) finfo_close($finfo);
        if ($detected) $mime = $detected;
    }

    $allowedMimes = ALLOWED_MIME_GROUPS[$ext] ?? [];
    if ($mime !== '' && $allowedMimes && !in_array($mime, $allowedMimes, true)) {
        return ['ok' => false, 'error' => 'File content does not match its extension.'];
    }

    // Prevent script/file uploads regardless of extension.
    $blocked = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'sh', 'htaccess'];
    if (in_array($ext, $blocked, true)) {
        return ['ok' => false, 'error' => 'This file type is not allowed for security reasons.'];
    }

    $year = date('Y');
    $subdir = 'files/' . $year;
    $dir = UPLOAD_DIR . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        return ['ok' => false, 'error' => 'Could not create the upload directory.'];
    }

    $stored = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $stored)) {
        return ['ok' => false, 'error' => 'The file could not be stored. Please try again.'];
    }

    return [
        'ok'      => true,
        'error'   => '',
        'path'    => $subdir . '/' . $stored,
        'name'    => $original,
        'stored'  => $stored,
        'mime'    => $mime ?: 'application/octet-stream',
        'size'    => (int)$file['size'],
    ];
}

function pretty_size(int $bytes): string {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 0) . ' KB';
    return $bytes . ' B';
}

function user_can_access_project(array $project): bool {
    $role = user_role();
    if (in_array($role, ['admin', 'staff'], true)) return true;
    return (int)$project['client_id'] === (int)(current_user()['id'] ?? 0);
}

/* ------------------------------------------------------------------
 * Pagination helper
 * ------------------------------------------------------------------ */
function paginate(int $total, int $perPage, int $page): array {
    $totalPages = max(1, (int)ceil($total / max(1, $perPage)));
    $page = max(1, min($totalPages, $page));
    $offset = ($page - 1) * $perPage;
    return ['totalPages' => $totalPages, 'page' => $page, 'offset' => $offset];
}

function pagination_links(int $totalPages, int $page, string $baseQuery): void {
    if ($totalPages <= 1) return;
    $sep = $baseQuery === '' ? '?' : '&';
    echo '<nav class="pagination" aria-label="Pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $cls = $i === $page ? 'page-link current' : 'page-link';
        echo '<a class="' . $cls . '" href="' . e($baseQuery . $sep . 'page=' . $i) . '">' . $i . '</a>';
    }
    echo '</nav>';
}

/* ------------------------------------------------------------------
 * Project lifecycle milestones (shared by client & admin views)
 * ------------------------------------------------------------------ */
function project_milestones(): array {
    return [
        'Request Submitted',
        'Requirement Review',
        'Quotation',
        'Approval',
        'Development',
        'Testing',
        'Delivery',
    ];
}

function milestone_index(string $status): int {
    $map = [
        'NEW' => 0, 'REVIEWING' => 1, 'QUOTATION' => 2, 'APPROVED' => 3,
        'IN_PROGRESS' => 4, 'WAITING_FOR_CLIENT' => 4,
        'TESTING' => 5, 'COMPLETED' => 6, 'CANCELLED' => -1,
    ];
    return $map[$status] ?? 0;
}

function render_milestones(string $status): void {
    $steps = project_milestones();
    $idx = milestone_index($status);
    echo '<div class="timeline">';
    foreach ($steps as $i => $label) {
        $cls = $idx < 0 ? '' : ($i < $idx ? 'done' : ($i === $idx ? 'current' : ''));
        echo '<div class="tl-item ' . $cls . '"><b>' . e($label) . '</b><span>' . ($i === $idx ? 'Current stage' : '') . '</span></div>';
    }
    echo '</div>';
}

/* ------------------------------------------------------------------
 * Icons (inline SVG, stroke based)
 * ------------------------------------------------------------------ */
function icon(string $name, string $class = ''): string {
    $paths = [
        'globe'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.7 3.8 9S14.5 18.4 12 21c-2.5-2.6-3.8-5.7-3.8-9S9.5 5.6 12 3z"/>',
        'cpu'       => '<rect x="7" y="7" width="10" height="10" rx="2"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3M10 10h4v4h-4z"/>',
        'cart'      => '<circle cx="9" cy="20" r="1.6"/><circle cx="17" cy="20" r="1.6"/><path d="M2.5 3h2l2.6 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20.5 7H6"/>',
        'code-s'    => '<path d="M8.5 7 4 12l4.5 5M15.5 7 20 12l-4.5 5M13 4l-2 16"/>',
        'palette'   => '<circle cx="13.5" cy="6.5" r=".9"/><circle cx="17.5" cy="10" r=".9"/><circle cx="8.5" cy="7.5" r=".9"/><circle cx="6" cy="12" r=".9"/><path d="M12 2a10 10 0 0 0 0 20c1.5 0 2-1 2-2 0-1.5-1.5-2-1.5-3.5S15 13 16.5 13H18a4 4 0 0 0 4-4A10 10 0 0 0 12 2z"/>',
        'shield'    => '<path d="M12 2 4 5v6c0 5 3.4 9 8 11 4.6-2 8-6 8-11V5z"/><path d="m9 12 2 2 4-4"/>',
        'layers'    => '<path d="m12 2 9 5-9 5-9-5z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'folder'    => '<path d="M3 6a2 2 0 0 1 2-2h4l2 3h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'chat'      => '<path d="M21 12a8 8 0 0 1-8 8H4l2-3a8 8 0 0 1-1-4 8 8 0 0 1 16 0z"/><path d="M8 10h8M8 13h5"/>',
        'bell'      => '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6z"/><path d="M10 19a2 2 0 0 0 4 0"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/>',
        'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 3-5 6.5-5s6.5 1.5 6.5 5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M17.5 15.4c2 .7 4 2 4 4.6"/>',
        'file'      => '<path d="M6 2h8l4 4v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v4h4M8 12h8M8 16h6"/>',
        'doc'       => '<path d="M6 2h8l4 4v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v4h4M8 12h8M8 16h8"/>',
        'money'     => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.8"/><path d="M6 9.5v5M18 9.5v5"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.4-2.3 1a7 7 0 0 0-2-1.2L14.2 3h-4l-.4 2.7a7 7 0 0 0-2 1.2l-2.3-1-2 3.4 2 1.5a7 7 0 0 0 0 2.4l-2 1.5 2 3.4 2.3-1a7 7 0 0 0 2 1.2l.4 2.7h4l.4-2.7a7 7 0 0 0 2-1.2l2.3 1 2-3.4-2-1.5c.06-.4.1-.8.1-1.2z"/>',
        'list'      => '<path d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check'     => '<path d="m4 12 5 5L20 6"/>',
        'x'         => '<path d="M6 6l12 12M18 6 6 18"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'phone'     => '<path d="M4 4h4l2 5-2.5 1.5a12 12 0 0 0 6 6L15 14l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 2 6a2 2 0 0 1 2-2z"/>',
        'mail'      => '<rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'pin'       => '<path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11z"/><circle cx="12" cy="10" r="2.6"/>',
        'star'      => '<path d="m12 3 2.7 5.5 6 .9-4.3 4.2 1 6-5.4-2.8-5.4 2.8 1-6L3.3 9.4l6-.9z"/>',
        'rocket'    => '<path d="M5 15c-1.5 1.3-2 5-2 5s3.7-.5 5-2c.8-1-.8-4.2-3-3zM14 4c3-1.5 6.5-1 6.5-1s.5 3.5-1 6.5L13 16l-5-5z"/><circle cx="14.5" cy="9.5" r="1.6"/>',
        'eye'       => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'lock'      => '<rect x="5" y="10.5" width="14" height="9" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'arrow'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-l'   => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'calendar'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>',
        'flag'      => '<path d="M5 21V4M5 5h13l-2.5 4 2.5 4H5"/>',
        'truck'     => '<path d="M2 6h12v9H2zM14 9h4l3 3v3h-7z"/><circle cx="6.5" cy="18" r="1.8"/><circle cx="17.5" cy="18" r="1.8"/>',
        'chart'     => '<path d="M3 21h18M5 17v-5M10 17V7M15 17v-8M20 17v-3"/>',
        'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'inbox'     => '<path d="M3 5h18v14H3z"/><path d="m3 12 6 3 3-4 3 4 6-3"/>',
        'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'edit'      => '<path d="M4 20h4L19 9l-4-4L4 16zM13 6l4 4"/>',
        'trash'     => '<path d="M4 6h16M9 6V4h6v2M6 6l1 14h10l1-14M10 10v6M14 10v6"/>',
        'send'      => '<path d="M21 3 10 14M21 3l-7 18-4-7-7-4z"/>',
        'download'  => '<path d="M12 3v11M7 10l5 5 5-5M4 19h16"/>',
        'upload'    => '<path d="M12 16V5M7 10l5-5 5 5M4 19h16"/>',
    ];
    $path = $paths[$name] ?? $paths['code-s'];
    $cls = $class !== '' ? ' class="' . e($class) . '"' : '';
    return '<svg' . $cls . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

/* ------------------------------------------------------------------
 * Simple email abstraction.
 * Returns false when SMTP mail is not configured — it never pretends
 * delivery works. Contact submissions are always stored in the DB.
 * ------------------------------------------------------------------ */
function rs_mail(string $to, string $subject, string $body): bool {
    $from = settings('company_email', '');
    if ($from === '' || (bool)ini_get('smtp_on') === false) {
        return false;
    }
    $headers = 'From: ' . $from . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
    return @mail($to, $subject, $body, $headers);
}

require_once __DIR__ . '/layout.php';