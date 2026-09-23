-- ============================================================
-- Reagan Soft Innovation Limited
-- Seed Data  (run AFTER schema.sql)
-- ============================================================
USE reagan_soft_innovation;

-- Services catalogue -----------------------------------------
-- price        = honest starting point (the entry-level figure)
-- price_max    = realistic upper bound for that service in UGX
--                * websites / e-commerce  up to UGX 10,000,000
--                * systems / software     up to UGX 20,000,000
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

-- Default settings --------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name',      'Reagan Soft Innovation Limited'),
('company_tagline',   'Digital Solutions That Move Your Business Forward'),
('company_founder',   'Reagan Otema'),
('company_phone',     '+256730314979'),
('company_email',     'info@reagansoft.com'),
('company_address',   'Jinja, Uganda'),
('company_whatsapp',  ''),
('company_fb',        ''),
('company_x',         ''),
('company_linkedin',  ''),
('company_about',     'Reagan Soft Innovation Limited is a software development company based in Jinja, Uganda. We design and build websites, business systems, e-commerce platforms and custom software that help organisations operate more effectively.'),
('currency',          'UGX'),
('tax_percent',       '0'),
('invoices_due_days', '14'),
('payment_mtn_number',      ''),
('payment_airtel_number',   ''),
('payment_bank_details',    ''),
('payment_gateway_status',  'not_configured'),
('registration_open', '1');

-- ============================================================
-- Admin account
-- Do NOT put a plaintext password here.
-- After importing, run the create_admin.php setup script
-- (or insert a password_hash() generated value manually).
-- ============================================================