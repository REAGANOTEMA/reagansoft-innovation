-- ============================================================
-- Reagan Soft Innovation Limited
-- WEBSITE DATABASE — UPGRADE (additive and idempotent)
-- Database: reagansoft_website
-- ============================================================
-- Brings an older installation up to the current schema without
-- dropping, truncating or rewriting any existing business data.
-- Every column is checked against information_schema before it is
-- added, so re-running this file changes nothing.
--
-- Run AFTER 01_SCHEMA.sql on an existing site, or use
-- 00_INSTALL_ALL.sql for a complete fresh install.
-- ============================================================
USE reagansoft_website;
SET @rsi_db := DATABASE();

SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `projects` ADD COLUMN `website_url` VARCHAR(255) NULL AFTER `requirements`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'website_url'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

-- ------------------------------------------------------------
-- projects.deliverables
--   A short, comma-separated list of what was actually handed over
--   ("Website, Business system, Mobile apps"). The Work page turns
--   each item into an icon badge, and the filter bar above the grid
--   filters on it.
-- ------------------------------------------------------------
SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `projects` ADD COLUMN `deliverables` VARCHAR(190) NULL AFTER `website_url`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'deliverables'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

-- ------------------------------------------------------------
-- projects.cover_art
--   Which drawn cover scene to use for the project: education,
--   hospitality, agriculture, health, commerce, logistics or
--   systems. Left empty, the Work page picks a scene from the
--   project title instead, so no card is ever left without artwork.
-- ------------------------------------------------------------
SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `projects` ADD COLUMN `cover_art` VARCHAR(40) NULL AFTER `deliverables`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'cover_art'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

-- ------------------------------------------------------------
-- Seed rows this upgrade introduced, so an existing site shows the
-- same Work page as a fresh install. Matched on their natural keys
-- exactly as database/seed.sql does, so re-running changes nothing.
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, phone, password_hash, role, company, address, active) VALUES
('Iganga School of Nursing and Midwifery', 'info@igangaschoolofnursingandmidwifery.ac.ug', '+256701489236', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Iganga School of Nursing and Midwifery', 'Iganga, Uganda', 1),
('Hotel Paradise on the Nile', 'info@hotelparadiseonthenile.co.ug', '+256754412880', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Hotel Paradise on the Nile', 'Main Street, Jinja, Uganda', 1)
ON DUPLICATE KEY UPDATE
  full_name     = VALUES(full_name),
  phone         = VALUES(phone),
  password_hash = VALUES(password_hash),
  role          = VALUES(role),
  company       = VALUES(company),
  address       = VALUES(address),
  active        = VALUES(active);

-- Iganga School of Nursing and Midwifery ΓÇö website, portal and apps,
-- signed off and handed over in June 2026.
INSERT INTO projects (ref_no, client_id, service_id, assigned_to, title, description, requirements, website_url, deliverables, cover_art, budget, priority, status, progress, deadline, started_at, completed_at, submitted_at) VALUES
('RSI-2026-00007',
 (SELECT id FROM users WHERE email = 'info@igangaschoolofnursingandmidwifery.ac.ug'),
 (SELECT id FROM services WHERE slug = 'web-mobile-apps'),
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Website, Student Portal & Apps ΓÇô Iganga School of Nursing and Midwifery',
 'One digital home for the school: a public website for applicants and parents, a records and fees portal the staff use every day, and a mobile app that puts results, fees and announcements in a student''s pocket.',
 'Public website with programmes, fees structure and contacts, online application and enquiry form, student records and fees portal, staff andbursar logins, results and progress reports, Android app for results and school announcements, SMS and email alerts, Google Maps, SSL and nightly backups.',
 'https://igangaschoolofnursingandmidwifery.ac.ug',
 'Website, Business system, Mobile apps',
 'education',
 38000000, 'high', 'COMPLETED', 100, '2026-06-30', '2026-01-12', '2026-06-26', '2025-11-24')
ON DUPLICATE KEY UPDATE
  client_id      = VALUES(client_id),
  service_id     = VALUES(service_id),
  assigned_to    = VALUES(assigned_to),
  title          = VALUES(title),
  description    = VALUES(description),
  requirements   = VALUES(requirements),
  website_url    = VALUES(website_url),
  deliverables   = VALUES(deliverables),
  cover_art      = VALUES(cover_art),
  budget         = VALUES(budget),
  priority       = VALUES(priority),
  status         = VALUES(status),
  progress       = VALUES(progress),
  deadline       = VALUES(deadline),
  started_at     = VALUES(started_at),
  completed_at   = VALUES(completed_at);

-- Hotel Paradise on the Nile ΓÇö the June 2026 handover. This row
-- already existed as an in-progress project, so the upgrade moves
-- it to COMPLETED and records the live address rather than adding a
-- second, contradictory project for the same client.
UPDATE projects
SET title        = 'Hotel Website, Booking System & Guest App ΓÇô Hotel Paradise on the Nile',
    description  = 'A digital front desk for the hotel: a fast website for rooms and facilities, a booking and availability system the front office runs itself, and a guest app for enquiries, directions and offers.',
    requirements = 'Website with rooms, facilities, gallery and contact pages, online availability and booking enquiry system, front-desk reservation calendar, guest and staff logins, Android guest app, WhatsApp and Google Maps, gallery the hotel updates itself, SSL and nightly backups.',
    website_url  = 'https://hotelparadiseonthenile.info',
    deliverables = 'Website, Business system, Mobile apps',
    cover_art    = 'hospitality',
    budget       = 24000000,
    priority     = 'high',
    status       = 'COMPLETED',
    progress     = 100,
    deadline     = '2026-06-30',
    started_at   = '2026-02-09',
    completed_at = '2026-06-19'
WHERE ref_no = 'RSI-2026-00006';

-- Kireka Farm Supplies Ltd ΓÇö the showcase columns, so the whole grid
-- carries the same artwork and badges. No live address is recorded
-- because the client has not published one.
UPDATE projects
SET deliverables = 'Website',
    cover_art    = 'agriculture'
WHERE ref_no = 'RSI-2026-00001';

-- ============================================================
-- PAYMENT DETAILS GATE
-- ============================================================
-- A client may not submit a payment of any kind until the billing
-- details on their account are complete. Without these columns the
-- gate cannot be evaluated, so on an older install the payment form
-- would have nothing to check and would let the payment straight
-- through. Adding them is therefore part of enabling the gate, not
-- an optional extra.
--
--   payer_type      'business' or 'individual'. An individual is not
--                   asked for a company name or a tax number, because
--                   they have neither.
--   tax_id          Uganda Revenue Authority TIN. Required when
--                   payer_type = 'business', so invoices can be
--                   raised against a real taxpayer.
--   national_id     NIN or passport number. Optional, but it is what
--                   lets us settle a payment query without phoning.
--   country         Defaults to Uganda so an existing Ugandan client
--                   is not forced to retype it.
--   city            District or town. The postal address alone is not
--                   enough to route a courier or a bank transfer.
--   billing_terms_at  Set the first time the client accepts the
--                   payment terms. Non-NULL is what marks the gate as
--                   passed, so it must never be back-filled ΓÇö every
--                   client has to accept the terms themselves.
--
-- Existing rows are deliberately left NULL: a client who has already
-- been trading with us is not assumed to have agreed to terms they
-- were never shown. They will be asked once, on their next visit.

SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `users` ADD COLUMN `payer_type` ENUM(''business'',''individual'') NOT NULL DEFAULT ''business'' AFTER `avatar`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'payer_type'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `users` ADD COLUMN `tax_id` VARCHAR(60) NULL AFTER `payer_type`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'tax_id'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `users` ADD COLUMN `national_id` VARCHAR(60) NULL AFTER `tax_id`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'national_id'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `users` ADD COLUMN `country` VARCHAR(80) NOT NULL DEFAULT ''Uganda'' AFTER `national_id`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'country'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `users` ADD COLUMN `city` VARCHAR(90) NULL AFTER `country`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'city'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

SET @rsi_sql := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE `users` ADD COLUMN `billing_terms_at` DATETIME NULL AFTER `city`',
    'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @rsi_db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'billing_terms_at'
);
PREPARE rsi_stmt FROM @rsi_sql;
EXECUTE rsi_stmt;
DEALLOCATE PREPARE rsi_stmt;

-- ------------------------------------------------------------
-- Company contact roles
--   MTN (+256730314979) is the WhatsApp and Mobile Money number.
--   Airtel (+256772514889) is for phone calls only, so it is no
--   longer offered as a payment destination.
--
-- Only the old default is corrected. A number the owner has
-- already customised is left untouched.
-- ------------------------------------------------------------
UPDATE settings
   SET setting_value = '+256772514889'
 WHERE setting_key = 'company_phone'
   AND setting_value = '+256730314979';

UPDATE settings
   SET setting_value = '+256730314979'
 WHERE setting_key = 'company_whatsapp'
   AND setting_value = '';

UPDATE settings
   SET setting_value = ''
 WHERE setting_key = 'payment_airtel_number'
   AND setting_value = '+256772514889';

-- ============================================================
-- Enterprise modules added after the original release
-- ============================================================
CREATE TABLE IF NOT EXISTS expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(80) NOT NULL,
  description VARCHAR(255) NOT NULL,
  amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  currency VARCHAR(8) NOT NULL DEFAULT 'UGX',
  expense_date DATE NOT NULL,
  project_id INT UNSIGNED NULL,
  recorded_by INT UNSIGNED NULL,
  reference VARCHAR(120) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_expenses_date (expense_date),
  KEY idx_expenses_category (category),
  KEY idx_expenses_project (project_id),
  CONSTRAINT fk_expenses_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
  CONSTRAINT fk_expenses_user FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  full_name VARCHAR(150) NULL,
  source VARCHAR(60) NOT NULL DEFAULT 'website',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_newsletter_email (email),
  KEY idx_newsletter_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings introduced by the enterprise upgrade. Existing values are
-- never overwritten: ON DUPLICATE KEY UPDATE setting_key = setting_key.
INSERT INTO settings (setting_key, setting_value) VALUES
('company_hours',           'Mon – Fri, 8:00am – 6:00pm EAT'),
('company_map_url',         'https://www.google.com/maps/search/?api=1&query=Jinja%2C+Uganda'),
('company_linkedin',        ''),
('company_x',               ''),
('company_fb',              ''),
('seo_keywords',            'software development Uganda, website design Jinja, school management system, hospital management system, POS Uganda, Reagansoft Innovation'),
('site_announcement',       ''),
('support_email',           'support@reagansoftinnovation.com'),
('newsletter_enabled',      '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;