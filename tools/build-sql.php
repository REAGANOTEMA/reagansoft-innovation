<?php
/**
 * Reagan Soft Innovation Limited — SQL packager.
 *
 * database/install.sql and database/install-portable.sql are *generated*
 * from database/schema.sql + database/seed.sql, so the two can never
 * drift apart. Run this after editing either source file:
 *
 *     php tools/build-sql.php
 *
 * It writes two files that differ only in how they reach the database:
 *
 *   install.sql            for a server you control (XAMPP, VPS). Creates
 *                          the database if it is missing and selects it.
 *
 *   install-portable.sql   for shared hosting, where the account is
 *                          usually NOT allowed to CREATE DATABASE and the
 *                          database name is chosen by the host. Select your
 *                          database in phpMyAdmin first; this file then
 *                          simply creates the tables inside it.
 *
 * Both are safe to re-run: CREATE TABLE IF NOT EXISTS everywhere, and
 * every seed row is matched on its natural key.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool runs from the command line only.\n");
}

$root = dirname(__DIR__);
$db   = $root . '/database';

/** Statements that only a server-owner account may run, and that would
 *  break an import into a host-named database. Stripped for the portable
 *  build. */
function strip_db_directives(string $sql): string
{
    return preg_replace(
        '/^[ \t]*(?:USE\s+[^;]+;|CREATE\s+DATABASE[^;]*;)[ \t]*$\n?/mi',
        '',
        $sql
    );
}

function read(string $file): string
{
    if (!is_file($file)) {
        fwrite(STDERR, "Missing source file: $file\n");
        exit(1);
    }
    return rtrim(str_replace("\r\n", "\n", (string)file_get_contents($file)));
}

function write(string $file, string $sql): void
{
    file_put_contents($file, $sql, LOCK_EX);
    printf("  %-34s %5d lines  %7.1f KB\n", basename($file), substr_count($sql, "\n") + 1, strlen($sql) / 1024);
}

$schema = read("$db/schema.sql");
$seed   = read("$db/seed.sql");

$serverHeader = <<<'SQL'
-- ============================================================
-- Reagan Soft Innovation Limited
-- INSTALL SCRIPT — for XAMPP / a server you control
-- ============================================================
-- phpMyAdmin: Import tab -> choose this file -> Go.
-- It creates the database, all 15 tables and the demo data in one
-- step, so it is the only file you need for a fresh install.
--
--   PHP >= 8.0  |  MySQL >= 5.7 / 8.0  |  MariaDB >= 10.2
--   InnoDB  |  utf8mb4
--
-- ON SHARED HOSTING THIS FILE WILL NOT WORK, because the account
-- there is not allowed to CREATE DATABASE and the database has a
-- host-chosen name. Use database/install-portable.sql instead.
--
-- SAFE TO RE-RUN. Nothing here drops, truncates or deletes:
-- tables are created with IF NOT EXISTS and every demo row is
-- inserted only when it is missing, so re-importing this file
-- repairs an incomplete install instead of destroying data.
-- For a clean rebuild, run database/reset.sql FIRST (that is
-- the only destructive script in this folder), then this file.
--
-- GENERATED FILE — do not edit. Source: database/schema.sql and
-- database/seed.sql. Rebuild with: php tools/build-sql.php
-- ============================================================


SQL;

$portableHeader = <<<'SQL'
-- ============================================================
-- Reagan Soft Innovation Limited
-- INSTALL SCRIPT — for SHARED HOSTING (cPanel, Plesk, etc.)
-- ============================================================
--
-- 1. In your host's control panel create a database and a database
--    user, and give that user ALL privileges on that one database.
-- 2. In phpMyAdmin, SELECT that database in the left-hand list.
-- 3. Import THIS file (Import tab -> choose this file -> Go).
--
-- Why a different file? On shared hosting the account is not allowed
-- to run CREATE DATABASE, and the database is named by the host
-- (for example "reagansoft_rsi", not "reagansoft_clients"). This
-- file therefore contains no CREATE DATABASE and no USE statement,
-- so it creates its tables inside whichever database you selected.
--
-- The app must then be told the real name. Either set the
-- environment variable RSI_DB_NAME, or edit DB_NAME in
-- config/config.php.
--
-- SAFE TO RE-RUN. Nothing here drops, truncates or deletes:
-- tables are created with IF NOT EXISTS and every demo row is
-- inserted only when it is missing.
--
-- GENERATED FILE — do not edit. Source: database/schema.sql and
-- database/seed.sql. Rebuild with: php tools/build-sql.php
-- ============================================================


SQL;

echo "\nRebuilding the SQL packages\n" . str_repeat('-', 46) . "\n";

write("$db/install.sql", $serverHeader . $schema . "\n\n" . $seed . "\n");
write("$db/install-portable.sql", $portableHeader . strip_db_directives($schema) . "\n\n" . strip_db_directives($seed) . "\n");

// Fail loudly if the portable build still carries a CREATE DATABASE / USE.
$portable = (string)file_get_contents("$db/install-portable.sql");
if (preg_match('/^\s*(?:USE\s|CREATE\s+DATABASE)/mi', $portable)) {
    fwrite(STDERR, "\n  ERROR: install-portable.sql still contains a USE or CREATE DATABASE statement.\n");
    exit(1);
}

echo "\n  install-portable.sql contains no USE / CREATE DATABASE — good.\n";
echo "  It creates " . substr_count($portable, 'CREATE TABLE IF NOT EXISTS') . " tables.\n\n";
exit(0);
