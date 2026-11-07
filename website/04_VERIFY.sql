-- ============================================================
-- Reagan Soft Innovation Limited
-- WEBSITE DATABASE — VERIFY (read-only)
-- Database: reagansoft_website
-- ============================================================
-- Import after 00_INSTALL_ALL.sql to confirm that every table and
-- the expected demo rows exist. This file changes nothing.
-- ============================================================

USE reagansoft_website;

SELECT 'database' AS check_name, DATABASE() AS value;
SELECT 'tables' AS check_name, COUNT(*) AS found
  FROM information_schema.tables
 WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE';

SELECT 'users' AS check_name, COUNT(*) AS found FROM users
UNION ALL SELECT 'services', COUNT(*) FROM services
UNION ALL SELECT 'settings', COUNT(*) FROM settings
UNION ALL SELECT 'projects', COUNT(*) FROM projects
UNION ALL SELECT 'project_tasks', COUNT(*) FROM project_tasks
UNION ALL SELECT 'project_files', COUNT(*) FROM project_files
UNION ALL SELECT 'project_messages', COUNT(*) FROM project_messages
UNION ALL SELECT 'notifications', COUNT(*) FROM notifications
UNION ALL SELECT 'quotations', COUNT(*) FROM quotations
UNION ALL SELECT 'quotation_items', COUNT(*) FROM quotation_items
UNION ALL SELECT 'invoices', COUNT(*) FROM invoices
UNION ALL SELECT 'invoice_items', COUNT(*) FROM invoice_items
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'expenses', COUNT(*) FROM expenses
UNION ALL SELECT 'contact_messages', COUNT(*) FROM contact_messages
UNION ALL SELECT 'newsletter_subscribers', COUNT(*) FROM newsletter_subscribers
UNION ALL SELECT 'activity_logs', COUNT(*) FROM activity_logs;

SELECT role, COUNT(*) AS accounts FROM users GROUP BY role;
SELECT email, role, active FROM users ORDER BY role, id;
SELECT COUNT(*) AS missing_settings
  FROM (
    SELECT 'company_name' AS k UNION ALL SELECT 'company_tagline' UNION ALL
    SELECT 'company_email' UNION ALL SELECT 'company_phone' UNION ALL
    SELECT 'company_whatsapp' UNION ALL SELECT 'company_address' UNION ALL
    SELECT 'registration_open' UNION ALL SELECT 'project_deposit' UNION ALL
    SELECT 'payment_mtn_number' UNION ALL SELECT 'currency'
  ) expected
 WHERE k NOT IN (SELECT setting_key FROM settings);
