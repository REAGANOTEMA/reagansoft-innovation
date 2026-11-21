-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 14, 2026 at 01:29 PM
-- Server version: 10.11.19-MariaDB
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reagansoft_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `company` varchar(190) DEFAULT NULL,
  `subject` varchar(190) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` varchar(80) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(8) NOT NULL DEFAULT 'UGX',
  `expense_date` date NOT NULL,
  `project_id` int(10) UNSIGNED DEFAULT NULL,
  `recorded_by` int(10) UNSIGNED DEFAULT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT IGNORE INTO `expenses` (`id`, `category`, `description`, `amount`, `currency`, `expense_date`, `project_id`, `recorded_by`, `reference`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Internet & hosting', 'Annual hosting and domain renewal', 850000.00, 'UGX', '2026-01-15', NULL, 1, 'HOST-2026-001', 'Paid by bank transfer', '2026-11-05 07:11:08', '2026-11-05 07:11:08'),
(2, 'Software licences', 'Design and development tools subscription', 420000.00, 'UGX', '2026-02-01', NULL, 1, 'TOOLS-2026-002', 'Monthly team licences', '2026-11-05 07:11:08', '2026-11-05 07:11:08'),
(3, 'Transport', 'Client site visits in Jinja and Iganga', 180000.00, 'UGX', '2026-03-08', 7, 2, 'TRIP-2026-003', 'Field requirements workshop', '2026-11-05 07:11:08', '2026-11-05 07:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_no` varchar(30) NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `quotation_id` int(10) UNSIGNED DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','partially_paid','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `is_deposit` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT IGNORE INTO `invoices` (`id`, `invoice_no`, `project_id`, `client_id`, `quotation_id`, `due_date`, `subtotal`, `discount`, `tax_percent`, `total`, `amount_paid`, `status`, `is_deposit`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'INV-2026-0001', 3, 5, 1, '2026-11-19', 4900000.00, 0.00, 0.00, 4900000.00, 0.00, 'sent', 1, 'First payment (50% deposit) for QT-2026-0001. Balance due on delivery.', '2026-10-31 07:13:45', '2026-11-05 07:11:08'),
(2, 'INV-2026-0002', 1, 3, NULL, '2026-04-24', 8500000.00, 0.00, 0.00, 8500000.00, 8500000.00, 'paid', 0, 'Final payment for the Kireka Farm Supplies website.', '2026-10-31 07:13:45', '2026-10-31 07:13:45');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT IGNORE INTO `invoice_items` (`id`, `invoice_id`, `description`, `quantity`, `unit_price`) VALUES
(1, 1, 'E-commerce development (50% first payment)', 1.00, 4900000.00),
(2, 2, 'Company website development', 1.00, 8500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(190) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `source` varchar(60) NOT NULL DEFAULT 'website',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT IGNORE INTO `newsletter_subscribers` (`id`, `email`, `full_name`, `source`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'hello@example.com', 'Website Visitor', 'website', 1, '2026-11-05 07:11:08', '2026-11-05 07:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `message` text DEFAULT NULL,
  `type` enum('info','success','warning','project','quotation','invoice','task') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `related_project_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT IGNORE INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `related_project_id`, `created_at`) VALUES
(1, 3, 'Project completed', 'Your website project RSI-2026-00001 was completed and launched. Thank you!', 'success', 0, 1, '2026-04-18 09:00:00'),
(2, 4, 'Task update', 'A task in your project RSI-2026-00002 was updated.', 'task', 0, 2, '2026-09-02 06:00:00'),
(3, 5, 'Your quotation is ready', 'Your quotation QT-2026-0001 is waiting for your approval.', 'quotation', 0, 3, '2026-09-10 06:30:00'),
(4, 1, 'New request received', 'Agnes Nakato submitted a new request for review.', 'info', 0, 4, '2026-09-20 07:00:00'),
(5, 7, 'Your project is live', 'Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.', 'project', 0, 6, '2026-06-19 12:30:00'),
(6, 8, 'Your project is live', 'The school website, student portal and app are live and the team has been trained. Sign in to your portal to get started.', 'project', 0, 7, '2026-06-26 13:00:00'),
(7, 7, 'Your project is live', 'Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.', 'project', 0, 6, '2026-06-19 12:30:00'),
(8, 7, 'Your project is live', 'Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.', 'project', 0, 6, '2026-06-19 12:30:00'),
(9, 7, 'Your project is live', 'Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.', 'project', 0, 6, '2026-06-19 12:30:00'),
(10, 7, 'Your project is live', 'Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.', 'project', 0, 6, '2026-06-19 12:30:00'),
(11, 7, 'Your project is live', 'Hotel Paradise on the Nile is now live at hotelparadiseonthenile.info, with the booking system and guest app. Everything you need is in your portal.', 'project', 0, 6, '2026-06-19 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('mtn_momo','airtel_money','bank','cash','other') NOT NULL DEFAULT 'other',
  `reference` varchar(120) DEFAULT NULL,
  `status` enum('pending','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `received_by` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT IGNORE INTO `payments` (`id`, `invoice_id`, `amount`, `method`, `reference`, `status`, `received_by`, `notes`, `confirmed_at`, `created_at`) VALUES
(1, 2, 8500000.00, 'bank', 'KFS-WEB-0420', 'confirmed', 1, 'Transferred from Kireka Farm Supplies bank account.', '2026-04-24 14:00:00', '2026-04-24 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `ref_no` varchar(30) NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `service_id` int(10) UNSIGNED DEFAULT NULL,
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `deliverables` varchar(190) DEFAULT NULL,
  `cover_art` varchar(40) DEFAULT NULL,
  `budget` decimal(14,2) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('NEW','REVIEWING','QUOTATION','APPROVED','IN_PROGRESS','WAITING_FOR_CLIENT','TESTING','COMPLETED','CANCELLED') NOT NULL DEFAULT 'NEW',
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT IGNORE INTO `projects` (`id`, `ref_no`, `client_id`, `service_id`, `assigned_to`, `title`, `description`, `requirements`, `website_url`, `deliverables`, `cover_art`, `budget`, `priority`, `status`, `progress`, `deadline`, `started_at`, `completed_at`, `submitted_at`, `updated_at`) VALUES
(1, 'RSI-2026-00001', 3, 1, 1, 'Company Website - Kireka Farm Supplies Ltd', 'A responsive company website that presents the farm supply catalogue, the company story and the contact details, with a lead-capture enquiry form and the SEO foundations a rural supplier needs to be found on Google.', 'Six information pages, enquiry form, WhatsApp integration, Google Map, photo gallery.', NULL, 'Website', 'agriculture', 8500000.00, 'normal', 'COMPLETED', 100, '2026-04-20', '2026-03-02 00:00:00', '2026-04-18 00:00:00', '2026-02-25 21:00:00', '2026-11-14 09:46:28'),
(2, 'RSI-2026-00002', 4, 3, 2, 'Sales & Stock Management System - Pearl Holdings Ltd', 'A custom management system for tracking sales, stock levels, suppliers and daily reports across the Pearl Holdings outlets in the Eastern region.', 'User roles for cashier and manager, product and stock modules, purchase tracking, daily sales reports, backup.', NULL, NULL, 'commerce', 14500000.00, 'high', 'IN_PROGRESS', 70, '2026-09-30', '2026-07-06 00:00:00', NULL, '2026-06-30 21:00:00', '2026-11-14 09:46:28'),
(3, 'RSI-2026-00003', 5, 2, 2, 'Ecommerce Store - Mulumba & Sons Traders', 'An online store for hardware and farm inputs with a product catalogue, cart, order management and mobile money ready payment architecture.', 'Product categories and search, cart and checkout, order dashboard, inventory, mobile money payment ready structure.', NULL, NULL, 'commerce', 9800000.00, 'normal', 'QUOTATION', 0, NULL, NULL, NULL, '2026-09-09 21:00:00', '2026-11-14 09:46:28'),
(4, 'RSI-2026-00004', 6, 3, NULL, 'Customer Feedback Portal - Agnes Nakato', 'A small portal where customers submit feedback and track the response, with a staff review dashboard.', 'Feedback form, automatic acknowledgements, staff review queue, simple analytics.', NULL, NULL, 'systems', 3500000.00, 'low', 'NEW', 0, NULL, NULL, NULL, '2026-09-19 21:00:00', '2026-11-14 09:46:28'),
(5, 'RSI-2026-00005', 3, 8, 1, 'Brand Refresh & Business Cards - Kireka Farm Supplies Ltd', 'Logo refresh, updated colour palette and print ready business cards and letterheads for the farm supply brand.', 'Two logo options, brand colours and typography, business cards, letterheads.', NULL, 'Branding', 'agriculture', 650000.00, 'normal', 'REVIEWING', 15, NULL, NULL, NULL, '2026-09-17 21:00:00', '2026-11-14 09:46:28'),
(6, 'RSI-2026-00006', 7, 1, 2, 'Hotel Website, Booking System & Guest App - Hotel Paradise on the Nile', 'A digital front desk for the hotel: a fast website for rooms and facilities, a booking and availability system the front office runs itself, and a guest app for enquiries, directions and offers.', 'Website with rooms, facilities, gallery and contact pages, online availability and booking enquiry system, front-desk reservation calendar, guest and staff logins, Android guest app, WhatsApp and Google Maps, gallery the hotel updates itself, SSL and nightly backups.', 'https://hotelparadiseonthenile.info', 'Website, Business system, Mobile apps', 'hospitality', 24000000.00, 'high', 'COMPLETED', 100, '2026-06-30', '2026-02-09 00:00:00', '2026-06-19 00:00:00', '2026-01-13 21:00:00', '2026-11-14 09:46:28'),
(7, 'RSI-2026-00007', 8, 5, 1, 'Website, Student Portal & Apps - Iganga School of Nursing and Midwifery', 'One digital home for the school: a public website for applicants and parents, a records and fees portal the staff use every day, and a mobile app that puts results, fees and announcements in a student\'s pocket.', 'Public website with programmes, fees structure and contacts, online application and enquiry form, student records and fees portal, staff andbursar logins, results and progress reports, Android app for results and school announcements, SMS and email alerts, Google Maps, SSL and nightly backups.', 'https://igangaschoolofnursingandmidwifery.ac.ug', 'Website, Business system, Mobile apps', 'education', 38000000.00, 'high', 'COMPLETED', 100, '2026-06-30', '2026-01-12 00:00:00', '2026-06-26 00:00:00', '2025-11-23 21:00:00', '2026-11-14 09:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

CREATE TABLE IF NOT EXISTS `project_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `uploader_id` int(10) UNSIGNED NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_messages`
--

CREATE TABLE IF NOT EXISTS `project_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `sender_role` enum('admin','staff','client') NOT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_messages`
--

INSERT IGNORE INTO `project_messages` (`id`, `project_id`, `sender_id`, `sender_role`, `message`, `is_internal`, `created_at`) VALUES
(1, 1, 3, 'client', 'Thank you for the great work on our website. The team at Kireka Farm Supplies is very happy with it.', 0, '2026-04-20 06:30:00'),
(2, 1, 1, 'admin', 'Thank you Amara! It was a pleasure working with you. We are available any time for maintenance.', 0, '2026-04-20 08:00:00'),
(3, 2, 4, 'client', 'Please confirm the final layout for the sales dashboard.', 0, '2026-09-01 07:15:00'),
(4, 2, 2, 'staff', 'Sharing the dashboard preview this week, and we are on track for the end-of-month review.', 0, '2026-09-02 05:40:00'),
(5, 2, 1, 'admin', 'Reminder: confirm Pearl Holdings DB backup schedule before go-live.', 1, '2026-09-02 06:00:00'),
(6, 6, 7, 'client', 'Please send the updated room photographs and the official contact numbers for the footer and booking form.', 0, '2026-03-02 07:20:00'),
(7, 6, 2, 'staff', 'Rooms and facilities pages are in progress. Send the photographs as soon as you can and we will load them immediately.', 0, '2026-03-03 05:15:00'),
(8, 6, 7, 'client', 'The site looks wonderful and the booking form already brought us two walk-in guests. Please show us how to update the gallery ourselves.', 0, '2026-06-19 10:40:00'),
(9, 6, 1, 'admin', 'Congratulations on your launch. The gallery, rooms and offers are all editable from the front desk login, and we are on WhatsApp whenever you need us.', 0, '2026-06-19 12:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE IF NOT EXISTS `project_tasks` (
  `id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('TODO','IN_PROGRESS','REVIEW','COMPLETED') NOT NULL DEFAULT 'TODO',
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_tasks`
--

INSERT IGNORE INTO `project_tasks` (`id`, `project_id`, `assigned_to`, `title`, `description`, `status`, `progress`, `priority`, `start_date`, `due_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Design and structure', 'Plan sitemap, wireframes and page structure for the Kireka site.', 'COMPLETED', 100, 'high', '2026-03-02', '2026-03-08', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(2, 1, 2, 'Home page build', 'Build the responsive home page and header.', 'COMPLETED', 100, 'high', '2026-03-09', '2026-03-16', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(3, 1, 2, 'Content upload & launch', 'Upload all copy and images, test on mobile and launch.', 'COMPLETED', 100, 'medium', '2026-03-20', '2026-04-18', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(4, 2, 2, 'Database design', 'Design tables for products, stock, sales and suppliers.', 'COMPLETED', 100, 'high', '2026-07-06', '2026-07-12', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(5, 2, 2, 'Sales dashboard module', 'Build the cashier sales entry and manager dashboard.', 'IN_PROGRESS', 60, 'high', '2026-07-15', '2026-09-01', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(6, 2, 1, 'Reports & exports', 'Daily sales reports and CSV export for management.', 'TODO', 0, 'medium', NULL, '2026-09-25', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(7, 3, 2, 'Catalogue & cart', 'Product catalogue, search and shopping cart.', 'TODO', 0, 'high', NULL, '2026-10-05', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(8, 6, 2, 'Rooms & facilities pages', 'Build the rooms, facilities and gallery pages with the hotel photography.', 'COMPLETED', 100, 'high', '2026-02-09', '2026-03-20', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(9, 6, 2, 'Booking enquiry form', 'Availability enquiry form that reaches the hotel WhatsApp and email, with the stay details captured.', 'COMPLETED', 100, 'high', '2026-03-23', '2026-04-24', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(10, 6, 2, 'Booking calendar & guest app', 'Front-desk reservation calendar plus the Android guest app for enquiries and offers.', 'COMPLETED', 100, 'high', '2026-04-27', '2026-06-05', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(11, 6, 1, 'Launch & handover', 'Final content check, mobile testing, launch and training for the hotel team.', 'COMPLETED', 100, 'medium', '2026-06-08', '2026-06-19', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(12, 7, 1, 'School website', 'Public site: programmes, fees structure, application and enquiry forms, news and contacts.', 'COMPLETED', 100, 'high', '2026-01-12', '2026-02-27', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(13, 7, 2, 'Student records & fees portal', 'Admissions, class registers, fees per term, receipts and progress reports for staff and bursar logins.', 'COMPLETED', 100, 'high', '2026-03-02', '2026-05-08', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(14, 7, 2, 'Android app', 'Student app with results, fee balance and school announcements, plus SMS and email alerts.', 'COMPLETED', 100, 'high', '2026-05-11', '2026-06-19', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(15, 7, 1, 'Training & handover', 'Train the registrar and bursar, load real data, go live and hand over with backups running.', 'COMPLETED', 100, 'medium', '2026-06-22', '2026-06-26', 1, '2026-10-31 07:13:45', '2026-10-31 07:13:45');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE IF NOT EXISTS `quotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `quotation_no` varchar(30) NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `issued_on` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','accepted','rejected','expired','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT IGNORE INTO `quotations` (`id`, `quotation_no`, `project_id`, `client_id`, `issued_on`, `expiry_date`, `subtotal`, `discount`, `tax_percent`, `total`, `status`, `notes`, `terms`, `created_at`, `updated_at`) VALUES
(1, 'QT-2026-0001', 3, 5, '2026-10-31', '2026-12-05', 9800000.00, 0.00, 0.00, 9800000.00, 'sent', 'E-commerce build for Mulumba & Sons Traders.', '50% deposit to start, balance on delivery. Free support for 30 days after launch.', '2026-10-31 07:13:45', '2026-11-05 07:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE IF NOT EXISTS `quotation_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `quotation_id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT IGNORE INTO `quotation_items` (`id`, `quotation_id`, `description`, `quantity`, `unit_price`) VALUES
(1, 1, 'E-commerce design & development', 1.00, 7000000.00),
(2, 1, 'Mobile money and bank payment ready architecture', 1.00, 2000000.00),
(3, 1, 'Testing, training & launch support', 1.00, 800000.00);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE IF NOT EXISTS `services` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `price_min` decimal(14,2) NOT NULL DEFAULT 0.00,
  `price_max` decimal(14,2) DEFAULT NULL,
  `currency` varchar(8) NOT NULL DEFAULT 'UGX',
  `pricing_type` enum('range','starting_from','contact_for_quote') NOT NULL DEFAULT 'starting_from',
  `price_note` varchar(120) NOT NULL DEFAULT '',
  `features` text DEFAULT NULL,
  `delivery_days` int(10) UNSIGNED DEFAULT NULL,
  `icon` varchar(60) NOT NULL DEFAULT 'code-s',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT IGNORE INTO `services` (`id`, `name`, `slug`, `description`, `price_min`, `price_max`, `currency`, `pricing_type`, `price_note`, `features`, `delivery_days`, `icon`, `status`, `sort_order`, `created_at`) VALUES
(1, 'Business Website Development', 'business-website', 'A professional, responsive company website with clear structure, contact integration, SEO foundations and a design that represents your brand.', 800000.00, 12000000.00, 'UGX', 'range', 'Professional company websites', 'Custom responsive design for mobile, tablet and desktop\r\nContact & enquiry form\r\nWhatsApp and phone integration\r\nSearch engine friendly structure\r\nGoogle Map & business details\r\nTraining on how to manage content', 14, 'globe', 'active', 1, '2026-10-31 07:13:45'),
(2, 'Ecommerce Development', 'ecommerce', 'Online stores with product catalogues, order management and a payment architecture ready for mobile money and bank integration.', 1200000.00, 18000000.00, 'UGX', 'range', 'Online stores & checkout', 'Product catalogue and categories\r\nShopping cart and checkout\r\nOrder management dashboard\r\nMobile money and bank payment ready architecture\r\nInventory management\r\nDelivery and order tracking', 30, 'cart', 'active', 2, '2026-10-31 07:13:45'),
(3, 'Custom Business Systems', 'business-systems', 'Management systems built for schools, hospitals, NGOs, government offices and companies, with client portals, dashboards, workflow automation and database driven tools around your real business processes.', 22000000.00, 55000000.00, 'UGX', 'range', 'Multi-role business systems', 'Role based user access\r\nOnline fees, payments & mobile money\r\nDashboards and reports\r\nWorkflow automation\r\nSMS notifications\r\nUser training and handover', 30, 'cpu', 'active', 3, '2026-10-31 07:13:45'),
(4, 'Software Development', 'software-dev', 'Custom web applications and internal business systems built with modern PHP/MySQL technology, secure by default and tailored to you.', 15000000.00, 45000000.00, 'UGX', 'range', 'Bespoke web applications', 'Requirements analysis\r\nCustom database design\r\nSecure authentication\r\nAdministration panels\r\nAPI and integration ready\r\nDeployment and support', 30, 'code-s', 'active', 4, '2026-10-31 07:13:45'),
(5, 'Web & Mobile App Development', 'web-mobile-apps', 'Business apps built to work as a website, an Android app and an iOS app, prepared, tested and published to the Apple App Store and Google Play.', 12000000.00, NULL, 'UGX', 'starting_from', 'App Store & Google Play builds', 'Web, Android & iOS builds\r\nApp Store & Google Play publication\r\nPush and SMS notifications\r\nMobile money & bank payments\r\nDashboards and admin panels\r\nTesting across devices', 30, 'mobile', 'active', 5, '2026-10-31 07:13:45'),
(6, 'Receipt & Billing Automation', 'receipt-billing', 'Automatic receipt and invoice generation, so every sale prints or sends a professional receipt instantly, with totals, mobile money references and daily reports.', 3500000.00, 9000000.00, 'UGX', 'range', 'Automated receipts & reports', 'Automatic receipt printing and sending\r\nInvoice generation and numbering\r\nMobile money reference capture\r\nDaily cash & sales totals\r\nShop, school and clinic billing\r\nReport formats for daily and monthly use', 14, 'receipt', 'active', 6, '2026-10-31 07:13:45'),
(7, 'Custom Digital Solutions', 'custom-solutions', 'A specific business problem that does not fit a ready made package? We design and build the right digital solution for it.', 0.00, NULL, 'UGX', 'contact_for_quote', 'Contact for a quote', 'Consultation and scoping\r\nFeasibility recommendation\r\nBespoke design and build\r\nTesting and quality assurance\r\nDeployment\r\nOngoing support', 21, 'layers', 'active', 7, '2026-10-31 07:13:45'),
(8, 'Graphic & Branding Design', 'branding', 'Logos, brand guidelines, business cards, posters and digital design that give your company a consistent and professional identity.', 350000.00, NULL, 'UGX', 'starting_from', 'Identity & brand systems', 'Logo design options\r\nBrand colour and typography\r\nBusiness cards and letterheads\r\nSocial media graphics\r\nPrint ready file delivery\r\nUsage guideline document', 7, 'palette', 'active', 8, '2026-10-31 07:13:45'),
(9, 'Website Maintenance', 'maintenance', 'Security updates, backups, content changes and technical support so your website stays fast, safe and up to date.', 180000.00, NULL, 'UGX', 'starting_from', 'Care plans & support', 'Monthly security checks\r\nRegular backups\r\nContent and image updates\r\nPerformance monitoring\r\nPriority technical support\r\nUptime reporting', 1, 'shield', 'active', 9, '2026-10-31 07:13:45'),
(10, 'Video Game Development', 'video-games', 'Custom video games and interactive media for mobile, web and PC, covering game design, graphics, scoring, levels and store ready builds.', 14000000.00, NULL, 'UGX', 'starting_from', 'Playable game builds', 'Game design and story development\r\n2D and 3D game graphics\r\nMobile, web and PC builds\r\nLevels, scores and progress saving\r\nMultiplayer and leaderboards\r\nTesting and store publication support', 45, 'gamepad', 'active', 10, '2026-10-31 07:13:45'),
(11, 'Digital Books & Publications', 'digital-books', 'Professional layout and cover design, editing and ebook formats so your books look great in print and on Kindle, tablets and phones.', 1200000.00, NULL, 'UGX', 'starting_from', 'Print & ebook production', 'Book layout and cover design\r\nEbook formats for common readers\r\nPrint ready file delivery\r\nEditing and proofreading\r\nTables, charts and diagrams\r\nProfessional illustration support', 14, 'book', 'active', 11, '2026-10-31 07:13:45'),
(12, 'Chatbots & Smart Automation', 'chat-automation', 'Business chatbots and smart automation that answer customer questions, capture leads and handle routine tasks automatically, day and night.', 6000000.00, 16000000.00, 'UGX', 'range', 'Conversational automation', 'Business chatbot built into your site\r\nAutomated replies and FAQs\r\nLead capture and handover\r\nWorkflow automation\r\nSMS and WhatsApp integration\r\nTraining and handover', 21, 'chat', 'active', 12, '2026-10-31 07:13:45'),
(13, 'System Integration & Payments', 'system-integration', 'Connecting your systems, websites and apps: mobile money and bank payment links, SMS services, APIs and data exchange between the tools you already use.', 4000000.00, 18000000.00, 'UGX', 'range', 'APIs, payments & data links', 'Mobile money & bank payment links\r\nSMS notification services\r\nAPI connections between systems\r\nData import and export\r\nSystem to system linking\r\nTesting and security review', 14, 'link', 'active', 13, '2026-10-31 07:13:45'),
(14, 'Cybersecurity & Audits', 'cybersecurity', 'Security audits, vulnerability fixes, backups and data protection so your website and systems stay safe from attacks and data loss.', 1400000.00, NULL, 'UGX', 'starting_from', 'Audits & hardening', 'Full security audit of your systems\r\nVulnerability and patch fixes\r\nBackup and recovery setup\r\nUser access and permission review\r\nThreat and activity monitoring\r\nSecurity handover guide', 7, 'lock', 'active', 14, '2026-10-31 07:13:45');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT IGNORE INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'company_name', 'Reagansoft Innovation Limited'),
(2, 'company_tagline', 'Innovating today for a smarter tomorrow.'),
(3, 'company_founder', 'Reagan Otema'),
(4, 'company_phone', '+256772514889'),
(5, 'company_email', 'info@reagansoftinnovation.com'),
(6, 'company_address', 'Jinja, Uganda'),
(7, 'company_whatsapp', '+256772514889'),
(8, 'company_fb', ''),
(9, 'company_x', ''),
(10, 'company_linkedin', ''),
(11, 'company_about', 'Reagansoft Innovation Limited is a software company based in Jinja, Uganda. We design and build websites, business systems, ecommerce stores and custom software for businesses across the country, and we stay around to look after them after launch.'),
(12, 'currency', 'UGX'),
(13, 'tax_percent', '0'),
(14, 'invoices_due_days', '14'),
(15, 'payment_mtn_number', '+256772514889'),
(16, 'payment_airtel_number', ''),
(17, 'payment_bank_details', ''),
(18, 'payment_gateway_status', 'not_configured'),
(19, 'project_deposit', '200000'),
(20, 'registration_open', '1'),
(42, 'company_hours', 'Mon - Fri, 8:00am - 6:00pm EAT'),
(43, 'company_map_url', 'https://www.google.com/maps/search/?api=1&query=Jinja%2C+Uganda'),
(44, 'seo_keywords', 'software development Uganda, website design Jinja, school management system, hospital management system, POS Uganda, Reagansoft Innovation'),
(45, 'site_announcement', ''),
(46, 'support_email', 'support@reagansoftinnovation.com'),
(47, 'newsletter_enabled', '1'),
(48, 'company_phone_alt', '+256730314979'),
(49, 'company_legal_name', 'ReaganSof Innovation'),
(50, 'company_registration_no', '80048847119307'),
(51, 'usd_rate', '3800'),
(52, 'company_registration_country', 'Uganda');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `failed_logins` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT IGNORE INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `company`, `address`, `avatar`, `payer_type`, `tax_id`, `national_id`, `country`, `city`, `billing_terms_at`, `active`, `failed_logins`, `locked_until`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'Reagan Otema', 'admin@reagansoft.com', '+256730314979', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'admin', 'Reagansoft Innovation Limited', 'Jinja, Uganda', NULL, 'business', NULL, NULL, 'Uganda', NULL, NULL, 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-11-14 09:46:28'),
(2, 'Sarah Namukasa', 'staff@reagansoft.com', '+256772514889', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'staff', NULL, NULL, NULL, 'business', NULL, NULL, 'Uganda', NULL, NULL, 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(3, 'Amara Kaggwa', 'amara@kirekafarms.com', '+256702456789', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Kireka Farm Supplies Ltd', 'Kireka, Kampala', NULL, 'business', '1002456789', '256754321098', 'Uganda', 'Kampala', '2026-01-12 09:14:00', 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(4, 'Grace Ayebare', 'grace@pearlholdings.com', '+256778987654', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Pearl Holdings Ltd', 'Jinja, Uganda', NULL, 'business', '1008765432', '256701234567', 'Uganda', 'Jinja', '2026-01-18 10:02:00', 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(5, 'David Mulumba', 'david@mulumbasons.com', '+256703246810', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Mulumba & Sons Traders', 'Iganga, Uganda', NULL, 'business', '1011325468', '256788990011', 'Uganda', 'Iganga', '2026-02-03 14:37:00', 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(6, 'Agnes Nakato', 'agnesnakato@gmail.com', '+256759135790', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', NULL, 'Plot 14, Nalufenya, Jinja', NULL, 'individual', NULL, '256700112233', 'Uganda', 'Jinja', '2026-02-11 08:55:00', 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(7, 'Hotel Paradise on the Nile', 'info@hotelparadiseonthenile.co.ug', '+256754412880', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Hotel Paradise on the Nile', 'Main Street, Jinja, Uganda', NULL, 'business', '1015246800', NULL, 'Uganda', 'Jinja', '2026-01-25 11:20:00', 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-10-31 07:13:45'),
(8, 'Iganga School of Nursing and Midwifery', 'info@igangaschoolofnursingandmidwifery.ac.ug', '+256701489236', '$2y$10$yzlD5fteOLtKy0yV4pB/HONLqntQJqYIEOI161ik7Ji0ct8QLbdGu', 'client', 'Iganga School of Nursing and Midwifery', 'Iganga, Uganda', NULL, 'business', '1009988776', NULL, 'Uganda', 'Iganga', '2026-01-14 16:08:00', 1, 0, NULL, NULL, '2026-10-31 07:13:45', '2026-10-31 07:13:45');

--
-- Idempotent schema helpers. Safe to re-import without dropping data.
SET FOREIGN_KEY_CHECKS=0;
DROP PROCEDURE IF EXISTS rsi_add_primary;
DROP PROCEDURE IF EXISTS rsi_add_index;
DROP PROCEDURE IF EXISTS rsi_add_fk;
DELIMITER $$
CREATE PROCEDURE rsi_add_primary(IN p_table VARCHAR(64), IN p_cols TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND INDEX_NAME = 'PRIMARY') THEN
    SET @s = CONCAT('ALTER TABLE `', p_table, '` ADD PRIMARY KEY (', p_cols, ')');
    PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
  END IF;
END$$
CREATE PROCEDURE rsi_add_index(IN p_table VARCHAR(64), IN p_index VARCHAR(64), IN p_cols TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND INDEX_NAME = p_index) THEN
    SET @s = CONCAT('ALTER TABLE `', p_table, '` ADD INDEX `', p_index, '` (', p_cols, ')');
    PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
  END IF;
END$$
CREATE PROCEDURE rsi_add_fk(IN p_table VARCHAR(64), IN p_name VARCHAR(64), IN p_sql TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND CONSTRAINT_NAME = p_name AND CONSTRAINT_TYPE = 'FOREIGN KEY') THEN
    SET @s = CONCAT('ALTER TABLE `', p_table, '` ADD CONSTRAINT `', p_name, '` ', p_sql);
    PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
  END IF;
END$$
DELIMITER ;
-- Idempotent indexes for dumped tables
--
CALL rsi_add_primary('activity_logs','`id`');
CALL rsi_add_index('activity_logs','idx_logs_entity','`entity_type`,`entity_id`');
CALL rsi_add_index('activity_logs','idx_logs_user','`user_id`');
CALL rsi_add_index('activity_logs','idx_logs_created','`created_at`');
CALL rsi_add_index('activity_logs','idx_logs_action','`action`');
CALL rsi_add_primary('contact_messages','`id`');
CALL rsi_add_index('contact_messages','idx_contact_read','`is_read`');
CALL rsi_add_index('contact_messages','idx_contact_created','`created_at`');
CALL rsi_add_primary('expenses','`id`');
CALL rsi_add_index('expenses','recorded_by','`recorded_by`');
CALL rsi_add_index('expenses','idx_expenses_date','`expense_date`');
CALL rsi_add_index('expenses','idx_expenses_category','`category`');
CALL rsi_add_index('expenses','idx_expenses_project','`project_id`');
CALL rsi_add_primary('invoices','`id`');
CALL rsi_add_index('invoices','invoice_no','`invoice_no`');
CALL rsi_add_index('invoices','quotation_id','`quotation_id`');
CALL rsi_add_index('invoices','idx_invoices_project','`project_id`');
CALL rsi_add_index('invoices','idx_invoices_client','`client_id`');
CALL rsi_add_index('invoices','idx_invoices_status','`status`');
CALL rsi_add_index('invoices','idx_invoices_due','`due_date`');
CALL rsi_add_primary('invoice_items','`id`');
CALL rsi_add_index('invoice_items','idx_ii_invoice','`invoice_id`');
CALL rsi_add_primary('newsletter_subscribers','`id`');
CALL rsi_add_index('newsletter_subscribers','email','`email`');
CALL rsi_add_index('newsletter_subscribers','idx_newsletter_active','`is_active`');
CALL rsi_add_primary('notifications','`id`');
CALL rsi_add_index('notifications','related_project_id','`related_project_id`');
CALL rsi_add_index('notifications','idx_notifications_user','`user_id`,`is_read`');
CALL rsi_add_primary('payments','`id`');
CALL rsi_add_index('payments','received_by','`received_by`');
CALL rsi_add_index('payments','idx_payments_invoice','`invoice_id`');
CALL rsi_add_index('payments','idx_payments_status','`status`');
CALL rsi_add_index('payments','idx_payments_created','`created_at`');
CALL rsi_add_primary('projects','`id`');
CALL rsi_add_index('projects','ref_no','`ref_no`');
CALL rsi_add_index('projects','idx_projects_client','`client_id`');
CALL rsi_add_index('projects','idx_projects_status','`status`');
CALL rsi_add_index('projects','idx_projects_assigned','`assigned_to`');
CALL rsi_add_index('projects','idx_projects_service','`service_id`');
CALL rsi_add_index('projects','idx_projects_updated','`updated_at`');
CALL rsi_add_index('projects','idx_projects_submitted','`submitted_at`');
CALL rsi_add_primary('project_files','`id`');
CALL rsi_add_index('project_files','stored_name','`stored_name`');
CALL rsi_add_index('project_files','uploader_id','`uploader_id`');
CALL rsi_add_index('project_files','idx_files_project','`project_id`');
CALL rsi_add_primary('project_messages','`id`');
CALL rsi_add_index('project_messages','sender_id','`sender_id`');
CALL rsi_add_index('project_messages','idx_messages_project','`project_id`');
CALL rsi_add_primary('project_tasks','`id`');
CALL rsi_add_index('project_tasks','created_by','`created_by`');
CALL rsi_add_index('project_tasks','idx_tasks_project','`project_id`');
CALL rsi_add_index('project_tasks','idx_tasks_status','`status`');
CALL rsi_add_index('project_tasks','idx_tasks_assigned','`assigned_to`');
CALL rsi_add_primary('quotations','`id`');
CALL rsi_add_index('quotations','quotation_no','`quotation_no`');
CALL rsi_add_index('quotations','idx_quotes_project','`project_id`');
CALL rsi_add_index('quotations','idx_quotes_client','`client_id`');
CALL rsi_add_primary('quotation_items','`id`');
CALL rsi_add_index('quotation_items','idx_qi_quotation','`quotation_id`');
CALL rsi_add_primary('services','`id`');
CALL rsi_add_index('services','slug','`slug`');
CALL rsi_add_index('services','idx_services_status','`status`');
CALL rsi_add_primary('settings','`id`');
CALL rsi_add_index('settings','setting_key','`setting_key`');
CALL rsi_add_primary('users','`id`');
CALL rsi_add_index('users','email','`email`');
CALL rsi_add_index('users','idx_users_role','`role`');
CALL rsi_add_index('users','idx_users_active','`active`');
CALL rsi_add_index('users','idx_users_created','`created_at`');

-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_messages`
--
ALTER TABLE `project_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Idempotent constraints for dumped tables
--
CALL rsi_add_fk('activity_logs','activity_logs_ibfk_1','FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('expenses','expenses_ibfk_1','FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('expenses','expenses_ibfk_2','FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('invoices','invoices_ibfk_1','FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('invoices','invoices_ibfk_2','FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('invoices','invoices_ibfk_3','FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('invoice_items','invoice_items_ibfk_1','FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('notifications','notifications_ibfk_1','FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('notifications','notifications_ibfk_2','FOREIGN KEY (`related_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('payments','payments_ibfk_1','FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('payments','payments_ibfk_2','FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('projects','projects_ibfk_1','FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('projects','projects_ibfk_2','FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('projects','projects_ibfk_3','FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('project_files','project_files_ibfk_1','FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('project_files','project_files_ibfk_2','FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('project_messages','project_messages_ibfk_1','FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('project_messages','project_messages_ibfk_2','FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('project_tasks','project_tasks_ibfk_1','FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('project_tasks','project_tasks_ibfk_2','FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('project_tasks','project_tasks_ibfk_3','FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL');
CALL rsi_add_fk('quotations','quotations_ibfk_1','FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('quotations','quotations_ibfk_2','FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
CALL rsi_add_fk('quotation_items','quotation_items_ibfk_1','FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE');
DROP PROCEDURE IF EXISTS rsi_add_primary;
DROP PROCEDURE IF EXISTS rsi_add_index;
DROP PROCEDURE IF EXISTS rsi_add_fk;
SET FOREIGN_KEY_CHECKS=1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
