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
CREATE DATABASE IF NOT EXISTS reagansoft_clients
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reagansoft_clients;

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