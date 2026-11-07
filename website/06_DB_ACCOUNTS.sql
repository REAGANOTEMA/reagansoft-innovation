-- ============================================================
-- Reagan Soft Innovation Limited
-- WEBSITE DATABASE — APPLICATION MYSQL ACCOUNT
-- ============================================================
-- Run ONCE as a MySQL/MariaDB administrator (root), not as the
-- application account:
--   C:\xampp\mysql\bin\mysql.exe -u root < website\06_DB_ACCOUNTS.sql
--
-- Creates the mirror database and the account used by
-- config/config.php (DB_MIRROR_USER = reagansoft_website).
-- Safe to re-run: the password is aligned each time.
--
-- SECURITY: change this password before production deployment and
-- update config/config.php or the RSI_DB_* environment variables.
-- ============================================================

CREATE DATABASE IF NOT EXISTS reagansoft_website
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'reagansoft_website'@'localhost'
  IDENTIFIED BY 'dORp*y_e[2@&JjD}';
ALTER USER 'reagansoft_website'@'localhost'
  IDENTIFIED BY 'dORp*y_e[2@&JjD}';
GRANT ALL PRIVILEGES ON reagansoft_website.*
  TO 'reagansoft_website'@'localhost';

CREATE USER IF NOT EXISTS 'reagansoft_website'@'127.0.0.1'
  IDENTIFIED BY 'dORp*y_e[2@&JjD}';
ALTER USER 'reagansoft_website'@'127.0.0.1'
  IDENTIFIED BY 'dORp*y_e[2@&JjD}';
GRANT ALL PRIVILEGES ON reagansoft_website.*
  TO 'reagansoft_website'@'127.0.0.1';

FLUSH PRIVILEGES;

SELECT user, host FROM mysql.user WHERE user = 'reagansoft_website';
