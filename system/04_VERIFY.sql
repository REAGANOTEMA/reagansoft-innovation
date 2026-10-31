-- ============================================================
-- Reagan Soft Innovation Limited
-- VERIFY INSTALL
-- Database: reagansoft_system
-- ============================================================
-- This is the database the application connects to. It holds accounts, clients, projects, quotations, invoices, payments, services, messages and settings.
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

USE `reagansoft_system`;

SELECT 'tables' AS check_name, COUNT(*) AS found
  FROM information_schema.tables
 WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE';

SELECT 'users' AS check_name, COUNT(*) AS found FROM users
UNION ALL SELECT 'services', COUNT(*) FROM services
UNION ALL SELECT 'settings', COUNT(*) FROM settings
UNION ALL SELECT 'projects', COUNT(*) FROM projects
UNION ALL SELECT 'invoices', COUNT(*) FROM invoices;
