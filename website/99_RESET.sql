-- ============================================================
-- Reagan Soft Innovation Limited
-- WEBSITE DATABASE — RESET (DESTRUCTIVE)
-- Database: reagansoft_website
-- ============================================================
-- THIS FILE DELETES ALL APPLICATION DATA: accounts, clients,
-- projects, tasks, files, messages, quotations, invoices, payments,
-- expenses and settings. Use it only when you intend to rebuild.
--
-- Correct rebuild sequence:
--   1. 99_RESET.sql        (drops every table)
--   2. 00_INSTALL_ALL.sql  (schema + seed + upgrade)
-- ============================================================

USE reagansoft_website;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS expenses;
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
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'All tables dropped from reagansoft_website. Import 00_INSTALL_ALL.sql next.' AS status;
