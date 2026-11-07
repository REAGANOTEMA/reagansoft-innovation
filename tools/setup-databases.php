<?php
/**
 * Reagan Soft Innovation Limited — database & account bootstrapper (CLI only).
 *
 * Creates both databases and both MySQL accounts, sets their passwords to
 * the values in config/config.php and grants each account full access to
 * its own database. It is idempotent: run it as many times as you like.
 *
 *   php tools/setup-databases.php
 *
 * It connects with an ADMIN account, because creating users needs the
 * CREATE USER privilege. On XAMPP the default admin is `root` with no
 * password, which is what this script uses unless you override it:
 *
 *   set RSI_ADMIN_DB_USER=root
 *   set RSI_ADMIN_DB_PASS=your-root-password
 *
 * Why this tool exists: a .sql file containing CREATE USER fails with
 * "#1227 Access denied" whenever it is imported through phpMyAdmin with a
 * non-admin account. A CLI script can report that clearly and be re-run
 * after the credentials are fixed, without ever touching your data.
 *
 * SECURITY: change the passwords in config/config.php before production,
 * then run this script once more so the MySQL accounts match.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool runs from the command line only.\n");
}

require __DIR__ . '/../config/config.php';

$adminUser = rsi_env('RSI_ADMIN_DB_USER') ?? 'root';
$adminPass = rsi_env('RSI_ADMIN_DB_PASS') ?? '';

$targets = [
    ['name' => DB_NAME, 'user' => DB_USER, 'pass' => DB_PASS],
];

if (DB_MIRROR !== '' && DB_MIRROR !== DB_NAME) {
    $targets[] = ['name' => DB_MIRROR, 'user' => DB_MIRROR_USER, 'pass' => DB_MIRROR_PASS];
}

function say(string $state, string $message): void
{
    $marks = ['ok' => '[ ok ]', 'warn' => '[warn]', 'fail' => '[FAIL]', 'info' => '[info]'];
    echo str_pad($marks[$state] ?? '[info]', 7) . $message . "\n";
}

function ident(string $value, string $label): string
{
    if (preg_match('/^[A-Za-z0-9_]+$/', $value) !== 1) {
        throw new RuntimeException('Invalid ' . $label . ' name: ' . $value);
    }
    return $value;
}

function quoteLiteral(PDO $pdo, string $value): string
{
    return $pdo->quote($value);
}

echo "\nReagan Soft Innovation — database setup\n";
echo str_repeat('=', 42) . "\n\n";

/* 1. Connect as the administrator. */
try {
    $admin = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        $adminUser,
        $adminPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    say('ok', 'Connected to MySQL ' . $admin->getAttribute(PDO::ATTR_SERVER_VERSION) . ' as ' . $adminUser);
} catch (Throwable $e) {
    say('fail', 'Could not connect as the admin account "' . $adminUser . '".');
    say('info', 'Set RSI_ADMIN_DB_USER / RSI_ADMIN_DB_PASS to a MySQL administrator and run again.');
    say('info', 'Detail: ' . $e->getMessage());
    exit(1);
}

$failures = 0;

/* 2. Create each database, account and grant. */
foreach ($targets as $target) {
    $dbName = ident($target['name'], 'database');
    $user   = ident($target['user'], 'user');
    $pass   = (string)$target['pass'];

    echo "\n" . $dbName . "  (owner: " . $user . ")\n";
    echo str_repeat('-', 42) . "\n";

    try {
        $admin->exec(
            'CREATE DATABASE IF NOT EXISTS `' . $dbName . '` '
            . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
        say('ok', 'Database exists');
    } catch (Throwable $e) {
        say('fail', 'Could not create database: ' . $e->getMessage());
        $failures++;
        continue;
    }

    foreach (['localhost', '127.0.0.1'] as $host) {
        $account = $user . "'@'" . $host;
        try {
            $admin->exec(
                "CREATE USER IF NOT EXISTS '" . $user . "'@'" . $host . "' "
                . 'IDENTIFIED BY ' . quoteLiteral($admin, $pass)
            );
            $admin->exec(
                "ALTER USER '" . $user . "'@'" . $host . "' "
                . 'IDENTIFIED BY ' . quoteLiteral($admin, $pass)
            );
            $admin->exec(
                "GRANT ALL PRIVILEGES ON `" . $dbName . "`.* TO '" . $user . "'@'" . $host . "'"
            );
            say('ok', 'Account ' . $account . ' ready and granted');
        } catch (Throwable $e) {
            say('fail', 'Could not configure ' . $account . ': ' . $e->getMessage());
            $failures++;
        }
    }
}

try {
    $admin->exec('FLUSH PRIVILEGES');
    say('ok', 'Privileges flushed');
} catch (Throwable $e) {
    say('warn', 'FLUSH PRIVILEGES skipped: ' . $e->getMessage());
}

/* 3. Prove the application account can actually reach its database. */
echo "\nVerification\n";
echo str_repeat('-', 42) . "\n";

foreach ($targets as $target) {
    try {
        $probe = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . $target['name'] . ';charset=utf8mb4',
            $target['user'],
            $target['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $tables = count($probe->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
        say('ok', $target['user'] . ' can use ' . $target['name'] . ' (' . $tables . ' tables)');
        if ($tables === 0) {
            say('info', 'Database is empty. Import the matching SQL pack next:');
            say('info', $target['name'] === DB_NAME
                ? '  C:\\xampp\\mysql\\bin\\mysql.exe -u root ' . DB_NAME . ' < sql\\system\\reagansoft_system.sql'
                : '  C:\\xampp\\mysql\\bin\\mysql.exe -u root ' . DB_MIRROR . ' < sql\\website\\reagansoft_website.sql');
        }
    } catch (Throwable $e) {
        say('fail', $target['user'] . ' cannot use ' . $target['name'] . ': ' . $e->getMessage());
        $failures++;
    }
}

echo "\n" . str_repeat('=', 42) . "\n";
if ($failures > 0) {
    echo "Finished with " . $failures . " problem(s). Fix the [FAIL] lines and run again.\n";
    exit(1);
}
echo "All databases and accounts are ready.\n";
exit(0);
