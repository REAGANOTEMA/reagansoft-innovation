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
-- price       = honest entry-level figure (UGX)
-- price_max   = realistic upper bound for that service in UGX
--               · websites / e-commerce  up to 10,000,000
--               · systems / software     up to 20,000,000
-- ============================================================
INSERT INTO services (name, slug, description, price, price_max, price_note, features, delivery_days, icon, status, sort_order) VALUES
('Business Website Development', 'business-website', 'A professional, responsive company website with clear structure, contact integration, SEO foundations and a design that represents your brand.', 800000, 10000000, 'From',
 'Custom responsive design for mobile, tablet and desktop
Contact & enquiry form
WhatsApp and phone integration
Search-engine friendly structure
Google Map & business details
Training on how to manage content',
  14, 'globe', 'active', 1),

('E-Commerce Development', 'ecommerce', 'Online stores with product catalogues, order management and a payment architecture ready for mobile money and bank integration.', 3000000, 10000000, 'From',
 'Product catalogue and categories
Shopping cart and checkout
Order management dashboard
Mobile money & bank payment-ready architecture
Inventory management
Delivery and order tracking',
  30, 'cart', 'active', 2),

('Custom Business Systems', 'business-systems', 'Client portals, internal management systems, dashboards, workflow automation and database-driven tools built around your real business processes.', 4000000, 20000000, 'From',
 'Role-based user access
Dashboards and reports
Form and data management
Workflow automation
Database design and optimisation
User training and handover',
  30, 'cpu', 'active', 3),

('Software Development', 'software-dev', 'Custom web applications and internal business systems built with modern PHP/MySQL technology, secure by default and tailored to you.', 2500000, 20000000, 'From',
 'Requirements analysis
Custom database design
Secure authentication
Administration panels
API-ready architecture
Deployment and support',
  30, 'code-s', 'active', 4),

('Custom Digital Solutions', 'custom-solutions', 'A specific business problem that does not fit an off-the-shelf package? We design and build the right digital solution for it.', 1500000, 20000000, 'From',
 'Consultation and scoping
Feasibility recommendation
Bespoke design and build
Testing and quality assurance
Deployment
Ongoing support',
  21, 'layers', 'active', 5),

('Graphic & Branding Design', 'branding', 'Logos, brand guidelines, business cards, posters and digital design that give your company a consistent and professional identity.', 250000, 1000000, 'From',
 'Logo design options
Brand colour and typography
Business cards and letterheads
Social media graphics
Print-ready file delivery
Usage guideline document',
  7, 'palette', 'active', 6),

('Website Maintenance', 'maintenance', 'Security updates, backups, content changes and technical support so your website stays fast, safe and up to date.', 150000, 800000, 'From',
 'Monthly security checks
Regular backups
Content and image updates
Performance monitoring
Priority technical support
Uptime reporting',
  1, 'shield', 'active', 7);

-- ============================================================
-- 02 · SETTINGS
-- ============================================================
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name',            'Reagan Soft Innovation Limited'),
('company_tagline',         'Digital Solutions That Move Your Business Forward'),
('company_founder',         'Reagan Otema'),
('company_phone',           '+256730314979'),
('company_email',           'info@reagansoft.com'),
('company_address',         'Jinja, Uganda'),
('company_whatsapp',        ''),
('company_fb',              ''),
('company_x',               ''),
('company_linkedin',        ''),
('company_about',           'Reagan Soft Innovation Limited is a software company based in Jinja, Uganda. We design and build websites, business systems, e-commerce stores and custom software for businesses across the country — and we stay around to look after them after launch.'),
('currency',                'UGX'),
('tax_percent',             '0'),
('invoices_due_days',       '14'),
('payment_mtn_number',      ''),
('payment_airtel_number',   ''),
('payment_bank_details',    ''),
('payment_gateway_status',  'not_configured'),
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
('RSI-2026-00003', 5, 2, 2, 'E-Commerce Store – Mulumba & Sons Traders',
 'An online store for hardware and farm inputs with a product catalogue, cart, order management and mobile-money-ready payment architecture.',
 'Product categories and search, cart and checkout, order dashboard, inventory, mobile money payment-ready structure.',
 9800000, 'normal', 'QUOTATION', 0, NULL, NULL, NULL, '2026-09-10'),
('RSI-2026-00004', 6, 3, NULL, 'Customer Feedback Portal – Agnes Nakato',
 'A small portal where customers submit feedback and track the response, with a staff review dashboard.',
 'Feedback form, automatic acknowledgements, staff review queue, simple analytics.',
 3500000, 'low', 'NEW', 0, NULL, NULL, NULL, '2026-09-20'),
('RSI-2026-00005', 3, 6, 1, 'Brand Refresh & Business Cards – Kireka Farm Supplies Ltd',
 'Logo refresh, updated colour palette and print-ready business cards and letterheads for the farm supply brand.',
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
(1, 'Mobile money & bank payment-ready architecture', 1, 2000000),
(1, 'Testing, training & launch support', 1, 800000);

-- ============================================================
-- 09 · INVOICES + ITEMS
-- ============================================================
INSERT INTO invoices (invoice_no, project_id, client_id, quotation_id, due_date, subtotal, discount, tax_percent, total, amount_paid, status, notes) VALUES
('INV-2026-0001', 3, 5, 1, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 9800000, 0, 0, 9800000, 0, 'sent', 'Deposit invoice for QT-2026-0001.'),
('INV-2026-0002', 1, 3, NULL, '2026-04-24', 8500000, 0, 0, 8500000, 8500000, 'paid', 'Final payment for the Kireka Farm Supplies website.');

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