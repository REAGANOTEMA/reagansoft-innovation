Reagansoft Innovation Limited
WEBSITE DATABASE PACK
Database: reagansoft_website

This is the mirror of reagansoft_system. It has the same 17 tables,
indexes, foreign keys and demo data. The application reads and writes
reagansoft_system; this database is the public/mirror copy.

FILES
reagansoft_website.sql  Complete, repeatable dump: 17 tables, indexes,
                        foreign keys, seed data and 8 demo accounts.
                        The file selects reagansoft_website itself, creates
                        tables only when missing and inserts seed rows with
                        INSERT IGNORE.

HOW TO IMPORT (phpMyAdmin)
1. Start Apache and MySQL.
2. Open phpMyAdmin. You do not need to preselect a database: the dump
   contains its own USE statement.
3. Open the Import tab, choose reagansoft_website.sql, press Go.
4. The import finishes with no errors, even when the tables or demo rows
   already exist. No table is dropped and no existing row is deleted.

If the database does not exist yet, create it first (utf8mb4 /
utf8mb4_unicode_ci) or run:
      C:\xampp\php\php.exe tools\setup-databases.php

HOW TO IMPORT (command line, from the project folder)
      C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < sql\website\reagansoft_website.sql

WHY YOU SAW "Table 'activity_logs' already exists"
Both database packs contain a table called activity_logs, and that is
correct: the two databases are separate. The old export had no
CREATE DATABASE and no USE statement, so importing both files into the
same selected database tried to create activity_logs twice. The dump in
this folder now starts with:

      CREATE DATABASE IF NOT EXISTS `reagansoft_website` ...;
      USE `reagansoft_website`;

so it always lands in the right database. The system pack does the
same for reagansoft_system. Importing them in any order is safe.

REPAIRING AN EXISTING TABLE
If an earlier import stopped halfway and activity_logs has no primary
key, no AUTO_INCREMENT or no indexes, run:

      C:\xampp\php\php.exe tools\repair-databases.php

It checks both databases, adds only the missing columns, primary key,
AUTO_INCREMENT, indexes and foreign key, then tests INSERT and SELECT
inside a rolled-back transaction. It never drops a table and never
deletes a record. Add --dry-run to preview the changes.

OLDER TEXT WITH EN DASHES
If an existing database still shows en dashes in project titles or the
company opening hours (they came from the original demo export), run:

      C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < sql\normalize-text.sql

It only replaces those characters with plain hyphens and is safe to run
more than once.

APPLICATION LOGIN (config/config.php)
  database: reagansoft_website
  user    : reagansoft_website
  password: see config/config.php (change it before production)

DEMO PORTAL ACCOUNTS - password for every account: Reagan@2026
  admin@reagansoft.com                              Administrator
  staff@reagansoft.com                              Staff
  amara@kirekafarms.com                             Client
  grace@pearlholdings.com                           Client
  david@mulumbasons.com                             Client
  agnesnakato@gmail.com                             Client
  info@hotelparadiseonthenile.co.ug                 Client
  info@igangaschoolofnursingandmidwifery.ac.ug      Client

Change every demo password after the first sign-in.
