-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Oct 12, 2026 at 04:41 PM
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
-- Database: `imarika_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `failed_attempts`, `locked_until`, `reset_token`, `reset_expires`, `created_at`) VALUES
(1, 'admin', 'richard2002kikoti@gmail.com', '$2y$10$GghevmHXfZtZok2bO9.KFeQgWZFdybDu1yzRWjb.UWP2LHJrrkhdq', 0, NULL, NULL, NULL, '2026-10-04 21:50:38');

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` int(11) NOT NULL,
  `student_name` varchar(200) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Female','Male') NOT NULL,
  `level` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `guardian_name` varchar(200) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  `birth_certificate_path` varchar(255) NOT NULL,
  `student_photo_path` varchar(255) NOT NULL,
  `status` enum('Pending','Reviewed','Accepted','Rejected') NOT NULL DEFAULT 'Pending',
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `student_name`, `dob`, `gender`, `level`, `address`, `guardian_name`, `relationship`, `phone`, `email`, `birth_certificate_path`, `student_photo_path`, `status`, `submitted_at`) VALUES
(3, 'VUMILIA CHANGALA', '2023-09-13', 'Female', 'Standard 1', 'P. O BOX 2324\r\nKILOLO', 'VUMILIA CHANGALA', 'Mother', '+255620823520', 'richard2002kikoti@gmail.com', 'includes/uploads/admissions/birth_certificate_20261012_162950_dbbbd337.pdf', 'includes/uploads/admissions/student_photo_20261012_162950_158b9e8e.jpg', 'Pending', '2026-10-12 17:29:50');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` enum('Announcements','Events','Sports','Academic Results') NOT NULL,
  `snippet` varchar(300) NOT NULL,
  `content` text DEFAULT NULL,
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `category`, `snippet`, `content`, `pinned`, `created_at`) VALUES
(3, 'MICHEZO', 'Sports', 'Kutakuwa na tamasha la michezo kwa vijana', 'wazazi pia mnakaribishwa', 0, '2026-10-09 17:00:51');

-- --------------------------------------------------------

--
-- Table structure for table `site_stats`
--

CREATE TABLE `site_stats` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `total_views` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_stats`
--

INSERT INTO `site_stats` (`id`, `total_views`) VALUES
(1, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_stats`
--
ALTER TABLE `site_stats`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
