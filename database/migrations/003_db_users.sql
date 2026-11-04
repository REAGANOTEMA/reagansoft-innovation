-- ============================================================
-- Reagan Soft Innovation Limited
-- MIGRATION 003 — Database accounts and privileges
-- ============================================================
-- Run once as a MySQL/MariaDB administrator (root), for example:
--   C:\xampp\mysql\bin\mysql.exe -u root < database\migrations\003_db_users.sql
--
-- This creates the two application accounts used by config/config.php.
-- If the accounts already exist their password is aligned with the
-- application configuration. It never touches application data.
--
-- SECURITY: rotate these passwords before production deployment and
-- update config/config.php (or the RSI_DB_* environment variables).
-- ============================================================

CREATE DATABASE IF NOT EXISTS reagansoft_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS reagansoft_website
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Application database user -----------------------------------
CREATE USER IF NOT EXISTS 'reagansoft_system'@'localhost'
  IDENTIFIED BY 'f.o9Wv2dP5Tmv]?+';
ALTER USER 'reagansoft_system'@'localhost'
  IDENTIFIED BY 'f.o9Wv2dP5Tmv]?+';
GRANT ALL PRIVILEGES ON reagansoft_system.*
  TO 'reagansoft_system'@'localhost';

CREATE USER IF NOT EXISTS 'reagansoft_system'@'127.0.0.1'
  IDENTIFIED BY 'f.o9Wv2dP5Tmv]?+';
ALTER USER 'reagansoft_system'@'127.0.0.1'
  IDENTIFIED BY 'f.o9Wv2dP5Tmv]?+';
GRANT ALL PRIVILEGES ON reagansoft_system.*
  TO 'reagansoft_system'@'127.0.0.1';

-- Mirror / website database user ------------------------------
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
