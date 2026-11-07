<?php
/**
 * Database installer / migrator (CLI only).
 *
 * Imports the complete, non-destructive SQL packs:
 *   system/00_INSTALL_ALL.sql   -> reagansoft_system
 *   website/00_INSTALL_ALL.sql  -> reagansoft_website
 *
 * Usage:
 *   php tools/migrate.php
 *   php tools/migrate.php --user=root --pass=secret
 *   php tools/migrate.php --with-accounts
 *   php tools/migrate.php --mysql="C:\xampp\mysql\bin\mysql.exe"
 *
 * Every script uses CREATE TABLE IF NOT EXISTS and upserts seed rows on
 * natural keys, so existing business data is never dropped or deleted.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool is command-line only.\n");
}

$options = getopt('', ['user::', 'pass::', 'mysql::', 'with-accounts']);
$user = (string)($options['user'] ?? 'root');
$pass = (string)($options['pass'] ?? '');
$mysql = (string)($options['mysql'] ?? '');
$withAccounts = array_key_exists('with-accounts', $options);

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
    'system/00_INSTALL_ALL.sql',
    'website/00_INSTALL_ALL.sql',
];
if ($withAccounts) {
    $files[] = 'system/06_DB_ACCOUNTS.sql';
    $files[] = 'website/06_DB_ACCOUNTS.sql';
}

echo "Reagan Soft Innovation - database installer\n";
echo str_repeat('=', 52) . "\n";

function run_sql_file(string $mysql, string $user, string $pass, string $path): array
{
    $cmd = [$mysql, '-u', $user, '--default-character-set=utf8mb4'];
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

$tables = ['users', 'services', 'projects', 'invoices', 'payments', 'expenses', 'newsletter_subscribers', 'settings'];
foreach ($tables as $table) {
    $st = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($table));
    echo str_pad($table, 26, '.') . ' ' . ($st->fetchColumn() ? 'present' : 'MISSING') . "\n";
}

echo "\nInstall complete. Existing tables and rows were preserved.\n";
