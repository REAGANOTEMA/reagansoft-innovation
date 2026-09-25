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
- MySQL 5.7 / 8+ or MariaDB 10.2+ · Apache or Nginx

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

**Alternative — command line**
```bash
cmd /c "C:\xampp\mysql\bin\mysql.exe -u reagansoft_reagansoft -p < database\install.sql"
```

### The scripts in `database/`

| File               | What it does                                             | Safe to re-run |
|--------------------|----------------------------------------------------------|----------------|
| `install.sql`      | schema + demo data in one import (phpMyAdmin / CLI)      | yes            |
| `schema.sql`       | creates the database and the 15 tables                   | yes            |
| `seed.sql`         | services, settings, accounts, demo projects & invoices   | yes            |
| `admin.sql`        | admin + staff logins only                                | yes            |
| `client.sql`       | the four demo client logins only                         | yes            |
| `reset.sql`        | **drops every table — deletes all data**                 | no (destructive) |

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

## Configuration — single database, your credentials

All settings live in `config/config.php`. To connect to a hosted database (when you
have the host, name, user and password), either edit those four constants or set
environment variables — no other file changes anywhere in the app:

| Variable       | Default                          |
|----------------|----------------------------------|
| `RSI_APP_URL`  | `http://localhost/reagansoft-innovation` |
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
| Staff  | `staff@reagansoft.com`    | Client management                   |
| Client | `amara@kirekafarms.com`   | Kireka Farm Supplies Ltd           |
| Client | `grace@pearlholdings.com` | Pearl Holdings Ltd                 |
| Client | `david@mulumbasons.com`   | Mulumba & Sons Traders (Iganga)    |
| Client | `agnesnakato@gmail.com`   | Individual client                   |

A seeded admin already exists, so `create_admin.php` refuses to run. Customise
company/payment details under **Admin → Settings**.

## Production checklist
- HTTPS + real domain, set `RSI_APP_URL` to it.
- Change every demo password and the `RSI_DB_*` credentials.
- Keep `uploads/` out of the public root or forbid script execution.
- Schedule database + uploads backups; wire email/SMS/WhatsApp (`inc/functions.php` → `rs_mail()`).
- Integrate MTN MoMo / Airtel Money only with the company's merchant credentials.
- Review Privacy/Terms/Cookie/Refund policy content before going live.