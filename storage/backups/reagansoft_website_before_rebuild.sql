-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: reagansoft_website
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_entity` (`entity_type`,`entity_id`),
  KEY `idx_logs_user` (`user_id`),
  KEY `idx_logs_created` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `company` varchar(190) DEFAULT NULL,
  `subject` varchar(190) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contact_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_ii_invoice` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (1,1,'E-commerce development (first phase)',1.00,4900000.00),(2,1,'Payment architecture setup',1.00,4900000.00),(3,2,'Company website development',1.00,8500000.00);
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(30) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `quotation_id` int(10) unsigned DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','partially_paid','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `is_deposit` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `quotation_id` (`quotation_id`),
  KEY `idx_invoices_project` (`project_id`),
  KEY `idx_invoices_client` (`client_id`),
  KEY `idx_invoices_status` (`status`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,'INV-2026-0001',3,5,1,'2026-11-14',9800000.00,0.00,0.00,9800000.00,0.00,'sent',1,'Deposit invoice for QT-2026-0001.','2026-10-31 06:51:48','2026-10-31 06:51:48'),(2,'INV-2026-0002',1,3,NULL,'2026-04-24',8500000.00,0.00,0.00,8500000.00,8500000.00,'paid',0,'Final payment for the Kireka Farm Supplies website.','2026-10-31 06:51:48','2026-10-31 06:51:48');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(190) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('info','success','warning','project','quotation','invoice','task') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `related_project_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `related_project_id` (`related_project_id`),
  KEY `idx_notifications_user` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`related_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,3,'Project completed','Your website project RSI-2026-00001 was completed and launched. Thank you!','success',0,1,'2026-04-18 10:00:00'),(2,4,'Task update','A task in your project RSI-2026-00002 was updated.','task',0,2,'2026-09-02 07:00:00'),(3,5,'Your quotation is ready','Your quotation QT-2026-0001 is waiting for your approval.','quotation',0,3,'2026-09-10 07:30:00'),(4,1,'New request received','Agnes Nakato submitted a new request for review.','info',0,4,'2026-09-20 08:00:00'),(5,7,'Your project is live','Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.','project',0,6,'2026-06-19 13:30:00'),(6,8,'Your project is live','The school website, student portal and app are live and the team has been trained. Sign in to your portal to get started.','project',0,7,'2026-06-26 14:00:00');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('mtn_momo','airtel_money','bank','cash','other') NOT NULL DEFAULT 'other',
  `reference` varchar(120) DEFAULT NULL,
  `status` enum('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `received_by` int(10) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `received_by` (`received_by`),
  KEY `idx_payments_invoice` (`invoice_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,2,8500000.00,'bank','KFS-WEB-0420','confirmed',1,'Transferred from Kireka Farm Supplies bank account.','2026-04-24 14:00:00','2026-04-24 12:00:00');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_files`
--

DROP TABLE IF EXISTS `project_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `uploader_id` int(10) unsigned NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `size_bytes` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stored_name` (`stored_name`),
  KEY `uploader_id` (`uploader_id`),
  KEY `idx_files_project` (`project_id`),
  CONSTRAINT `project_files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_files_ibfk_2` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_files`
--

LOCK TABLES `project_files` WRITE;
/*!40000 ALTER TABLE `project_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_messages`
--

DROP TABLE IF EXISTS `project_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `sender_role` enum('admin','staff','client') NOT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `idx_messages_project` (`project_id`),
  CONSTRAINT `project_messages_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_messages`
--

LOCK TABLES `project_messages` WRITE;
/*!40000 ALTER TABLE `project_messages` DISABLE KEYS */;
INSERT INTO `project_messages` VALUES (1,1,3,'client','Thank you for the great work on our website. The team at Kireka Farm Supplies is very happy with it.',0,'2026-04-20 07:30:00'),(2,1,1,'admin','Thank you Amara! It was a pleasure working with you. We are available any time for maintenance.',0,'2026-04-20 09:00:00'),(3,2,4,'client','Please confirm the final layout for the sales dashboard.',0,'2026-09-01 08:15:00'),(4,2,2,'staff','Sharing the dashboard preview this week, and we are on track for the end-of-month review.',0,'2026-09-02 06:40:00'),(5,2,1,'admin','Reminder: confirm Pearl Holdings DB backup schedule before go-live.',1,'2026-09-02 07:00:00'),(6,6,7,'client','Please send the updated room photographs and the official contact numbers for the footer and booking form.',0,'2026-03-02 09:20:00'),(7,6,2,'staff','Rooms and facilities pages are in progress. Send the photographs as soon as you can and we will load them immediately.',0,'2026-03-03 07:15:00'),(8,6,7,'client','The site looks wonderful and the booking form already brought us two walk-in guests. Please show us how to update the gallery ourselves.',0,'2026-06-19 11:40:00'),(9,6,1,'admin','Congratulations on your launch. The gallery, rooms and offers are all editable from the front desk login, and we are on WhatsApp whenever you need us.',0,'2026-06-19 13:05:00');
/*!40000 ALTER TABLE `project_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_tasks`
--

DROP TABLE IF EXISTS `project_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('TODO','IN_PROGRESS','REVIEW','COMPLETED') NOT NULL DEFAULT 'TODO',
  `progress` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_tasks_project` (`project_id`),
  KEY `idx_tasks_status` (`status`),
  KEY `idx_tasks_assigned` (`assigned_to`),
  CONSTRAINT `project_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `project_tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_tasks`
--

LOCK TABLES `project_tasks` WRITE;
/*!40000 ALTER TABLE `project_tasks` DISABLE KEYS */;
INSERT INTO `project_tasks` VALUES (1,1,1,'Design and structure','Plan sitemap, wireframes and page structure for the Kireka site.','COMPLETED',100,'high','2026-03-02','2026-03-08',1,'2026-10-31 06:51:43','2026-10-31 06:51:43'),(2,1,2,'Home page build','Build the responsive home page and header.','COMPLETED',100,'high','2026-03-09','2026-03-16',1,'2026-10-31 06:51:43','2026-10-31 06:51:43'),(3,1,2,'Content upload & launch','Upload all copy and images, test on mobile and launch.','COMPLETED',100,'medium','2026-03-20','2026-04-18',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(4,2,2,'Database design','Design tables for products, stock, sales and suppliers.','COMPLETED',100,'high','2026-07-06','2026-07-12',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(5,2,2,'Sales dashboard module','Build the cashier sales entry and manager dashboard.','IN_PROGRESS',60,'high','2026-07-15','2026-09-01',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(6,2,1,'Reports & exports','Daily sales reports and CSV export for management.','TODO',0,'medium',NULL,'2026-09-25',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(7,3,2,'Catalogue & cart','Product catalogue, search and shopping cart.','TODO',0,'high',NULL,'2026-10-05',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(8,6,2,'Rooms & facilities pages','Build the rooms, facilities and gallery pages with the hotel photography.','COMPLETED',100,'high','2026-02-09','2026-03-20',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(9,6,2,'Booking enquiry form','Availability enquiry form that reaches the hotel WhatsApp and email, with the stay details captured.','COMPLETED',100,'high','2026-03-23','2026-04-24',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(10,6,2,'Booking calendar & guest app','Front-desk reservation calendar plus the Android guest app for enquiries and offers.','COMPLETED',100,'high','2026-04-27','2026-06-05',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(11,6,1,'Launch & handover','Final content check, mobile testing, launch and training for the hotel team.','COMPLETED',100,'medium','2026-06-08','2026-06-19',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(12,7,1,'School website','Public site: programmes, fees structure, application and enquiry forms, news and contacts.','COMPLETED',100,'high','2026-01-12','2026-02-27',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(13,7,2,'Student records & fees portal','Admissions, class registers, fees per term, receipts and progress reports for staff and bursar logins.','COMPLETED',100,'high','2026-03-02','2026-05-08',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(14,7,2,'Android app','Student app with results, fee balance and school announcements, plus SMS and email alerts.','COMPLETED',100,'high','2026-05-11','2026-06-19',1,'2026-10-31 06:51:44','2026-10-31 06:51:44'),(15,7,1,'Training & handover','Train the registrar and bursar, load real data, go live and hand over with backups running.','COMPLETED',100,'medium','2026-06-22','2026-06-26',1,'2026-10-31 06:51:45','2026-10-31 06:51:45');
/*!40000 ALTER TABLE `project_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ref_no` varchar(30) NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `deliverables` varchar(190) DEFAULT NULL,
  `cover_art` varchar(40) DEFAULT NULL,
  `budget` decimal(14,2) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('NEW','REVIEWING','QUOTATION','APPROVED','IN_PROGRESS','WAITING_FOR_CLIENT','TESTING','COMPLETED','CANCELLED') NOT NULL DEFAULT 'NEW',
  `progress` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_no` (`ref_no`),
  KEY `idx_projects_client` (`client_id`),
  KEY `idx_projects_status` (`status`),
  KEY `idx_projects_assigned` (`assigned_to`),
  KEY `idx_projects_service` (`service_id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'RSI-2026-00001',3,1,1,'Company Website ╬ô├ç├┤ Kireka Farm Supplies Ltd','A responsive company website that presents the farm supply catalogue, the company story and the contact details, with a lead-capture enquiry form and the SEO foundations a rural supplier needs to be found on Google.','Six information pages, enquiry form, WhatsApp integration, Google Map, photo gallery.',NULL,'Website','agriculture',8500000.00,'normal','COMPLETED',100,'2026-04-20','2026-03-02 00:00:00','2026-04-18 00:00:00','2026-02-25 23:00:00','2026-10-31 06:51:43'),(2,'RSI-2026-00002',4,3,2,'Sales & Stock Management System ╬ô├ç├┤ Pearl Holdings Ltd','A custom management system for tracking sales, stock levels, suppliers and daily reports across the Pearl Holdings outlets in the Eastern region.','User roles for cashier and manager, product and stock modules, purchase tracking, daily sales reports, backup.',NULL,NULL,'commerce',14500000.00,'high','IN_PROGRESS',70,'2026-09-30','2026-07-06 00:00:00',NULL,'2026-06-30 22:00:00','2026-10-31 06:51:43'),(3,'RSI-2026-00003',5,2,2,'Ecommerce Store ╬ô├ç├┤ Mulumba & Sons Traders','An online store for hardware and farm inputs with a product catalogue, cart, order management and mobile money ready payment architecture.','Product categories and search, cart and checkout, order dashboard, inventory, mobile money payment ready structure.',NULL,NULL,'commerce',9800000.00,'normal','QUOTATION',0,NULL,NULL,NULL,'2026-09-09 22:00:00','2026-10-31 06:51:43'),(4,'RSI-2026-00004',6,3,NULL,'Customer Feedback Portal ╬ô├ç├┤ Agnes Nakato','A small portal where customers submit feedback and track the response, with a staff review dashboard.','Feedback form, automatic acknowledgements, staff review queue, simple analytics.',NULL,NULL,'systems',3500000.00,'low','NEW',0,NULL,NULL,NULL,'2026-09-19 22:00:00','2026-10-31 06:51:43'),(5,'RSI-2026-00005',3,8,1,'Brand Refresh & Business Cards ╬ô├ç├┤ Kireka Farm Supplies Ltd','Logo refresh, updated colour palette and print ready business cards and letterheads for the farm supply brand.','Two logo options, brand colours and typography, business cards, letterheads.',NULL,'Branding','agriculture',650000.00,'normal','REVIEWING',15,NULL,NULL,NULL,'2026-09-17 22:00:00','2026-10-31 06:51:43'),(6,'RSI-2026-00006',7,1,2,'Hotel Website, Booking System & Guest App ╬ô├ç├┤ Hotel Paradise on the Nile','A digital front desk for the hotel: a fast website for rooms and facilities, a booking and availability system the front office runs itself, and a guest app for enquiries, directions and offers.','Website with rooms, facilities, gallery and contact pages, online availability and booking enquiry system, front-desk reservation calendar, guest and staff logins, Android guest app, WhatsApp and Google Maps, gallery the hotel updates itself, SSL and nightly backups.','https://hotelparadiseonthenile.info','Website, Business system, Mobile apps','hospitality',24000000.00,'high','COMPLETED',100,'2026-06-30','2026-02-09 00:00:00','2026-06-19 00:00:00','2026-01-13 23:00:00','2026-10-31 06:51:52'),(7,'RSI-2026-00007',8,5,1,'Website, Student Portal & Apps ╬ô├ç├┤ Iganga School of Nursing and Midwifery','One digital home for the school: a public website for applicants and parents, a records and fees portal the staff use every day, and a mobile app that puts results, fees and announcements in a student\'s pocket.','Public website with programmes, fees structure and contacts, online application and enquiry form, student records and fees portal, staff andbursar logins, results and progress reports, Android app for results and school announcements, SMS and email alerts, Google Maps, SSL and nightly backups.','https://igangaschoolofnursingandmidwifery.ac.ug','Website, Business system, Mobile apps','education',38000000.00,'high','COMPLETED',100,'2026-06-30','2026-01-12 00:00:00','2026-06-26 00:00:00','2025-11-23 23:00:00','2026-10-31 06:51:52');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotation_items`
--

DROP TABLE IF EXISTS `quotation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotation_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` int(10) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_qi_quotation` (`quotation_id`),
  CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotation_items`
--

LOCK TABLES `quotation_items` WRITE;
/*!40000 ALTER TABLE `quotation_items` DISABLE KEYS */;
INSERT INTO `quotation_items` VALUES (1,1,'E-commerce design & development',1.00,7000000.00),(2,1,'Mobile money and bank payment ready architecture',1.00,2000000.00),(3,1,'Testing, training & launch support',1.00,800000.00);
/*!40000 ALTER TABLE `quotation_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quotation_no` varchar(30) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `issued_on` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','accepted','rejected','expired','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_no` (`quotation_no`),
  KEY `idx_quotes_project` (`project_id`),
  KEY `idx_quotes_client` (`client_id`),
  CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotations`
--

LOCK TABLES `quotations` WRITE;
/*!40000 ALTER TABLE `quotations` DISABLE KEYS */;
INSERT INTO `quotations` VALUES (1,'QT-2026-0001',3,5,'2026-10-31','2026-11-30',9800000.00,0.00,0.00,9800000.00,'sent','E-commerce build for Mulumba & Sons Traders.','50% deposit to start, balance on delivery. Free support for 30 days after launch.','2026-10-31 06:51:47','2026-10-31 06:51:47');
/*!40000 ALTER TABLE `quotations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `price_min` decimal(14,2) NOT NULL DEFAULT 0.00,
  `price_max` decimal(14,2) DEFAULT NULL,
  `currency` varchar(8) NOT NULL DEFAULT 'UGX',
  `pricing_type` enum('range','starting_from','contact_for_quote') NOT NULL DEFAULT 'starting_from',
  `price_note` varchar(120) NOT NULL DEFAULT '',
  `features` text DEFAULT NULL,
  `delivery_days` int(10) unsigned DEFAULT NULL,
  `icon` varchar(60) NOT NULL DEFAULT 'code-s',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_services_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Business Website Development','business-website','A professional, responsive company website with clear structure, contact integration, SEO foundations and a design that represents your brand.',500000.00,10000000.00,'UGX','range','Professional websites','Custom responsive design for mobile, tablet and desktop\nContact & enquiry form\nWhatsApp and phone integration\nSearch engine friendly structure\nGoogle Map & business details\nTraining on how to manage content',14,'globe','active',1,'2026-10-31 06:51:42'),(2,'Ecommerce Development','ecommerce','Online stores with product catalogues, order management and a payment architecture ready for mobile money and bank integration.',500000.00,10000000.00,'UGX','range','Websites & online stores','Product catalogue and categories\nShopping cart and checkout\nOrder management dashboard\nMobile money and bank payment ready architecture\nInventory management\nDelivery and order tracking',30,'cart','active',2,'2026-10-31 06:51:42'),(3,'Custom Business Systems','business-systems','Management systems built for schools, hospitals, NGOs, government offices and companies, with client portals, dashboards, workflow automation and database driven tools around your real business processes.',20000000.00,40000000.00,'UGX','range','Custom business systems','Role based user access\nOnline fees, payments & mobile money\nDashboards and reports\nWorkflow automation\nSMS notifications\nUser training and handover',30,'cpu','active',3,'2026-10-31 06:51:42'),(4,'Software Development','software-dev','Custom web applications and internal business systems built with modern PHP/MySQL technology, secure by default and tailored to you.',20000000.00,40000000.00,'UGX','range','Custom software & business systems','Requirements analysis\nCustom database design\nSecure authentication\nAdministration panels\nAPI and integration ready\nDeployment and support',30,'code-s','active',4,'2026-10-31 06:51:42'),(5,'Web & Mobile App Development','web-mobile-apps','Business apps built to work as a website, an Android app and an iOS app, prepared, tested and published to the Apple App Store and Google Play.',10000000.00,NULL,'UGX','starting_from','Apps for the App Store & Google Play','Web, Android & iOS builds\nApp Store & Google Play publication\nPush and SMS notifications\nMobile money & bank payments\nDashboards and admin panels\nTesting across devices',30,'mobile','active',5,'2026-10-31 06:51:42'),(6,'Receipt & Billing Automation','receipt-billing','Automatic receipt and invoice generation, so every sale prints or sends a professional receipt instantly, with totals, mobile money references and daily reports.',3000000.00,10000000.00,'UGX','range','Automatic receipts & invoices','Automatic receipt printing and sending\nInvoice generation and numbering\nMobile money reference capture\nDaily cash & sales totals\nShop, school and clinic billing\nReport formats for daily and monthly use',14,'receipt','active',6,'2026-10-31 06:51:42'),(7,'Custom Digital Solutions','custom-solutions','A specific business problem that does not fit a ready made package? We design and build the right digital solution for it.',0.00,NULL,'UGX','contact_for_quote','Contact for a quote','Consultation and scoping\nFeasibility recommendation\nBespoke design and build\nTesting and quality assurance\nDeployment\nOngoing support',21,'layers','active',7,'2026-10-31 06:51:42'),(8,'Graphic & Branding Design','branding','Logos, brand guidelines, business cards, posters and digital design that give your company a consistent and professional identity.',250000.00,NULL,'UGX','starting_from','From','Logo design options\nBrand colour and typography\nBusiness cards and letterheads\nSocial media graphics\nPrint ready file delivery\nUsage guideline document',7,'palette','active',8,'2026-10-31 06:51:42'),(9,'Website Maintenance','maintenance','Security updates, backups, content changes and technical support so your website stays fast, safe and up to date.',150000.00,NULL,'UGX','starting_from','From','Monthly security checks\nRegular backups\nContent and image updates\nPerformance monitoring\nPriority technical support\nUptime reporting',1,'shield','active',9,'2026-10-31 06:51:42'),(10,'Video Game Development','video-games','Custom video games and interactive media for mobile, web and PC, covering game design, graphics, scoring, levels and store ready builds.',12000000.00,NULL,'UGX','starting_from','Custom games & interactive media','Game design and story development\n2D and 3D game graphics\nMobile, web and PC builds\nLevels, scores and progress saving\nMultiplayer and leaderboards\nTesting and store publication support',45,'gamepad','active',10,'2026-10-31 06:51:42'),(11,'Digital Books & Publications','digital-books','Professional layout and cover design, editing and ebook formats so your books look great in print and on Kindle, tablets and phones.',1000000.00,NULL,'UGX','starting_from','Ebooks and print ready books','Book layout and cover design\nEbook formats for common readers\nPrint ready file delivery\nEditing and proofreading\nTables, charts and diagrams\nProfessional illustration support',14,'book','active',11,'2026-10-31 06:51:42'),(12,'Chatbots & Smart Automation','chat-automation','Business chatbots and smart automation that answer customer questions, capture leads and handle routine tasks automatically, day and night.',5000000.00,15000000.00,'UGX','range','Chatbots & business automation','Business chatbot built into your site\nAutomated replies and FAQs\nLead capture and handover\nWorkflow automation\nSMS and WhatsApp integration\nTraining and handover',21,'chat','active',12,'2026-10-31 06:51:42'),(13,'System Integration & Payments','system-integration','Connecting your systems, websites and apps: mobile money and bank payment links, SMS services, APIs and data exchange between the tools you already use.',3000000.00,15000000.00,'UGX','range','Payments, SMS, APIs & system links','Mobile money & bank payment links\nSMS notification services\nAPI connections between systems\nData import and export\nSystem to system linking\nTesting and security review',14,'link','active',13,'2026-10-31 06:51:42'),(14,'Cybersecurity & Audits','cybersecurity','Security audits, vulnerability fixes, backups and data protection so your website and systems stay safe from attacks and data loss.',1000000.00,NULL,'UGX','starting_from','Security audits & protection','Full security audit of your systems\nVulnerability and patch fixes\nBackup and recovery setup\nUser access and permission review\nThreat and activity monitoring\nSecurity handover guide',7,'lock','active',14,'2026-10-31 06:51:42');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'company_name','Reagan Soft Innovation Limited'),(2,'company_tagline','Innovating today for a smarter tomorrow.'),(3,'company_founder','Reagan Otema'),(4,'company_phone','+256772514889'),(5,'company_email','info@reagansoftinnovation.com'),(6,'company_address','Jinja, Uganda'),(7,'company_whatsapp','+256730314979'),(8,'company_fb',''),(9,'company_x',''),(10,'company_linkedin',''),(11,'company_about','Reagan Soft Innovation Limited is a software company based in Jinja, Uganda. We design and build websites, business systems, ecommerce stores and custom software for businesses across the country, and we stay around to look after them after launch.'),(12,'currency','UGX'),(13,'tax_percent','0'),(14,'invoices_due_days','14'),(15,'payment_mtn_number','+256730314979'),(16,'payment_airtel_number',''),(17,'payment_bank_details',''),(18,'payment_gateway_status','not_configured'),(19,'project_deposit','200000'),(20,'registration_open','1');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','staff','client') NOT NULL DEFAULT 'client',
  `company` varchar(190) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `payer_type` enum('business','individual') NOT NULL DEFAULT 'business',
  `tax_id` varchar(60) DEFAULT NULL,
  `national_id` varchar(60) DEFAULT NULL,
  `country` varchar(80) NOT NULL DEFAULT 'Uganda',
  `city` varchar(90) DEFAULT NULL,
  `billing_terms_at` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `failed_logins` int(10) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Reagan Otema','admin@reagansoft.com','+256730314979','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','admin','Reagan Soft Innovation Limited','Jinja, Uganda',NULL,'business',NULL,NULL,'Uganda',NULL,NULL,1,0,NULL,NULL,'2026-10-31 06:51:42','2026-10-31 06:51:42'),(2,'Sarah Namukasa','staff@reagansoft.com','+256772514889','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','staff',NULL,NULL,NULL,'business',NULL,NULL,'Uganda',NULL,NULL,1,0,NULL,NULL,'2026-10-31 06:51:42','2026-10-31 06:51:42'),(3,'Amara Kaggwa','amara@kirekafarms.com','+256702456789','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','client','Kireka Farm Supplies Ltd','Kireka, Kampala',NULL,'business','1002456789','256754321098','Uganda','Kampala','2026-01-12 09:14:00',1,0,NULL,NULL,'2026-10-31 06:51:43','2026-10-31 06:51:43'),(4,'Grace Ayebare','grace@pearlholdings.com','+256778987654','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','client','Pearl Holdings Ltd','Jinja, Uganda',NULL,'business','1008765432','256701234567','Uganda','Jinja','2026-01-18 10:02:00',1,0,NULL,NULL,'2026-10-31 06:51:43','2026-10-31 06:51:43'),(5,'David Mulumba','david@mulumbasons.com','+256703246810','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','client','Mulumba & Sons Traders','Iganga, Uganda',NULL,'business','1011325468','256788990011','Uganda','Iganga','2026-02-03 14:37:00',1,0,NULL,NULL,'2026-10-31 06:51:43','2026-10-31 06:51:43'),(6,'Agnes Nakato','agnesnakato@gmail.com','+256759135790','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','client',NULL,'Plot 14, Nalufenya, Jinja',NULL,'individual',NULL,'256700112233','Uganda','Jinja','2026-02-11 08:55:00',1,0,NULL,NULL,'2026-10-31 06:51:43','2026-10-31 06:51:43'),(7,'Hotel Paradise on the Nile','info@hotelparadiseonthenile.co.ug','+256754412880','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','client','Hotel Paradise on the Nile','Main Street, Jinja, Uganda',NULL,'business','1015246800',NULL,'Uganda','Jinja','2026-01-25 11:20:00',1,0,NULL,NULL,'2026-10-31 06:51:43','2026-10-31 06:51:43'),(8,'Iganga School of Nursing and Midwifery','info@igangaschoolofnursingandmidwifery.ac.ug','+256701489236','$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu','client','Iganga School of Nursing and Midwifery','Iganga, Uganda',NULL,'business','1009988776',NULL,'Uganda','Iganga','2026-01-14 16:08:00',1,0,NULL,NULL,'2026-10-31 06:51:43','2026-10-31 06:51:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-05  9:47:51
