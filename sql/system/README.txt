Reagan Soft Innovation Limited
SYSTEM DATABASE PACK
Database: reagansoft_system

This is the active application database. It holds accounts, clients,
projects, tasks, files, messages, quotations, invoices, payments,
expenses, services, contact messages, settings and audit logs.

FILES
reagansoft_system.sql      Complete dump: 17 tables, indexes, foreign keys,
                           seed data and 8 demo accounts.
verify.sql                 Safe row-count / health check. Starts with
                           USE reagansoft_system, so it never fails with
                           "#1109 Unknown table 'users' in information_schema".
upgrade-2026-11-08.sql     Safe, repeatable data upgrade (WhatsApp / call
                           numbers and project-title text repair).

HOW TO IMPORT (phpMyAdmin)
1. Start Apache and MySQL (XAMPP control panel).
2. Click the target database in the left-hand list FIRST:
      reagansoft_system
3. Open the Import tab, choose reagansoft_system.sql, press Go.
4. Open verify.sql in the SQL tab and run it. You should see 17 tables
   and 8 users.

The dump starts with SET FOREIGN_KEY_CHECKS=0 and DROP TABLE IF EXISTS
for every table, so it can be imported over an existing database
without "#1050 Table already exists". Importing replaces the current
contents of reagansoft_system, so take a backup first.

If the database does not exist yet, create it first (utf8mb4 /
utf8mb4_unicode_ci) or run:
      C:\xampp\php\php.exe tools\setup-databases.php

HOW TO IMPORT (command line, from the project folder)
      C:\xampp\mysql\bin\mysql.exe -u root reagansoft_system < sql\system\reagansoft_system.sql

WHY YOU SAW THESE ERRORS
- "#1109 Unknown table 'users' in information_schema"
  The check query was run while phpMyAdmin was showing the
  information_schema database. Select reagansoft_system first, or run
  verify.sql (it contains USE reagansoft_system).
- "#1227 Access denied; you need the CREATE USER privilege"
  The dump does NOT contain CREATE USER/GRANT. That error only appears
  when the CREATE USER snippet from the README is pasted into a
  non-root phpMyAdmin session. Use tools\setup-databases.php, or run
  that snippet as the MySQL root user. Nothing in this folder needs it.

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
