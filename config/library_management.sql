-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2026 at 04:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publication_year` int(11) DEFAULT NULL,
  `book_cover` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `language` varchar(50) DEFAULT 'English',
  `pages` int(11) DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `total_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 1,
  `pdf_file` varchar(255) DEFAULT NULL,
  `rack_number` varchar(50) DEFAULT NULL,
  `shelf_number` varchar(50) DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `is_reference` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `isbn`, `title`, `author`, `publisher`, `publication_year`, `book_cover`, `description`, `category_id`, `subcategory_id`, `language`, `pages`, `edition`, `total_copies`, `available_copies`, `pdf_file`, `rack_number`, `shelf_number`, `purchase_price`, `is_reference`, `created_at`, `updated_at`, `created_by`) VALUES
(1, '978-0-545-01022-1', 'The Great Gatsby', 'F. Scott Fitzgerald', 'Scribner', 1925, 'cover_1.png', 'A classic American novel set during the Jazz Age in New York.', 1, 1, 'English', 180, '1st', 5, 5, 'book_1.pdf', NULL, NULL, 15.99, 0, '2026-03-22 12:24:17', '2026-04-18 13:27:20', 1),
(2, '978-0-14-118277-0', 'Pride and Prejudice', 'Jane Austen', 'Penguin Classics', 1813, 'cover_2.jpg', 'A romantic novel of manners and marriage set in Georgian England.', 1, 1, 'English', 432, '1st', 4, 4, 'book_2.pdf', NULL, NULL, 12.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:01:51', 1),
(3, '978-0-7432-7356-5', '1984', 'George Orwell', 'Penguin Books', 1949, 'cover_3.jpg', 'A dystopian novel set in a totalitarian superstate.', 1, 3, 'English', 328, '1st', 6, 6, 'book_3.pdf', NULL, NULL, 14.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:06:20', 1),
(4, '978-0-06-112008-4', 'To Kill a Mockingbird', 'Harper Lee', 'HarperCollins', 1960, 'cover_4.jpg', 'A gripping tale of racial injustice and childhood innocence.', 1, 2, 'English', 324, '1st', 5, 5, 'book_4.pdf', NULL, NULL, 16.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:07:03', 1),
(5, '978-0-14-118706-5', 'Moby Dick', 'Herman Melville', 'Penguin Classics', 1848, 'cover_5.jpg', 'An epic novel about Captain Ahab obsessive quest.', 1, 3, 'English', 585, '1st', 3, 3, 'book_5.pdf', NULL, NULL, 18.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:07:28', 1),
(6, '978-0-7434-2817-1', 'The Catcher in the Rye', 'J.D. Salinger', 'Little, Brown', 1951, 'cover_6.jpg', 'A coming-of-age novel following Holden Caulfield.', 1, 2, 'English', 277, '1st', 4, 4, 'book_6.pdf', NULL, NULL, 15.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:07:40', 1),
(7, '978-0-06-093546-7', 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, 'cover_7.jpg', 'A landmark volume in science writing.', 3, 6, 'English', 237, '1st', 3, 3, 'book_7.pdf', NULL, NULL, 18.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:07:49', 1),
(8, '978-0-14-028329-7', 'The Odyssey', 'Homer', 'Penguin Classics', 800, 'cover_8.jpg', 'An epic Greek poem following Odysseus journey home.', 4, 8, 'English', 375, '1st', 3, 3, 'book_8.pdf', NULL, NULL, 14.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:08:00', 1),
(9, '978-0-345-33312-0', 'Dune', 'Frank Herbert', 'ACE', 1965, 'cover_9.jpg', 'A science fiction masterpiece set on the desert planet Arrakis.', 1, 3, 'English', 682, '1st', 4, 4, 'book_9.pdf', NULL, NULL, 17.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:08:12', 1),
(10, '978-0-7432-7357-2', 'Wuthering Heights', 'Emily Bronte', 'Penguin Classics', 1847, 'cover_10.jpg', 'A dark gothic novel exploring themes of passion and revenge.', 1, 1, 'English', 352, '1st', 3, 3, 'book_10.pdf', NULL, NULL, 13.99, 0, '2026-03-22 12:24:17', '2026-04-18 14:08:43', 1),
(12, '978-0-14-028330-4', 'Outlander', 'Diana Gabaldon', 'Delacorte Press', 1991, 'cover_12.jpg', 'A time-traveling romance across centuries.', 1, 1, 'English', 688, '1st', 3, 3, 'book_12.pdf', NULL, NULL, 16.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:09:00', 1),
(13, '978-0-7432-7358-9', 'The Notebook', 'Nicholas Sparks', 'Grand Central Publishing', 1996, 'cover_13.jpg', 'A timeless love story of two souls reunited.', 1, 1, 'English', 214, '1st', 5, 5, 'book_13.pdf', NULL, NULL, 14.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:09:11', 1),
(14, '978-0-345-33313-1', 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'Norstedts', 2005, 'cover_14.jpg', 'A gripping mystery involving a missing person and corporate conspiracy.', 1, 2, 'English', 465, '1st', 4, 4, 'book_14.pdf', NULL, NULL, 16.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:09:21', 1),
(15, '978-0-06-112010-7', 'Murder on the Orient Express', 'Agatha Christie', 'Collins Crime Club', 1934, 'cover_15.jpg', 'A classic locked-room mystery aboard a luxury train.', 1, 2, 'English', 256, '1st', 4, 4, 'book_15.pdf', NULL, NULL, 12.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:09:30', 1),
(16, '978-0-14-118707-2', 'The Da Vinci Code', 'Dan Brown', 'Doubleday', 2003, 'cover_16.jpg', 'A thrilling mystery involving art, history, and ancient secrets.', 1, 2, 'English', 454, '1st', 5, 5, 'book_16.pdf', NULL, NULL, 15.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:09:42', 1),
(17, '978-0-7432-7359-6', 'The Lord of the Rings', 'J.R.R. Tolkien', 'Allen & Unwin', 1954, 'cover_17.jpg', 'An epic fantasy trilogy about the fight against dark forces.', 1, 3, 'English', 1178, 'Complete', 3, 3, 'book_17.pdf', NULL, NULL, 29.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:10:02', 1),
(20, '978-0-14-118708-9', 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster', 2011, 'cover_20.jpg', 'Biography of the Apple founder and visionary.', 2, 4, 'English', 630, '1st', 4, 4, 'book_20.pdf', NULL, NULL, 17.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:10:12', 1),
(21, '978-0-7432-7360-2', 'Becoming', 'Michelle Obama', 'Crown Publishing', 2018, 'cover_21.jpg', 'Memoir of the former First Lady of the United States.', 2, 4, 'English', 426, '1st', 5, 5, 'book_21.pdf', NULL, NULL, 18.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:10:29', 1),
(24, '978-0-14-118709-6', 'Cosmos', 'Carl Sagan', 'Random House', 1980, 'cover_24.jpg', 'A journey through space and time exploring the cosmos.', 3, 6, 'English', 978, '1st', 2, 2, 'book_24.pdf', NULL, NULL, 19.99, 0, '2026-04-09 12:05:27', '2026-04-18 14:10:38', 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`) VALUES
(1, 'Fiction', 'Fictional novels and stories', '2026-03-22 12:24:17'),
(2, 'Non-Fiction', 'Non-fictional books and references', '2026-03-22 12:24:17'),
(3, 'Science', 'Science and technology books', '2026-03-22 12:24:17'),
(4, 'History', 'Historical books and references', '2026-03-22 12:24:17'),
(5, 'Self-Help', 'Self-help and personal development', '2026-03-22 12:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `user_id`, `email`, `ip_address`, `attempt_time`, `success`) VALUES
(1, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-09 13:22:13', 1),
(3, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-09 14:33:41', 0),
(4, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-09 14:33:47', 1),
(5, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-09 14:55:16', 1),
(6, 1, 'admin@librarysystem.com', '::1', '2026-04-09 15:05:20', 1),
(7, 1, 'admin@librarysystem.com', '::1', '2026-04-09 15:17:34', 1),
(8, 1, 'admin@librarysystem.com', '::1', '2026-04-09 15:29:57', 1),
(9, 1, 'admin@librarysystem.com', '::1', '2026-04-09 15:57:23', 1),
(10, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-09 15:58:04', 1),
(11, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 05:43:52', 1),
(12, 1, 'admin@librarysystem.com', '::1', '2026-04-10 05:44:01', 1),
(13, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 05:45:26', 1),
(14, 1, 'admin@librarysystem.com', '::1', '2026-04-10 05:45:47', 1),
(15, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 05:50:11', 1),
(19, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 07:03:01', 1),
(30, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 07:11:41', 0),
(31, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 07:11:43', 0),
(34, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 07:26:36', 1),
(35, 1, 'admin@librarysystem.com', '::1', '2026-04-10 07:26:47', 1),
(36, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-10 07:56:43', 1),
(38, 12, 'hentailover1959@gmail.com', '::1', '2026-04-18 12:16:27', 1),
(39, 12, 'hentailover1959@gmail.com', '::1', '2026-04-18 12:17:20', 1),
(40, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-18 12:21:17', 0),
(41, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-18 12:21:21', 0),
(42, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-18 12:21:28', 1),
(43, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-18 12:22:59', 1),
(44, 12, 'hentailover1959@gmail.com', '::1', '2026-04-18 12:35:11', 1),
(45, 2, 'rohan.14yahoo@gmail.com', '::1', '2026-04-18 13:20:23', 1),
(46, 12, 'hentailover1959@gmail.com', '::1', '2026-04-18 14:11:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`, `used`) VALUES
(41, 2, '4d7a6e54dafea2b1ff8de4f80a9b82abbb57a579853b9dd2f3efc349e68d056b', '2026-04-18 12:55:23', '2026-04-18 11:55:23', 0),
(42, 2, '5be1c5e602241aba8a2b5d6f8c9b04f6f68eb295acd6b7c0c9ff48c04977f9cb', '2026-04-18 13:09:11', '2026-04-18 12:09:11', 0),
(43, 2, '51aad10e29627e631dac642291a51c7c364c7e8562d365d7e459576df66f8bcb', '2026-04-18 13:10:24', '2026-04-18 12:10:24', 0),
(44, 1, 'd0fcaf032650a04f6d5fb000a15fc8d97f895ac7ee156c7ee12ca06e18f415f4', '2026-04-18 13:10:57', '2026-04-18 12:10:57', 0),
(45, 2, 'aa0fd5cfffa381eea8413e0c607324f7f896de4173aeeb0356715181058f6fcb', '2026-04-18 13:11:15', '2026-04-18 12:11:15', 0);

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`subcategory_id`, `category_id`, `subcategory_name`, `description`, `created_at`) VALUES
(1, 1, 'Romance', 'Romantic novels and love stories', '2026-03-22 12:24:17'),
(2, 1, 'Mystery', 'Mystery and detective novels', '2026-03-22 12:24:17'),
(3, 1, 'Fantasy', 'Fantasy and magical stories', '2026-03-22 12:24:17'),
(4, 2, 'Biography', 'Biographical works and memoirs', '2026-03-22 12:24:17'),
(5, 2, 'Self-Help', 'Practical self-improvement guides', '2026-03-22 12:24:17'),
(6, 3, 'Physics', 'Physics and astronomy books', '2026-03-22 12:24:17'),
(7, 3, 'Biology', 'Biology and natural sciences', '2026-03-22 12:24:17'),
(8, 4, 'Ancient History', 'Ancient civilizations and history', '2026-03-22 12:24:17'),
(9, 4, 'Modern History', 'Modern era and contemporary history', '2026-03-22 12:24:17'),
(10, 5, 'Motivation', 'Motivational and inspirational books', '2026-03-22 12:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `two_factor_attempts`
--

CREATE TABLE `two_factor_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `two_factor_auth`
--

CREATE TABLE `two_factor_auth` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `backup_codes` varchar(1000) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('admin','librarian','member') DEFAULT 'member',
  `membership_status` enum('active','inactive','suspended') DEFAULT 'active',
  `membership_number` varchar(50) DEFAULT NULL,
  `membership_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `outstanding_fine` decimal(10,2) DEFAULT 0.00,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `email_verification_token` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone`, `password_hash`, `date_of_birth`, `address`, `city`, `state`, `postal_code`, `country`, `profile_picture`, `role`, `membership_status`, `membership_number`, `membership_date`, `expiry_date`, `outstanding_fine`, `is_verified`, `verification_token`, `last_login`, `created_at`, `updated_at`, `created_by`, `email_verification_token`, `email_verified_at`) VALUES
(1, 'System Administrator', 'admin@librarysystem.com', '', '$2y$10$rghsKU.sGka5RE.BT9635uXyOqRW3TJ84w0WRRlKzcoKsT6YvGTnW', NULL, '', '', '', '', '', 'profile_1_1774197573.jpg', 'member', 'active', 'ADM-001', '2026-03-22 12:24:17', NULL, 0.00, 1, NULL, '2026-04-10 07:26:47', '2026-03-22 12:24:17', '2026-04-10 07:26:47', NULL, NULL, NULL),
(2, 'Rod Christian Mojado', 'rohan.14yahoo@gmail.com', '555-0001', '$2y$10$F0DHQXseb5jI8tDVw6CsfuUwzVENazWVklw48pMPkhZtKJDL8pJR.', NULL, '', '', '', '', '', 'profile_2_1774197550.jpg', 'admin', 'active', 'MEM1773923430', '2026-03-22 12:24:17', NULL, 0.00, 1, NULL, '2026-04-18 13:20:23', '2026-03-22 12:24:17', '2026-04-18 13:20:23', NULL, NULL, NULL),
(12, 'Krishna Paul Quisora', 'hentailover1959@gmail.com', '', '$2y$10$O8RkLOplVFTNyN6VkScvtuWhLmje25K/qGyfeOGw6emEDUCaEYG46', '0000-00-00', '', '', '', '', '', NULL, 'member', 'active', 'MEM1776514525', '2026-04-18 12:15:25', NULL, 0.00, 1, 'c10d7a2a0b2f758bdeb0d39cd7c596cf', '2026-04-18 14:11:22', '2026-04-18 12:15:25', '2026-04-18 14:11:22', NULL, 'cd379f09889388854937449f47d997d71f7d946662015c66dba71ec3960010ce', '2026-04-18 12:16:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_author` (`author`),
  ADD KEY `idx_isbn` (`isbn`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_email_time` (`email`,`attempt_time`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempt_time`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD UNIQUE KEY `unique_subcat` (`category_id`,`subcategory_name`);

--
-- Indexes for table `two_factor_attempts`
--
ALTER TABLE `two_factor_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_time` (`user_id`,`attempt_time`);

--
-- Indexes for table `two_factor_auth`
--
ALTER TABLE `two_factor_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `membership_number` (`membership_number`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `two_factor_attempts`
--
ALTER TABLE `two_factor_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `two_factor_auth`
--
ALTER TABLE `two_factor_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `two_factor_attempts`
--
ALTER TABLE `two_factor_attempts`
  ADD CONSTRAINT `two_factor_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `two_factor_auth`
--
ALTER TABLE `two_factor_auth`
  ADD CONSTRAINT `two_factor_auth_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
