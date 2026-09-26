# Reagan Soft Innovation Limited — Website + Client Portal

A professional white / deep-blue / light-blue business website with a full PHP + MySQL
client & admin project-management portal for **Reagan Soft Innovation Limited**
(Jinja, Uganda). The whole app runs on **one database:**

```
reagansoft_clients      <- the app database (this is the one that matters)
reagansoft_admin        <- identical mirror kept on the same host account
```

Both are named after the company — `reagansoft_*`. The mirror exists only so the
hosting account keeps a second, identical store; the app never reads from it.

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
4. Submit the deposit (MTN MoMo / Airtel Money / bank / cash / other) with your
   transaction reference. The payment is confirmed by the team, credited against
   your quotation.

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

The backend is **PHP only** — no Python, no Node, no second language to install
or deploy. One XAMPP-style stack runs everything.

| Layer      | Choice                                                        |
|------------|---------------------------------------------------------------|
| Backend    | PHP 8 (procedural + PDO, no framework, no Composer)           |
| Database   | MySQL 5.7/8 or MariaDB 10.2+ · InnoDB · utf8mb4                |
| Web server | Apache (`.htaccess` guards) or Nginx                          |
| Frontend   | HTML5, hand-written CSS, vanilla JavaScript — no build step    |
| Icons      | inline SVG helper (`icon()` in `inc/functions.php`)            |
| Images     | WebP assets in `assets/img`, checked by `tools/img-opt.php`   |
| Email      | PHP `mail()` through `rs_mail()` in `inc/functions.php`        |

There is no bundler, transpiler or package manager: you can edit a file, save,
and refresh the browser. Uploading the folder to any PHP 8 host is a deployment.

## Installation

**Recommended — web installer (one click)**
1. Start Apache + MySQL in the XAMPP control panel.
2. Open <http://localhost/reagansoft-innovation/install.php> and click one button.
   It creates `reagansoft_clients`, imports `database/schema.sql`, then
   `database/seed.sql`, then brings the `reagansoft_admin` mirror into the
   same state.
3. **Delete `install.php`** afterwards.

**Alternative — phpMyAdmin**
1. Open <http://localhost/phpmyadmin> → **Import** → choose `database/install.sql` → **Go**
   (database + tables + demo data in one step).

**Alternative — command line** (run from the project folder)
```bash
cmd /c "C:\xampp\mysql\bin\mysql.exe -u reagansoft_reagansoft -p < database\install.sql"
```

### Where to run each file in `database/`

Every file selects `reagansoft_clients` itself, so the database you pick in
phpMyAdmin is ignored. Pick the method, then run the file:

| File               | What it does                                             | Safe to re-run |
|--------------------|----------------------------------------------------------|----------------|
| `install.sql`      | schema + demo data in one import (phpMyAdmin / CLI)      | yes            |
| `schema.sql`       | creates the database and the 15 tables                   | yes            |
| `seed.sql`         | services, settings, accounts, demo projects & invoices   | yes            |
| `admin.sql`        | admin + staff logins only                                | yes            |
| `client.sql`       | the five demo client logins only                         | yes            |
| `reset.sql`        | **drops every table — deletes all data**                 | no (destructive) |

- **phpMyAdmin:** Import tab → choose the file → Go. `install.sql` is the one to
  pick for a fresh site; the others are for topping up an existing one.
- **Command line:** `mysql -u USER -p < database\FILE.sql`
- **Web:** only `install.php`. The `.sql` files are blocked from the browser by
  `.htaccess`, deliberately.
- **Clean rebuild:** `reset.sql` → `install.sql`. Nothing else, and only when
  you intend to lose the data.

Every script except `reset.sql` is non-destructive: tables use
`CREATE TABLE IF NOT EXISTS` and every row is matched on its natural key
(`slug`, `setting_key`, `email`, `ref_no`, `quotation_no`, `invoice_no`,
`reference`, title), so re-importing updates instead of duplicating. A clean
rebuild is `reset.sql` → `schema.sql` → `seed.sql`, or just `reset.sql` →
`install.sql`.

`admin.sql` and `client.sql` also create the `users` table if it is missing and
qualify every table name (`reagansoft_clients`.`users`), so they can be run from
phpMyAdmin with **any** database selected. Error 1146
*"Table 'reagansoft_xxx.users' doesn't exist"* simply means the schema has never
been imported — run `install.sql` (or open `install.php`) once.

`install.sql` is generated from `schema.sql` + `seed.sql`; edit those two and
regenerate rather than editing the copies in the lower half of `install.sql`.

Seed rows never hard-code an ID. Each one looks its parent up by its natural key
(`email`, `slug`, `ref_no`, `quotation_no`, `invoice_no`), because MySQL and
MariaDB both burn auto-increment values on `ON DUPLICATE KEY UPDATE` — hard-coded
IDs would break the second time a script was imported.

## Command-line tools in `tools/`

Both are CLI-only (they refuse a browser request) and read-only. Run them from
the project folder:

```bash
php tools/db-check.php    # connection, schema, row counts, logins, mirror, writable paths
php tools/img-opt.php     # broken / unused / oversized image report
```

`db-check.php` is the quickest way to answer "is the database right?" after an
import; it prints a `FAIL` line per problem and exits non-zero. `img-opt.php`
should report *"Images are healthy"* — run it after swapping any artwork.

## Deploying to shared hosting (cPanel / Plesk)

Shared hosts usually give you **one** database, **name it themselves**, and
forbid `CREATE DATABASE`. Two symptoms follow from that, and both are
already handled — this section only explains which file to use.

```
#1044 - Access denied for user 'you'@'localhost' to database 'information_schema'
#1044 - Access denied for user 'you'@'localhost' to database 'reagansoft_clients'
```

| File                     | Use it when                                                    |
|--------------------------|----------------------------------------------------------------|
| `install.sql`            | XAMPP, VPS or dedicated server — you may create databases       |
| **`install-portable.sql`**| **shared hosting — select your database in phpMyAdmin first**    |

**Steps**
1. In the host panel create a database and a database user. Note the exact
   database name — it is usually prefixed, e.g. `reagansoft_rsi`.
2. Give that user **all privileges on that one database** and nothing else.
3. In phpMyAdmin, **click your database in the left-hand list** so it is the
   selected one.
4. Import `database/install-portable.sql` (Import → choose file → Go).
5. Point the app at it — either in `config/config.php`:
   ```php
   define('DB_NAME', 'reagansoft_rsi');   // the host's name, not ours
   ```
   or with environment variables (`RSI_DB_NAME`, `RSI_DB_USER`, `RSI_DB_PASS`).
6. Open `install.php` once to confirm, then **delete `install.php` and
   `create_admin.php`**.
7. Run `php tools/db-check.php` if you have SSH — it prints the connected
   user, the database actually in use and the privileges, which is the
   fastest way to spot a mismatch.

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

**Regenerating the SQL packages.** `install.sql` and `install-portable.sql`
are generated from `schema.sql` + `seed.sql` so they can never drift apart.
After editing either source file run:

```bash
php tools/build-sql.php
```

## Configuration — single database, your credentials

All settings live in `config/config.php`. To connect to a hosted database (when you
have the host, name, user and password), either edit those four constants or set
environment variables — no other file changes anywhere in the app:

| Variable       | Default                          |
|----------------|----------------------------------|
| `RSI_APP_URL`  | *auto-detected from the request* (scheme + host + install path) |
| `RSI_DB_HOST`  | `localhost`                      |
| `RSI_DB_NAME`  | `reagansoft_clients`             |
| `RSI_DB_MIRROR`| `reagansoft_admin`               |
| `RSI_DB_USER`  | `reagansoft_reagansoft`          |
| `RSI_DB_PASS`  | *(set in `config/config.php`)*   |
| `RSI_DEBUG`    | *(unset — hides error details)*  |

The `reagansoft_reagansoft` database user needs privileges on both
`reagansoft_clients` (the active app database) and `reagansoft_admin` (its mirror):

```sql
CREATE DATABASE IF NOT EXISTS reagansoft_clients CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS reagansoft_admin   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON reagansoft_clients.* TO 'reagansoft_reagansoft'@'localhost';
GRANT ALL PRIVILEGES ON reagansoft_admin.*   TO 'reagansoft_reagansoft'@'localhost';
FLUSH PRIVILEGES;
```

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
a **placeholder** — replace the name, email, phone and address in
`database/seed.sql`, then re-run `install.sql` (or edit the account in
Admin → Clients) before going live.

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
- Integrate MTN MoMo / Airtel Money only with the company's merchant credentials.
- Run `php tools/db-check.php` after any import.
- Review Privacy/Terms/Cookie/Refund policy content before going live.
