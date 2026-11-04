-- ============================================================
-- Reagan Soft Innovation Limited
-- MIGRATION 002 — Additional platform settings (additive, idempotent)
-- ============================================================
-- Uses INSERT ... ON DUPLICATE KEY UPDATE setting_key = setting_key so an
-- existing administrator value is NEVER overwritten.
-- ============================================================

USE reagansoft_system;

INSERT INTO settings (setting_key, setting_value) VALUES
  ('company_hours', 'Mon – Fri, 8:00am – 6:00pm EAT'),
  ('company_map_url', 'https://www.google.com/maps/search/?api=1&query=Jinja%2C+Uganda'),
  ('company_linkedin', ''),
  ('company_x', ''),
  ('company_fb', ''),
  ('seo_keywords', 'software development Uganda, website design Jinja, school management system, hospital management system, POS Uganda, Reagansoft Innovation'),
  ('site_announcement', ''),
  ('support_email', 'support@reagansoftinnovation.com'),
  ('newsletter_enabled', '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
