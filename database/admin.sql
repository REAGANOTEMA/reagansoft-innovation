-- ============================================================
-- Reagan Soft Innovation Limited
-- ADMIN & STAFF ACCOUNTS  (database/admin.sql)
-- ============================================================
-- Run AFTER  database/schema.sql  (which creates the `users` table).
-- Safe to run at any time — accounts are upserted by email, so
-- re-running this file never creates duplicates.
--
-- Password for every account below:   Reagan@2026
--   admin@reagansoft.com   (Administrator)
--   staff@reagansoft.com   (Staff)
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

INSERT INTO users (full_name, email, phone, password_hash, role, company, address, active) VALUES
('Reagan Otema',   'admin@reagansoft.com', '+256730314979', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'admin', 'Reagan Soft Innovation Limited', 'Jinja, Uganda', 1),
('Sarah Namukasa', 'staff@reagansoft.com', '+256771234567', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'staff', NULL, NULL, 1)
ON DUPLICATE KEY UPDATE
  full_name     = VALUES(full_name),
  phone         = VALUES(phone),
  password_hash = VALUES(password_hash),
  role          = VALUES(role),
  company       = VALUES(company),
  address       = VALUES(address),
  active        = VALUES(active);