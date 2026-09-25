-- ============================================================
-- Reagan Soft Innovation Limited
-- ADMIN & STAFF ACCOUNTS  (database/admin.sql)
-- ============================================================
-- This file is SELF-SUFFICIENT and SAFE TO RE-RUN.
--   * it creates the `users` table if it is missing
--   * it upserts the accounts by email, so re-running never
--     creates duplicates and never deletes anything
--
-- Database : reagansoft_clients   (the one the app uses)
-- Mirror   : reagansoft_admin    (same schema, host-account copy)
-- Run it with any database selected in phpMyAdmin — every table
-- name below is fully qualified, so the wrong-database error
--     #1146 "Table 'reagansoft_xxx.users' doesn't exist"
-- cannot happen from this file any more.
--
-- Password for every account below:   Reagan@2026
--   admin@reagansoft.com   +256730314979   (Administrator)
--   staff@reagansoft.com   +256772514889   (Staff)
--
-- Change these passwords after first login, then remove any
-- accounts you no longer need from this file.
--
-- Note: database/seed.sql already creates the same two accounts
-- together with demo projects and business data. Use this file
-- when you only need the portal accounts (no demo data), or to
-- restore/reset the admin and staff logins.
-- ============================================================
USE reagansoft_clients;

-- ------------------------------------------------------------
-- 1. The `users` table must exist before the accounts can be
--    written. Kept identical to the definition in schema.sql.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reagansoft_clients`.`users` (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','staff','client') NOT NULL DEFAULT 'client',
  company VARCHAR(190) NULL,
  address VARCHAR(255) NULL,
  avatar VARCHAR(255) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  failed_logins INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Accounts (upsert by email)
-- ------------------------------------------------------------
INSERT INTO `reagansoft_clients`.`users`
  (full_name, email, phone, password_hash, role, company, address, active) VALUES
  ('Reagan Otema',   'admin@reagansoft.com', '+256730314979', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'admin', 'Reagan Soft Innovation Limited', 'Jinja, Uganda', 1),
  ('Sarah Namukasa', 'staff@reagansoft.com', '+256772514889', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'staff', NULL, NULL, 1)
ON DUPLICATE KEY UPDATE
  full_name     = VALUES(full_name),
  phone         = VALUES(phone),
  password_hash = VALUES(password_hash),
  role          = VALUES(role),
  company       = VALUES(company),
  address       = VALUES(address),
  active        = VALUES(active);
