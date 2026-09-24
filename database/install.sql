-- ============================================================
-- Reagan Soft Innovation Limited
-- INSTALL SCRIPT (for phpMyAdmin / SQL import)
--
-- This file combines the schema and the seed data into a single
-- script. In phpMyAdmin: Import tab - choose this file - Go.
-- It creates the database, all tables and demo data automatically.
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
CREATE DATABASE IF NOT EXISTS reagan_soft_innovation
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reagan_soft_innovation;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS settings;
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
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- users (ADMIN / STAFF / CLIENT)
-- ------------------------------------------------------------
CREATE TABLE users (
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
-- services (database-driven catalogue)
-- ------------------------------------------------------------
CREATE TABLE services (
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
CREATE TABLE projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref_no VARCHAR(30) NOT NULL UNIQUE,               -- RSI-2026-00001
  client_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NULL,
  assigned_to INT UNSIGNED NULL,                    -- staff/admin user
  title VARCHAR(190) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT NULL,
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
CREATE TABLE project_tasks (
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
CREATE TABLE project_files (
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
CREATE TABLE project_messages (
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
CREATE TABLE notifications (
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
CREATE TABLE quotations (
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

CREATE TABLE quotation_items (
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
CREATE TABLE invoices (
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

CREATE TABLE invoice_items (
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
CREATE TABLE payments (
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
CREATE TABLE contact_messages (
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
CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL UNIQUE,
  setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- activity_logs (audit trail)
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
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
-- SEED DATA
-- ============================================================
-- Run AFTER  database/schema.sql  (which drops & recreates tables).
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
--   admin@reagansoft.com   (Administrator)
--   staff@reagansoft.com   (Staff)
--   amara@kirekafarms.com, grace@pearlholdings.com,
--   david@mulumbasons.com, agnesnakato@gmail.com   (Clients)
-- Change these passwords after logging in, then delete anything
-- you no longer need from this file.
-- ============================================================
USE reagan_soft_innovation;

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
  7, 'lock', 'active', 14);

-- ============================================================
-- 02 · SETTINGS
-- ============================================================
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name',            'Reagan Soft Innovation Limited'),
('company_tagline',         'Software built properly, and kept working.'),
('company_founder',         'Reagan Otema'),
('company_phone',           '+256730314979'),
('company_email',           'info@reagansoft.com'),
('company_address',         'Jinja, Uganda'),
('company_whatsapp',        '+256772514889'),
('company_fb',              ''),
('company_x',               ''),
('company_linkedin',        ''),
('company_about',           'Reagan Soft Innovation Limited is a software company based in Jinja, Uganda. We design and build websites, business systems, ecommerce stores and custom software for businesses across the country, and we stay around to look after them after launch.'),
('currency',                'UGX'),
('tax_percent',             '0'),
('invoices_due_days',       '14'),
('payment_mtn_number',      '+256730314979'),
('payment_airtel_number',   '+256771234567'),
('payment_bank_details',    ''),
('payment_gateway_status',  'not_configured'),
('project_deposit',         '200000'),
('registration_open',       '1');

-- ============================================================
-- 03 · ADMIN & STAFF
-- ============================================================
-- The administrator manages the whole portal. Staff serve clients
-- (requests, projects, quotations, invoices, files, messages).
-- ============================================================
INSERT INTO users (full_name, email, phone, password_hash, role, company, address, active) VALUES
('Reagan Otema',   'admin@reagansoft.com', '+256730314979', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'admin', 'Reagan Soft Innovation Limited', 'Jinja, Uganda', 1),
('Sarah Namukasa', 'staff@reagansoft.com', '+256771234567', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'staff', NULL, NULL, 1);

-- ============================================================
-- 04 · CLIENTS
-- ============================================================
INSERT INTO users (full_name, email, phone, password_hash, role, company, address, active) VALUES
('Amara Kaggwa',  'amara@kirekafarms.com',   '+256702456789', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Kireka Farm Supplies Ltd', 'Kireka, Kampala', 1),
('Grace Ayebare', 'grace@pearlholdings.com', '+256778987654', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Pearl Holdings Ltd', 'Jinja, Uganda', 1),
('David Mulumba', 'david@mulumbasons.com',   '+256703246810', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Mulumba & Sons Traders', 'Iganga, Uganda', 1),
('Agnes Nakato',  'agnesnakato@gmail.com',   '+256759135790', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', NULL, 'Jinja, Uganda', 1);

-- ============================================================
-- 05 · PROJECTS
-- ============================================================
-- user ids:  1 admin · 2 staff · 3 Kireka · 4 Pearl · 5 Mulumba · 6 Nakato
-- ============================================================
INSERT INTO projects (ref_no, client_id, service_id, assigned_to, title, description, requirements, budget, priority, status, progress, deadline, started_at, completed_at, submitted_at) VALUES
('RSI-2026-00001', 3, 1, 1, 'Company Website – Kireka Farm Supplies Ltd',
 'A responsive company website to present the farm supply catalogue, company story and contact details, with a lead-capture enquiry form and SEO foundations.',
 'Six information pages, enquiry form, WhatsApp integration, Google Map, photo gallery.',
 8500000, 'normal', 'COMPLETED', 100, '2026-04-20', '2026-03-02', '2026-04-18', '2026-02-26'),
('RSI-2026-00002', 4, 3, 2, 'Sales & Stock Management System – Pearl Holdings Ltd',
 'A custom management system for tracking sales, stock levels, suppliers and daily reports across the Pearl Holdings outlets in the Eastern region.',
 'User roles for cashier and manager, product and stock modules, purchase tracking, daily sales reports, backup.',
 14500000, 'high', 'IN_PROGRESS', 70, '2026-09-30', '2026-07-06', NULL, '2026-07-01'),
('RSI-2026-00003', 5, 2, 2, 'Ecommerce Store – Mulumba & Sons Traders',
 'An online store for hardware and farm inputs with a product catalogue, cart, order management and mobile money ready payment architecture.',
 'Product categories and search, cart and checkout, order dashboard, inventory, mobile money payment ready structure.',
 9800000, 'normal', 'QUOTATION', 0, NULL, NULL, NULL, '2026-09-10'),
('RSI-2026-00004', 6, 3, NULL, 'Customer Feedback Portal – Agnes Nakato',
 'A small portal where customers submit feedback and track the response, with a staff review dashboard.',
 'Feedback form, automatic acknowledgements, staff review queue, simple analytics.',
 3500000, 'low', 'NEW', 0, NULL, NULL, NULL, '2026-09-20'),
('RSI-2026-00005', 3, 6, 1, 'Brand Refresh & Business Cards – Kireka Farm Supplies Ltd',
 'Logo refresh, updated colour palette and print ready business cards and letterheads for the farm supply brand.',
 'Two logo options, brand colours and typography, business cards, letterheads.',
 650000, 'normal', 'REVIEWING', 15, NULL, NULL, NULL, '2026-09-18');

-- ============================================================
-- 06 · TASKS
-- ============================================================
INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by) VALUES
(1, 1, 'Design and structure', 'Plan sitemap, wireframes and page structure for the Kireka site.', 'COMPLETED', 100, 'high', '2026-03-02', '2026-03-08', 1),
(1, 2, 'Home page build', 'Build the responsive home page and header.', 'COMPLETED', 100, 'high', '2026-03-09', '2026-03-16', 1),
(1, 2, 'Content upload & launch', 'Upload all copy and images, test on mobile and launch.', 'COMPLETED', 100, 'medium', '2026-03-20', '2026-04-18', 1),
(2, 2, 'Database design', 'Design tables for products, stock, sales and suppliers.', 'COMPLETED', 100, 'high', '2026-07-06', '2026-07-12', 1),
(2, 2, 'Sales dashboard module', 'Build the cashier sales entry and manager dashboard.', 'IN_PROGRESS', 60, 'high', '2026-07-15', '2026-09-01', 1),
(2, 1, 'Reports & exports', 'Daily sales reports and CSV export for management.', 'TODO', 0, 'medium', NULL, '2026-09-25', 1),
(3, 2, 'Catalogue & cart', 'Product catalogue, search and shopping cart.', 'TODO', 0, 'high', NULL, '2026-10-05', 1);

-- ============================================================
-- 07 · MESSAGES
-- ============================================================
INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at) VALUES
(1, 3, 'client', 'Thank you for the great work on our website. The team at Kireka Farm Supplies is very happy with it.', 0, '2026-04-20 09:30:00'),
(1, 1, 'admin', 'Thank you Amara! It was a pleasure working with you. We are available any time for maintenance.', 0, '2026-04-20 11:00:00'),
(2, 4, 'client', 'Please confirm the final layout for the sales dashboard.', 0, '2026-09-01 10:15:00'),
(2, 2, 'staff', 'Sharing the dashboard preview this week, and we are on track for the end-of-month review.', 0, '2026-09-02 08:40:00'),
(2, 1, 'admin', 'Reminder: confirm Pearl Holdings DB backup schedule before go-live.', 1, '2026-09-02 09:00:00');

-- ============================================================
-- 08 · QUOTATIONS + ITEMS
-- ============================================================
INSERT INTO quotations (quotation_no, project_id, client_id, issued_on, expiry_date, subtotal, discount, tax_percent, total, status, notes, terms) VALUES
('QT-2026-0001', 3, 5, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 9800000, 0, 0, 9800000, 'sent',
 'E-commerce build for Mulumba & Sons Traders.',
 '50% deposit to start, balance on delivery. Free support for 30 days after launch.');

INSERT INTO quotation_items (quotation_id, description, quantity, unit_price) VALUES
(1, 'E-commerce design & development', 1, 7000000),
(1, 'Mobile money and bank payment ready architecture', 1, 2000000),
(1, 'Testing, training & launch support', 1, 800000);

-- ============================================================
-- 09 · INVOICES + ITEMS
-- ============================================================
INSERT INTO invoices (invoice_no, project_id, client_id, quotation_id, due_date, subtotal, discount, tax_percent, total, amount_paid, status, is_deposit, notes) VALUES
('INV-2026-0001', 3, 5, 1, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 9800000, 0, 0, 9800000, 0, 'sent', 1, 'Deposit invoice for QT-2026-0001.'),
('INV-2026-0002', 1, 3, NULL, '2026-04-24', 8500000, 0, 0, 8500000, 8500000, 'paid', 0, 'Final payment for the Kireka Farm Supplies website.');

INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) VALUES
(1, 'E-commerce development (first phase)', 1, 4900000),
(1, 'Payment architecture setup', 1, 4900000),
(2, 'Company website development', 1, 8500000);

-- ============================================================
-- 10 · PAYMENTS
-- ============================================================
INSERT INTO payments (invoice_id, amount, method, reference, status, received_by, notes, confirmed_at, created_at) VALUES
(2, 8500000, 'bank', 'KFS-WEB-0420', 'confirmed', 1, 'Transferred from Kireka Farm Supplies bank account.', '2026-04-24 14:00:00', '2026-04-24 14:00:00');

-- ============================================================
-- 11 · NOTIFICATIONS
-- ============================================================
INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at) VALUES
(3, 'Project completed', 'Your website project RSI-2026-00001 was completed and launched. Thank you!', 'success', 1, 0, '2026-04-18 12:00:00'),
(4, 'Task update', 'A task in your project RSI-2026-00002 was updated.', 'task', 2, 0, '2026-09-02 09:00:00'),
(5, 'Your quotation is ready', 'Your quotation QT-2026-0001 is waiting for your approval.', 'quotation', 3, 0, '2026-09-10 09:30:00'),
(1, 'New request received', 'Agnes Nakato submitted a new request for review.', 'info', 4, 0, '2026-09-20 10:00:00');
