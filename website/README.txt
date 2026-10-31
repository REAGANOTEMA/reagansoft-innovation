WEBSITE DATABASE - mirror of the system database
Database: reagansoft_website

This is an identical mirror of reagansoft_system. Keep it in step by importing the same pack, or let install.php rebuild it.

HOW TO IMPORT IN phpMyAdmin
1. Start Apache and MySQL.
2. Open http://localhost/phpmyadmin
3. Click Import.
4. Choose 00_INSTALL_ALL.sql and click Go.
   That creates the database, all tables and the demo data.

FILES
00_INSTALL_ALL.sql   schema + seed + upgrade in one step
01_SCHEMA.sql        tables only
02_SEED.sql          services, settings, accounts and demo data
03_UPGRADE.sql       safe column repairs for older installs
04_VERIFY.sql        read-only row counts
05_INSTALL_PORTABLE.sql  for a host-named database: select it first
99_RESET.sql         DESTRUCTIVE: drops every table in reagansoft_website

ACCOUNT PASSWORDS
Every demo account uses the password Reagan@2026.
Change every password after the first sign-in.

CONTACT ROLES
MTN +256730314979       WhatsApp and Mobile Money
Airtel +256772514889    phone calls only

The two folders are identical in structure. Import this whole pack
into reagansoft_website; the other folder rebuilds the matching mirror database.
