<?php
/**
 * Database migration runner (CLI only).
 *
 * Runs the additive enterprise migrations in order, then verifies the
 * application accounts can connect using the credentials in config/config.php.
 *
 * Usage:
 *   php tools/migrate.php
 *   php tools/migrate.php --user=root --pass=secret
 *   php tools/migrate.php --mysql="C:\xampp\mysql\bin\mysql.exe"
 *
 * It never edits and never deletes existing data.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool is command-line only.\n");
}

$options = getopt('', ['user::', 'pass::', 'mysql::']);
$user = (string)($options['user'] ?? 'root');
$pass = (string)($options['pass'] ?? '');
$mysql = (string)($options['mysql'] ?? '');

if ($mysql === '') {
    $candidates = [
        'C:\\xampp\\mysql\\bin\\mysql.exe',
        'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
        'mysql',
    ];
    foreach ($candidates as $candidate) {
        if ($candidate === 'mysql' || is_file($candidate)) {
            $mysql = $candidate;
            break;
        }
    }
}

$root = dirname(__DIR__);
$files = [
    'database/migrations/001_enterprise_platform.sql',
    'database/migrations/002_platform_settings.sql',
    'database/migrations/003_db_users.sql',
];

echo "Reagan Soft Innovation - additive migrations\n";
echo str_repeat('=', 52) . "\n";

/**
 * Runs one SQL file through the mysql client using stdin (so DELIMITER,
 * stored procedures and multi-statement files all work exactly as they do
 * when imported through phpMyAdmin).
 */
function run_sql_file(string $mysql, string $user, string $pass, string $path): array
{
    $cmd = [$mysql, '-u', $user];
    if ($pass !== '') {
        $cmd[] = '-p' . $pass;
    }
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($process)) {
        return [1, 'Could not start the mysql client.'];
    }
    $sql = (string)file_get_contents($path);
    fwrite($pipes[0], $sql);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    return [$code, trim($stdout . "\n" . $stderr)];
}

foreach ($files as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        fwrite(STDERR, "MISSING  {$relative}\n");
        exit(1);
    }
    echo "Running  {$relative} ... ";
    [$code, $output] = run_sql_file($mysql, $user, $pass, $path);
    if ($code !== 0) {
        echo "FAIL\n";
        fwrite(STDERR, $output . "\n");
        exit($code);
    }
    echo "OK\n";
}

require_once $root . '/config/config.php';

echo str_repeat('=', 52) . "\n";
try {
    $pdo = db();
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo "Application login ..... OK ({$dbName})\n";
} catch (Throwable $e) {
    echo "Application login ..... FAIL\n";
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$tables = ['expenses', 'newsletter_subscribers', 'users', 'projects', 'invoices'];
foreach ($tables as $table) {
    $st = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($table));
    echo str_pad($table, 26, '.') . ' ' . ($st->fetchColumn() ? 'present' : 'MISSING') . "\n";
}

echo "\nMigrations complete. Existing tables and rows were not modified.\n";
