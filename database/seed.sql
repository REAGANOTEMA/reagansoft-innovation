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
--   david@mulumbasons.com, agnesnakato@gmail.com   (Clients)
-- Change these passwords after logging in, then delete anything
-- you no longer need from this file.
--
-- To restore the logins without touching the demo data use
-- database/admin.sql and database/client.sql instead.
-- ============================================================
USE reagansoft_clients;

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
('company_whatsapp',        '+256772514889'),
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

-- ============================================================
-- 05 · PROJECTS  (upsert by ref_no)
-- ============================================================
-- Demo rows below reference the seeded ids:
--   user ids    1 admin · 2 staff · 3 Kireka · 4 Pearl · 5 Mulumba · 6 Nakato
--   project ids 1 … 5
-- They are correct on a fresh install and on any re-run of this
-- file. In a live database use the admin portal / the client
-- request form instead of re-seeding.
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
 650000, 'normal', 'REVIEWING', 15, NULL, NULL, NULL, '2026-09-18')
ON DUPLICATE KEY UPDATE
  client_id      = VALUES(client_id),
  service_id     = VALUES(service_id),
  assigned_to    = VALUES(assigned_to),
  title          = VALUES(title),
  description    = VALUES(description),
  requirements   = VALUES(requirements),
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
SELECT 1, 1, 'Design and structure', 'Plan sitemap, wireframes and page structure for the Kireka site.', 'COMPLETED', 100, 'high', '2026-03-02', '2026-03-08', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = 1 AND title = 'Design and structure');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT 1, 2, 'Home page build', 'Build the responsive home page and header.', 'COMPLETED', 100, 'high', '2026-03-09', '2026-03-16', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = 1 AND title = 'Home page build');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT 1, 2, 'Content upload & launch', 'Upload all copy and images, test on mobile and launch.', 'COMPLETED', 100, 'medium', '2026-03-20', '2026-04-18', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = 1 AND title = 'Content upload & launch');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT 2, 2, 'Database design', 'Design tables for products, stock, sales and suppliers.', 'COMPLETED', 100, 'high', '2026-07-06', '2026-07-12', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = 2 AND title = 'Database design');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT 2, 2, 'Sales dashboard module', 'Build the cashier sales entry and manager dashboard.', 'IN_PROGRESS', 60, 'high', '2026-07-15', '2026-09-01', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = 2 AND title = 'Sales dashboard module');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT 2, 1, 'Reports & exports', 'Daily sales reports and CSV export for management.', 'TODO', 0, 'medium', NULL, '2026-09-25', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = 2 AND title = 'Reports & exports');

INSERT INTO project_tasks (project_id, assigned_to, title, description, status, progress, priority, start_date, due_date, created_by)
SELECT 3, 2, 'Catalogue & cart', 'Product catalogue, search and shopping cart.', 'TODO', 0, 'high', NULL, '2026-10-05', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_tasks WHERE project_id = 3 AND title = 'Catalogue & cart');

-- ============================================================
-- 07 · MESSAGES  (inserted only when the same message is not there)
-- ============================================================
INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT 1, 3, 'client', 'Thank you for the great work on our website. The team at Kireka Farm Supplies is very happy with it.', 0, '2026-04-20 09:30:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = 1 AND sender_id = 3 AND message LIKE 'Thank you for the great work%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT 1, 1, 'admin', 'Thank you Amara! It was a pleasure working with you. We are available any time for maintenance.', 0, '2026-04-20 11:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = 1 AND sender_id = 1 AND message LIKE 'Thank you Amara!%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT 2, 4, 'client', 'Please confirm the final layout for the sales dashboard.', 0, '2026-09-01 10:15:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = 2 AND sender_id = 4 AND message LIKE 'Please confirm the final layout%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT 2, 2, 'staff', 'Sharing the dashboard preview this week, and we are on track for the end-of-month review.', 0, '2026-09-02 08:40:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = 2 AND sender_id = 2 AND message LIKE 'Sharing the dashboard preview%');

INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal, created_at)
SELECT 2, 1, 'admin', 'Reminder: confirm Pearl Holdings DB backup schedule before go-live.', 1, '2026-09-02 09:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM project_messages WHERE project_id = 2 AND sender_id = 1 AND message LIKE 'Reminder: confirm Pearl%');

-- ============================================================
-- 08 · QUOTATIONS + ITEMS  (upsert by quotation_no)
-- ============================================================
INSERT INTO quotations (quotation_no, project_id, client_id, issued_on, expiry_date, subtotal, discount, tax_percent, total, status, notes, terms) VALUES
('QT-2026-0001', 3, 5, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 9800000, 0, 0, 9800000, 'sent',
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
('INV-2026-0001', 3, 5, 1, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 9800000, 0, 0, 9800000, 0, 'sent', 1, 'Deposit invoice for QT-2026-0001.'),
('INV-2026-0002', 1, 3, NULL, '2026-04-24', 8500000, 0, 0, 8500000, 8500000, 'paid', 0, 'Final payment for the Kireka Farm Supplies website.')
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
SELECT i.id, 8500000, 'bank', 'KFS-WEB-0420', 'confirmed', 1, 'Transferred from Kireka Farm Supplies bank account.', '2026-04-24 14:00:00', '2026-04-24 14:00:00'
FROM invoices i WHERE i.invoice_no = 'INV-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM payments p WHERE p.invoice_id = i.id AND p.reference = 'KFS-WEB-0420');

-- ============================================================
-- 11 · NOTIFICATIONS  (matched on user + title)
-- ============================================================
INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT 3, 'Project completed', 'Your website project RSI-2026-00001 was completed and launched. Thank you!', 'success', 1, 0, '2026-04-18 12:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = 3 AND title = 'Project completed');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT 4, 'Task update', 'A task in your project RSI-2026-00002 was updated.', 'task', 2, 0, '2026-09-02 09:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = 4 AND title = 'Task update');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT 5, 'Your quotation is ready', 'Your quotation QT-2026-0001 is waiting for your approval.', 'quotation', 3, 0, '2026-09-10 09:30:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = 5 AND title = 'Your quotation is ready');

INSERT INTO notifications (user_id, title, message, type, related_project_id, is_read, created_at)
SELECT 1, 'New request received', 'Agnes Nakato submitted a new request for review.', 'info', 4, 0, '2026-09-20 10:00:00'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM notifications WHERE user_id = 1 AND title = 'New request received');
