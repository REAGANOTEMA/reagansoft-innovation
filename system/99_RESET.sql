-- ============================================================
-- Reagan Soft Innovation Limited
-- RESET - DESTRUCTIVE
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

-- ============================================================
-- Reagan Soft Innovation Limited
-- RESET  (database/reset.sql)
-- ============================================================
-- THE ONLY DESTRUCTIVE SCRIPT IN THIS FOLDER.
--
-- It drops every table in the database named below, which
-- DELETES ALL DATA: accounts, projects, quotations, invoices,
-- payments, messages and uploaded-file records.
--
-- Use it only for a clean rebuild:
--     1. database/reset.sql      (wipes)
--     2. database/schema.sql     (recreates the tables)
--     3. database/seed.sql       (reloads the demo data)
-- or simply import database/install.sql after this file.
--
-- To restore the portal logins WITHOUT losing data, do not run
-- this file ΓÇö run database/admin.sql and database/client.sql.
-- ============================================================
USE reagansoft_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS quotation_items;
DROP TABLE IF EXISTS quotations;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS project_messages;
DROP TABLE IF EXISTS project_files;
DROP TABLE IF EXISTS project_tasks;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;
