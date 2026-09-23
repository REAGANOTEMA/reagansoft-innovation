# Reagan Soft Innovation Limited — Business Website + Client Portal

A professional white / deep-blue / light-blue business website with a full PHP + MySQL
client & admin project-management portal for Reagan Soft Innovation Limited (Jinja, Uganda).

## What's included

**Public site**
- Responsive homepage, services, pricing (UGX ranges up to 10M websites / 20M systems),
  portfolio, about and contact pages
- Real Reagan Soft Innovation logo across nav, footer, login, favicon and social share image
- WhatsApp, phone, email and social links; contact form stored in the database
- Client registration and secure login with brute-force lockout

**Client portal**
- Dashboard, project list and detail view (files, messages, quotations, invoices, activity)
- New work-request submission with file attachments (10 MB limit)
- Quotation approval, invoice payment requests, secure file downloads, notifications
- Profile settings

**Staff portal (`admin/`)**
- Requests queue, project management, tasks, messages (project + contact inbox), files
- Quotations and invoices with multi-item line editing, discounts and tax
- Payment intake with pending/confirmed handling and automatic invoice status updates
- Client directory with suspend/restore, service catalogue CRUD, team/user management
- Reports (revenue analytics, project status, top clients), company settings, audit trail

**Engineering**
- CSRF tokens on every POST, PDO prepared statements everywhere
- Role-based guards (`require_staff`, `require_admin`) with 403 responses
- Password hashing (bcrypt), security headers + CSP, upload validation
- Activity auditing on all important actions + in-app notifications

## Requirements
- PHP 8.0+ (8.0.30 verified) with PDO MySQL
- MySQL 5.7 / 8+
- Apache or Nginx

## Installation

1. Create the database and import the schema:

```sql
CREATE DATABASE reagan_soft_innovation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import schema then seed (seeds realistic demo data — clients, projects, tasks,
   a quotation, invoices and a payment):

```bash
mysql -u root reagan_soft_innovation < database/schema.sql
mysql -u root reagan_soft_innovation < database/seed.sql
```

On Windows PowerShell, use `cmd /c` because `<` redirection is otherwise unavailable:

```powershell
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root reagan_soft_innovation < database\schema.sql"
cmd /c "C:\xampp\mysql\bin\mysql.exe -u root reagan_soft_innovation < database\seed.sql"
```

3. Configuration is read from `config/config.php`; override in production via environment:

| Variable            | Default                          |
|---------------------|----------------------------------|
| `RSI_APP_URL`       | `http://localhost/reagansoft-innovation` |
| `RSI_DB_HOST`       | `localhost`                      |
| `RSI_DB_NAME`       | `reagan_soft_innovation`         |
| `RSI_DB_USER`       | `root`                           |
| `RSI_DB_PASS`       | *(empty for XAMPP root)*         |
| `RSI_DEBUG`         | *(unset — hides error details)*  |

4. Ensure `uploads/` is writable by PHP and `storage/logs/` exists (created automatically).
5. Open `http://localhost/reagansoft-innovation/`.

## Demo accounts (seeded — change these in production!)

All accounts use the password `Reagan@2026`:

| Role    | Email                     | Notes                                  |
|---------|---------------------------|----------------------------------------|
| Admin   | `admin@reagansoft.com`    | Full access incl. reports, settings, audit |
| Staff   | `staff@reagansoft.com`    | Client management (no admin-only pages)    |
| Client  | `amara@kirekafarms.com`   | Kireka Farm Supplies Ltd               |
| Client  | `grace@pearlholdings.com` | Pearl Holdings Ltd                     |
| Client  | `david@mulumbasons.com`   | Mulumba & Sons Traders                 |
| Client  | `agnesnakato@gmail.com`   | Individual client                       |

A seeded admin already exists, so `create_admin.php` refuses to run — rely on the demo
account instead. Never leave the demo password in production.

Customise company/payment details under **Admin → Settings**. Portfolios, prices and
logos are all database- and file-driven under `assets/img/`.

## Production checklist
- Use HTTPS with a real domain and set `RSI_APP_URL` to the live HTTPS URL.
- Change every demo password and DB credentials (`RSI_DB_*`).
- Keep `uploads/` out of the public web root or forbid script execution there.
- Schedule regular database and uploads backups.
- Wire email/SMS/WhatsApp notifications (`inc/functions.php` → `rs_mail()`).
- Integrate MTN MoMo / Airtel Money only with the company's merchant credentials.
- Add privacy policy, terms, refund/cancellation policy appropriate to the business.