-- ============================================================
-- Reagan Soft Innovation Limited
-- MIGRATION 001 — Enterprise platform additions
-- ============================================================
-- ADDITIVE ONLY. This file never drops, renames or truncates a table.
-- It is safe to run repeatedly (IF NOT EXISTS / guarded indexes).
--
-- Run AFTER the existing system/00_INSTALL_ALL.sql (or import order):
--   1. system/00_INSTALL_ALL.sql
--   2. database/migrations/001_enterprise_platform.sql
--   3. database/migrations/002_platform_settings.sql
-- ============================================================

USE reagansoft_system;

-- ------------------------------------------------------------
-- expenses (finance module requested by operations)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(80) NOT NULL,
  description VARCHAR(255) NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  currency VARCHAR(8) NOT NULL DEFAULT 'UGX',
  expense_date DATE NOT NULL,
  project_id INT UNSIGNED NULL,
  recorded_by INT UNSIGNED NULL,
  reference VARCHAR(120) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_expenses_date (expense_date),
  INDEX idx_expenses_category (category),
  INDEX idx_expenses_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- newsletter_subscribers (public marketing opt-in)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  full_name VARCHAR(150) NULL,
  source VARCHAR(60) NOT NULL DEFAULT 'website',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_newsletter_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Guarded index creation (MariaDB/MySQL compatible, idempotent)
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS rsi_add_index;
DELIMITER //
CREATE PROCEDURE rsi_add_index(IN p_table VARCHAR(64), IN p_index VARCHAR(64), IN p_cols VARCHAR(255))
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = p_table
      AND INDEX_NAME = p_index
  ) THEN
    SET @ddl = CONCAT('CREATE INDEX `', p_index, '` ON `', p_table, '` (', p_cols, ')');
    PREPARE stmt FROM @ddl;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END //
DELIMITER ;

CALL rsi_add_index('projects', 'idx_projects_updated', '`updated_at`');
CALL rsi_add_index('projects', 'idx_projects_submitted', '`submitted_at`');
CALL rsi_add_index('invoices', 'idx_invoices_due', '`due_date`');
CALL rsi_add_index('payments', 'idx_payments_status', '`status`');
CALL rsi_add_index('payments', 'idx_payments_created', '`created_at`');
CALL rsi_add_index('contact_messages', 'idx_contact_created', '`created_at`');
CALL rsi_add_index('activity_logs', 'idx_logs_action', '`action`');
CALL rsi_add_index('users', 'idx_users_created', '`created_at`');

DROP PROCEDURE IF EXISTS rsi_add_index;
