# Reagansoft Innovation Limited, Website + Client Portal

A professional white / deep-blue / light-blue business website with a full PHP + MySQL
client & admin project-management portal for **Reagansoft Innovation Limited**
(Jinja, Uganda). The app uses two databases:

```
reagansoft_system      <- active app database (accounts, projects, billing)
reagansoft_website     <- identical mirror kept on the same host account
```

Both are named after the company, `reagansoft_*`, and each has its own login
credentials in `config/config.php`. `install.php` builds the system database from
`sql/system/reagansoft_system.sql` and the website database from
`sql/website/reagansoft_website.sql`. The app reads and writes only
`reagansoft_system`; the mirror is a full, importable copy.

## What's included

**Public site**
- Responsive homepage, services, pricing (UGX ranges up to 10M websites / 40M systems),
  portfolio, about and contact pages
- Real Reagansoft Innovation logo across nav, footer, login, favicon and social share image
- Company motto, *"Innovating today for a smarter tomorrow."*
- Legal pages (Privacy, Terms, Refund, Cookie Policy); WhatsApp/phone/email/social links;
  database-backed contact form
- Engineering look: blueprint grid heroes, HUD readouts, particle network, 3D card tilt,
  scroll reveals, count-up stats (Space Grotesk / Inter / JetBrains Mono)

**Client flow, Order → Choose a program → Sign in / Register → Pay**
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

**Recommended, web installer (one click)**
1. Start Apache + MySQL in the XAMPP control panel.
2. Run `C:\xampp\php\php.exe tools\setup-databases.php` once. It creates both
   databases and both MySQL logins (a non-root phpMyAdmin session cannot run
   `CREATE USER`).
3. Open <http://localhost/reagansoft-innovation/install.php> and click one button.
   It imports `sql/system/reagansoft_system.sql` into `reagansoft_system`, then
   builds the identical `reagansoft_website` mirror from
   `sql/website/reagansoft_website.sql`.
4. **Delete `install.php`** afterwards.

**Alternative, phpMyAdmin**
1. Create the two databases (or run `tools\setup-databases.php`).
2. Click `reagansoft_system` in the left-hand list → **Import** →
   `sql/system/reagansoft_system.sql` → **Go**.
3. Click `reagansoft_website` → **Import** →
   `sql/website/reagansoft_website.sql` → **Go**.

**Alternative, command line** (run from the project folder)
```bash
C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < sql\system\reagansoft_system.sql
C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < sql\website\reagansoft_website.sql
```

### The SQL dumps

Each file is a complete, repeatable export of one database: all 17 tables, their
indexes and foreign keys, plus the seed rows (8 demo accounts, 14 services,
settings and sample projects). Both files start with `SET FOREIGN_KEY_CHECKS=0`,
`SET NAMES utf8mb4`, `CREATE DATABASE IF NOT EXISTS` and `USE`, then use
`CREATE TABLE IF NOT EXISTS` and `INSERT IGNORE`. Importing them in any order is
safe: nothing is dropped and no existing row is overwritten. **Existing data is
kept**, so take a backup before a first import on a live host.

| File | Database |
|------|----------|
| `sql/system/reagansoft_system.sql` | `reagansoft_system` (active app) |
| `sql/website/reagansoft_website.sql` | `reagansoft_website` (mirror) |

Each folder has a `README.txt` with the same instructions. Two helpers cover
older installations:

| File | Purpose |
|------|---------|
| `tools/repair-databases.php` | Adds only the missing columns, primary key, AUTO_INCREMENT, indexes and `activity_logs` foreign key, then verifies INSERT/SELECT in a rolled-back transaction. Add `--dry-run` to preview. |
| `sql/normalize-text.sql` | Replaces en dashes in project titles and opening hours with plain hyphens. Safe to run more than once. |

If you edit a dump, re-test the import on an empty scratch database before
shipping it.

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
should report *"Images are healthy"*, run it after swapping any artwork.

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
7. Run `php tools/db-check.php` if you have SSH, it prints the connected
   user, the database actually in use, the mirror state and privileges.

**Single-database hosts:** the mirror database is optional. Switch it off with
either of these (setting the environment variable to an empty value works on
Linux/Apache; editing the constant works everywhere):

```php
define('DB_MIRROR', '');   // config/config.php, simplest
```
```
RSI_DB_MIRROR=             // empty value on the server
```

`db-check.php` then reports `Mirror disabled` instead of an access error.

## Web server rules (Apache, LiteSpeed, Nginx, IIS)

The site needs one rule set per server type. Nothing else changes:

| Server | File to use | Notes |
|--------|-------------|-------|
| Apache 2.4 / LiteSpeed | `.htaccess` (already in the root) | Serves the SPA, blocks source folders, sets PWA MIME types and cache headers. `AllowOverride All` must be enabled. |
| Nginx | `deploy/nginx.conf.example` | Nginx ignores `.htaccess`; copy the `server` block into `sites-available`, set `root`, `server_name` and the PHP-FPM socket. |
| IIS / Windows hosting | `deploy/web.config` | Copy to the site root as `web.config`; the host must already have the PHP handler configured. |

After uploading a rebuilt `app/`, the root URL, any deep link (`/about`,
`/services/...`) and `/api/v1/public/site` must all return `200`. Static
assets, `sw.js` and `assets/img/site.webmanifest` must return `200` as well.

## Install as an app (PWA)

The public site is installable on Android, iPhone/iPad, Windows, macOS and
Linux. Requirements are already in the repo:

- `assets/img/site.webmanifest` - name, colours, shortcuts, 192/512 and
  maskable icons.
- `sw.js` at the web root - offline shell, cached assets, network-only API.
- Apple tags and the manifest link injected by `spa.php`.
- Service worker registration in `frontend/src/main.tsx` (production build).

**Install**

- **Android (Chrome/Edge/Samsung Internet):** open the site, tap the install
  banner or menu -> **Install app / Add to Home screen**.
- **iPhone/iPad (Safari):** Share -> **Add to Home Screen**. iOS uses the
  Apple touch icon and standalone display.
- **Windows/Linux (Chrome/Edge):** install icon in the address bar, or menu ->
  **Install Reagansoft**.
- **macOS (Safari 17+ / Chrome):** File -> **Add to Dock**, or the install icon
  in the address bar.

Install prompts only appear on **HTTPS**, never on a plain-HTTP domain.
`localhost` is the exception for testing. If the prompt does not appear,
confirm the manifest returns `application/manifest+json`, `sw.js` returns
JavaScript and the page has no console errors.

## Configuration, two databases, separate credentials

All settings live in `config/config.php`. The active database and the mirror
each have their own user and password. Either edit the constants or set
environment variables, no other file changes anywhere in the app:

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
| `RSI_DEBUG`             | *(unset, hides error details)*  |

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

Password for **every** account below: `Reagan@2026`, change these in production!

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
a **placeholder**, edit the account in Admin → Clients, or change the seed rows
in `sql/system/reagansoft_system.sql` (and the website mirror), then re-import
into an empty database before going live.

A seeded admin already exists, so `create_admin.php` refuses to run. Customise
company/payment details under **Admin → Settings**.

## Production checklist
- HTTPS + real domain. `RSI_APP_URL` is detected from the request (scheme, host and
  install path, including `X-Forwarded-Proto`/`X-Forwarded-Host` behind a proxy);
  set it explicitly only when the site must advertise a different canonical origin.
- Upload the root `.htaccess` (Apache/LiteSpeed), or use `deploy/nginx.conf.example`
  / `deploy/web.config` on those servers. Deploying only `app/` without the root
  rules makes deep links return 404 and hides the PWA.
- Upload `sw.js`, `assets/img/site.webmanifest`, `icon-192.png`, `icon-512.png`
  and both `icon-maskable-*.png` files. Keep HTTPS enabled so the install prompt
  can appear.
- Change every demo password and the `RSI_DB_*` credentials.
- Delete `install.php` (and `create_admin.php`) from the server.
- Keep `uploads/` out of the public root; `uploads/.htaccess` already blocks
  script execution there.
- Schedule database + uploads backups; wire email/SMS/WhatsApp (`inc/functions.php` -> `rs_mail()`).
- Integrate MTN MoMo only with the company's merchant credentials. MTN is also
  the WhatsApp number; Airtel is for voice calls only.
- Run `php tools/repair-databases.php` and `sql/normalize-text.sql` if the live
  databases came from an older import.
- Run `php tools/db-check.php` after any import.
- Review Privacy/Terms/Cookie/Refund policy content before going live.
