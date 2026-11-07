-- ============================================================
-- Reagan Soft Innovation Limited
-- SYSTEM DATABASE — APPLICATION MYSQL ACCOUNT
-- ============================================================
-- Run ONCE as a MySQL/MariaDB administrator (root), not as the
-- application account:
--   C:\xampp\mysql\bin\mysql.exe -u root < system\06_DB_ACCOUNTS.sql
--
-- Creates the database and the account used by config/config.php
-- (DB_USER = reagansoft_system). Safe to re-run: the password is
-- aligned with the application configuration each time.
--
-- SECURITY: change this password before production deployment and
-- update config/config.php or the RSI_DB_* environment variables.
-- ============================================================

CREATE DATABASE IF NOT EXISTS reagansoft_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

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

FLUSH PRIVILEGES;

SELECT user, host FROM mysql.user WHERE user = 'reagansoft_system';
