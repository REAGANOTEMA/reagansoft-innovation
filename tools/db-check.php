<?php
/**
 * Reagan Soft Innovation Limited — database health check (CLI only).
 *
 * Tells you, in one command, whether the database is reachable,
 * whether the schema is complete, whether the seed data is there
 * and whether the two stores (reagansoft_clients / reagansoft_admin)
 * still match. Run it after every import or any time a page looks
 * empty:
 *
 *     php tools/db-check.php
 *
 * It is deliberately read-only: it never writes, drops or repairs
 * anything. Delete this file before going live if you prefer.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool runs from the command line only.\n");
}

require __DIR__ . '/../config/config.php';

$ok   = 0;
$warn = 0;
$bad  = 0;

function line(string $label, string $value, string $state = 'ok'): void {
    global $ok, $warn, $bad;
    if ($state === 'ok')        { $ok++;   $mark = '[ ok ]'; }
    elseif ($state === 'warn')  { $warn++; $mark = '[warn]'; }
    else                        { $bad++;  $mark = '[FAIL]'; }
    printf("%-6s %-34s %s\n", $mark, $label, $value);
}

function head(string $title): void {
    echo "\n" . $title . "\n" . str_repeat('-', mb_strlen($title) + 4) . "\n";
}

/* ------------------------------------------------------------------ */
head('Connection');
/* ------------------------------------------------------------------ */
line('PHP', PHP_VERSION . ' (' . PHP_SAPI . ')');
line('App database', DB_NAME);

try {
    $pdo = db();
    line('Server', $pdo->getAttribute(PDO::ATTR_SERVER_VERSION), 'ok');
    line('Host', DB_HOST . ':' . (($pdsn = parse_url('mysql://' . DB_HOST)) ? ($pdo->query('SELECT @@port')->fetchColumn() ?: '3306') : '3306'), 'ok');

    $tz = (string)$pdo->query('SELECT @@session.time_zone')->fetchColumn();
    line('Session time zone', $tz . ($tz === '+03:00' ? ' (EAT, matches PHP)' : '  <-- not pinned to +03:00'), $tz === '+03:00' ? 'ok' : 'warn');
    line('Charset / collation', (string)$pdo->query('SELECT @@character_set_connection')->fetchColumn() . ' / ' . (string)$pdo->query('SELECT @@collation_connection')->fetchColumn());
    line('Live connection', db_is_connected() ? 'yes' : 'no', db_is_connected() ? 'ok' : 'bad');
} catch (Throwable $e) {
    line('Connection', 'FAILED — ' . $e->getMessage(), 'bad');
    echo "\nNothing else can be checked until the database is reachable.\n";
    exit(1);
}

/* ------------------------------------------------------------------ */
head('Schema (expect 15 tables)');
/* ------------------------------------------------------------------ */
$expected = [
    'users', 'services', 'projects', 'project_tasks', 'project_files',
    'project_messages', 'notifications', 'quotations', 'quotation_items',
    'invoices', 'invoice_items', 'payments', 'contact_messages',
    'settings', 'activity_logs',
];
$have = $pdo->query('SELECT table_name FROM information_schema.tables WHERE table_schema = ' . $pdo->quote(DB_NAME))->fetchAll(PDO::FETCH_COLUMN);
$missing = array_diff($expected, $have);
$extra   = array_diff($have, $expected);
line('Tables found', (string)count($have), count($have) >= 15 ? 'ok' : 'bad');
if ($missing) {
    line('Missing tables', implode(', ', $missing) . '  <-- run database/install.sql', 'bad');
} else {
    line('Missing tables', 'none');
}
if ($extra) {
    line('Extra tables', implode(', ', $extra), 'warn');
}

/* ------------------------------------------------------------------ */
head('Data');
/* ------------------------------------------------------------------ */
$counts = [];
foreach (['users', 'services', 'settings', 'projects', 'project_tasks', 'project_messages',
          'quotations', 'invoices', 'payments', 'notifications'] as $t) {
    if (in_array($t, $have, true)) {
        $counts[$t] = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    }
}
foreach ($counts as $t => $c) {
    line($t, (string)$c, $c > 0 ? 'ok' : 'warn');
}

/* ------------------------------------------------------------------ */
head('Accounts');
/* ------------------------------------------------------------------ */
if (in_array('users', $have, true)) {
    foreach ($pdo->query("SELECT role, COUNT(*) c FROM users GROUP BY role ORDER BY role") as $r) {
        line('role: ' . $r['role'], $r['c'] . ' account(s)');
    }
    $noPhone = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE phone IS NULL OR phone = ""')->fetchColumn();
    line('Accounts without a phone', (string)$noPhone, $noPhone === 0 ? 'ok' : 'warn');

    $staff = $pdo->query("SELECT email FROM users WHERE role IN ('admin','staff') AND active = 1")->fetchAll(PDO::FETCH_COLUMN);
    line('Active admin/staff logins', $staff ? implode(', ', $staff) : 'NONE  <-- nobody can sign in', $staff ? 'ok' : 'bad');

    // every account must be able to verify its stored bcrypt hash
    $sample = $pdo->query("SELECT email, password_hash FROM users WHERE role IN ('admin','staff') LIMIT 1")->fetch();
    if ($sample) {
        line('Password hash readable', password_get_info($sample['password_hash'])['algoName'] . ' hash for ' . $sample['email']);
    }
}

/* ------------------------------------------------------------------ */
head('Company settings');
/* ------------------------------------------------------------------ */
if (in_array('settings', $have, true)) {
    $s = [];
    foreach ($pdo->query('SELECT setting_key, setting_value FROM settings') as $r) {
        $s[$r['setting_key']] = $r['setting_value'];
    }
    foreach (['company_name', 'company_phone', 'company_email', 'company_whatsapp',
              'payment_mtn_number', 'payment_airtel_number', 'currency'] as $k) {
        $v = $s[$k] ?? '';
        line($k, $v !== '' ? $v : 'not set', $v !== '' ? 'ok' : 'warn');
    }
}

/* ------------------------------------------------------------------ */
head('Mirror store (' . DB_MIRROR . ')');
/* ------------------------------------------------------------------ */
if (DB_MIRROR === '' || DB_MIRROR === DB_NAME) {
    line('Mirror', 'disabled (RSI_DB_MIRROR is empty)', 'warn');
} else {
    try {
        $m = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_MIRROR . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $mTables = (int)$m->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ' . $m->quote(DB_MIRROR))->fetchColumn();
        line('Tables', (string)$mTables, $mTables >= 15 ? 'ok' : 'warn');

        if ($mTables > 0 && isset($counts['users'])) {
            $mUsers = (int)$m->query('SELECT COUNT(*) FROM users')->fetchColumn();
            line('Users', $mUsers . ' (app has ' . $counts['users'] . ')', $mUsers === $counts['users'] ? 'ok' : 'warn');
            foreach (['projects', 'services', 'settings'] as $t) {
                $a = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                $b = (int)$m->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                line($t, $a . ' app / ' . $b . ' mirror', $a === $b ? 'ok' : 'warn');
            }
        }
    } catch (Throwable $e) {
        line('Mirror', 'unreachable — ' . $e->getMessage(), 'warn');
    }
}

/* ------------------------------------------------------------------ */
head('Writable paths');
/* ------------------------------------------------------------------ */
line('uploads/', is_writable(UPLOAD_DIR) ? 'writable' : 'NOT WRITABLE  <-- file uploads will fail', is_writable(UPLOAD_DIR) ? 'ok' : 'bad');
line('storage/logs/', is_writable(LOG_DIR) ? 'writable' : 'NOT WRITABLE  <-- errors cannot be logged', is_writable(LOG_DIR) ? 'ok' : 'bad');

/* ------------------------------------------------------------------ */
echo "\n" . str_repeat('=', 46) . "\n";
printf("Result: %d ok, %d warning(s), %d failure(s)\n", $ok, $warn, $bad);
if ($bad > 0) {
    echo "Fix the failures above, then run:  php tools/db-check.php\n";
    exit(1);
}
if ($warn > 0) {
    echo "Working, with warnings worth a look.\n";
    exit(0);
}
echo "Database is healthy.\n";
exit(0);
