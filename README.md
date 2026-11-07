# Reagan Soft Innovation Limited — Website + Client Portal

A professional white / deep-blue / light-blue business website with a full PHP + MySQL
client & admin project-management portal for **Reagan Soft Innovation Limited**
(Jinja, Uganda). The app uses two databases:

```
reagansoft_system      <- active app database (accounts, projects, billing)
reagansoft_website     <- identical mirror kept on the same host account
```

Both are named after the company — `reagansoft_*` — and each has its own login
credentials in `config/config.php`. `install.php` builds the system database from
`sql/system/reagansoft_system.sql` and the website database from
`sql/website/reagansoft_website.sql`. The app reads and writes only
`reagansoft_system`; the mirror is a full, importable copy.

## What's included

**Public site**
- Responsive homepage, services, pricing (UGX ranges up to 10M websites / 20M systems),
  portfolio, about and contact pages
- Real Reagan Soft Innovation logo across nav, footer, login, favicon and social share image
- Company motto, *"Innovating today for a smarter tomorrow."*
- Legal pages (Privacy, Terms, Refund, Cookie Policy); WhatsApp/phone/email/social links;
  database-backed contact form
- Engineering look: blueprint grid heroes, HUD readouts, particle network, 3D card tilt,
  scroll reveals, count-up stats (Space Grotesk / Inter / JetBrains Mono)

**Client flow — Order → Choose a program → Sign in / Register → Pay**
1. Pick a program on the home/services/pricing pages → "Choose this program".
2. Review the plan and the one-time deposit on `checkout.php` (a guest gets a
   "Sign in &nbsp;·&nbsp; Create account" gate at the end).
3. Signing in or registering returns you to the exact checkout step with your
   program remembered.
4. Submit the deposit (MTN MoMo / bank / cash / other) with your transaction
   reference. The payment is confirmed by the team and credited against your
   quotation. MTN is also the WhatsApp number; Airtel is for phone calls only.

**Client portal**
- Dashboard, projects, files, messages, quotations, invoices, notifications, profile
- New work-request submission with file attachments (10 MB limit)

**Staff portal (`admin/`)**
- Requests queue, project management, tasks, messages, files
- Quotations & invoices with multi-item line editing, discounts and tax
- Payment intake (pending/confirmed) with automatic invoice status updates
- Client directory, service catalogue, team management, reports, settings, audit trail

**Engineering**
- CSRF on every POST, PDO prepared statements throughout, role guards
- bcrypt passwords, security headers + CSP, upload validation, brute-force lockout
- Activity auditing + in-app notifications

## Requirements
- PHP 8.0+ (verified on 8.0.30) with PDO MySQL
- MySQL 5.7 / 8+ or MariaDB 10.2+ (verified on MariaDB 10.4.32) · Apache or Nginx

## Technology stack

The backend is **PHP 8 + PDO**; the public experience and console are a
**React + TypeScript** single-page app built with Vite into `app/`. Legacy
PHP pages remain available for the server-rendered flows and portals.

| Layer           | Choice                                                        |
|-----------------|---------------------------------------------------------------|
| Backend         | PHP 8 (procedural + PDO, no framework, no Composer)           |
| Database        | MySQL 5.7/8 or MariaDB 10.2+ · InnoDB · utf8mb4                |
| Web server      | Apache (`.htaccess` guards) or Nginx                          |
| Frontend app    | React 18, TypeScript, Vite, built to `app/`                    |
| Legacy frontend | HTML5, hand-written CSS, vanilla JavaScript                    |
| Icons           | inline SVG helper (`icon()`) + React icon components           |
| Images          | WebP/AVIF/JPG in `assets/img`, checked by `tools/img-opt.php` |
| Email           | PHP `mail()` through `rs_mail()` in `inc/functions.php`        |

Rebuild the React app after changing anything in `frontend/`:

```bash
cd frontend
npm install
npm run build
```

Vite writes the production bundle to `../app`, which the root URL serves via
`spa.php` + `.htaccess`.

## Installation

**Recommended — web installer (one click)**
1. Start Apache + MySQL in the XAMPP control panel.
2. Run `C:\xampp\php\php.exe tools\setup-databases.php` once. It creates both
   databases and both MySQL logins (a non-root phpMyAdmin session cannot run
   `CREATE USER`).
3. Open <http://localhost/reagansoft-innovation/install.php> and click one button.
   It imports `sql/system/reagansoft_system.sql` into `reagansoft_system`, then
   builds the identical `reagansoft_website` mirror from
   `sql/website/reagansoft_website.sql`.
4. **Delete `install.php`** afterwards.

**Alternative — phpMyAdmin**
1. Create the two databases (or run `tools\setup-databases.php`).
2. Click `reagansoft_system` in the left-hand list → **Import** →
   `sql/system/reagansoft_system.sql` → **Go**.
3. Click `reagansoft_website` → **Import** →
   `sql/website/reagansoft_website.sql` → **Go**.

**Alternative — command line** (run from the project folder)
```bash
C:\xampp\mysql\bin\mysql.exe -u root reagansoft_system  < sql\system\reagansoft_system.sql
C:\xampp\mysql\bin\mysql.exe -u root reagansoft_website < sql\website\reagansoft_website.sql
```

### The SQL dumps

Each file is a complete phpMyAdmin export of one database: all 17 tables, their
indexes and foreign keys, plus the seed rows (8 demo accounts, 14 services,
settings and sample projects). Import it into an **empty** database. Re-importing
over existing tables is intentionally not supported — use the reinstall action in
`install.php`, or drop the tables first.

| File | Database |
|------|----------|
| `sql/system/reagansoft_system.sql` | `reagansoft_system` (active app) |
| `sql/website/reagansoft_website.sql` | `reagansoft_website` (mirror) |

There is no separate `01_SCHEMA` / `02_SEED` / `03_UPGRADE` / `04_VERIFY` split
any more, and no generated copies to rebuild. If you edit a dump, re-test the
import on an empty scratch database before shipping it.

`CREATE USER` is deliberately **not** in any `.sql` file: importing it through
phpMyAdmin as a non-root user fails with `#1227 Access denied`.
`tools/setup-databases.php` creates and verifies both accounts safely.

## Command-line tools in `tools/`

All are CLI-only (they refuse a browser request). Run them from the project
folder:

```bash
php tools\setup-databases.php   # create both databases + MySQL logins, then verify
php tools\db-check.php          # connection, schema, row counts, logins, mirror, writable paths
php tools\migrate.php           # import both dumps into empty databases
php tools\img-opt.php           # broken / unused / oversized image report
```

`setup-databases.php` is the only script that runs `CREATE USER`/`GRANT`; that is
why no `.sql` file can trigger `#1227 Access denied`.

`db-check.php` is the quickest way to answer "is the database right?" after an
import; it prints a `FAIL` line per problem and exits non-zero. `img-opt.php`
should report *"Images are healthy"* — run it after swapping any artwork.

## Deploying to shared hosting (cPanel / Plesk)

Shared hosts often name databases themselves and forbid `CREATE DATABASE`, so
create the databases in the host panel and import the dumps into the empty
databases you selected.

**Steps**
1. In the host panel create the databases and their users. If your host gives
   different names (for example `acct_reagansoft_system`), note them exactly.
2. Give each user **all privileges on its own database** and nothing else.
3. In phpMyAdmin, **click the target database in the left-hand list**.
4. Import `sql/system/reagansoft_system.sql` for the active database, then
   `sql/website/reagansoft_website.sql` for the mirror.
5. Point the app at the real names and credentials in `config/config.php`:
   ```php
   define('DB_NAME', 'acct_reagansoft_system');
   define('DB_USER', 'acct_system_user');
   define('DB_PASS', '...');
   define('DB_MIRROR', 'acct_reagansoft_website');
   define('DB_MIRROR_USER', 'acct_website_user');
   define('DB_MIRROR_PASS', '...');
   ```
   or use the `RSI_DB_*` environment variables.
6. Open `install.php` once to confirm, then **delete `install.php` and
   `create_admin.php`**.
7. Run `php tools/db-check.php` if you have SSH — it prints the connected
   user, the database actually in use, the mirror state and privileges.

**Single-database hosts:** the mirror database is optional. Switch it off with
either of these (setting the environment variable to an empty value works on
Linux/Apache; editing the constant works everywhere):

```php
define('DB_MIRROR', '');   // config/config.php — simplest
```
```
RSI_DB_MIRROR=             // empty value on the server
```

`db-check.php` then reports `Mirror disabled` instead of an access error.

## Configuration — two databases, separate credentials

All settings live in `config/config.php`. The active database and the mirror
each have their own user and password. Either edit the constants or set
environment variables — no other file changes anywhere in the app:

| Variable                | Default                          |
|-------------------------|----------------------------------|
| `RSI_APP_URL`           | *auto-detected from the request* (scheme + host + install path) |
| `RSI_DB_HOST`           | `localhost`                      |
| `RSI_DB_NAME`           | `reagansoft_system`              |
| `RSI_DB_USER`           | `reagansoft_system`              |
| `RSI_DB_PASS`           | *(set in `config/config.php`)*   |
| `RSI_DB_MIRROR`         | `reagansoft_website`             |
| `RSI_DB_MIRROR_USER`    | `reagansoft_website`             |
| `RSI_DB_MIRROR_PASS`    | *(set in `config/config.php`)*   |
| `RSI_DEBUG`             | *(unset — hides error details)*  |

The system user needs privileges on `reagansoft_system`; the website user needs
privileges on `reagansoft_website`. To create both on a local XAMPP install:

```sql
CREATE DATABASE IF NOT EXISTS reagansoft_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS reagansoft_website
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'reagansoft_system'@'localhost'
  IDENTIFIED BY 'f.o9Wv2dP5Tmv]?+';
CREATE USER IF NOT EXISTS 'reagansoft_website'@'localhost'
  IDENTIFIED BY 'dORp*y_e[2@&JjD}';

GRANT ALL PRIVILEGES ON reagansoft_system.*  TO 'reagansoft_system'@'localhost';
GRANT ALL PRIVILEGES ON reagansoft_website.* TO 'reagansoft_website'@'localhost';
FLUSH PRIVILEGES;
```

If both databases are owned by one MySQL user, set `DB_MIRROR_USER` and
`DB_MIRROR_PASS` to the same values as `DB_USER` and `DB_PASS`.
Set `RSI_DB_MIRROR=''` if the host account only allows a single database.

Ensure `uploads/` is writable by PHP; `storage/logs/` is created automatically.

## Demo accounts (seeded)

Password for **every** account below: `Reagan@2026` — change these in production!

| Role   | Email                     | Notes                              |
|--------|---------------------------|------------------------------------|
| Admin  | `admin@reagansoft.com`    | Full access incl. reports, settings |
| Staff  | `staff@reagansoft.com`    | Client management · +256772514889  |
| Client | `amara@kirekafarms.com`   | Kireka Farm Supplies Ltd           |
| Client | `grace@pearlholdings.com` | Pearl Holdings Ltd                 |
| Client | `david@mulumbasons.com`   | Mulumba & Sons Traders (Iganga)    |
| Client | `agnesnakato@gmail.com`   | Individual client                   |
| Client | `info@hotelparadiseonthenile.co.ug` | Hotel Paradise on the Nile · +256754412880 |

The hotel signs in to the client portal and can see project **RSI-2026-00006**
(website & booking enquiry) with its three tasks and message thread. Every row is
a **placeholder** — edit the account in Admin → Clients, or change the seed rows
in `sql/system/reagansoft_system.sql` (and the website mirror), then re-import
into an empty database before going live.

A seeded admin already exists, so `create_admin.php` refuses to run. Customise
company/payment details under **Admin → Settings**.

## Production checklist
- HTTPS + real domain. `RSI_APP_URL` is detected from the request (scheme, host and
  install path, including `X-Forwarded-Proto`/`X-Forwarded-Host` behind a proxy);
  set it explicitly only when the site must advertise a different canonical origin.
- Change every demo password and the `RSI_DB_*` credentials.
- Delete `install.php` (and `create_admin.php`) from the server.
- Keep `uploads/` out of the public root; `uploads/.htaccess` already blocks
  script execution there.
- Schedule database + uploads backups; wire email/SMS/WhatsApp (`inc/functions.php` → `rs_mail()`).
- Integrate MTN MoMo only with the company's merchant credentials. MTN is also
  the WhatsApp number; Airtel is for voice calls only.
- Run `php tools/db-check.php` after any import.
- Review Privacy/Terms/Cookie/Refund policy content before going live.
