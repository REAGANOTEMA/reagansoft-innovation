-- ============================================================
-- Reagan Soft Innovation Limited
-- CLIENT ACCOUNTS  (database/client.sql)
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
--   amara@kirekafarms.com     +256702456789  (Kireka Farm Supplies Ltd)
--   grace@pearlholdings.com   +256778987654  (Pearl Holdings Ltd)
--   david@mulumbasons.com     +256703246810  (Mulumba & Sons Traders)
--   agnesnakato@gmail.com     +256759135790  (Individual client)
--   info@hotelparadiseonthenile.co.ug  +256754412880
--                               (Hotel Paradise on the Nile, Jinja)
--
-- These are demo accounts with placeholder numbers. Change the
-- passwords after logging in, then delete anything you no longer
-- need from this file. Replace the Hotel Paradise contact
-- details with the hotel's real ones before going live.
--
-- Note: database/seed.sql already creates the same client accounts
-- together with demo projects and business data. Use this file when
-- you only need the portal accounts (no demo data), or to
-- restore/reset the client logins.
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
-- 2. Client accounts (upsert by email)
-- ------------------------------------------------------------
INSERT INTO `reagansoft_clients`.`users`
  (full_name, email, phone, password_hash, role, company, address, active) VALUES
  ('Amara Kaggwa',  'amara@kirekafarms.com',   '+256702456789', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Kireka Farm Supplies Ltd', 'Kireka, Kampala', 1),
  ('Grace Ayebare', 'grace@pearlholdings.com', '+256778987654', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Pearl Holdings Ltd', 'Jinja, Uganda', 1),
  ('David Mulumba', 'david@mulumbasons.com',   '+256703246810', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Mulumba & Sons Traders', 'Iganga, Uganda', 1),
  ('Agnes Nakato',  'agnesnakato@gmail.com',   '+256759135790', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', NULL, 'Jinja, Uganda', 1),
('Hotel Paradise on the Nile', 'info@hotelparadiseonthenile.co.ug', '+256754412880', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Hotel Paradise on the Nile', 'Main Street, Jinja, Uganda', 1)
ON DUPLICATE KEY UPDATE
  full_name     = VALUES(full_name),
  phone         = VALUES(phone),
  password_hash = VALUES(password_hash),
  role          = VALUES(role),
  company       = VALUES(company),
  address       = VALUES(address),
  active        = VALUES(active);
