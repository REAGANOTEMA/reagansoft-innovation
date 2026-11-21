-- Unique service pricing and 75% first-payment alignment.
-- Safe to run more than once.

UPDATE `services` SET
  `price_min` = CASE `id` WHEN 1 THEN 800000.00 WHEN 2 THEN 1200000.00 WHEN 3 THEN 22000000.00 WHEN 4 THEN 15000000.00 WHEN 5 THEN 12000000.00 WHEN 6 THEN 3500000.00 WHEN 7 THEN 0.00 WHEN 8 THEN 350000.00 WHEN 9 THEN 180000.00 WHEN 10 THEN 14000000.00 WHEN 11 THEN 1200000.00 WHEN 12 THEN 6000000.00 WHEN 13 THEN 4000000.00 WHEN 14 THEN 1400000.00 ELSE `price_min` END,
  `price_max` = CASE `id` WHEN 1 THEN 12000000.00 WHEN 2 THEN 18000000.00 WHEN 3 THEN 55000000.00 WHEN 4 THEN 45000000.00 WHEN 6 THEN 9000000.00 WHEN 12 THEN 16000000.00 WHEN 13 THEN 18000000.00 ELSE `price_max` END,
  `price_note` = CASE `id`
    WHEN 1 THEN 'Professional company websites'
    WHEN 2 THEN 'Online stores & checkout'
    WHEN 3 THEN 'Multi-role business systems'
    WHEN 4 THEN 'Bespoke web applications'
    WHEN 5 THEN 'App Store & Google Play builds'
    WHEN 6 THEN 'Automated receipts & reports'
    WHEN 7 THEN 'Contact for a quote'
    WHEN 8 THEN 'Identity & brand systems'
    WHEN 9 THEN 'Care plans & support'
    WHEN 10 THEN 'Playable game builds'
    WHEN 11 THEN 'Print & ebook production'
    WHEN 12 THEN 'Conversational automation'
    WHEN 13 THEN 'APIs, payments & data links'
    WHEN 14 THEN 'Audits & hardening'
    ELSE `price_note`
  END
WHERE `id` BETWEEN 1 AND 14;
