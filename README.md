# Reagan Soft Innovation Limited — Full-Stack Starter

A professional white / blue / light-blue business website plus PHP/MySQL client portal.

## Included
- Responsive public website
- Custom RSI SVG logo
- Client registration and secure login
- Client dashboard
- Service catalogue with UGX pricing
- Work/request submission
- File attachments (10MB limit)
- Request status tracking
- Admin dashboard
- Admin task assignment and progress
- CSRF protection
- PDO prepared statements
- Password hashing
- Mobile-first layout
- WhatsApp and phone contact details
- MySQL database schema

## Requirements
PHP 8.1+ recommended, MySQL 8+, Apache/Nginx with PHP.

## Installation
1. Create a MySQL database and import `database/schema.sql`.
2. Edit `config/config.php` with your database host/name/user/password and `APP_URL`.
3. Ensure `uploads/` is writable by PHP.
4. Put the folder in your PHP web root (for XAMPP: `htdocs/reagan_soft_innovation`).
5. Open `http://localhost/reagan_soft_innovation/`.
6. Register a normal client account to test the portal.

## Create the first admin
Run this temporary PHP command on your server:
`php -r "echo password_hash('CHANGE_THIS_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"`
Then insert the hash:
`INSERT INTO users(full_name,email,phone,password_hash,role) VALUES('RSI Admin','admin@yourdomain.com','+256730314979','PASTE_HASH_HERE','admin');`

Delete/avoid exposing any password-generation scripts after setup.

## Production checklist
- Use HTTPS and a real domain.
- Change DB credentials and admin password.
- Set APP_URL to the live HTTPS domain.
- Keep `uploads/` outside public access or add server rules to prevent script execution there.
- Configure regular database and file backups.
- Add email/SMS/WhatsApp notifications if required.
- Integrate MTN MoMo/Airtel Money only after obtaining the company's merchant/API credentials.
- Add privacy policy, terms, refund/cancellation policy and consent wording appropriate to your business.
- Configure server-side rate limiting and login throttling for production.
- Add a professional business email such as info@yourdomain.
