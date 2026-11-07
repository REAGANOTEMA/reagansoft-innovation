Reagan Soft Innovation Limited
SYSTEM DATABASE PACK
Database: reagansoft_system

This is the active application database. It holds accounts, clients,
projects, tasks, files, messages, quotations, invoices, payments,
expenses, services, contact messages, settings and audit logs.

IMPORT
1. Start Apache and MySQL (e.g. XAMPP).
2. Open http://localhost/phpmyadmin
3. Import tab -> choose 00_INSTALL_ALL.sql -> Go.
   That creates the database, all 17 tables and the demo data.

FILES
00_INSTALL_ALL.sql        schema + seed + upgrade in one import
01_SCHEMA.sql             creates the database and 17 tables
02_SEED.sql               services, settings, accounts and demo data
03_UPGRADE.sql            safe additive repairs for older installs
04_VERIFY.sql             read-only table and row-count checks
05_INSTALL_PORTABLE.sql   for host-named databases: select it first
06_DB_ACCOUNTS.sql        creates the MySQL login (run as root)
99_RESET.sql              DESTRUCTIVE: drops every table in this database

COMMAND LINE (XAMPP on Windows)
  C:\xampp\mysql\bin\mysql.exe -u root < system\00_INSTALL_ALL.sql
  C:\xampp\mysql\bin\mysql.exe -u root < system\06_DB_ACCOUNTS.sql
  C:\xampp\php\php.exe tools\db-check.php

APPLICATION LOGIN (config/config.php)
  database: reagansoft_system
  user    : reagansoft_system
  password: f.o9Wv2dP5Tmv]?+

DEMO PORTAL ACCOUNTS - password for every account: Reagan@2026
  admin@reagansoft.com                              Administrator
  staff@reagansoft.com                              Staff
  amara@kirekafarms.com                             Client
  grace@pearlholdings.com                           Client
  david@mulumbasons.com                             Client
  agnesnakato@gmail.com                             Client
  info@hotelparadiseonthenile.co.ug                 Client
  info@igangaschoolofnursingandmidwifery.ac.ug      Client

Change every password after the first sign-in.
