# Database migrations

This folder is **additive only**. The original, shipped SQL packs
(`system/` and `website/`, plus `database/system/` and `database/website/`)
are never edited by the enterprise upgrade.

## Run order

1. Existing installation (already present on this machine):
   - `system/00_INSTALL_ALL.sql` → `reagansoft_system`
   - `website/00_INSTALL_ALL.sql` → `reagansoft_website`
2. `001_enterprise_platform.sql` — adds `expenses`, `newsletter_subscribers`
   and guarded indexes. Safe to re-run.
3. `002_platform_settings.sql` — adds new setting keys without overwriting
   any existing administrator value. Safe to re-run.
4. `003_db_users.sql` — run **as root** once to create/align the two
   application MySQL accounts and their grants.

## Commands (XAMPP on Windows)

```powershell
C:\xampp\mysql\bin\mysql.exe -u root < database\migrations\001_enterprise_platform.sql
C:\xampp\mysql\bin\mysql.exe -u root < database\migrations\002_platform_settings.sql
C:\xampp\mysql\bin\mysql.exe -u root < database\migrations\003_db_users.sql
```

or run the helper:

```powershell
C:\xampp\php\php.exe tools\migrate.php
```

`tools/migrate.php` is CLI-only, prints each step, and refuses to run in a
browser. It uses `root` with an empty password by default (XAMPP); pass
`--user=NAME --pass=SECRET` for other setups.

## Rollback

There is intentionally no destructive rollback. If you need to remove the
new objects (only after taking a backup):

```sql
USE reagansoft_system;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS expenses;
```
