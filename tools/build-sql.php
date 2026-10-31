<?php
/**
 * Reagan Soft Innovation Limited - SQL packager.
 *
 * Builds two complete, self-contained SQL packages from the canonical
 * sources in database/:
 *
 *   system/   -> reagansoft_system   (the active application database)
 *   website/  -> reagansoft_website  (identical mirror database)
 *
 * Run after editing database/schema.sql, database/seed.sql,
 * database/upgrade.sql or database/reset.sql:
 *
 *     php tools/build-sql.php
 *
 * Every generated file is safe to re-run. Nothing drops data except
 * reset.sql, which is deliberately destructive and clearly named.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool runs from the command line only.\n");
}

$root = dirname(__DIR__);
$src  = $root . '/database';

$targets = [
    'system' => [
        'db'    => 'reagansoft_system',
        'title' => 'SYSTEM DATABASE - the active Reagan Soft Innovation application',
        'role'  => 'This is the database the application connects to. It holds accounts, clients, projects, quotations, invoices, payments, services, messages and settings.',
    ],
    'website' => [
        'db'    => 'reagansoft_website',
        'title' => 'WEBSITE DATABASE - mirror of the system database',
        'role'  => 'This is an identical mirror of reagansoft_system. Keep it in step by importing the same pack, or let install.php rebuild it.',
    ],
];

function read_source(string $file): string
{
    if (!is_file($file)) {
        fwrite(STDERR, "Missing source file: $file\n");
        exit(1);
    }
    return rtrim(str_replace("\r\n", "\n", (string)file_get_contents($file)));
}

function write_file(string $file, string $sql): void
{
    if (file_put_contents($file, $sql, LOCK_EX) === false) {
        fwrite(STDERR, "Could not write: $file\n");
        exit(1);
    }
    printf("  %-30s %5d lines  %7.1f KB\n", basename(dirname($file)) . '/' . basename($file), substr_count($sql, "\n") + 1, strlen($sql) / 1024);
}

function rebrand(string $sql, string $db): string
{
    return str_replace('reagansoft_system', $db, $sql);
}

function strip_db_directives(string $sql): string
{
    return preg_replace(
        '/^[ \t]*(?:USE\s+[^;]+;|CREATE\s+DATABASE[^;]*;)[ \t]*$\n?/mi',
        '',
        $sql
    );
}

function header_for(string $db, string $role, string $fileRole): string
{
    return <<<SQL
-- ============================================================
-- Reagan Soft Innovation Limited
-- {$fileRole}
-- Database: {$db}
-- ============================================================
-- {$role}
--
-- PHP >= 8.0 | MySQL >= 5.7 / 8.0 | MariaDB >= 10.2
-- InnoDB | utf8mb4
--
-- Import the files in number order:
--   01_SCHEMA.sql   tables (safe to re-run)
--   02_SEED.sql     services, settings, demo accounts and samples
--   03_UPGRADE.sql  adds columns an older install may be missing
--   04_VERIFY.sql   read-only checks
--   00_INSTALL_ALL.sql  schema + seed + upgrade in one import
--   99_RESET.sql    DESTRUCTIVE - drops every table, then rebuild
--
-- On shared hosting: select the database in phpMyAdmin first, then
-- import 00_INSTALL_ALL.sql. On XAMPP you may import it directly.
-- ============================================================


SQL;
}

$schema  = read_source($src . '/schema.sql');
$seed    = read_source($src . '/seed.sql');
$upgrade = read_source($src . '/upgrade.sql');
$reset   = read_source($src . '/reset.sql');

echo "\nRebuilding the SQL packages\n" . str_repeat('-', 46) . "\n";

foreach ($targets as $folder => $meta) {
    $db   = $meta['db'];
    $dir  = $root . '/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fwrite(STDERR, "Could not create folder: $dir\n");
        exit(1);
    }

    $s = rebrand($schema, $db);
    $d = rebrand($seed, $db);
    $r = rebrand($reset, $db);

    $install = header_for($db, $meta['role'], 'INSTALL ALL - one-file import')
        . $s . "\n\n" . $d . "\n\n" . $upgrade . "\n";

    $portable = header_for($db, $meta['role'], 'PORTABLE INSTALL - select the database first')
        . "SELECT DATABASE() AS installing_into;\n\n"
        . strip_db_directives($s) . "\n\n"
        . strip_db_directives($d) . "\n\n"
        . strip_db_directives($upgrade) . "\n";

    $verify = header_for($db, $meta['role'], 'VERIFY INSTALL')
        . "USE `{$db}`;\n\n"
        . "SELECT 'tables' AS check_name, COUNT(*) AS found\n"
        . "  FROM information_schema.tables\n"
        . " WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE';\n\n"
        . "SELECT 'users' AS check_name, COUNT(*) AS found FROM users\n"
        . "UNION ALL SELECT 'services', COUNT(*) FROM services\n"
        . "UNION ALL SELECT 'settings', COUNT(*) FROM settings\n"
        . "UNION ALL SELECT 'projects', COUNT(*) FROM projects\n"
        . "UNION ALL SELECT 'invoices', COUNT(*) FROM invoices;\n";

    $readme = <<<TXT
{$meta['title']}
Database: {$db}

{$meta['role']}

HOW TO IMPORT IN phpMyAdmin
1. Start Apache and MySQL.
2. Open http://localhost/phpmyadmin
3. Click Import.
4. Choose 00_INSTALL_ALL.sql and click Go.
   That creates the database, all tables and the demo data.

FILES
00_INSTALL_ALL.sql   schema + seed + upgrade in one step
01_SCHEMA.sql        tables only
02_SEED.sql          services, settings, accounts and demo data
03_UPGRADE.sql       safe column repairs for older installs
04_VERIFY.sql        read-only row counts
05_INSTALL_PORTABLE.sql  for a host-named database: select it first
99_RESET.sql         DESTRUCTIVE: drops every table in {$db}

ACCOUNT PASSWORDS
Every demo account uses the password Reagan@2026.
Change every password after the first sign-in.

CONTACT ROLES
MTN +256730314979       WhatsApp and Mobile Money
Airtel +256772514889    phone calls only

The two folders are identical in structure. Import this whole pack
into {$db}; the other folder rebuilds the matching mirror database.
TXT;

    write_file($dir . '/01_SCHEMA.sql', header_for($db, $meta['role'], 'SCHEMA') . $s . "\n");
    write_file($dir . '/02_SEED.sql', header_for($db, $meta['role'], 'SEED DATA') . $d . "\n");
    write_file($dir . '/03_UPGRADE.sql', header_for($db, $meta['role'], 'UPGRADE') . $upgrade . "\n");
    write_file($dir . '/00_INSTALL_ALL.sql', $install);
    write_file($dir . '/05_INSTALL_PORTABLE.sql', $portable);
    write_file($dir . '/04_VERIFY.sql', $verify);
    write_file($dir . '/99_RESET.sql', header_for($db, $meta['role'], 'RESET - DESTRUCTIVE') . $r . "\n");
    write_file($dir . '/README.txt', $readme . "\n");

    foreach (['00_INSTALL_ALL.sql', '01_SCHEMA.sql', '02_SEED.sql', '03_UPGRADE.sql', '05_INSTALL_PORTABLE.sql', '99_RESET.sql'] as $check) {
        $built = (string)file_get_contents($dir . '/' . $check);
        if (preg_match('/\breagansoft_(?:clients|admin|rsi)\b/i', $built) === 1) {
            fwrite(STDERR, "\n  ERROR: {$folder}/{$check} still contains an old database name.\n");
            exit(1);
        }
        if (strpos($built, $db) === false) {
            fwrite(STDERR, "\n  ERROR: {$folder}/{$check} does not mention {$db}.\n");
            exit(1);
        }
    }

    $portableOut = (string)file_get_contents($dir . '/05_INSTALL_PORTABLE.sql');
    if (preg_match('/^\s*(?:USE\s|CREATE\s+DATABASE)/mi', $portableOut) === 1) {
        fwrite(STDERR, "\n  ERROR: {$folder}/05_INSTALL_PORTABLE.sql still contains USE or CREATE DATABASE.\n");
        exit(1);
    }
}

echo "\n  Old database names removed. Both packs are ready.\n\n";
exit(0);
