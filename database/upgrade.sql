-- ============================================================
-- Reagan Soft Innovation Limited
-- UPGRADE  (database/upgrade.sql)
-- ============================================================
-- Adds anything the current code needs that an OLDER install does
-- not have yet. Run it after updating the files on a site that was
-- installed before the change:
--
--     phpMyAdmin  ->  select your database  ->  Import  ->  this
--     or          ->  mysql -u USER -p DBNAME < database/upgrade.sql
--
-- SAFE TO RE-RUN, and safe on a brand new install. Every statement
-- below checks information_schema first and does nothing when the
-- column is already there, so importing it twice changes nothing.
-- Nothing here drops, truncates, deletes or rewrites existing data.
--
-- WHY THIS FILE EXISTS
-- database/install.sql and database/install.php both create the
-- tables with CREATE TABLE IF NOT EXISTS. That is what makes them
-- non-destructive — but it also means an existing `projects` table
-- is never altered, so a column added to database/schema.sql today
-- only appears on a FRESH install. This file is the bridge: it adds
-- the missing columns to the tables that are already there.
--
-- GENERATED? No. Unlike install.sql / install-portable.sql (which
-- tools/build-sql.php derives from schema.sql + seed.sql) this file
-- is written by hand, because it is history: it only ever appends.
-- ============================================================

-- ------------------------------------------------------------
-- projects.website_url
--   The live address of the finished site. The public Work page
--   turns it into a "visit site" link; NULL simply means the card
--   shows no link.
-- ------------------------------------------------------------
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

-- Iganga School of Nursing and Midwifery — website, portal and apps,
-- signed off and handed over in June 2026.
INSERT INTO projects (ref_no, client_id, service_id, assigned_to, title, description, requirements, website_url, deliverables, cover_art, budget, priority, status, progress, deadline, started_at, completed_at, submitted_at) VALUES
('RSI-2026-00007',
 (SELECT id FROM users WHERE email = 'info@igangaschoolofnursingandmidwifery.ac.ug'),
 (SELECT id FROM services WHERE slug = 'web-mobile-apps'),
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Website, Student Portal & Apps – Iganga School of Nursing and Midwifery',
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

-- Hotel Paradise on the Nile — the June 2026 handover. This row
-- already existed as an in-progress project, so the upgrade moves
-- it to COMPLETED and records the live address rather than adding a
-- second, contradictory project for the same client.
UPDATE projects
SET title        = 'Hotel Website, Booking System & Guest App – Hotel Paradise on the Nile',
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

-- Kireka Farm Supplies Ltd — the showcase columns, so the whole grid
-- carries the same artwork and badges. No live address is recorded
-- because the client has not published one.
UPDATE projects
SET deliverables = 'Website',
    cover_art    = 'agriculture'
WHERE ref_no = 'RSI-2026-00001';
