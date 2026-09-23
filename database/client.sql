-- ============================================================
-- Reagan Soft Innovation Limited
-- CLIENT ACCOUNTS  (database/client.sql)
-- ============================================================
-- Run AFTER  database/schema.sql  (which creates the `users` table).
-- Safe to run at any time — accounts are upserted by email, so
-- re-running this file never creates duplicates.
--
-- Password for every account below:   Reagan@2026
--   amara@kirekafarms.com     (Kireka Farm Supplies Ltd)
--   grace@pearlholdings.com   (Pearl Holdings Ltd)
--   david@mulumbasons.com     (Mulumba & Sons Traders)
--   agnesnakato@gmail.com     (Individual client)
--
-- Change these passwords after first login, then remove any
-- accounts you no longer need from this file.
--
-- Note: database/seed.sql already creates the same client accounts
-- together with demo projects and business data. Use this file when
-- you only need the portal accounts (no demo data), or to
-- restore/reset client logins.
-- ============================================================
USE reagan_soft_innovation;

INSERT INTO users (full_name, email, phone, password_hash, role, company, address, active) VALUES
('Amara Kaggwa',  'amara@kirekafarms.com',   '+256702456789', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Kireka Farm Supplies Ltd', 'Kireka, Kampala', 1),
('Grace Ayebare', 'grace@pearlholdings.com', '+256778987654', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Pearl Holdings Ltd', 'Jinja, Uganda', 1),
('David Mulumba', 'david@mulumbasons.com',   '+256703246810', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Mulumba & Sons Traders', 'Iganga, Uganda', 1),
('Agnes Nakato',  'agnesnakato@gmail.com',   '+256759135790', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', NULL, 'Jinja, Uganda', 1)
ON DUPLICATE KEY UPDATE
  full_name     = VALUES(full_name),
  phone         = VALUES(phone),
  password_hash = VALUES(password_hash),
  role          = VALUES(role),
  company       = VALUES(company),
  address       = VALUES(address),
  active        = VALUES(active);