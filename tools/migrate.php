<?php
/**
 * Database installer / migrator (CLI only).
 *
 * Imports the complete, non-destructive SQL dumps:
 *   sql/system/reagansoft_system.sql   -> reagansoft_system
 *   sql/website/reagansoft_website.sql -> reagansoft_website
 *
 * Usage:
 *   php tools/migrate.php
 *   php tools/migrate.php --user=root --pass=secret
 *   php tools/migrate.php --with-accounts
 *   php tools/migrate.php --mysql="C:\xampp\mysql\bin\mysql.exe"
 *
 * Every dump is loaded into an explicit database name, so the file never
 * depends on a USE statement or a preselected phpMyAdmin database.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool is command-line only.\n");
}

require_once __DIR__ . '/../config/config.php';

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
$targets = [
    ['db' => DB_NAME, 'file' => 'sql/system/reagansoft_system.sql'],
];
if (DB_MIRROR !== '' && DB_MIRROR !== DB_NAME) {
    $targets[] = ['db' => DB_MIRROR, 'file' => 'sql/website/reagansoft_website.sql'];
}

echo "Reagan Soft Innovation - database installer\n";
echo str_repeat('=', 52) . "\n";

function run_sql_file(string $mysql, string $user, string $pass, string $path, string $db = ''): array
{
    $cmd = [$mysql, '-u', $user, '--default-character-set=utf8mb4'];
    if ($pass !== '') {
        $cmd[] = '-p' . $pass;
    }
    if ($db !== '') {
        $cmd[] = '-D';
        $cmd[] = $db;
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

function run_sql_string(string $mysql, string $user, string $pass, string $sql): array
{
    $cmd = [$mysql, '-u', $user, '--default-character-set=utf8mb4', '-N', '-B'];
    if ($pass !== '') {
        $cmd[] = '-p' . $pass;
    }
    $cmd[] = '-e';
    $cmd[] = $sql;
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($process)) {
        return [1, 'Could not start the mysql client.'];
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    return [$code, trim($stdout . "\n" . $stderr)];
}

if ($withAccounts) {
    echo "Accounts ... tools/setup-databases.php handles CREATE USER safely.\n";
    putenv('RSI_ADMIN_DB_USER=' . $user);
    putenv('RSI_ADMIN_DB_PASS=' . $pass);
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/tools/setup-databases.php'), $accountsCode);
    if ($accountsCode !== 0) {
        fwrite(STDERR, "Account setup failed with exit code {$accountsCode}.\n");
    }
}

foreach ($targets as $target) {
    $db = (string)$target['db'];
    if (preg_match('/^[A-Za-z0-9_]+$/', $db) !== 1) {
        fwrite(STDERR, "INVALID database name: {$db}\n");
        exit(1);
    }
    $relative = (string)$target['file'];
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        fwrite(STDERR, "MISSING  {$relative}\n");
        exit(1);
    }

    echo "Creating {$db} ... ";
    [$createCode, $createOutput] = run_sql_string(
        $mysql,
        $user,
        $pass,
        'CREATE DATABASE IF NOT EXISTS `' . $db . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );
    if ($createCode !== 0) {
        echo "FAIL\n";
        fwrite(STDERR, $createOutput . "\n");
        exit($createCode);
    }
    echo "OK\n";

    [$countCode, $countOutput] = run_sql_string(
        $mysql,
        $user,
        $pass,
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '"
        . str_replace("'", "''", $db) . "' AND TABLE_TYPE = 'BASE TABLE'"
    );
    if ($countCode === 0 && (int)trim($countOutput) > 0) {
        echo "SKIP     {$db} already has " . (int)trim($countOutput) . " table(s); left untouched.\n";
        echo "         Use install.php (reinstall) if you want a clean rebuild.\n";
        continue;
    }

    echo "Running  {$relative} -> {$db} ... ";
    [$code, $output] = run_sql_file($mysql, $user, $pass, $path, $db);
    if ($code !== 0) {
        echo "FAIL\n";
        fwrite(STDERR, $output . "\n");
        exit($code);
    }
    echo "OK\n";
}

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

echo "\nInstall complete. Import the dumps into empty databases (or use install.php reinstall).\n";
