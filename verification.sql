-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2025 at 06:39 PM
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
-- Database: `verification`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `role` varchar(50) NOT NULL DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`, `last_login`, `created_at`, `is_active`, `role`) VALUES
(1, 'bilmid', '$2y$10$2tGRc0dx2JaNWvwSMEQj0uJw..Yg3/9HVtBj3G3z.d2SZZ32lITge', 'Ahmad Abdulhamid', '2025-08-16 19:34:35', '2025-08-15 17:54:17', 1, 'Admin'),
(2, 'Ahmad', '$2y$10$zxhFIhCDH6pufs8IJgWA2.H2QGXkfbz07mrV3PYIBMyuPqBinmhpi', 'Sani Abubakar', '2025-08-17 17:25:00', '2025-08-16 14:50:12', 1, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_ips`
--

CREATE TABLE `blocked_ips` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `reason` text NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `blocked_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `digital_signature` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `is_revoked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `institution_id`, `certificate_number`, `student_name`, `course_name`, `issue_date`, `expiry_date`, `qr_code`, `digital_signature`, `file_path`, `is_revoked`, `created_at`) VALUES
(1, 1, 'CERT-0001-4PMFCRX3', 'Ahmad Abdulhamid', 'Computer Science', '2025-02-02', NULL, NULL, NULL, '../uploads/certificates/CERT-0001-4PMFCRX3_smartserveLLL.jpg', 0, '2025-08-16 12:24:03'),
(2, 1, 'CERT-0001-86N5O9A1', 'Ibrahim Yakubu', 'Software Engineering', '2025-05-15', NULL, NULL, NULL, NULL, 0, '2025-08-16 19:20:25'),
(3, 1, 'CERT-0001-D8EAA0A5', 'Bilmid Ahmad', 'Data Analysis', '2025-05-28', NULL, 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=http%3A%2F%2Flocalhost%2Fcertificate-verification-system%2Fpublic%2Fverify.php%3Fcertificate%3DCERT-0001-D8EAA0A5', NULL, NULL, 0, '2025-08-17 09:28:38');

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

CREATE TABLE `institutions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institutions`
--

INSERT INTO `institutions` (`id`, `name`, `email`, `password`, `address`, `phone`, `api_key`, `is_active`, `created_at`, `last_login`) VALUES
(1, 'Nasarawa State University, Keffi', 'nsuk@gmail.com', '$2y$10$60yJAYwlfi80Ug8bbmv7m.GoaQto/ye1oFl74bmqxBJb58vg9.4Dq', 'Keffi', '08069715695', '700cbf1467518838b03301aa7f91808e', 1, '2025-08-15 18:18:36', '2025-08-16 18:16:51'),
(2, 'Federal University, Lafia', 'fulafia@gmail.com', '$2y$10$Apdj70mp17HqRSerF.FS3ue1b6BKhWrcKh.R0TFXKnsi4CPy0pmVG', 'Lafia, Nasarawa State', '08067077742', '2b5b0070d219180d20933366a740f6de', 1, '2025-08-16 16:35:15', '2025-08-16 18:11:00'),
(3, 'Nasarawa State College of Health', 'nchstk@gmail.com', '$2y$10$8MZ8abAXHxK/ZPVG4lJxzeojmY8A/jBG73gy.zHTfhQQQfN6uYFWa', 'Keffi, Nasarawa State', '08167077742', '6661cbd0f108691a1447aaaf7f5825fe', 1, '2025-08-16 18:55:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `display_name` varchar(100) DEFAULT NULL,
  `setting_type` enum('text','textarea','number','email','password','select','checkbox','radio') DEFAULT 'text',
  `options` text DEFAULT NULL COMMENT 'JSON options for select, radio, etc.',
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `display_name`, `setting_type`, `options`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Certificate Verification', 'general', 'Site Name', 'text', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:58:28'),
(2, 'site_email', 'admin@certverify.com', 'general', 'Site Email', 'email', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34'),
(3, 'timezone', 'UTC', 'general', 'Timezone', 'select', '[\"UTC\", \"Africa/Lagos\", \"America/New_York\", \"Europe/London\"]', 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34'),
(4, 'maintenance_mode', '0', 'general', 'Maintenance Mode', 'checkbox', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34'),
(5, 'verification_expiry', '365', 'verification', 'Verification Expiry (days)', 'number', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34'),
(6, 'allow_public_verification', '1', 'verification', 'Allow Public Verification', 'checkbox', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34'),
(7, 'smtp_host', '', 'email', 'SMTP Host', 'text', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34'),
(8, 'smtp_port', '587', 'email', 'SMTP Port', 'number', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34'),
(9, 'smtp_username', 'Ahmad', 'email', 'SMTP Username', 'text', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:58:28'),
(10, 'smtp_password', '123456', 'email', 'SMTP Password', 'password', NULL, 0, '2025-08-16 19:51:34', '2025-08-16 19:58:28'),
(11, 'smtp_encryption', 'tls', 'email', 'SMTP Encryption', 'select', '[\"none\", \"ssl\", \"tls\"]', 0, '2025-08-16 19:51:34', '2025-08-16 19:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` int(11) NOT NULL,
  `certificate_id` int(11) NOT NULL,
  `verifier_ip` varchar(50) DEFAULT NULL,
  `verifier_details` text DEFAULT NULL,
  `verification_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('valid','invalid','revoked') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verifications`
--

INSERT INTO `verifications` (`id`, `certificate_id`, `verifier_ip`, `verifier_details`, `verification_date`, `status`) VALUES
(1, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:15:16', 'valid'),
(2, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:15:29', 'valid'),
(3, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:16:05', 'valid'),
(4, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:18:17', 'valid'),
(5, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:18:26', 'valid'),
(6, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:18:30', 'valid'),
(7, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:21:15', 'valid'),
(8, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:23:05', 'valid'),
(9, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:23:20', 'valid'),
(10, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:23:32', 'valid'),
(11, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:24:43', 'valid'),
(12, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:24:52', 'valid'),
(13, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:25:00', 'valid'),
(14, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:25:19', 'valid'),
(15, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:25:40', 'valid'),
(16, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:27:32', 'valid'),
(17, 1, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/admin\\/manage-certificates.php\"}', '2025-08-16 19:28:17', 'valid'),
(18, 2, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/institution\\/certificate-list.php\"}', '2025-08-17 09:27:50', 'valid'),
(19, 3, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/institution\\/certificate-list.php\"}', '2025-08-17 09:28:51', 'valid'),
(20, 2, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/institution\\/verify-certificate.php\"}', '2025-08-17 09:30:41', 'valid'),
(21, 3, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/public\\/search.php?search=CERT-0001-D8EAA0A5\"}', '2025-08-17 09:34:16', 'valid'),
(22, 3, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/public\\/search.php?search=CERT-0001-D8EAA0A5\"}', '2025-08-17 09:34:53', 'valid'),
(23, 3, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/public\\/verify.php\"}', '2025-08-17 09:35:11', 'valid'),
(24, 2, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/public\\/verify.php\"}', '2025-08-17 09:40:44', 'valid'),
(25, 2, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/public\\/verify.php\"}', '2025-08-17 09:47:42', 'valid'),
(26, 3, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/public\\/verify.php\"}', '2025-08-17 16:09:48', 'valid'),
(27, 3, '::1', '{\"HTTP_USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/109.0.0.0 Safari\\/537.36\",\"HTTP_REFERER\":\"http:\\/\\/localhost\\/certificate-verification-system\\/public\\/search.php?search=CERT-0001-D8EAA0A5\"}', '2025-08-17 16:23:43', 'valid');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_address` (`ip_address`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `institution_id` (`institution_id`);

--
-- Indexes for table `institutions`
--
ALTER TABLE `institutions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `api_key` (`api_key`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `certificate_id` (`certificate_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `institutions`
--
ALTER TABLE `institutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`);

--
-- Constraints for table `verifications`
--
ALTER TABLE `verifications`
  ADD CONSTRAINT `verifications_ibfk_1` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
