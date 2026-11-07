Reagan Soft Innovation Limited
SYSTEM DATABASE PACK
Database: reagansoft_system

This is the active application database. It holds accounts, clients,
projects, tasks, files, messages, quotations, invoices, payments,
expenses, services, contact messages, settings and audit logs.

FILE
reagansoft_system.sql   complete dump: 17 tables, indexes, foreign keys,
                        seed data and 8 demo accounts

IMPORT (into an empty database)
1. Start Apache and MySQL (e.g. XAMPP).
2. Create both databases and their MySQL logins:
     C:\xampp\php\php.exe tools\setup-databases.php
3. phpMyAdmin: click reagansoft_system in the left list, open Import,
   choose reagansoft_system.sql and press Go.
   Or from the project folder:
     C:\xampp\mysql\bin\mysql.exe -u root reagansoft_system < sql\system\reagansoft_system.sql
4. Verify everything:
     C:\xampp\php\php.exe tools\db-check.php

Import into an EMPTY database. Do not re-import over existing tables.
To rebuild from scratch, use the reinstall action in install.php, or drop
the tables first.

WHY THERE IS NO CREATE USER HERE
A .sql file containing CREATE USER fails with "#1227 Access denied" when it
is imported through phpMyAdmin with a non-root account. This dump contains
no CREATE USER, GRANT or USE statement, so it cannot trigger that error.
tools\setup-databases.php creates and verifies both database accounts.

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
