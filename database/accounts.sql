-- ---------------------------------------------------------------------------
-- Reagan Soft Innovation Limited — database accounts
-- ---------------------------------------------------------------------------
-- One MySQL account per database, named after the database it owns (the
-- convention used by the other stores on this host). Each account is scoped
-- to its own database only, so a leak of one password cannot reach the others.
--
-- Passwords contain (, ), {, }, ;, *, +, ! and other shell/SQL metacharacters.
-- Always type them exactly as written — this file is the single source of
-- truth for the credentials that config/config.php expects.
--
-- Run as root (or a host account with CREATE USER + GRANT):
--     mysql -u root -p < database/accounts.sql
--
-- Re-running this file is safe: it resets the passwords and re-applies grants.
-- ---------------------------------------------------------------------------

-- The two application databases. Created only if the hosting panel has not
-- already created them.
CREATE DATABASE IF NOT EXISTS `reagansoft_clients` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `reagansoft_admin`  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Scratch stores for testing database/seed.sql and database/install.sql
-- without ever touching live data.
CREATE DATABASE IF NOT EXISTS `reagansoft_seed`    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS `reagansoft_install` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Live application database: everything the site reads and writes.
-- ---------------------------------------------------------------------------
CREATE USER IF NOT EXISTS `reagansoft_clients`@`localhost` IDENTIFIED BY 'W(Cg{y;PJmvYM(2{';
ALTER USER `reagansoft_clients`@`localhost` IDENTIFIED BY 'W(Cg{y;PJmvYM(2{';
GRANT ALL PRIVILEGES ON `reagansoft_clients`.* TO `reagansoft_clients`@`localhost`;

-- ---------------------------------------------------------------------------
-- Mirror database: same schema and data as the app database, kept in step by
-- install.php. Deliberately NOT reached by the running site.
-- ---------------------------------------------------------------------------
CREATE USER IF NOT EXISTS `reagansoft_admin`@`localhost` IDENTIFIED BY '2)VAsS,I*e{7;-}U';
ALTER USER `reagansoft_admin`@`localhost` IDENTIFIED BY '2)VAsS,I*e{7;-}U';
GRANT ALL PRIVILEGES ON `reagansoft_admin`.* TO `reagansoft_admin`@`localhost`;

-- ---------------------------------------------------------------------------
-- Scratch store for database/seed.sql.
-- ---------------------------------------------------------------------------
CREATE USER IF NOT EXISTS `reagansoft_seed`@`localhost` IDENTIFIED BY 'V4yxMP_5aW;PojFS';
ALTER USER `reagansoft_seed`@`localhost` IDENTIFIED BY 'V4yxMP_5aW;PojFS';
GRANT ALL PRIVILEGES ON `reagansoft_seed`.* TO `reagansoft_seed`@`localhost`;

-- ---------------------------------------------------------------------------
-- Scratch store for database/install.sql.
-- ---------------------------------------------------------------------------
CREATE USER IF NOT EXISTS `reagansoft_install`@`localhost` IDENTIFIED BY 'F~!.HBEb9x+D!0-g';
ALTER USER `reagansoft_install`@`localhost` IDENTIFIED BY 'F~!.HBEb9x+D!0-g';
GRANT ALL PRIVILEGES ON `reagansoft_install`.* TO `reagansoft_install`@`localhost`;

FLUSH PRIVILEGES;

-- ---------------------------------------------------------------------------
-- The old shared account is no longer used by the application. Remove it
-- once the site has been re-checked with `php tools/db-check.php`:
--     DROP USER IF EXISTS `reagansoft_reagansoft`@`localhost`;
-- ---------------------------------------------------------------------------
