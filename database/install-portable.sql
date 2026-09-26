-- ============================================================
-- Reagan Soft Innovation Limited
-- INSTALL SCRIPT — for SHARED HOSTING (cPanel, Plesk, etc.)
-- ============================================================
--
-- 1. In your host's control panel create a database and a database
--    user, and give that user ALL privileges on that one database.
-- 2. In phpMyAdmin, SELECT that database in the left-hand list.
-- 3. Import THIS file (Import tab -> choose this file -> Go).
--
-- Why a different file? On shared hosting the account is not allowed
-- to run CREATE DATABASE, and the database is named by the host
-- (for example "reagansoft_rsi", not "reagansoft_clients"). This
-- file therefore contains no CREATE DATABASE and no USE statement,
-- so it creates its tables inside whichever database you selected.
--
-- The app must then be told the real name. Either set the
-- environment variable RSI_DB_NAME, or edit DB_NAME in
-- config/config.php.
--
-- SAFE TO RE-RUN. Nothing here drops, truncates or deletes:
-- tables are created with IF NOT EXISTS and every demo row is
-- inserted only when it is missing.
--
-- GENERATED FILE — do not edit. Source: database/schema.sql and
-- database/seed.sql. Rebuild with: php tools/build-sql.php
-- ============================================================

-- ============================================================
-- Reagan Soft Innovation Limited
-- Database Schema
-- PHP >= 8.0  |  MySQL >= 5.7 / 8.0  |  InnoDB  |  utf8mb4
--
-- Table contents
--   users             ADMIN / STAFF / CLIENT portal accounts
--   services          sellable service catalogue
--   projects          client work (one per request)
--   project_tasks     delivery tasks on a project
--   project_files     private file attachments
--   project_messages  project conversations (is_internal = staff only)
--   notifications     in-portal alerts
--   quotations        + quotation_items  (line items)
--   invoices          + invoice_items    (line items)
--   payments          pending/confirmed payment history
--   contact_messages  contact-form submissions
--   settings          centralised configuration
--   activity_logs     full audit trail
-- ============================================================
-- ============================================================
-- SAFE TO RE-RUN. Every table is created with IF NOT EXISTS, so
-- importing this file never drops or overwrites existing data.
-- For a clean rebuild (wipes every table) run database/reset.sql
-- first — that is the only destructive script in this folder.
--
-- MySQL >= 5.7 / 8.0 · MariaDB >= 10.2  |  InnoDB  |  utf8mb4
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- users (ADMIN / STAFF / CLIENT)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','staff','client') NOT NULL DEFAULT 'client',
  company VARCHAR(190) NULL,
  address VARCHAR(255) NULL,
  avatar VARCHAR(255) NULL,
  -- Payment details. A client cannot submit ANY payment until these are
  -- complete — see inc/billing.php, which is the single place that
  -- decides whether the gate is satisfied. payer_type = 'individual'
  -- is the "I am not paying as a business" escape hatch, and it is what
  -- stops company / tax_id from being demanded from someone who has no
  -- business to declare.
  payer_type ENUM('business','individual') NOT NULL DEFAULT 'business',
  tax_id VARCHAR(60) NULL,
  national_id VARCHAR(60) NULL,
  country VARCHAR(80) NOT NULL DEFAULT 'Uganda',
  city VARCHAR(90) NULL,
  billing_terms_at DATETIME NULL,
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
-- services (database-driven catalogue)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  description TEXT NOT NULL,
  price_min DECIMAL(14,2) NOT NULL DEFAULT 0,   -- lower bound of the price
  price_max DECIMAL(14,2) NULL,                  -- upper bound (NULL = no range)
  currency VARCHAR(8) NOT NULL DEFAULT 'UGX',
  pricing_type ENUM('range','starting_from','contact_for_quote') NOT NULL DEFAULT 'starting_from',
  price_note VARCHAR(120) NOT NULL DEFAULT '',   -- short label, e.g. "Professional websites"
  features TEXT NULL,                 -- one feature per line
  delivery_days INT UNSIGNED NULL,    -- estimated delivery time
  icon VARCHAR(60) NOT NULL DEFAULT 'code-s',   -- icon key from icon set
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_services_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- projects (a service request becomes a project immediately)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref_no VARCHAR(30) NOT NULL UNIQUE,               -- RSI-2026-00001
  client_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NULL,
  assigned_to INT UNSIGNED NULL,                    -- staff/admin user
  title VARCHAR(190) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT NULL,
  -- Showcase fields, read by the public Work page. All three are optional:
  -- a project without a live site simply shows no "visit" link.
  website_url  VARCHAR(255) NULL,                   -- https://… the client can open
  deliverables VARCHAR(190) NULL,                   -- "Website, Business system, Mobile apps"
  cover_art    VARCHAR(40)  NULL,                   -- artwork key: education, hospitality, …
  budget DECIMAL(14,2) NULL,
  priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  status ENUM('NEW','REVIEWING','QUOTATION','APPROVED','IN_PROGRESS','WAITING_FOR_CLIENT','TESTING','COMPLETED','CANCELLED') NOT NULL DEFAULT 'NEW',
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  deadline DATE NULL,
  started_at DATETIME NULL,
  completed_at DATETIME NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_projects_client (client_id),
  INDEX idx_projects_status (status),
  INDEX idx_projects_assigned (assigned_to),
  INDEX idx_projects_service (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- project_tasks
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS project_tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  assigned_to INT UNSIGNED NULL,
  title VARCHAR(190) NOT NULL,
  description TEXT NULL,
  status ENUM('TODO','IN_PROGRESS','REVIEW','COMPLETED') NOT NULL DEFAULT 'TODO',
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  start_date DATE NULL,
  due_date DATE NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_tasks_project (project_id),
  INDEX idx_tasks_status (status),
  INDEX idx_tasks_assigned (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- project_files (private; served only through download.php)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS project_files (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  uploader_id INT UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL UNIQUE,
  file_path VARCHAR(255) NOT NULL,                  -- relative to UPLOAD_DIR
  mime_type VARCHAR(120) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_files_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- project_messages (project-specific; is_internal hidden from clients)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS project_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  sender_id INT UNSIGNED NOT NULL,
  sender_role ENUM('admin','staff','client') NOT NULL,
  message TEXT NOT NULL,
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_messages_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- notifications
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  message TEXT NULL,
  type ENUM('info','success','warning','project','quotation','invoice','task') NOT NULL DEFAULT 'info',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  related_project_id INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (related_project_id) REFERENCES projects(id) ON DELETE SET NULL,
  INDEX idx_notifications_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- quotations
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS quotations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quotation_no VARCHAR(30) NOT NULL UNIQUE,
  project_id INT UNSIGNED NOT NULL,
  client_id INT UNSIGNED NOT NULL,
  issued_on DATE NOT NULL,
  expiry_date DATE NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  status ENUM('draft','sent','accepted','rejected','expired','cancelled') NOT NULL DEFAULT 'draft',
  notes TEXT NULL,
  terms TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_quotes_project (project_id),
  INDEX idx_quotes_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quotation_id INT UNSIGNED NOT NULL,
  description VARCHAR(255) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
  INDEX idx_qi_quotation (quotation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- invoices
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(30) NOT NULL UNIQUE,
  project_id INT UNSIGNED NOT NULL,
  client_id INT UNSIGNED NOT NULL,
  quotation_id INT UNSIGNED NULL,
  due_date DATE NULL,
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  discount DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  amount_paid DECIMAL(14,2) NOT NULL DEFAULT 0,
  status ENUM('draft','sent','partially_paid','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  is_deposit TINYINT(1) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE SET NULL,
  INDEX idx_invoices_project (project_id),
  INDEX idx_invoices_client (client_id),
  INDEX idx_invoices_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  description VARCHAR(255) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  INDEX idx_ii_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- payments (manual confirmation workflow)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  method ENUM('mtn_momo','airtel_money','bank','cash','other') NOT NULL DEFAULT 'other',
  reference VARCHAR(120) NULL,
  status ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  received_by INT UNSIGNED NULL,
  notes TEXT NULL,
  confirmed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_payments_invoice (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- contact_messages (stored contact-form submissions)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  company VARCHAR(190) NULL,
  subject VARCHAR(190) NULL,
  message TEXT NOT NULL,
  ip_address VARCHAR(45) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contact_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- settings (centralized configuration)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL UNIQUE,
  setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- activity_logs (audit trail)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,             -- NULL for guests
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(60) NULL,
  entity_id INT UNSIGNED NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_logs_entity (entity_type, entity_id),
  INDEX idx_logs_user (user_id),
  INDEX idx_logs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Reagan Soft Innovation Limited
-- SEED DATA  (database/seed.sql)
-- ============================================================
-- Database : reagansoft_clients  (the one the app uses)
-- Run AFTER database/schema.sql  — or use database/install.sql,
-- which is this file plus the schema in one import.
--
-- SAFE TO RE-RUN. Every row is matched on its natural key
-- (slug, setting_key, email, ref_no, quotation_no, invoice_no,
-- title, reference) and inserted only when it is missing, so
-- re-importing this file updates the demo data and never
-- duplicates or deletes anything.
--
-- Contents
--   01. SERVICES            the sellable catalogue
--   02. SETTINGS            brand, contact & payment configuration
--   03. ADMIN & STAFF       portal accounts (admin@reagansoft.com,
--                           staff@reagansoft.com)
--   04. CLIENTS             client portal accounts
--   05. PROJECTS            sample client work (RSI-2026-00001 …)
--   06. TASKS               delivery tasks on the projects
--   07. MESSAGES            project conversations
--   08. QUOTATIONS + ITEMS  one sent quotation with line items
--   09. INVOICES + ITEMS    issued & paid invoices
--   10. PAYMENTS            confirmed payment history
--   11. NOTIFICATIONS       in-portal alerts for the demo users
--
-- Password for EVERY demo account below:   Reagan@2026
--   admin@reagansoft.com   +256730314979   (Administrator)
--   staff@reagansoft.com   +256772514889   (Staff)
--   amara@kirekafarms.com, grace@pearlholdings.com,
--   david@mulumbasons.com, agnesnakato@gmail.com,
--   info@hotelparadiseonthenile.co.ug   (Clients)
-- Change these passwords after logging in, then delete anything
-- you no longer need from this file.
--
-- To restore the logins without touching the demo data use
-- database/admin.sql and database/client.sql instead.
-- ============================================================

-- ============================================================
-- 01 · SERVICES
-- ============================================================
-- price_min / price_max = the authoritative service ranges (UGX)
--               · websites / ecommerce        500,000 – 10,000,000
--               · receipt & billing systems    3,000,000 – 10,000,000
--               · web & mobile apps            from 10,000,000
--               · business systems / software  20,000,000 – 40,000,000
--               · branding / maintenance       starting price only
--               · custom solutions             contact for a quote
-- ============================================================
INSERT INTO services (name, slug, description, price_min, price_max, currency, pricing_type, price_note, features, delivery_days, icon, status, sort_order) VALUES
('Business Website Development', 'business-website', 'A professional, responsive company website with clear structure, contact integration, SEO foundations and a design that represents your brand.', 500000, 10000000, 'UGX', 'range', 'Professional websites',
 'Custom responsive design for mobile, tablet and desktop
Contact & enquiry form
WhatsApp and phone integration
Search engine friendly structure
Google Map & business details
Training on how to manage content',
  14, 'globe', 'active', 1),

('Ecommerce Development', 'ecommerce', 'Online stores with product catalogues, order management and a payment architecture ready for mobile money and bank integration.', 500000, 10000000, 'UGX', 'range', 'Websites & online stores',
 'Product catalogue and categories
Shopping cart and checkout
Order management dashboard
Mobile money and bank payment ready architecture
Inventory management
Delivery and order tracking',
  30, 'cart', 'active', 2),

('Custom Business Systems', 'business-systems', 'Management systems built for schools, hospitals, NGOs, government offices and companies, with client portals, dashboards, workflow automation and database driven tools around your real business processes.', 20000000, 40000000, 'UGX', 'range', 'Custom business systems',
 'Role based user access
Online fees, payments & mobile money
Dashboards and reports
Workflow automation
SMS notifications
User training and handover',
  30, 'cpu', 'active', 3),

('Software Development', 'software-dev', 'Custom web applications and internal business systems built with modern PHP/MySQL technology, secure by default and tailored to you.', 20000000, 40000000, 'UGX', 'range', 'Custom software & business systems',
 'Requirements analysis
Custom database design
Secure authentication
Administration panels
API and integration ready
Deployment and support',
  30, 'code-s', 'active', 4),

('Web & Mobile App Development', 'web-mobile-apps', 'Business apps built to work as a website, an Android app and an iOS app, prepared, tested and published to the Apple App Store and Google Play.', 10000000, NULL, 'UGX', 'starting_from', 'Apps for the App Store & Google Play',
 'Web, Android & iOS builds
App Store & Google Play publication
Push and SMS notifications
Mobile money & bank payments
Dashboards and admin panels
Testing across devices',
  30, 'mobile', 'active', 5),

('Receipt & Billing Automation', 'receipt-billing', 'Automatic receipt and invoice generation, so every sale prints or sends a professional receipt instantly, with totals, mobile money references and daily reports.', 3000000, 10000000, 'UGX', 'range', 'Automatic receipts & invoices',
 'Automatic receipt printing and sending
Invoice generation and numbering
Mobile money reference capture
Daily cash & sales totals
Shop, school and clinic billing
Report formats for daily and monthly use',
  14, 'receipt', 'active', 6),

('Custom Digital Solutions', 'custom-solutions', 'A specific business problem that does not fit a ready made package? We design and build the right digital solution for it.', 0, NULL, 'UGX', 'contact_for_quote', 'Contact for a quote',
 'Consultation and scoping
Feasibility recommendation
Bespoke design and build
Testing and quality assurance
Deployment
Ongoing support',
  21, 'layers', 'active', 7),

('Graphic & Branding Design', 'branding', 'Logos, brand guidelines, business cards, posters and digital design that give your company a consistent and professional identity.', 250000, NULL, 'UGX', 'starting_from', 'From',
 'Logo design options
Brand colour and typography
Business cards and letterheads
Social media graphics
Print ready file delivery
Usage guideline document',
  7, 'palette', 'active', 8),

('Website Maintenance', 'maintenance', 'Security updates, backups, content changes and technical support so your website stays fast, safe and up to date.', 150000, NULL, 'UGX', 'starting_from', 'From',
 'Monthly security checks
Regular backups
Content and image updates
Performance monitoring
Priority technical support
Uptime reporting',
  1, 'shield', 'active', 9),

('Video Game Development', 'video-games', 'Custom video games and interactive media for mobile, web and PC, covering game design, graphics, scoring, levels and store ready builds.', 12000000, NULL, 'UGX', 'starting_from', 'Custom games & interactive media',
 'Game design and story development
2D and 3D game graphics
Mobile, web and PC builds
Levels, scores and progress saving
Multiplayer and leaderboards
Testing and store publication support',
  45, 'gamepad', 'active', 10),

('Digital Books & Publications', 'digital-books', 'Professional layout and cover design, editing and ebook formats so your books look great in print and on Kindle, tablets and phones.', 1000000, NULL, 'UGX', 'starting_from', 'Ebooks and print ready books',
 'Book layout and cover design
Ebook formats for common readers
Print ready file delivery
Editing and proofreading
Tables, charts and diagrams
Professional illustration support',
  14, 'book', 'active', 11),

('Chatbots & Smart Automation', 'chat-automation', 'Business chatbots and smart automation that answer customer questions, capture leads and handle routine tasks automatically, day and night.', 5000000, 15000000, 'UGX', 'range', 'Chatbots & business automation',
 'Business chatbot built into your site
Automated replies and FAQs
Lead capture and handover
Workflow automation
SMS and WhatsApp integration
Training and handover',
  21, 'chat', 'active', 12),

('System Integration & Payments', 'system-integration', 'Connecting your systems, websites and apps: mobile money and bank payment links, SMS services, APIs and data exchange between the tools you already use.', 3000000, 15000000, 'UGX', 'range', 'Payments, SMS, APIs & system links',
 'Mobile money & bank payment links
SMS notification services
API connections between systems
Data import and export
System to system linking
Testing and security review',
  14, 'link', 'active', 13),

('Cybersecurity & Audits', 'cybersecurity', 'Security audits, vulnerability fixes, backups and data protection so your website and systems stay safe from attacks and data loss.', 1000000, NULL, 'UGX', 'starting_from', 'Security audits & protection',
 'Full security audit of your systems
Vulnerability and patch fixes
Backup and recovery setup
User access and permission review
Threat and activity monitoring
Security handover guide',
  7, 'lock', 'active', 14)
ON DUPLICATE KEY UPDATE
  name          = VALUES(name),
  description   = VALUES(description),
  price_min     = VALUES(price_min),
  price_max     = VALUES(price_max),
  currency      = VALUES(currency),
  pricing_type  = VALUES(pricing_type),
  price_note    = VALUES(price_note),
  features      = VALUES(features),
  delivery_days = VALUES(delivery_days),
  icon          = VALUES(icon),
  status        = VALUES(status),
  sort_order    = VALUES(sort_order);

-- ============================================================
-- 02 · SETTINGS  (upsert by setting_key)
-- ============================================================
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name',            'Reagan Soft Innovation Limited'),
('company_tagline',         'Innovating today for a smarter tomorrow.'),
('company_founder',         'Reagan Otema'),
('company_phone',           '+256730314979'),
('company_email',           'info@reagansoft.com'),
('company_address',         'Jinja, Uganda'),
  ('company_whatsapp',        '+256730314979'),
('company_fb',              ''),
('company_x',               ''),
('company_linkedin',        ''),
('company_about',           'Reagan Soft Innovation Limited is a software company based in Jinja, Uganda. We design and build websites, business systems, ecommerce stores and custom software for businesses across the country, and we stay around to look after them after launch.'),
('currency',                'UGX'),
('tax_percent',             '0'),
('invoices_due_days',       '14'),
('payment_mtn_number',      '+256730314979'),
('payment_airtel_number',   '+256772514889'),
('payment_bank_details',    ''),
('payment_gateway_status',  'not_configured'),
('project_deposit',         '200000'),
('registration_open',       '1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================================
-- 03 · ADMIN & STAFF  (upsert by email)
-- ============================================================
-- The administrator manages the whole portal. Staff serve clients
-- (requests, projects, quotations, invoices, files, messages).
-- ============================================================
INSERT INTO users (full_name, email, phone, password_hash, role, company, address, active) VALUES
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

-- ============================================================
-- 04 · CLIENTS  (upsert by email)
-- ============================================================
-- payer_type / tax_id / country / city / billing_terms_at are the
-- payment-details gate (inc/billing.php). A client cannot submit any
-- payment until these are complete, so the demo accounts are seeded
-- complete: a reviewer can log in as any of them and walk a payment
-- all the way through without inventing data. A brand new account
-- registered from the website starts with them empty and is walked
-- through the gate, which is the path a real client takes.
--
-- Agnes Nakato has no company, so she is seeded as an 'individual'.
-- She is the worked example of the branch that does not demand a
-- company name or a tax number from someone who has neither.
INSERT INTO users (full_name, email, phone, password_hash, role, company, address, payer_type, tax_id, national_id, country, city, billing_terms_at, active) VALUES
('Amara Kaggwa',  'amara@kirekafarms.com',   '+256702456789', '$2y$10$yzlD5fteOLtLy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Kireka Farm Supplies Ltd', 'Kireka, Kampala',   'business',  '1002456789', '256754321098', 'Uganda', 'Kampala', '2026-01-12 09:14:00', 1),
('Grace Ayebare', 'grace@pearlholdings.com', '+256778987654', '$2y$10$yzlD5fteOLtLy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Pearl Holdings Ltd', 'Jinja, Uganda',            'business',  '1008765432', '256701234567', 'Uganda', 'Jinja',   '2026-01-18 10:02:00', 1),
('David Mulumba', 'david@mulumbasons.com',   '+256703246810', '$2y$10$yzlD5fteOLtLy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Mulumba & Sons Traders', 'Iganga, Uganda',        'business',  '1011325468', '256788990011', 'Uganda', 'Iganga',  '2026-02-03 14:37:00', 1),
('Agnes Nakato',  'agnesnakato@gmail.com',   '+256759135790', '$2y$10$yzlD5fteOLtLy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', NULL, 'Plot 14, Nalufenya, Jinja',                'individual', NULL,      '256700112233', 'Uganda', 'Jinja',   '2026-02-11 08:55:00', 1),
('Hotel Paradise on the Nile', 'info@hotelparadiseonthenile.co.ug', '+256754412880', '$2y$10$yzlD5fteOLtLy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Hotel Paradise on the Nile', 'Main Street, Jinja, Uganda', 'business', '1015246800', NULL, 'Uganda', 'Jinja', '2026-01-25 11:20:00', 1),
('Iganga School of Nursing and Midwifery', 'info@igangaschoolofnursingandmidwifery.ac.ug', '+256701489236', '$2y$10$yzlD5fteOLtLy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Iganga School of Nursing and Midwifery', 'Iganga, Uganda', 'business', '1009988776', NULL, 'Uganda', 'Iganga', '2026-01-14 16:08:00', 1)
ON DUPLICATE KEY UPDATE
  full_name        = VALUES(full_name),
  phone            = VALUES(phone),
  password_hash    = VALUES(password_hash),
  role             = VALUES(role),
  company          = VALUES(company),
  address          = VALUES(address),
  payer_type       = VALUES(payer_type),
  tax_id           = VALUES(tax_id),
  national_id      = VALUES(national_id),
  country          = VALUES(country),
  city             = VALUES(city),
  billing_terms_at = VALUES(billing_terms_at),
  active           = VALUES(active);

-- ============================================================
-- 05 · PROJECTS  (upsert by ref_no)
-- ============================================================
-- Every reference below is resolved by LOOKUP (user email, service
-- slug), never by a hard-coded id. Ids are not predictable on a
-- re-run — MySQL and MariaDB consume an auto-increment value for
-- every `ON DUPLICATE KEY UPDATE` row even when nothing is
-- inserted — so a hard-coded id would attach demo data to the
-- wrong client. Lookups make this file safe on a fresh install
-- and on any database that already has rows.
--
-- The last three columns — website_url, deliverables and cover_art
-- — are what the public Work page (portfolio.php) reads. They are
-- showcase data, not project-management data, which is why they sit
-- apart from budget / status / dates:
--
--   website_url   the live address of the finished site. The card
--                 grows a "visit site" link; NULL means no link.
--   deliverables  what was actually handed over, comma separated.
--                 Each item becomes an icon badge on the card, and
--                 the filter bar above the grid filters on it.
--   cover_art     which drawn cover scene to use — education,
--                 hospitality, agriculture, health, commerce,
--                 logistics or systems. Empty means the page infers
--                 one from the title, so a card is never bare.
--
-- On an install that predates these columns, run database/upgrade.sql
-- once: it adds them to the table that is already there and loads
-- the same rows, so an old site ends up identical to a fresh one.
-- ============================================================
INSERT INTO projects (ref_no, client_id, service_id, assigned_to, title, description, requirements, website_url, deliverables, cover_art, budget, priority, status, progress, deadline, started_at, completed_at, submitted_at) VALUES
('RSI-2026-00001',
 (SELECT id FROM users WHERE email = 'amara@kirekafarms.com'),
 (SELECT id FROM services WHERE slug = 'business-website'),
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Company Website – Kireka Farm Supplies Ltd',
 'A responsive company website that presents the farm supply catalogue, the company story and the contact details, with a lead-capture enquiry form and the SEO foundations a rural supplier needs to be found on Google.',
 'Six information pages, enquiry form, WhatsApp integration, Google Map, photo gallery.',
 NULL, 'Website', 'agriculture',
 8500000, 'normal', 'COMPLETED', 100, '2026-04-20', '2026-03-02', '2026-04-18', '2026-02-26'),
('RSI-2026-00002',
 (SELECT id FROM users WHERE email = 'grace@pearlholdings.com'),
 (SELECT id FROM services WHERE slug = 'business-systems'),
 (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Sales & Stock Management System – Pearl Holdings Ltd',
 'A custom management system for tracking sales, stock levels, suppliers and daily reports across the Pearl Holdings outlets in the Eastern region.',
 'User roles for cashier and manager, product and stock modules, purchase tracking, daily sales reports, backup.',
 NULL, NULL, 'commerce',
 14500000, 'high', 'IN_PROGRESS', 70, '2026-09-30', '2026-07-06', NULL, '2026-07-01'),
('RSI-2026-00003',
 (SELECT id FROM users WHERE email = 'david@mulumbasons.com'),
 (SELECT id FROM services WHERE slug = 'ecommerce'),
 (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Ecommerce Store – Mulumba & Sons Traders',
 'An online store for hardware and farm inputs with a product catalogue, cart, order management and mobile money ready payment architecture.',
 'Product categories and search, cart and checkout, order dashboard, inventory, mobile money payment ready structure.',
 NULL, NULL, 'commerce',
 9800000, 'normal', 'QUOTATION', 0, NULL, NULL, NULL, '2026-09-10'),
('RSI-2026-00004',
 (SELECT id FROM users WHERE email = 'agnesnakato@gmail.com'),
 (SELECT id FROM services WHERE slug = 'business-systems'),
 NULL,
 'Customer Feedback Portal – Agnes Nakato',
 'A small portal where customers submit feedback and track the response, with a staff review dashboard.',
 'Feedback form, automatic acknowledgements, staff review queue, simple analytics.',
 NULL, NULL, 'systems',
 3500000, 'low', 'NEW', 0, NULL, NULL, NULL, '2026-09-20'),
('RSI-2026-00005',
 (SELECT id FROM users WHERE email = 'amara@kirekafarms.com'),
 (SELECT id FROM services WHERE slug = 'branding'),
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Brand Refresh & Business Cards – Kireka Farm Supplies Ltd',
 'Logo refresh, updated colour palette and print ready business cards and letterheads for the farm supply brand.',
 'Two logo options, brand colours and typography, business cards, letterheads.',
 NULL, 'Branding', 'agriculture',
 650000, 'normal', 'REVIEWING', 15, NULL, NULL, NULL, '2026-09-18'),
('RSI-2026-00006',
 (SELECT id FROM users WHERE email = 'info@hotelparadiseonthenile.co.ug'),
 (SELECT id FROM services WHERE slug = 'business-website'),
 (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Hotel Website, Booking System & Guest App – Hotel Paradise on the Nile',
 'A digital front desk for the hotel: a fast website that presents the rooms, the facilities and the view over the Nile, a booking and availability system the front office runs itself, and a guest app for enquiries, directions and offers.',
 'Website with rooms, facilities, gallery and contact pages, online availability and booking enquiry system, front-desk reservation calendar, guest and staff logins, Android guest app, WhatsApp and Google Maps, gallery the hotel updates itself, SSL and nightly backups.',
 'https://hotelparadiseonthenile.info', 'Website, Business system, Mobile apps', 'hospitality',
 24000000, 'high', 'COMPLETED', 100, '2026-06-30', '2026-02-09', '2026-06-19', '2026-01-14'),
('RSI-2026-00007',
 (SELECT id FROM users WHERE email = 'info@igangaschoolofnursingandmidwifery.ac.ug'),
 (SELECT id FROM services WHERE slug = 'web-mobile-apps'),
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Website, Student Portal & Apps – Iganga School of Nursing and Midwifery',
 'One digital home for the school: a public website for applicants and parents, a records and fees portal the staff and bursar use every day, and a mobile app that puts results, fees and school announcements in a student''s pocket.',
 'Public website with programmes, fees structure and contacts, online application and enquiry form, student records and fees portal, staff and bursar logins, results and progress reports, Android app for results and school announcements, SMS and email alerts, Google Maps, SSL and nightly backups.',
 'https://igangaschoolofnursingandmidwifery.ac.ug', 'Website, Business system, Mobile apps', 'education',
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

-- ============================================================
-- 06 · TASKS  (inserted only when the task title is not there yet)
-- ============================================================
INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Design and structure', 'Plan sitemap, wireframes and page structure for the Kireka site.', 'COMPLETED', 100, 'high', '2026-03-02', '2026-03-08',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001') AND title = 'Design and structure');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Home page build', 'Build the responsive home page and header.', 'COMPLETED', 100, 'high', '2026-03-09', '2026-03-16',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001') AND title = 'Home page build');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Content upload & launch', 'Upload all copy and images, test on mobile and launch.', 'COMPLETED', 100, 'medium', '2026-03-20', '2026-04-18',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001') AND title = 'Content upload & launch');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Database design', 'Design tables for products, stock, sales and suppliers.', 'COMPLETED', 100, 'high', '2026-07-06', '2026-07-12',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002') AND title = 'Database design');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Sales dashboard module', 'Build the cashier sales entry and manager dashboard.', 'IN_PROGRESS', 60, 'high', '2026-07-15', '2026-09-01',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002') AND title = 'Sales dashboard module');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Reports & exports', 'Daily sales reports and CSV export for management.', 'TODO', 0, 'medium', NULL, '2026-09-25',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002') AND title = 'Reports & exports');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00003'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Catalogue & cart', 'Product catalogue, search and shopping cart.', 'TODO', 0, 'high', NULL, '2026-10-05',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00003') AND title = 'Catalogue & cart');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Rooms & facilities pages', 'Build the rooms, facilities and gallery pages with the hotel photography.', 'COMPLETED', 100, 'high', '2026-02-09', '2026-03-20',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND title = 'Rooms & facilities pages');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Booking enquiry form', 'Availability enquiry form that reaches the hotel WhatsApp and email, with the stay details captured.', 'COMPLETED', 100, 'high', '2026-03-23', '2026-04-24',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND title = 'Booking enquiry form');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Booking calendar & guest app', 'Front-desk reservation calendar plus the Android guest app for enquiries and offers.', 'COMPLETED', 100, 'high', '2026-04-27', '2026-06-05',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND title = 'Booking calendar & guest app');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Launch & handover', 'Final content check, mobile testing, launch and training for the hotel team.', 'COMPLETED', 100, 'medium', '2026-06-08', '2026-06-19',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND title = 'Launch & handover');

-- Iganga School of Nursing and Midwifery
INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'School website', 'Public site: programmes, fees structure, application and enquiry forms, news and contacts.', 'COMPLETED', 100, 'high', '2026-01-12', '2026-02-27',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007') AND title = 'School website');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Student records & fees portal', 'Admissions, class registers, fees per term, receipts and progress reports for staff and bursar logins.', 'COMPLETED', 100, 'high', '2026-03-02', '2026-05-08',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007') AND title = 'Student records & fees portal');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'Android app', 'Student app with results, fee balance and school announcements, plus SMS and email alerts.', 'COMPLETED', 100, 'high', '2026-05-11', '2026-06-19',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007') AND title = 'Android app');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Training & handover', 'Train the registrar and bursar, load real data, go live and hand over with backups running.', 'COMPLETED', 100, 'medium', '2026-06-22', '2026-06-26',
 (SELECT id FROM users WHERE email = 'admin@reagansoft.com')
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007') AND title = 'Training & handover');

-- ============================================================
-- 07 · MESSAGES  (inserted only when the same message is not there)
-- ============================================================
INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001'), (SELECT id FROM users WHERE email = 'amara@kirekafarms.com'),
 'client', 'Thank you for the great work on our website. The team at Kireka Farm Supplies is very happy with it.', 0, '2026-04-20 09:30:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001') AND message LIKE 'Thank you for the great work%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'admin', 'Thank you Amara! It was a pleasure working with you. We are available any time for maintenance.', 0, '2026-04-20 11:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001') AND message LIKE 'Thank you Amara!%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002'), (SELECT id FROM users WHERE email = 'grace@pearlholdings.com'),
 'client', 'Please confirm the final layout for the sales dashboard.', 0, '2026-09-01 10:15:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002') AND message LIKE 'Please confirm the final layout%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'staff', 'Sharing the dashboard preview this week, and we are on track for the end-of-month review.', 0, '2026-09-02 08:40:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002') AND message LIKE 'Sharing the dashboard preview%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'admin', 'Reminder: confirm Pearl Holdings DB backup schedule before go-live.', 1, '2026-09-02 09:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002') AND message LIKE 'Reminder: confirm Pearl%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'info@hotelparadiseonthenile.co.ug'),
 'client', 'Please send the updated room photographs and the official contact numbers for the footer and booking form.', 0, '2026-03-02 10:20:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND message LIKE 'Please send the updated room%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'staff@reagansoft.com'),
 'staff', 'Rooms and facilities pages are in progress. Send the photographs as soon as you can and we will load them immediately.', 0, '2026-03-03 08:15:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND message LIKE 'Rooms and facilities pages%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'info@hotelparadiseonthenile.co.ug'),
 'client', 'The site looks wonderful and the booking form already brought us two walk-in guests. Please show us how to update the gallery ourselves.', 0, '2026-06-19 13:40:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND message LIKE 'The site looks wonderful%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'admin', 'Congratulations on your launch. The gallery, rooms and offers are all editable from the front desk login, and we are on WhatsApp whenever you need us.', 0, '2026-06-19 15:05:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006') AND message LIKE 'Congratulations on your launch%');

-- ============================================================
-- 08 · QUOTATIONS + ITEMS  (upsert by quotation_no)
-- ============================================================
INSERT INTO quotations (quotation_no, project_id, client_id, issued_on, expiry_date, subtotal, discount, tax_percent, total, status, notes, terms) VALUES
('QT-2026-0001',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00003'),
 (SELECT id FROM users WHERE email = 'david@mulumbasons.com'),
 CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 9800000, 0, 0, 9800000, 'sent',
 'E-commerce build for Mulumba & Sons Traders.',
 '50% deposit to start, balance on delivery. Free support for 30 days after launch.')
ON DUPLICATE KEY UPDATE
  project_id   = VALUES(project_id),
  client_id    = VALUES(client_id),
  expiry_date  = VALUES(expiry_date),
  subtotal     = VALUES(subtotal),
  discount     = VALUES(discount),
  tax_percent  = VALUES(tax_percent),
  total        = VALUES(total),
  status       = VALUES(status),
  notes        = VALUES(notes),
  terms        = VALUES(terms);

-- items are matched on quotation_no, so re-running never duplicates them
INSERT INTO quotation_items (quotation_id, description, quantity, unit_price)
SELECT q.id, 'E-commerce design & development', 1, 7000000
FROM quotations q WHERE q.quotation_no = 'QT-2026-0001'
  AND NOT EXISTS (SELECT 1 FROM quotation_items qi WHERE qi.quotation_id = q.id AND qi.description = 'E-commerce design & development');

INSERT INTO quotation_items (quotation_id, description, quantity, unit_price)
SELECT q.id, 'Mobile money and bank payment ready architecture', 1, 2000000
FROM quotations q WHERE q.quotation_no = 'QT-2026-0001'
  AND NOT EXISTS (SELECT 1 FROM quotation_items qi WHERE qi.quotation_id = q.id AND qi.description = 'Mobile money and bank payment ready architecture');

INSERT INTO quotation_items (quotation_id, description, quantity, unit_price)
SELECT q.id, 'Testing, training & launch support', 1, 800000
FROM quotations q WHERE q.quotation_no = 'QT-2026-0001'
  AND NOT EXISTS (SELECT 1 FROM quotation_items qi WHERE qi.quotation_id = q.id AND qi.description = 'Testing, training & launch support');

-- ============================================================
-- 09 · INVOICES + ITEMS  (upsert by invoice_no)
-- ============================================================
INSERT INTO invoices (invoice_no, project_id, client_id, quotation_id, due_date, subtotal, discount, tax_percent, total, amount_paid, status, is_deposit, notes) VALUES
('INV-2026-0001',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00003'),
 (SELECT id FROM users WHERE email = 'david@mulumbasons.com'),
 (SELECT id FROM quotations WHERE quotation_no = 'QT-2026-0001'),
 DATE_ADD(CURDATE(), INTERVAL 14 DAY), 9800000, 0, 0, 9800000, 0, 'sent', 1, 'Deposit invoice for QT-2026-0001.'),
('INV-2026-0002',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001'),
 (SELECT id FROM users WHERE email = 'amara@kirekafarms.com'),
 NULL, '2026-04-24', 8500000, 0, 0, 8500000, 8500000, 'paid', 0, 'Final payment for the Kireka Farm Supplies website.')
ON DUPLICATE KEY UPDATE
  project_id    = VALUES(project_id),
  client_id     = VALUES(client_id),
  quotation_id  = VALUES(quotation_id),
  due_date      = VALUES(due_date),
  subtotal      = VALUES(subtotal),
  discount      = VALUES(discount),
  tax_percent   = VALUES(tax_percent),
  total         = VALUES(total),
  amount_paid   = VALUES(amount_paid),
  status        = VALUES(status),
  is_deposit    = VALUES(is_deposit),
  notes         = VALUES(notes);

-- items are matched on invoice_no, so re-running never duplicates them
INSERT INTO invoice_items (invoice_id, description, quantity, unit_price)
SELECT i.id, 'E-commerce development (first phase)', 1, 4900000
FROM invoices i WHERE i.invoice_no = 'INV-2026-0001'
  AND NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.invoice_id = i.id AND ii.description = 'E-commerce development (first phase)');

INSERT INTO invoice_items (invoice_id, description, quantity, unit_price)
SELECT i.id, 'Payment architecture setup', 1, 4900000
FROM invoices i WHERE i.invoice_no = 'INV-2026-0001'
  AND NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.invoice_id = i.id AND ii.description = 'Payment architecture setup');

INSERT INTO invoice_items (invoice_id, description, quantity, unit_price)
SELECT i.id, 'Company website development', 1, 8500000
FROM invoices i WHERE i.invoice_no = 'INV-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM invoice_items ii WHERE ii.invoice_id = i.id AND ii.description = 'Company website development');

-- ============================================================
-- 10 · PAYMENTS  (matched on invoice + reference)
-- ============================================================
INSERT INTO payments (invoice_id, amount, method, reference, status, received_by, notes, confirmed_at, created_at)
SELECT i.id, 8500000, 'bank', 'KFS-WEB-0420', 'confirmed', (SELECT id FROM users WHERE email = 'admin@reagansoft.com'),
 'Transferred from Kireka Farm Supplies bank account.', '2026-04-24 14:00:00', '2026-04-24 14:00:00'
FROM invoices i WHERE i.invoice_no = 'INV-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.invoice_id = i.id AND p.reference = 'KFS-WEB-0420');

-- ============================================================
-- 11 · NOTIFICATIONS  (matched on user + title)
-- ============================================================
INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT (SELECT id FROM users WHERE email = 'amara@kirekafarms.com'), 'Project completed',
 'Your website project RSI-2026-00001 was completed and launched. Thank you!', 'success',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00001'), 0, '2026-04-18 12:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = (SELECT id FROM users WHERE email = 'amara@kirekafarms.com') AND title = 'Project completed');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT (SELECT id FROM users WHERE email = 'grace@pearlholdings.com'), 'Task update',
 'A task in your project RSI-2026-00002 was updated.', 'task',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00002'), 0, '2026-09-02 09:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = (SELECT id FROM users WHERE email = 'grace@pearlholdings.com') AND title = 'Task update');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT (SELECT id FROM users WHERE email = 'david@mulumbasons.com'), 'Your quotation is ready',
 'Your quotation QT-2026-0001 is waiting for your approval.', 'quotation',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00003'), 0, '2026-09-10 09:30:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = (SELECT id FROM users WHERE email = 'david@mulumbasons.com') AND title = 'Your quotation is ready');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT (SELECT id FROM users WHERE email = 'admin@reagansoft.com'), 'New request received',
 'Agnes Nakato submitted a new request for review.', 'info',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00004'), 0, '2026-09-20 10:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = (SELECT id FROM users WHERE email = 'admin@reagansoft.com') AND title = 'New request received');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT (SELECT id FROM users WHERE email = 'info@hotelparadiseonthenile.co.ug'), 'Your project is live',
 'Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.', 'project',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00006'), 0, '2026-06-19 15:30:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = (SELECT id FROM users WHERE email = 'info@hotelparadiseonthenile.co.ug') AND title = 'Your project is in progress');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT (SELECT id FROM users WHERE email = 'info@igangaschoolofnursingandmidwifery.ac.ug'), 'Your project is live',
 'The school website, student portal and app are live and the team has been trained. Sign in to your portal to get started.', 'project',
 (SELECT id FROM projects WHERE ref_no = 'RSI-2026-00007'), 0, '2026-06-26 16:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = (SELECT id FROM users WHERE email = 'info@igangaschoolofnursingandmidwifery.ac.ug') AND title = 'Your project is live');
