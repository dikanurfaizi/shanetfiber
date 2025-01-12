-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2025 at 06:56 AM
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
-- Database: `shanetfiber_mgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `auto_isolirs`
--

CREATE TABLE `auto_isolirs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `mikrotik_id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` varchar(255) NOT NULL,
  `script_id` varchar(255) NOT NULL,
  `schedule_id` varchar(255) NOT NULL,
  `address_list_isolir` varchar(255) DEFAULT NULL,
  `activation_date` enum('false','true') NOT NULL DEFAULT 'false',
  `due_date` varchar(255) DEFAULT NULL,
  `comment_unpayment` varchar(255) NOT NULL,
  `comment_payment` varchar(255) NOT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `nat_id` varchar(255) DEFAULT NULL,
  `nat_dst_address` varchar(255) DEFAULT NULL,
  `nat_src_address_list` varchar(255) DEFAULT NULL,
  `nat_dst_address_list` varchar(255) DEFAULT NULL,
  `proxy_access_id` varchar(255) DEFAULT NULL,
  `proxy_access_src_address` varchar(255) DEFAULT NULL,
  `proxy_access_action_data` varchar(255) DEFAULT NULL,
  `proxy_access_action` enum('redirect','deny','allow') NOT NULL DEFAULT 'deny',
  `firewall_filter_id` varchar(255) NOT NULL DEFAULT '0',
  `address_list_non_isolir` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auto_isolirs`
--

INSERT INTO `auto_isolirs` (`id`, `name`, `mikrotik_id`, `profile_id`, `script_id`, `schedule_id`, `address_list_isolir`, `activation_date`, `due_date`, `comment_unpayment`, `comment_payment`, `disabled`, `created_at`, `updated_at`, `deleted_at`, `nat_id`, `nat_dst_address`, `nat_src_address_list`, `nat_dst_address_list`, `proxy_access_id`, `proxy_access_src_address`, `proxy_access_action_data`, `proxy_access_action`, `firewall_filter_id`, `address_list_non_isolir`) VALUES
(2, 'Notif_Pembayaran', 1, 'Expire', '*C60', '*262', NULL, 'false', '16', 'belum_lunas', 'lunas', 'false', '2024-09-29 11:45:50', '2024-10-27 07:39:16', NULL, '*3645', '172.10.10.2', 'Isolir-List', NULL, '*4', '25.25.25.0/24', '172.10.10.2/isolir', 'redirect', '*1252', NULL),
(3, 'Notification_Payment', 18, 'Isolir', '*2BE', '*13', NULL, 'false', '11', 'belum_lunas', 'lunas', 'false', '2024-10-27 16:23:14', '2024-10-27 16:23:14', NULL, '0', NULL, NULL, NULL, '0', NULL, NULL, 'deny', '0', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `bank` varchar(255) NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `slug`, `name`, `bank`, `account_number`, `disabled`, `created_at`, `updated_at`) VALUES
(1, 'bank-central-asia-zvpa', 'Dian Ardiansyah', 'Bank Central Asia', '2990407315', 'false', '2024-09-30 06:01:50', '2024-09-30 06:01:58'),
(2, 'bank-mandiri-9mq4', 'Dian Ardiansyah', 'Bank Mandiri', '1730012695905', 'false', '2024-09-30 06:03:29', '2024-09-30 06:03:33'),
(3, 'bca-mjdd', 'PT. Global Sarana Elektronika', 'Bank Central Asia', '2317111157', 'false', '2024-12-02 23:46:43', '2024-12-02 23:47:23'),
(4, 'bank-bri-3nwj', 'PT. Global Sarana Elektronika', 'Bank Rakyat Indonesia', '007501001957304', 'true', '2024-12-02 23:48:02', '2024-12-02 23:56:15');

-- --------------------------------------------------------

--
-- Table structure for table `billings`
--

CREATE TABLE `billings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `billing_periode_id` bigint(20) UNSIGNED NOT NULL,
  `billing_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `paket_name` varchar(255) NOT NULL,
  `paket_price` int(11) NOT NULL,
  `created_name` varchar(255) NOT NULL,
  `status` enum('BL','LS','PL') NOT NULL DEFAULT 'BL',
  `teller_name` varchar(255) DEFAULT NULL,
  `payment_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tax` int(11) DEFAULT NULL,
  `pay_later` timestamp NULL DEFAULT NULL,
  `receipt_id` int(11) DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billings`
--

INSERT INTO `billings` (`id`, `slug`, `billing_periode_id`, `billing_number`, `customer_id`, `customer_name`, `paket_name`, `paket_price`, `created_name`, `status`, `teller_name`, `payment_time`, `created_at`, `updated_at`, `tax`, `pay_later`, `receipt_id`, `due_date`) VALUES
(1, '1-qtcg', 1, '1-mrgi-10-2024', 1, '100210121@subhan', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-03 15:23:27', '2024-09-30 22:58:49', '2024-10-03 15:23:27', 0, NULL, 15, '2024-10-29 17:00:00'),
(2, '2-j742', 1, '2-qkrr-10-2024', 2, '100210212@iissugiarti', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 14:17:08', '2024-09-30 22:58:49', '2024-10-13 14:17:08', 0, NULL, 58, '2024-10-29 17:00:00'),
(3, '3-a20n', 1, '3-l4m8-10-2024', 3, '100210220@niaagustin', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-01 00:52:04', '2024-09-30 22:58:49', '2024-10-01 00:52:04', 0, NULL, 4, '2024-10-29 17:00:00'),
(4, '4-tqly', 1, '4-71zw-10-2024', 4, '150210411@surya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Admin', '2024-09-30 23:03:27', '2024-09-30 22:58:49', '2024-09-30 23:03:27', 0, NULL, 3, '2024-10-29 17:00:00'),
(5, '5-e0ho', 1, '5-dlov-10-2024', 5, '150210712@koesnadi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 12:14:09', '2024-09-30 22:58:49', '2024-10-06 12:14:09', 0, NULL, 22, '2024-10-29 17:00:00'),
(6, '6-5wd4', 1, '6-yynv-10-2024', 6, '150211002@cucu', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-01 10:53:22', '2024-09-30 22:58:49', '2024-10-01 10:53:22', 0, NULL, 7, '2024-10-29 17:00:00'),
(7, '7-hrq7', 1, '7-izge-10-2024', 7, '100220430@dinda', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 03:47:42', '2024-09-30 22:58:49', '2024-10-14 03:47:42', 0, NULL, 61, '2024-10-29 17:00:00'),
(8, '8-zffk', 1, '8-hcka-10-2024', 8, '100220918@darsih', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Admin ShaNet Fiber', '2024-10-28 00:05:32', '2024-09-30 22:58:49', '2024-10-28 00:05:32', 0, '2024-10-25 17:00:00', 94, '2024-10-29 17:00:00'),
(9, '9-vpav', 1, '9-fezd-10-2024', 9, '100221009@eko', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 04:15:50', '2024-09-30 22:58:49', '2024-10-13 04:15:50', 0, NULL, 56, '2024-10-29 17:00:00'),
(10, '10-nmsn', 1, '10-sv7p-10-2024', 10, '100221020@ustdali', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 04:46:11', '2024-09-30 22:58:49', '2024-10-15 04:46:11', 0, NULL, 68, '2024-10-29 17:00:00'),
(11, '11-tg3c', 1, '11-nuca-10-2024', 11, '100221029@rita', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 04:24:27', '2024-09-30 22:58:49', '2024-10-16 04:24:27', 0, NULL, 82, '2024-10-29 17:00:00'),
(12, '12-rjwc', 1, '12-fo6j-10-2024', 12, '150240118@suud', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-18 05:48:15', '2024-09-30 22:58:49', '2024-10-18 05:48:15', 0, NULL, 87, '2024-10-29 17:00:00'),
(13, '13-ce0x', 1, '13-cpsx-10-2024', 13, '100221107@anggi', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 06:54:50', '2024-09-30 22:58:49', '2024-10-07 06:54:50', 0, NULL, 31, '2024-10-29 17:00:00'),
(14, '14-kclc', 1, '14-l6j9-10-2024', 14, '150221120@adhiga', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 10:22:40', '2024-09-30 22:58:49', '2024-10-16 10:22:40', 0, '2024-10-19 17:00:00', 85, '2024-10-29 17:00:00'),
(15, '15-xura', 1, '15-pfum-10-2024', 15, '200211114@armiyanti', 'Paket 2 (15 Mbps)', 200000, 'Admin', 'LS', 'Admin', '2024-09-30 23:00:03', '2024-09-30 22:58:49', '2024-09-30 23:00:03', 0, NULL, 1, '2024-10-29 17:00:00'),
(16, '16-iflw', 1, '16-zqoq-10-2024', 16, '100230102@rdcell', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 08:47:21', '2024-09-30 22:58:49', '2024-10-16 08:47:21', 0, NULL, 84, '2024-10-29 17:00:00'),
(18, '18-zc5k', 1, '18-rzgx-10-2024', 18, '150230402@iip', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 23:51:08', '2024-09-30 22:58:49', '2024-10-02 23:51:08', 0, NULL, 14, '2024-10-29 17:00:00'),
(19, '19-akuh', 1, '19-11cq-10-2024', 19, '150230408@ahmadraja', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-11 11:58:11', '2024-09-30 22:58:49', '2024-10-11 11:58:11', 0, NULL, 54, '2024-10-29 17:00:00'),
(20, '20-jhup', 1, '20-nisa-10-2024', 20, '150230503@lamjaya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 00:38:44', '2024-09-30 22:58:49', '2024-10-06 00:38:44', 0, NULL, 19, '2024-10-29 17:00:00'),
(21, '21-pu4z', 1, '21-ttf1-10-2024', 21, '150230717@ibuayu', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 03:18:08', '2024-09-30 22:58:49', '2024-10-15 03:18:08', 0, NULL, 67, '2024-10-29 17:00:00'),
(22, '22-pdup', 1, '22-m7in-10-2024', 22, '100230729@jojo', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 07:59:57', '2024-09-30 22:58:49', '2024-10-07 07:59:57', 0, NULL, 33, '2024-10-29 17:00:00'),
(23, '23-5jkg', 1, '23-m8v1-10-2024', 23, '150230804@rafka', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Admin', '2024-09-30 23:03:12', '2024-09-30 22:58:49', '2024-09-30 23:03:12', 0, NULL, 2, '2024-10-29 17:00:00'),
(24, '24-ci2r', 1, '24-xol2-10-2024', 24, '150231101@neni', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 00:36:33', '2024-09-30 22:58:49', '2024-10-15 00:36:33', 0, NULL, 65, '2024-10-29 17:00:00'),
(25, '25-9wf7', 1, '25-ceiz-10-2024', 25, '150231115@heriyanto', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 08:02:43', '2024-09-30 22:58:49', '2024-10-15 08:02:43', 0, NULL, 70, '2024-10-29 17:00:00'),
(26, '26-czlm', 1, '26-iiw3-10-2024', 26, '075230101@meisya', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 02:33:08', '2024-09-30 22:58:49', '2024-10-13 02:33:08', 0, NULL, 55, '2024-10-29 17:00:00'),
(27, '27-4aq2', 1, '27-brvn-10-2024', 27, '100231205@novi', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 14:13:15', '2024-09-30 22:58:50', '2024-10-02 14:13:15', 0, NULL, 10, '2024-10-29 17:00:00'),
(28, '28-731i', 1, '28-cgg8-10-2024', 28, '100231210@hasma', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 22:25:33', '2024-09-30 22:58:50', '2024-10-14 22:25:33', 0, NULL, 63, '2024-10-29 17:00:00'),
(29, '29-z5et', 1, '29-upjm-10-2024', 29, '150231216@euis', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 14:13:05', '2024-09-30 22:58:50', '2024-10-02 14:13:05', 0, NULL, 9, '2024-10-29 17:00:00'),
(30, '30-ygl8', 1, '30-3yvw-10-2024', 30, '100231217@amoy', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 12:54:42', '2024-09-30 22:58:50', '2024-10-15 12:54:42', 0, NULL, 75, '2024-10-29 17:00:00'),
(31, '31-rxu0', 1, '31-dqf3-10-2024', 31, '150240211@pakzaky', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-08 05:35:58', '2024-09-30 22:58:50', '2024-10-08 05:35:58', 0, NULL, 39, '2024-10-29 17:00:00'),
(32, '32-vyc5', 1, '32-8mvw-10-2024', 32, '100240219@tehai', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 11:32:47', '2024-09-30 22:58:50', '2024-10-15 11:32:47', 0, NULL, 73, '2024-10-29 17:00:00'),
(33, '33-wt0q', 1, '33-tr6c-10-2024', 33, '150240311@slamet', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 02:37:49', '2024-09-30 22:58:50', '2024-10-14 02:37:49', 0, NULL, 60, '2024-10-29 17:00:00'),
(34, '34-omj3', 1, '34-ej6y-10-2024', 34, '170231220@iburuli', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-11 03:24:26', '2024-09-30 22:58:50', '2024-10-11 03:24:26', 0, NULL, 50, '2024-10-29 17:00:00'),
(35, '35-om5x', 1, '35-10nm-10-2024', 35, '100221019@hafis', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 14:13:53', '2024-09-30 22:58:50', '2024-10-02 14:13:53', 0, NULL, 11, '2024-10-29 17:00:00'),
(36, '36-tslu', 1, '36-gqol-10-2024', 36, '200240417@yanti', 'Paket 2 (15 Mbps)', 200000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 23:16:59', '2024-09-30 22:58:50', '2024-10-13 23:16:59', 0, NULL, 59, '2024-10-29 17:00:00'),
(37, '38-afqj', 1, '38-mfwl-10-2024', 38, '150240125@warungbali', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 22:57:44', '2024-09-30 22:58:50', '2024-10-14 22:57:44', 0, NULL, 64, '2024-10-29 17:00:00'),
(38, '39-odak', 1, '39-xekf-10-2024', 39, '100210120@hexsa', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-08 04:20:01', '2024-09-30 22:58:50', '2024-10-08 04:20:01', 0, NULL, 37, '2024-10-29 17:00:00'),
(39, '40-tnwv', 1, '40-erql-10-2024', 40, '100240501@siti', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 02:37:35', '2024-09-30 22:58:50', '2024-10-15 02:37:35', 0, NULL, 66, '2024-10-29 17:00:00'),
(40, '41-ikam', 1, '41-xfht-10-2024', 41, '100231112@wandi', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 00:17:31', '2024-09-30 22:58:50', '2024-10-16 00:17:31', 0, NULL, 78, '2024-10-29 17:00:00'),
(41, '42-lyzo', 1, '42-lhkm-10-2024', 42, '100240503@rizal', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 11:17:53', '2024-09-30 22:58:50', '2024-10-13 11:17:53', 0, NULL, 57, '2024-10-29 17:00:00'),
(42, '43-pwwu', 1, '43-1agz-10-2024', 43, '150240510@pakedi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 08:33:09', '2024-09-30 22:58:50', '2024-10-16 08:33:09', 0, NULL, 83, '2024-10-29 17:00:00'),
(43, '44-cl5s', 1, '44-fqr3-10-2024', 44, '150221217@furqon', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 08:23:23', '2024-09-30 22:58:50', '2024-10-07 08:23:23', 0, NULL, 36, '2024-10-29 17:00:00'),
(44, '45-ydmk', 1, '45-mbmp-10-2024', 45, '150221106@aam', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-09 10:34:17', '2024-09-30 22:58:50', '2024-10-09 10:34:17', 0, NULL, 44, '2024-10-29 17:00:00'),
(45, '46-dxdt', 1, '46-jdks-10-2024', 46, '150210312@sikefi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 05:35:57', '2024-09-30 22:58:50', '2024-10-07 05:35:57', 0, NULL, 26, '2024-10-29 17:00:00'),
(46, '47-hivz', 1, '47-p4em-10-2024', 47, '150231029@mulya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-11-26 23:39:21', '2024-09-30 22:58:50', '2024-11-26 23:39:21', 0, '2024-10-19 17:00:00', 235, '2024-10-29 17:00:00'),
(47, '48-9htp', 1, '48-bz2d-10-2024', 48, '150240710@sulastri', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 12:29:23', '2024-09-30 22:58:50', '2024-10-06 12:29:23', 0, NULL, 23, '2024-10-29 17:00:00'),
(48, '49-ucyx', 1, '49-iu4n-10-2024', 49, '150240621@miayamgeprek', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 11:32:29', '2024-09-30 22:58:50', '2024-10-15 11:32:29', 0, NULL, 72, '2024-10-29 17:00:00'),
(49, '50-tgyz', 1, '50-hgqu-10-2024', 50, '150230725@ustdfery', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-01 10:51:28', '2024-09-30 22:58:50', '2024-10-01 10:51:28', 0, NULL, 6, '2024-10-29 17:00:00'),
(50, '51-jg1t', 1, '51-d8xq-10-2024', 51, '150240711@tehenok', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-11 06:05:12', '2024-09-30 22:58:50', '2024-10-11 06:05:12', 0, NULL, 51, '2024-10-29 17:00:00'),
(51, '52-coep', 1, '52-ezsd-10-2024', 52, '150240725@ahmad', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-25 09:05:01', '2024-09-30 22:58:50', '2024-10-25 09:05:01', 0, NULL, 92, '2024-10-29 17:00:00'),
(52, '53-kx3r', 1, '53-o8wp-10-2024', 53, 'Marie Maharani Widyaningrum', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-11 10:51:27', '2024-09-30 22:58:50', '2024-10-11 10:51:27', 0, NULL, 53, '2024-10-12 17:00:00'),
(53, '54-5enx', 1, '54-mxv3-10-2024', 54, '120240310@linda', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 12:53:47', '2024-09-30 22:58:50', '2024-10-15 12:53:47', 0, NULL, 74, '2024-10-29 17:00:00'),
(54, '55-d3gz', 1, '55-eg1k-10-2024', 55, '100240225@agis', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 13:02:44', '2024-09-30 22:58:50', '2024-10-10 13:02:44', 0, NULL, 48, '2024-10-29 17:00:00'),
(55, '56-ggr6', 1, '56-fw6k-10-2024', 56, '150240807@nayunda', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-05 02:28:46', '2024-09-30 22:58:50', '2024-10-05 02:28:46', 0, NULL, 17, '2024-10-29 17:00:00'),
(56, '57-jxct', 1, '57-uhdj-10-2024', 57, '200230910@iyan', 'Paket 2 (15 Mbps)', 200000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-08 05:26:50', '2024-09-30 22:58:50', '2024-10-08 05:26:50', 0, NULL, 38, '2024-10-29 17:00:00'),
(57, '58-cbdt', 1, '58-aqr3-10-2024', 58, '150240811@nurfakriah', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 12:20:00', '2024-09-30 22:58:50', '2024-10-14 12:20:00', 0, NULL, 62, '2024-10-29 17:00:00'),
(59, '60-el5z', 1, '60-zokf-10-2024', 60, '150240612@jepri', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 22:26:21', '2024-09-30 22:58:50', '2024-10-15 22:26:21', 0, NULL, 76, '2024-10-29 17:00:00'),
(60, '61-pcpn', 1, '61-9plz-10-2024', 61, '150240817@ibuani', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 12:07:03', '2024-09-30 22:58:50', '2024-10-06 12:07:03', 0, NULL, 20, '2024-10-29 17:00:00'),
(61, '62-zalm', 1, '62-nxjg-10-2024', 62, '250230601@abdulrahman', 'Paket 3 (20 Mbps)', 250000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-05 23:01:07', '2024-09-30 22:58:50', '2024-10-05 23:01:07', 0, NULL, 18, '2024-10-29 17:00:00'),
(62, '63-qpbs', 1, '63-78f3-10-2024', 63, '150240824@dendi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 16:00:34', '2024-09-30 22:58:50', '2024-10-10 16:00:34', 0, NULL, 49, '2024-10-29 17:00:00'),
(63, '64-ckry', 1, '64-mv5g-10-2024', 64, '100240901@bunehe', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-11 10:51:08', '2024-09-30 22:58:50', '2024-10-11 10:51:08', 0, NULL, 52, '2024-10-29 17:00:00'),
(64, '65-l5du', 1, '65-psah-10-2024', 65, '150240901@pakkurdi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 00:37:57', '2024-09-30 22:58:51', '2024-10-16 00:37:57', 0, NULL, 80, '2024-10-29 17:00:00'),
(65, '66-9rk2', 1, '66-o7dx-10-2024', 66, '150240305@goradhigha', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 01:42:41', '2024-09-30 22:58:51', '2024-10-10 01:42:41', 0, NULL, 46, '2024-10-29 17:00:00'),
(66, '67-jlue', 1, '67-eice-10-2024', 67, 'Lulu Andriani', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-09 02:45:06', '2024-09-30 22:58:51', '2024-10-09 02:45:06', 0, NULL, 42, '2024-10-07 17:00:00'),
(67, '68-dpyo', 1, '68-10jr-10-2024', 68, 'Ma Aisah', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-09 02:50:05', '2024-09-30 22:58:51', '2024-10-09 02:50:05', 0, NULL, 43, '2024-10-07 17:00:00'),
(68, '69-oob6', 1, '69-bs6v-10-2024', 69, '150240908@alhikam', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-01 22:35:59', '2024-09-30 22:58:51', '2024-10-01 22:35:59', 0, NULL, 8, '2024-10-29 17:00:00'),
(69, '70-ysxb', 1, '70-nzgg-10-2024', 70, '150240912@pakkurdi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 00:37:52', '2024-09-30 22:58:51', '2024-10-16 00:37:52', 0, NULL, 79, '2024-10-29 17:00:00'),
(70, '71-rpnq', 1, '71-lhrc-10-2024', 71, '100240917@enen', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 13:02:32', '2024-09-30 22:58:51', '2024-10-10 13:02:32', 0, NULL, 47, '2024-10-29 17:00:00'),
(71, '72-xq6x', 1, '72-r558-10-2024', 72, '100240715@bmcell', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-16 01:11:52', '2024-09-30 22:58:51', '2024-10-16 01:11:52', 0, NULL, 81, '2024-10-29 17:00:00'),
(72, '73-d29v', 1, '73-cxuj-10-2024', 73, 'Asep Topik Hidayat', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-08 09:36:03', '2024-09-30 22:58:51', '2024-10-08 09:36:03', 0, NULL, 41, '2024-10-21 17:00:00'),
(73, '74-9qb9', 1, '74-cvve-10-2024', 74, 'Egy Laksma Suandi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 14:14:59', '2024-09-30 22:58:51', '2024-10-02 14:14:59', 0, NULL, 13, '2024-10-23 17:00:00'),
(75, '77-drev', 1, '77-ymlm-10-2024', 77, 'Nazri Nasier', 'Paket 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-04 05:21:33', '2024-10-04 05:20:17', '2024-10-04 05:21:33', NULL, NULL, 16, NULL),
(76, '78-aazb', 1, '78-sil8-10-2024', 78, 'Moh. Gian Firtyanza', 'Paket 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-09 13:04:59', '2024-10-09 13:04:42', '2024-10-09 13:04:59', NULL, NULL, 45, NULL),
(77, '79-g824', 1, '79-znlv-10-2024', 79, 'Intan Ayu Sri Wahyuni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-17 07:45:35', '2024-10-17 05:25:35', '2024-10-17 07:45:35', NULL, NULL, 86, NULL),
(78, '80-eq9i', 1, '80-ccub-10-2024', 80, 'Nurhayati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-20 12:34:23', '2024-10-20 12:25:51', '2024-10-20 12:34:23', NULL, NULL, 90, NULL),
(79, '81-ubun', 1, '81-e7x4-10-2024', 81, 'Irvan Mohammad Regent', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-27 03:01:54', '2024-10-27 02:52:51', '2024-10-27 03:01:54', NULL, NULL, 93, NULL),
(80, '137-t16k', 1, '137-yz1p-10-2024', 137, 'Fera', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-30 01:18:39', '2024-10-30 01:12:30', '2024-10-30 01:18:39', NULL, NULL, 95, NULL),
(81, '138-qqjz', 1, '138-yqkj-10-2024', 138, 'Robi Anggara', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-30 03:21:53', '2024-10-30 03:14:39', '2024-10-30 03:21:53', NULL, NULL, 96, NULL),
(200, '1-hahb', 3, '1-mbm0-11-2024', 1, 'Muh Subhan', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-05 18:03:10', '2024-11-01 19:13:57', '2024-11-05 18:03:10', 0, NULL, 149, '2024-11-20 10:00:00'),
(201, '2-ydhf', 3, '2-c07u-11-2024', 2, 'Iis Sugiarti', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-15 20:48:03', '2024-11-01 19:13:57', '2024-11-15 20:48:03', 0, NULL, 221, '2024-11-11 10:00:00'),
(202, '3-k5hw', 3, '3-r0xw-11-2024', 3, 'Asep Nasihin', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-02 20:17:39', '2024-11-01 19:13:58', '2024-11-02 20:17:39', 0, NULL, 112, '2024-11-19 10:00:00'),
(203, '4-ymng', 3, '4-hcod-11-2024', 4, 'Pak Surya', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 19:33:36', '2024-11-01 19:13:59', '2024-11-01 19:33:36', 0, NULL, 104, '2024-11-10 10:00:00'),
(204, '5-fivv', 3, '5-olvh-11-2024', 5, 'Koesnadi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-04 19:34:09', '2024-11-01 19:13:59', '2024-11-04 19:34:09', 0, NULL, 128, '2024-11-11 10:00:00'),
(205, '6-nwtz', 3, '6-gdel-11-2024', 6, 'Cucu Suryani', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 19:28:46', '2024-11-01 19:14:00', '2024-11-01 19:28:46', 0, NULL, 102, '2024-11-01 10:00:00'),
(206, '7-qbpg', 3, '7-2lhx-11-2024', 7, 'Sumarni', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-08 18:42:10', '2024-11-01 19:14:00', '2024-11-08 18:42:10', 0, NULL, 181, '2024-11-29 10:00:00'),
(207, '8-00ey', 3, '8-hdun-11-2024', 8, 'Teh Darsih', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-20 16:23:10', '2024-11-01 19:14:01', '2024-11-20 16:23:10', 0, '2024-11-19 10:00:00', 231, '2024-11-17 10:00:00'),
(208, '9-5z4m', 3, '9-rvks-11-2024', 9, 'Pak Eko', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 17:23:52', '2024-11-01 19:14:01', '2024-11-15 17:23:52', 0, NULL, 217, '2024-11-08 10:00:00'),
(209, '10-whsd', 3, '10-lyzh-11-2024', 10, 'Ali Akbar', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 01:44:41', '2024-11-01 19:14:02', '2024-11-15 01:44:41', 0, NULL, 213, '2024-11-19 10:00:00'),
(210, '11-dwvj', 3, '11-nnva-11-2024', 11, 'Rita Susanti', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-06 03:00:22', '2024-11-01 19:14:03', '2024-11-06 03:00:22', 0, NULL, 154, '2024-11-28 10:00:00'),
(211, '12-q2z0', 3, '12-c19f-11-2024', 12, 'Pak Suud', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-16 05:52:44', '2024-11-01 19:14:03', '2024-11-16 05:52:44', 0, NULL, 225, '2024-11-17 10:00:00'),
(212, '13-lgpg', 3, '13-va2n-11-2024', 13, 'Mamah Anggi', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-03 06:37:04', '2024-11-01 19:14:04', '2024-11-03 06:37:04', 0, NULL, 118, '2024-11-06 10:00:00'),
(213, '14-on5g', 3, '14-4fyq-11-2024', 14, 'Ai Solihah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-19 05:18:06', '2024-11-01 19:14:05', '2024-11-19 05:18:06', 0, '2024-11-19 10:00:00', 230, '2024-11-19 10:00:00'),
(214, '15-9m9o', 3, '15-vsgl-11-2024', 15, 'Armiyanti', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-03 16:04:16', '2024-11-01 19:14:05', '2024-11-03 16:04:16', 0, NULL, 119, '2024-11-13 10:00:00'),
(215, '16-16rt', 3, '16-hrzm-11-2024', 16, 'Riska Azizah', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-11 17:38:59', '2024-11-01 19:14:06', '2024-11-11 17:38:59', 0, NULL, 195, '2024-11-01 10:00:00'),
(217, '18-5sgy', 3, '18-nx8q-11-2024', 18, 'Iip', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 00:06:47', '2024-11-01 19:14:07', '2024-11-02 00:06:47', 0, NULL, 105, '2024-11-01 10:00:00'),
(218, '19-rocw', 3, '19-qjc8-11-2024', 19, 'Ahmad Saepuloh', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 00:33:12', '2024-11-01 19:14:08', '2024-11-12 00:33:12', 0, NULL, 198, '2024-11-07 10:00:00'),
(219, '20-hy9f', 3, '20-z0ha-11-2024', 20, 'Pak Lili', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 19:20:43', '2024-11-01 19:14:08', '2024-11-01 19:20:43', 0, NULL, 98, '2024-11-02 10:00:00'),
(220, '21-ojfi', 3, '21-psof-11-2024', 21, 'Ibu Ayu', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 01:58:58', '2024-11-01 19:14:09', '2024-11-15 01:58:58', 0, NULL, 214, '2024-11-16 10:00:00'),
(221, '22-ypzf', 3, '22-zdnw-11-2024', 22, 'Ratih Aisyah', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-04 19:36:56', '2024-11-01 19:14:09', '2024-11-04 19:36:56', 0, NULL, 129, '2024-11-28 10:00:00'),
(222, '23-gieb', 3, '23-iwy7-11-2024', 23, 'AA Ramli', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-06 20:10:10', '2024-11-01 19:14:10', '2024-11-06 20:10:10', 0, NULL, 160, '2024-11-03 10:00:00'),
(223, '24-24in', 3, '24-qvwe-11-2024', 24, 'Neni Nurhaeni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 02:49:05', '2024-11-01 19:14:11', '2024-11-15 02:49:05', 0, NULL, 215, '2024-10-31 10:00:00'),
(224, '25-hsqq', 3, '25-pjp7-11-2024', 25, 'Etik Aprilia Ratna Sari', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 23:36:39', '2024-11-01 19:14:11', '2024-11-15 23:36:39', 0, NULL, 224, '2024-11-14 10:00:00'),
(225, '26-w2en', 3, '26-plut-11-2024', 26, 'Narsiah', 'ShaNet 0 (Upto 7 Mbps)', 75000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 02:53:26', '2024-11-01 19:14:13', '2024-11-12 02:53:26', 0, NULL, 199, '2024-10-31 10:00:00'),
(226, '27-m8wy', 3, '27-axct-11-2024', 27, 'Novi Eka Purwaningsih', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-05 18:37:33', '2024-11-01 19:14:14', '2024-11-05 18:37:33', 0, NULL, 151, '2024-11-04 10:00:00'),
(227, '28-vxka', 3, '28-047u-11-2024', 28, 'Hasma Wati', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-11 06:10:22', '2024-11-01 19:14:14', '2024-11-11 06:10:22', 0, NULL, 194, '2024-11-09 10:00:00'),
(228, '29-sehu', 3, '29-ai5p-11-2024', 29, 'Euis Ratnasari', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-04 00:46:26', '2024-11-01 19:14:15', '2024-11-04 00:46:26', 0, NULL, 123, '2024-11-15 10:00:00'),
(229, '30-qrak', 3, '30-vtp0-11-2024', 30, 'Adi Amoy', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-13 20:52:48', '2024-11-01 19:14:15', '2024-11-13 20:52:48', 0, NULL, 208, '2024-11-16 10:00:00'),
(230, '31-euns', 3, '31-wh7k-11-2024', 31, 'A Zaky Alattas', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-07 18:32:27', '2024-11-01 19:14:16', '2024-11-07 18:32:27', 0, NULL, 171, '2024-11-23 10:00:00'),
(231, '32-s5y8', 3, '32-aedr-11-2024', 32, 'Teh Ai', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 21:15:31', '2024-11-01 19:14:17', '2024-11-15 21:15:31', 0, '2024-11-16 10:00:00', 222, '2024-11-18 10:00:00'),
(232, '33-v8rn', 3, '33-gbjq-11-2024', 33, 'Pak Slamet', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-13 05:15:04', '2024-11-01 19:14:17', '2024-11-13 05:15:04', 0, NULL, 206, '2024-11-10 10:00:00'),
(233, '34-rrs8', 3, '34-p4xd-11-2024', 34, 'Debbie Febriana', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-19 04:10:52', '2024-11-01 19:14:18', '2024-11-19 04:10:52', 0, '2024-11-17 10:00:00', 229, '2024-11-19 10:00:00'),
(234, '35-pftv', 3, '35-fivd-11-2024', 35, 'Hafis', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-04 16:43:09', '2024-11-01 19:14:19', '2024-11-04 16:43:09', 0, NULL, 126, '2024-11-18 10:00:00'),
(235, '36-n7fe', 3, '36-dlfs-11-2024', 36, 'Triyanti', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-13 21:26:27', '2024-11-01 19:14:19', '2024-11-13 21:26:27', 0, NULL, 209, '2024-11-16 10:00:00'),
(236, '38-jbwy', 3, '38-oz0x-11-2024', 38, 'Yuan Firmansyah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 18:52:06', '2024-11-01 19:14:20', '2024-11-15 18:52:06', 0, NULL, 218, '2024-11-24 10:00:00'),
(237, '39-v82l', 3, '39-ls64-11-2024', 39, 'Dede Suherman', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-14 16:16:00', '2024-11-01 19:14:20', '2024-11-14 16:16:00', 0, NULL, 212, '2024-11-19 10:00:00'),
(238, '40-uoft', 3, '40-xy79-11-2024', 40, 'Ida Siti', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 03:17:36', '2024-11-01 19:14:21', '2024-11-12 03:17:36', 0, NULL, 200, '2024-10-31 10:00:00'),
(239, '41-xvu0', 3, '41-6znk-11-2024', 41, 'Wandi Suhendi', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-17 06:24:43', '2024-11-01 19:14:22', '2024-11-17 06:24:43', 0, NULL, 226, '2024-11-11 10:00:00'),
(240, '42-0rez', 3, '42-ys4l-11-2024', 42, 'Muhammad Fahrizal', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-13 05:55:27', '2024-11-01 19:14:22', '2024-11-13 05:55:27', 0, NULL, 207, '2024-11-02 10:00:00'),
(241, '43-sggr', 3, '43-9ots-11-2024', 43, 'Edi Suswanto', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 23:34:59', '2024-11-01 19:14:23', '2024-11-12 23:34:59', 0, NULL, 205, '2024-11-29 10:00:00'),
(242, '44-gcdo', 3, '44-myok-11-2024', 44, 'Tri Nurhidayati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-03 00:49:01', '2024-11-01 19:14:24', '2024-11-03 00:49:01', 0, NULL, 114, '2024-11-16 10:00:00'),
(243, '45-3gip', 3, '45-tzlo-11-2024', 45, 'Pak Aam', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-09 06:13:15', '2024-11-01 19:14:24', '2024-11-09 06:13:15', 0, NULL, 184, '2024-11-05 10:00:00'),
(244, '46-fzie', 3, '46-7gb1-11-2024', 46, 'Rohani Rusmiati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-11 04:23:38', '2024-11-01 19:14:25', '2024-11-11 04:23:38', 0, NULL, 193, '2024-11-11 10:00:00'),
(245, '47-rg8i', 3, '47-cwpb-11-2024', 47, 'Mulyana', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-26 23:39:18', '2024-11-01 19:14:26', '2024-11-26 23:39:18', 0, NULL, 234, '2024-11-28 10:00:00'),
(246, '48-q5sy', 3, '48-e4lh-11-2024', 48, 'Sri Sulastri', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-02 18:15:36', '2024-11-01 19:14:27', '2024-11-02 18:15:36', 0, NULL, 108, '2024-11-09 10:00:00'),
(247, '49-qnho', 3, '49-ckru-11-2024', 49, 'Mie Ayam & Geprek', 'ShaNet 1 (10 Mbps)', 75000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 04:43:42', '2024-11-01 19:14:28', '2024-11-15 04:43:42', 0, NULL, 216, '2024-11-20 10:00:00'),
(248, '50-bn5o', 3, '50-gzip-11-2024', 50, 'Ferry Kurniawan', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-03 20:07:54', '2024-11-01 19:14:28', '2024-11-03 20:07:54', 0, NULL, 122, '2024-11-24 10:00:00'),
(249, '51-v146', 3, '51-tzei-11-2024', 51, 'Nok Reni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-09 07:02:00', '2024-11-01 19:14:29', '2024-11-09 07:02:00', 0, NULL, 185, '2024-11-10 10:00:00'),
(250, '52-ywal', 3, '52-5tk8-11-2024', 52, 'Ahmad Sugianto', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-17 14:40:23', '2024-11-01 19:14:30', '2024-11-17 14:40:23', 0, NULL, 227, '2024-11-24 10:00:00'),
(251, '53-c8b4', 3, '53-td4p-11-2024', 53, 'Maharani Widyaningrum', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-03 17:38:29', '2024-11-01 19:14:30', '2024-11-03 17:38:29', 0, NULL, 120, '2024-11-12 10:00:00'),
(252, '54-rtcj', 3, '54-gywm-11-2024', 54, 'Linda', 'ShaNet 0 (7 Mbps)', 120000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-11 20:40:19', '2024-11-01 19:14:31', '2024-11-11 20:40:19', 0, NULL, 196, '2024-11-09 10:00:00'),
(253, '55-mwnz', 3, '55-fwon-11-2024', 55, 'Agista Utari', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 05:11:38', '2024-11-01 19:14:31', '2024-11-12 05:11:38', 0, NULL, 201, '2024-11-24 10:00:00'),
(254, '56-2oy9', 3, '56-dhev-11-2024', 56, 'Agustian Setiadi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-02 23:53:55', '2024-11-01 19:14:32', '2024-11-02 23:53:55', 0, NULL, 113, '2024-11-06 10:00:00'),
(255, '57-l4oy', 3, '57-byxe-11-2024', 57, 'Pak Iyan', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-08 18:40:53', '2024-11-01 19:14:33', '2024-11-08 18:40:53', 0, NULL, 180, '2024-11-09 10:00:00'),
(256, '58-0v5q', 3, '58-scmx-11-2024', 58, 'Nur Fakriah Putri', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-14 05:11:02', '2024-11-01 19:14:33', '2024-11-14 05:11:02', 0, NULL, 210, '2024-11-10 10:00:00'),
(258, '60-6rst', 3, '60-0yge-11-2024', 60, 'Pak Jepri', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-08 18:38:42', '2024-11-01 19:14:35', '2024-11-08 18:38:42', 0, NULL, 179, '2024-11-11 10:00:00'),
(259, '61-w1ex', 3, '61-l8uy-11-2024', 61, 'Bu Ani', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-04 04:12:55', '2024-11-01 19:14:35', '2024-11-04 04:12:55', 0, NULL, 125, '2024-11-16 10:00:00'),
(260, '62-fgwi', 3, '62-qpue-11-2024', 62, 'Yeyet Abdulrachman', 'ShaNet 3 (20 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-03 20:05:57', '2024-11-01 19:14:36', '2024-11-03 20:05:57', 0, NULL, 121, '2024-10-31 10:00:00'),
(261, '63-emra', 3, '63-xjj7-11-2024', 63, 'Pak Dendi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 06:14:00', '2024-11-01 19:14:37', '2024-11-12 06:14:00', 0, NULL, 202, '2024-11-23 10:00:00'),
(262, '64-kmfg', 3, '64-jtq9-11-2024', 64, 'Marya Ningsih', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-09 19:43:30', '2024-11-01 19:14:37', '2024-11-09 19:43:30', 0, NULL, 186, '2024-10-31 10:00:00'),
(263, '65-gsju', 3, '65-ez2l-11-2024', 65, 'Pak Kurdi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 20:14:14', '2024-11-01 19:14:38', '2024-11-15 20:14:14', 0, NULL, 219, '2024-10-31 10:00:00'),
(264, '66-kcjt', 3, '66-pyhi-11-2024', 66, 'Kodrat (GOR ADHIGA)', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-07 05:29:03', '2024-11-01 19:14:38', '2024-11-07 05:29:03', 0, NULL, 161, '2024-11-04 10:00:00'),
(265, '67-zb2e', 3, '67-yll5-11-2024', 67, 'Lulu Andriani', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 19:35:10', '2024-11-01 19:14:39', '2024-11-12 19:35:10', 0, NULL, 203, '2024-11-07 10:00:00'),
(266, '68-nmsy', 3, '68-m1z1-11-2024', 68, 'Ma Aisah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-12 19:36:04', '2024-11-01 19:14:39', '2024-11-12 19:36:04', 0, NULL, 204, '2024-11-07 10:00:00'),
(267, '69-yujj', 3, '69-hixd-11-2024', 69, 'Alya Iklima Hidayah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 19:23:31', '2024-11-01 19:14:40', '2024-11-01 19:23:31', 0, NULL, 100, '2024-11-07 10:00:00'),
(268, '70-7esu', 3, '70-weoh-11-2024', 70, 'Pak Kurdi 2', 'ShaNet 1 (10 Mbps)', 100500, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 20:14:19', '2024-11-01 19:14:40', '2024-11-15 20:14:19', 0, NULL, 220, '2024-11-11 10:00:00'),
(269, '71-eboq', 3, '71-ljaw-11-2024', 71, 'Teh Enen', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-08 04:46:40', '2024-11-01 19:14:41', '2024-11-08 04:46:40', 0, NULL, 177, '2024-11-05 10:00:00'),
(270, '72-jobe', 3, '72-liyq-11-2024', 72, 'Rina Konter BMCell', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-15 22:30:14', '2024-11-01 19:14:41', '2024-11-15 22:30:14', 0, NULL, 223, '2024-11-14 10:00:00'),
(271, '73-tphs', 3, '73-vpym-11-2024', 73, 'Asep Topik Hidayat', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-05 18:25:16', '2024-11-01 19:14:42', '2024-11-05 18:25:16', 0, NULL, 150, '2024-11-21 10:00:00'),
(272, '74-kjhe', 3, '74-zcar-11-2024', 74, 'Egy Laksma Suandi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-04 01:48:13', '2024-11-01 19:14:42', '2024-11-04 01:48:13', 0, NULL, 124, '2024-11-23 10:00:00'),
(273, '75-eayk', 3, '75-rpo9-11-2024', 75, 'H. M Abu Rasyid. AS. SH', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 19:21:56', '2024-11-01 19:14:43', '2024-11-01 19:21:56', 0, NULL, 99, '2024-11-01 10:00:00'),
(274, '77-gefm', 3, '77-upnd-11-2024', 77, 'Nazri Nasier', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-14 14:52:55', '2024-11-01 19:14:44', '2024-11-14 14:52:55', 0, NULL, 211, '2024-11-03 10:00:00'),
(275, '78-yiah', 3, '78-439n-11-2024', 78, 'Moh. Gian Firtyanza', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-05 14:29:31', '2024-11-01 19:14:44', '2024-11-05 14:29:31', 0, NULL, 130, '2024-11-09 10:00:00'),
(276, '79-fdhp', 3, '79-xwky-11-2024', 79, 'Intan Ayu Sri Wahyuni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 19:32:03', '2024-11-01 19:14:45', '2024-11-01 19:32:03', 0, NULL, 103, '2024-11-16 10:00:00'),
(277, '80-21tq', 3, '80-jhj8-11-2024', 80, 'Nurhayati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-03 03:58:58', '2024-11-01 19:14:45', '2024-11-03 03:58:58', 0, NULL, 117, '2024-11-20 10:00:00'),
(278, '81-6a8z', 3, '81-kbqz-11-2024', 81, 'Irvan Mohammad Regent', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 19:26:12', '2024-11-01 19:14:46', '2024-11-01 19:26:12', 0, NULL, 101, '2024-11-26 10:00:00'),
(279, '138-ndig', 3, '138-mk68-11-2024', 138, 'Robi Anggara', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-10 03:01:56', '2024-11-01 19:14:46', '2024-11-10 03:01:56', 0, NULL, 189, '2024-11-29 10:00:00'),
(280, '82-5qbn', 3, '82-5zh5-11-2024', 82, 'Nuryani', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 05:58:00', '2024-11-01 19:14:48', '2024-11-07 05:58:00', 0, NULL, 166, '2024-11-05 10:00:00'),
(281, '83-jbp6', 3, '83-jjde-11-2024', 83, '200240420@anggi', 'Basic 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:46:05', '2024-11-01 19:14:49', '2024-11-05 14:46:05', 0, NULL, 135, '2024-11-28 10:00:00'),
(282, '85-xhxk', 3, '85-lcos-11-2024', 85, 'Siti Mariam', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 15:05:39', '2024-11-01 19:14:50', '2024-11-05 15:05:39', 0, NULL, 147, '2024-11-26 10:00:00'),
(283, '86-cldg', 3, '86-wo9w-11-2024', 86, 'Desi Nurdianti', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 15:03:44', '2024-11-01 19:14:50', '2024-11-05 15:03:44', 0, NULL, 146, '2024-11-26 10:00:00'),
(284, '87-ewcd', 3, '87-4saf-11-2024', 87, 'Iis Ismawati', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:50:45', '2024-11-01 19:14:57', '2024-11-05 14:50:45', 0, NULL, 140, '2024-11-26 10:00:00'),
(285, '88-w7fo', 3, '88-tmyq-11-2024', 88, 'Imar Sulaeman', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:53:45', '2024-11-01 19:14:57', '2024-11-05 14:53:45', 0, NULL, 144, '2024-11-27 10:00:00'),
(286, '89-w9lx', 3, '89-qcc8-11-2024', 89, '125240428@adis', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 19:04:59', '2024-11-01 19:14:58', '2024-11-02 19:04:59', 0, NULL, 109, '2024-11-27 10:00:00'),
(287, '90-bpr1', 3, '90-msjd-11-2024', 90, 'Satim Silegar', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-08 04:45:25', '2024-11-01 19:14:59', '2024-11-08 04:45:25', 0, NULL, 174, '2024-11-27 10:00:00'),
(288, '91-hpsk', 3, '91-llvk-11-2024', 91, '125240430@heni', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-09 02:01:59', '2024-11-01 19:15:00', '2024-11-09 02:01:59', 0, NULL, 183, '2024-11-29 10:00:00'),
(289, '92-piuq', 3, '92-33wx-11-2024', 92, 'Juju Juarsih', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 19:05:56', '2024-11-01 19:15:00', '2024-11-02 19:05:56', 0, NULL, 110, '2024-10-31 10:00:00'),
(290, '93-mppo', 3, '93-5jlx-11-2024', 93, '125240501@deni', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 19:15:02', '2024-11-01 19:15:02', 0, NULL, NULL, '2024-10-31 10:00:00'),
(291, '94-79fc', 3, '94-cucq-11-2024', 94, 'Julian Ratu Malik Kusum', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-11 21:58:44', '2024-11-01 19:15:02', '2024-11-11 21:58:44', 0, NULL, 197, '2024-11-03 10:00:00'),
(292, '95-uq2j', 3, '95-ime6-11-2024', 95, '125240420@rosidah', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-06 19:56:50', '2024-11-01 19:15:04', '2024-11-06 19:56:50', 0, NULL, 158, '2024-11-19 10:00:00'),
(293, '96-rg7z', 3, '96-uzx3-11-2024', 96, '125240509@cici', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 05:57:10', '2024-11-01 19:15:05', '2024-11-07 05:57:10', 0, NULL, 164, '2024-11-28 10:00:00'),
(294, '97-geym', 3, '97-kuvz-11-2024', 97, '125240510@pepen', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:47:25', '2024-11-01 19:15:05', '2024-11-05 14:47:25', 0, NULL, 137, '2024-11-09 10:00:00'),
(295, '98-33re', 3, '98-oqfi-11-2024', 98, '125240510@wayanto', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-06 17:43:38', '2024-11-01 19:15:06', '2024-11-06 17:43:38', 0, NULL, 157, '2024-11-09 10:00:00'),
(296, '99-e9ff', 3, '99-yubd-11-2024', 99, '125240514@miung', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:48:20', '2024-11-01 19:15:07', '2024-11-05 14:48:20', 0, NULL, 138, '2024-11-13 10:00:00'),
(297, '100-iy4n', 3, '100-vosp-11-2024', 100, 'Dadan Hamdani', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-08 04:45:53', '2024-11-01 19:15:07', '2024-11-08 04:45:53', 0, NULL, 175, '2024-11-13 10:00:00'),
(298, '101-fmnk', 3, '101-lvvq-11-2024', 101, '125240514@sule', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 19:15:08', '2024-11-01 19:15:08', 0, NULL, NULL, '2024-11-13 10:00:00'),
(299, '102-s9ff', 3, '102-x9ht-11-2024', 102, 'Heri Suandi', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-10 19:29:05', '2024-11-01 19:15:08', '2024-11-10 19:29:05', 0, NULL, 192, '2024-11-13 10:00:00'),
(300, '103-qzrr', 3, '103-zxem-11-2024', 103, 'Juhdi', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 05:58:30', '2024-11-01 19:15:09', '2024-11-07 05:58:30', 0, NULL, 167, '2024-10-31 10:00:00'),
(301, '104-i9nm', 3, '104-ikpa-11-2024', 104, '150240515@rtoji', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-06 17:43:16', '2024-11-01 19:15:11', '2024-11-06 17:43:16', 0, NULL, 156, '2024-11-14 10:00:00'),
(302, '105-magk', 3, '105-elfs-11-2024', 105, '150240518@suntari', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 19:07:13', '2024-11-01 19:15:11', '2024-11-07 19:07:13', 0, NULL, 173, '2024-11-28 10:00:00'),
(303, '106-ocis', 3, '106-kldl-11-2024', 106, 'Iqbal Budi Prasetya', 'Basic 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 16:09:52', '2024-11-01 19:15:12', '2024-11-02 16:09:52', 0, NULL, 107, '2024-11-03 10:00:00'),
(304, '107-61ln', 3, '107-se07-11-2024', 107, 'Engko', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-08 04:46:08', '2024-11-01 19:15:13', '2024-11-08 04:46:08', 0, NULL, 176, '2024-11-24 10:00:00'),
(305, '108-gl3k', 3, '108-vryj-11-2024', 108, 'Arya Cahyana Putra', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-09 22:27:59', '2024-11-01 19:15:13', '2024-11-09 22:27:59', 0, NULL, 188, '2024-10-31 10:00:00'),
(306, '109-dujn', 3, '109-id8h-11-2024', 109, 'Wida', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 13:40:26', '2024-11-01 19:15:14', '2024-11-07 13:40:26', 0, NULL, 170, '2024-11-02 10:00:00'),
(307, '110-hwct', 3, '110-oy0b-11-2024', 110, '150240608@h.imon', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-06 12:46:27', '2024-11-01 19:15:14', '2024-11-06 12:46:27', 0, NULL, 155, '2024-11-28 10:00:00'),
(308, '111-q983', 3, '111-1e6a-11-2024', 111, '150240612@julianto', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-10 07:55:07', '2024-11-01 19:15:15', '2024-11-10 07:55:07', 0, NULL, 191, '2024-11-28 10:00:00'),
(309, '112-gacb', 3, '112-3hui-11-2024', 112, '150240420@okis', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-06 19:57:07', '2024-11-01 19:15:17', '2024-11-06 19:57:07', 0, NULL, 159, '2024-11-19 10:00:00'),
(310, '113-4eij', 3, '113-k78f-11-2024', 113, '150240622@nengsiti', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:39:31', '2024-11-01 19:15:17', '2024-11-05 14:39:31', 0, NULL, 131, '2024-11-28 10:00:00'),
(311, '114-hcjk', 3, '114-soki-11-2024', 114, 'Dedi Haerudi', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 13:24:36', '2024-11-01 19:15:18', '2024-11-07 13:24:36', 0, NULL, 169, '2024-11-21 10:00:00'),
(312, '115-r0kf', 3, '115-gxad-11-2024', 115, 'Amania Kusumah', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-10 07:54:06', '2024-11-01 19:15:19', '2024-11-10 07:54:06', 0, NULL, 190, '2024-11-28 10:00:00'),
(313, '116-wq63', 3, '116-3kav-11-2024', 116, 'Nurul Aeni', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 20:12:23', '2024-11-01 19:15:19', '2024-11-05 20:12:23', 0, NULL, 152, '2024-11-28 10:00:00'),
(314, '117-834k', 3, '117-4vfc-11-2024', 117, '150240701@ojak', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 16:09:31', '2024-11-01 19:15:20', '2024-11-02 16:09:31', 0, NULL, 106, '2024-11-28 10:00:00'),
(315, '118-xevn', 3, '118-ygw8-11-2024', 118, 'Redi Natisna', 'Basic 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:51:24', '2024-11-01 19:15:21', '2024-11-05 14:51:24', 0, NULL, 141, '2024-11-28 10:00:00'),
(316, '119-mvwj', 3, '119-p6ru-11-2024', 119, 'Wahyu Maulana', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:55:24', '2024-11-01 19:15:21', '2024-11-05 14:55:24', 0, NULL, 145, '2024-11-29 10:00:00'),
(317, '120-yy5m', 3, '120-cps7-11-2024', 120, 'Usman Abdul Sobari', 'Basic 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 19:06:34', '2024-11-01 19:15:22', '2024-11-02 19:06:34', 0, NULL, 111, '2024-11-03 10:00:00'),
(318, '121-omf2', 3, '121-6npt-11-2024', 121, '150240704@bajuri', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-09 22:27:33', '2024-11-01 19:15:23', '2024-11-09 22:27:33', 0, NULL, 187, '2024-11-28 10:00:00'),
(319, '123-gxjd', 3, '123-7ol3-11-2024', 123, 'Salmah Saleha', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:49:13', '2024-11-01 19:15:24', '2024-11-05 14:49:13', 0, NULL, 139, '2024-11-07 10:00:00'),
(320, '124-cndz', 3, '124-5ek2-11-2024', 124, 'Neneng Suratni', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:51:50', '2024-11-01 19:15:24', '2024-11-05 14:51:50', 0, NULL, 142, '2024-11-27 10:00:00'),
(321, '125-xih2', 3, '125-hnim-11-2024', 125, 'Herman', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 22:41:41', '2024-11-01 19:15:25', '2024-11-05 22:41:41', 0, NULL, 153, '2024-11-06 10:00:00'),
(322, '126-iqmx', 3, '126-8b5n-11-2024', 126, '150240912@syarip', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 05:54:34', '2024-11-01 19:15:25', '2024-11-07 05:54:34', 0, NULL, 162, '2024-11-28 10:00:00'),
(323, '127-unpt', 3, '127-6ul6-11-2024', 127, '150241004@misbah', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:45:29', '2024-11-01 19:15:27', '2024-11-05 14:45:29', 0, NULL, 134, '2024-11-28 10:00:00'),
(324, '128-b5xs', 3, '128-tssx-11-2024', 128, 'Jajang Salahudin', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 05:54:58', '2024-11-01 19:15:27', '2024-11-07 05:54:58', 0, NULL, 163, '2024-11-05 10:00:00'),
(325, '129-thct', 3, '129-1tat-11-2024', 129, 'Dadi Kusnadi', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-07 05:57:35', '2024-11-01 19:15:28', '2024-11-07 05:57:35', 0, NULL, 165, '2024-11-16 10:00:00'),
(326, '130-vmyz', 3, '130-eouu-11-2024', 130, 'Darma Wirawan', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 15:07:24', '2024-11-01 19:15:28', '2024-11-05 15:07:24', 0, NULL, 148, '2024-11-12 10:00:00'),
(327, '131-rjhx', 3, '131-ey0h-11-2024', 131, '200240715@sulis', 'Basic 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:46:31', '2024-11-01 19:15:29', '2024-11-05 14:46:31', 0, NULL, 136, '2024-11-28 10:00:00'),
(328, '132-6ujw', 3, '132-tavi-11-2024', 132, '150240720@agus', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:43:58', '2024-11-01 19:15:30', '2024-11-05 14:43:58', 0, NULL, 132, '2024-11-28 10:00:00'),
(329, '133-c3bx', 3, '133-3xxc-11-2024', 133, '150240720@sulis', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:44:51', '2024-11-01 19:15:31', '2024-11-05 14:44:51', 0, NULL, 133, '2024-11-28 10:00:00'),
(330, '134-y01f', 3, '134-vpxx-11-2024', 134, 'Rika Rahmawati', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-08 23:11:39', '2024-11-01 19:15:32', '2024-11-08 23:11:39', 0, NULL, 182, '2024-11-26 10:00:00'),
(331, '135-mpbu', 3, '135-jczr-11-2024', 135, '150240815@yuyun', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-08 04:48:35', '2024-11-01 19:15:32', '2024-11-08 04:48:35', 0, NULL, 178, '2024-11-28 10:00:00'),
(332, '136-38vh', 3, '136-6bsc-11-2024', 136, '150240825@wahid', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-18 21:27:31', '2024-11-01 19:15:33', '2024-11-18 21:27:31', 0, NULL, 228, '2024-11-28 10:00:00'),
(333, '137-p94i', 3, '137-h5py-11-2024', 137, 'Fera', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-11-05 14:53:11', '2024-11-01 19:15:33', '2024-11-05 14:53:11', 0, NULL, 143, '2024-11-29 10:00:00'),
(334, '139-fzuy', 3, '139-fwpy-11-2024', 139, 'Iis Nurjanah', 'ShaNet 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-26 01:16:41', '2024-11-26 01:10:32', '2024-11-26 01:16:41', NULL, NULL, 232, NULL);
INSERT INTO `billings` (`id`, `slug`, `billing_periode_id`, `billing_number`, `customer_id`, `customer_name`, `paket_name`, `paket_price`, `created_name`, `status`, `teller_name`, `payment_time`, `created_at`, `updated_at`, `tax`, `pay_later`, `receipt_id`, `due_date`) VALUES
(335, '140-ktgh', 3, '140-z8pn-11-2024', 140, 'Rifal Yudhi Pratama', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-26 02:55:44', '2024-11-26 02:39:06', '2024-11-26 02:55:44', NULL, NULL, 233, NULL),
(338, '145-0szj', 3, '145-qsjq-11-2024', 145, 'Agus Rahmat (Azril)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-29 01:45:13', '2024-11-29 01:44:57', '2024-11-29 01:45:13', NULL, NULL, 246, NULL),
(339, '146-x4fx', 3, '146-rgqb-11-2024', 146, 'Ani Sumarni', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-29 19:57:43', '2024-11-29 19:57:13', '2024-11-29 19:57:43', NULL, NULL, 247, NULL),
(340, '147-rxur', 3, '147-3enj-11-2024', 147, 'Neng Helawati', 'ShaNet 2 (15 Mbps)', 150000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-11-30 02:13:14', '2024-11-30 02:11:01', '2024-11-30 02:13:14', NULL, NULL, 248, NULL),
(341, '148-uukr', 3, '148-efyh-11-2024', 148, 'Rinji Maulani', 'ShaNet 2 (15 Mbps)', 150000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-11-30 03:14:44', '2024-11-30 03:13:33', '2024-11-30 03:14:44', NULL, NULL, 249, NULL),
(342, '149-agt8', 3, '149-6igr-11-2024', 149, 'Iman Nurjamansyah', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-11-30 03:26:10', '2024-11-30 03:25:57', '2024-11-30 03:26:10', NULL, NULL, 250, NULL),
(343, '150-p9gb', 3, '150-o9fk-11-2024', 150, 'Dani Ramdani', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-11-30 03:34:35', '2024-11-30 03:31:43', '2024-11-30 03:34:35', NULL, NULL, 251, NULL),
(344, '151-sbiw', 3, '151-xqfq-11-2024', 151, 'Mujinah', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-11-30 03:35:49', '2024-11-30 03:32:44', '2024-11-30 03:35:49', NULL, NULL, 252, NULL),
(345, '152-zrt8', 3, '152-s3au-11-2024', 152, 'Rodiah', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-11-30 03:36:04', '2024-11-30 03:34:10', '2024-11-30 03:36:04', NULL, NULL, 253, NULL),
(346, '153-li6d', 3, '153-l6wd-11-2024', 153, 'Tika Rachmawati', 'ShaNet 1 (10 Mbps)', 125000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-11-30 04:38:29', '2024-11-30 04:38:18', '2024-11-30 04:38:29', NULL, NULL, 254, NULL),
(347, '154-cn2b', 3, '154-1qgs-11-2024', 154, 'Fadilah Nurhasanah', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Dian Ardiansyah', '2024-12-01 01:19:07', '2024-12-01 00:46:17', '2024-12-01 01:19:07', NULL, NULL, 255, NULL),
(348, '155-kxzo', 3, '155-4jvo-11-2024', 155, 'Mimin Rukmini', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Dian Ardiansyah', '2024-12-01 01:19:13', '2024-12-01 00:48:40', '2024-12-01 01:19:13', NULL, NULL, 256, NULL),
(349, '1-twpx', 4, '1-z4am-12-2024', 1, 'Muh Subhan', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-02 22:22:58', '2024-12-01 03:04:45', '2024-12-02 22:22:58', 0, NULL, 290, '2024-12-20 10:00:00'),
(350, '2-k9ew', 4, '2-jhtu-12-2024', 2, 'Iis Sugiarti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-10 06:58:00', '2024-12-01 03:04:46', '2024-12-10 06:58:00', 0, NULL, 365, '2024-12-11 10:00:00'),
(351, '3-t3j5', 4, '3-hszf-12-2024', 3, 'Asep Nasihin', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 23:50:07', '2024-12-01 03:04:46', '2024-12-01 23:50:07', 0, NULL, 281, '2024-12-19 10:00:00'),
(352, '4-a7km', 4, '4-4kvf-12-2024', 4, 'Pak Surya', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-02 21:18:53', '2024-12-01 03:04:46', '2024-12-02 21:18:53', 0, NULL, 289, '2024-12-10 10:00:00'),
(353, '5-d4ot', 4, '5-offx-12-2024', 5, 'Koesnadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-05 02:22:33', '2024-12-01 03:04:46', '2024-12-05 02:22:33', 0, NULL, 306, '2024-12-11 10:00:00'),
(354, '6-2vfk', 4, '6-ji8b-12-2024', 6, 'Cucu Suryani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-02 03:06:25', '2024-12-01 03:04:46', '2024-12-02 03:06:25', 0, NULL, 283, '2024-12-01 10:00:00'),
(355, '7-knr8', 4, '7-yrpv-12-2024', 7, 'Sumarni', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-09 19:29:34', '2024-12-01 03:04:46', '2024-12-09 19:29:34', 0, NULL, 355, '2024-12-29 10:00:00'),
(356, '8-572t', 4, '8-saxw-12-2024', 8, 'Teh Darsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'PL', 'Admin ShaNet Fiber', NULL, '2024-12-01 03:04:46', '2024-12-14 20:15:30', 0, '2024-12-19 10:00:00', NULL, '2024-12-17 10:00:00'),
(357, '9-mcg8', 4, '9-bel5-12-2024', 9, 'Pak Eko', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:46', '2024-12-01 03:04:46', 0, NULL, NULL, '2024-12-08 10:00:00'),
(358, '10-euek', 4, '10-7zj1-12-2024', 10, 'Ali Akbar', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-14 20:19:42', '2024-12-01 03:04:46', '2024-12-14 20:19:42', 0, NULL, 390, '2024-12-19 10:00:00'),
(359, '11-asyi', 4, '11-xkrp-12-2024', 11, 'Rita Susanti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-04 03:01:09', '2024-12-01 03:04:46', '2024-12-04 03:01:09', 0, NULL, 295, '2024-12-28 10:00:00'),
(360, '12-toqa', 4, '12-umkh-12-2024', 12, 'Pak Suud', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:46', '2024-12-01 03:04:46', 0, NULL, NULL, '2024-12-17 10:00:00'),
(361, '13-jyun', 4, '13-iuyl-12-2024', 13, 'Mamah Anggi', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-09 01:54:12', '2024-12-01 03:04:47', '2024-12-09 01:54:12', 0, NULL, 343, '2024-12-06 10:00:00'),
(362, '14-ktgo', 4, '14-3ty9-12-2024', 14, 'Ai Solihah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:47', '2024-12-01 03:04:47', 0, NULL, NULL, '2024-12-19 10:00:00'),
(363, '15-ren9', 4, '15-glwi-12-2024', 15, 'Armiyanti', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-05 06:58:29', '2024-12-01 03:04:47', '2024-12-05 06:58:29', 0, NULL, 309, '2024-12-13 10:00:00'),
(364, '16-4vtx', 4, '16-wfdh-12-2024', 16, 'Riska Azizah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-05 01:39:55', '2024-12-01 03:04:47', '2024-12-05 01:39:55', 0, NULL, 305, '2024-12-01 10:00:00'),
(365, '18-o2c5', 4, '18-ccfw-12-2024', 18, 'Iip', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-04 21:01:23', '2024-12-01 03:04:47', '2024-12-04 21:01:23', 0, NULL, 303, '2024-12-01 10:00:00'),
(366, '19-wwvq', 4, '19-synm-12-2024', 19, 'Ahmad Saepuloh', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-12 02:08:33', '2024-12-01 03:04:47', '2024-12-12 02:08:33', 0, NULL, 375, '2024-12-07 10:00:00'),
(367, '20-n6ni', 4, '20-2w1k-12-2024', 20, 'Pak Lili', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:46:28', '2024-12-01 03:04:47', '2024-12-01 03:46:28', 0, NULL, 259, '2024-12-02 10:00:00'),
(368, '21-1ny0', 4, '21-iwr9-12-2024', 21, 'Ibu Ayu', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-13 23:23:54', '2024-12-01 03:04:47', '2024-12-13 23:23:54', 0, NULL, 384, '2024-12-16 10:00:00'),
(369, '22-aet5', 4, '22-1h0w-12-2024', 22, 'Ratih Aisyah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-04 17:06:22', '2024-12-01 03:04:47', '2024-12-04 17:06:22', 0, NULL, 301, '2024-12-28 10:00:00'),
(370, '23-4u5o', 4, '23-uwq9-12-2024', 23, 'AA Ramli', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 17:50:39', '2024-12-01 03:04:47', '2024-12-01 17:50:39', 0, NULL, 276, '2024-12-03 10:00:00'),
(371, '24-1td6', 4, '24-zokl-12-2024', 24, 'Neni Nurhaeni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:47', '2024-12-01 03:04:47', 0, NULL, NULL, '2024-11-30 10:00:00'),
(372, '25-rfcb', 4, '25-neob-12-2024', 25, 'Etik Aprilia Ratna Sari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-14 05:18:55', '2024-12-01 03:04:47', '2024-12-14 05:18:55', 0, NULL, 387, '2024-12-14 10:00:00'),
(373, '26-fsmt', 4, '26-78dq-12-2024', 26, 'Narsiah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-07 21:35:43', '2024-12-01 03:04:48', '2024-12-07 21:35:43', 0, NULL, 327, '2024-11-30 10:00:00'),
(374, '27-ebkb', 4, '27-094h-12-2024', 27, 'Novi Eka Purwaningsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-02 17:57:35', '2024-12-01 03:04:48', '2024-12-02 17:57:35', 0, NULL, 287, '2024-12-04 10:00:00'),
(375, '28-nmxi', 4, '28-i1vh-12-2024', 28, 'Hasma Wati', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-12 06:43:51', '2024-12-01 03:04:48', '2024-12-12 06:43:51', 0, NULL, 379, '2024-12-09 10:00:00'),
(376, '29-ohqg', 4, '29-xivo-12-2024', 29, 'Euis Ratnasari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 19:44:55', '2024-12-01 03:04:48', '2024-12-01 19:44:55', 0, NULL, 278, '2024-12-15 10:00:00'),
(377, '30-r1h0', 4, '30-kjpc-12-2024', 30, 'Adi Amoy', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:48', '2024-12-01 03:04:48', 0, NULL, NULL, '2024-12-16 10:00:00'),
(378, '31-iraq', 4, '31-xath-12-2024', 31, 'A Zaky Alattas', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-12 02:51:55', '2024-12-01 03:04:48', '2024-12-12 02:51:55', 0, NULL, 376, '2024-12-23 10:00:00'),
(379, '32-exfs', 4, '32-k1po-12-2024', 32, 'Teh Ai', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:48', '2024-12-01 03:04:48', 0, NULL, NULL, '2024-12-18 10:00:00'),
(380, '33-rlow', 4, '33-4eri-12-2024', 33, 'Pak Slamet', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-13 23:41:36', '2024-12-01 03:04:48', '2024-12-13 23:41:36', 0, NULL, 385, '2024-12-10 10:00:00'),
(381, '34-m3zf', 4, '34-htqr-12-2024', 34, 'Debbie Febriana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'PL', 'Admin ShaNet Fiber', NULL, '2024-12-01 03:04:48', '2024-12-14 20:25:56', 0, '2024-12-16 10:00:00', NULL, '2024-12-19 10:00:00'),
(382, '35-07br', 4, '35-aa1f-12-2024', 35, 'Hafis', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 16:44:32', '2024-12-01 03:04:48', '2024-12-06 16:44:32', 0, NULL, 318, '2024-12-18 10:00:00'),
(383, '36-kyiy', 4, '36-plfm-12-2024', 36, 'Triyanti', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-14 01:25:18', '2024-12-01 03:04:48', '2024-12-14 01:25:18', 0, NULL, 386, '2024-12-16 10:00:00'),
(384, '38-f9vm', 4, '38-efvk-12-2024', 38, 'Yuan Firmansyah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-14 17:01:55', '2024-12-01 03:04:49', '2024-12-14 17:01:55', 0, NULL, 388, '2024-12-24 10:00:00'),
(385, '39-a6gc', 4, '39-3sg5-12-2024', 39, 'Dede Suherman', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-12-01 03:12:05', '2024-12-01 03:04:49', '2024-12-01 03:12:05', 0, NULL, 257, '2024-12-19 10:00:00'),
(386, '40-cqgx', 4, '40-84jo-12-2024', 40, 'Ida Siti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-12 20:06:49', '2024-12-01 03:04:49', '2024-12-12 20:06:49', 0, NULL, 380, '2024-11-30 10:00:00'),
(387, '41-ieyo', 4, '41-wko6-12-2024', 41, 'Wandi Suhendi', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:49', '2024-12-01 03:04:49', 0, NULL, NULL, '2024-12-11 10:00:00'),
(388, '42-gxv9', 4, '42-ksvu-12-2024', 42, 'Muhammad Fahrizal', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 06:26:03', '2024-12-01 03:04:49', '2024-12-06 06:26:03', 0, NULL, 314, '2024-12-02 10:00:00'),
(389, '43-z26e', 4, '43-udum-12-2024', 43, 'Edi Suswanto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-13 17:51:23', '2024-12-01 03:04:49', '2024-12-13 17:51:23', 0, NULL, 382, '2024-12-09 10:00:00'),
(390, '44-5ehz', 4, '44-y72w-12-2024', 44, 'Tri Nurhidayati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 18:36:05', '2024-12-01 03:04:49', '2024-12-01 18:36:05', 0, NULL, 277, '2024-12-16 10:00:00'),
(391, '45-pnma', 4, '45-zwnj-12-2024', 45, 'Pak Aam', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-10 19:35:01', '2024-12-01 03:04:49', '2024-12-10 19:35:01', 0, NULL, 372, '2024-12-05 10:00:00'),
(392, '46-sbrk', 4, '46-bzqz-12-2024', 46, 'Rohani Rusmiati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:49', '2024-12-01 03:04:49', 0, NULL, NULL, '2024-12-11 10:00:00'),
(393, '47-fmht', 4, '47-lddu-12-2024', 47, 'Mulyana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-12-01 06:13:52', '2024-12-01 03:04:49', '2024-12-01 06:13:52', 0, NULL, 274, '2024-12-28 10:00:00'),
(394, '48-irv2', 4, '48-wkig-12-2024', 48, 'Sri Sulastri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 19:12:59', '2024-12-01 03:04:49', '2024-12-06 19:12:59', 0, NULL, 321, '2024-12-09 10:00:00'),
(395, '49-dapv', 4, '49-6fzy-12-2024', 49, 'Mie Ayam & Geprek', 'ShaNet 2 (15 Mbps)', 75000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:50', '2024-12-01 03:04:50', 0, NULL, NULL, '2024-12-20 10:00:00'),
(396, '50-flzz', 4, '50-2pqx-12-2024', 50, 'Ferry Kurniawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-04 20:34:37', '2024-12-01 03:04:50', '2024-12-04 20:34:37', 0, NULL, 302, '2024-12-24 10:00:00'),
(397, '51-ey9i', 4, '51-2o3a-12-2024', 51, 'Nok Reni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-09 19:42:53', '2024-12-01 03:04:50', '2024-12-09 19:42:53', 0, NULL, 356, '2024-12-10 10:00:00'),
(398, '52-twma', 4, '52-wax5-12-2024', 52, 'Ahmad Sugianto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:50', '2024-12-01 03:04:50', 0, NULL, NULL, '2024-12-24 10:00:00'),
(399, '53-cdlr', 4, '53-lw7r-12-2024', 53, 'Maharani Widyaningrum', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-13 03:24:20', '2024-12-01 03:04:50', '2024-12-13 03:24:20', 0, NULL, 381, '2024-12-12 10:00:00'),
(400, '54-4p8f', 4, '54-lhyt-12-2024', 54, 'Linda', 'ShaNet 1 (10 Mbps)', 120000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-14 20:13:16', '2024-12-01 03:04:50', '2024-12-14 20:13:16', 0, NULL, 389, '2024-12-09 10:00:00'),
(401, '55-rxqs', 4, '55-yqqn-12-2024', 55, 'Agista Utari', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-12 06:30:32', '2024-12-01 03:04:50', '2024-12-12 06:30:32', 0, NULL, 378, '2024-12-24 10:00:00'),
(402, '56-5skj', 4, '56-pqwe-12-2024', 56, 'Agustian Setiadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 19:47:27', '2024-12-01 03:04:50', '2024-12-01 19:47:27', 0, NULL, 279, '2024-12-06 10:00:00'),
(403, '57-r7yc', 4, '57-xgaf-12-2024', 57, 'Pak Iyan', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-08 04:59:59', '2024-12-01 03:04:50', '2024-12-08 04:59:59', 0, NULL, 331, '2024-12-09 10:00:00'),
(404, '58-yvbu', 4, '58-at8h-12-2024', 58, 'Nur Fakriah Putri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-13 19:58:49', '2024-12-01 03:04:50', '2024-12-13 19:58:49', 0, NULL, 383, '2024-12-10 10:00:00'),
(405, '60-qcdi', 4, '60-rnsm-12-2024', 60, 'Pak Jepri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 17:49:22', '2024-12-01 03:04:50', '2024-12-06 17:49:22', 0, NULL, 319, '2024-12-11 10:00:00'),
(406, '61-jqts', 4, '61-4yd5-12-2024', 61, 'Bu Ani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-12-01 03:12:37', '2024-12-01 03:04:51', '2024-12-01 03:12:37', 0, NULL, 258, '2024-12-16 10:00:00'),
(407, '62-1emb', 4, '62-amiw-12-2024', 62, 'Yeyet Abdulrachman', 'ShaNet 4 (40 Mbps)', 250000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 19:05:46', '2024-12-01 03:04:51', '2024-12-06 19:05:46', 0, NULL, 320, '2024-11-30 10:00:00'),
(408, '63-xosn', 4, '63-fkqp-12-2024', 63, 'Pak Dendi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-10 17:44:42', '2024-12-01 03:04:51', '2024-12-10 17:44:42', 0, NULL, 368, '2024-12-23 10:00:00'),
(409, '64-sxo7', 4, '64-v5tq-12-2024', 64, 'Marya Ningsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:51', '2024-12-01 03:04:51', 0, NULL, NULL, '2024-11-30 10:00:00'),
(410, '65-qs1x', 4, '65-gglw-12-2024', 65, 'Pak Kurdi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:51', '2024-12-01 03:04:51', 0, NULL, NULL, '2024-11-30 10:00:00'),
(411, '66-7qfx', 4, '66-r3dg-12-2024', 66, 'Kodrat (GOR ADHIGA)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 03:33:16', '2024-12-01 03:04:51', '2024-12-06 03:33:16', 0, NULL, 312, '2024-12-04 10:00:00'),
(412, '67-j1ty', 4, '67-w0g0-12-2024', 67, 'Lulu Andriani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-10 19:11:03', '2024-12-01 03:04:51', '2024-12-10 19:11:03', 0, NULL, 370, '2024-12-07 10:00:00'),
(413, '68-pjbu', 4, '68-zo0u-12-2024', 68, 'Ma Aisah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-10 19:12:48', '2024-12-01 03:04:51', '2024-12-10 19:12:48', 0, NULL, 371, '2024-12-07 10:00:00'),
(414, '69-ljc0', 4, '69-youi-12-2024', 69, 'Alya Iklima Hidayah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:53:01', '2024-12-01 03:04:51', '2024-12-01 03:53:01', 0, NULL, 266, '2024-12-07 10:00:00'),
(415, '70-cfrs', 4, '70-suj0-12-2024', 70, 'Pak Kurdi 2', 'ShaNet 2 (15 Mbps)', 100500, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:52', '2024-12-01 03:04:52', 0, NULL, NULL, '2024-12-11 10:00:00'),
(416, '71-wnb6', 4, '71-c0l7-12-2024', 71, 'Teh Enen', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-10 05:05:45', '2024-12-01 03:04:52', '2024-12-10 05:05:45', 0, NULL, 363, '2024-12-05 10:00:00'),
(417, '72-0cer', 4, '72-cbsu-12-2024', 72, 'Rina Konter BMCell', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:52', '2024-12-01 03:04:52', 0, NULL, NULL, '2024-12-14 10:00:00'),
(418, '73-ah5w', 4, '73-4ohb-12-2024', 73, 'Asep Topik Hidayat', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-02 20:07:58', '2024-12-01 03:04:52', '2024-12-02 20:07:58', 0, NULL, 288, '2024-12-21 10:00:00'),
(419, '74-ca6i', 4, '74-fslj-12-2024', 74, 'Egy Laksma Suandi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-07 23:06:59', '2024-12-01 03:04:52', '2024-12-07 23:06:59', 0, NULL, 328, '2024-12-23 10:00:00'),
(420, '75-7f4t', 4, '75-gjbr-12-2024', 75, 'H. M Abu Rasyid. AS. SH', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:52', '2024-12-01 03:04:52', 0, NULL, NULL, '2024-12-01 10:00:00'),
(421, '77-xwpd', 4, '77-4gl9-12-2024', 77, 'Nazri Nasier', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:53', '2024-12-01 03:04:53', 0, NULL, NULL, '2024-12-03 10:00:00'),
(422, '78-afkw', 4, '78-ug6f-12-2024', 78, 'Moh. Gian Firtyanza', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-05 06:57:28', '2024-12-01 03:04:53', '2024-12-05 06:57:28', 0, NULL, 308, '2024-12-09 10:00:00'),
(423, '79-wayf', 4, '79-rilf-12-2024', 79, 'Intan Ayu Sri Wahyuni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-12-01 06:15:25', '2024-12-01 03:04:53', '2024-12-01 06:15:25', 0, NULL, 275, '2024-12-16 10:00:00'),
(424, '80-ap1p', 4, '80-ezeo-12-2024', 80, 'Nurhayati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 04:54:38', '2024-12-01 03:04:54', '2024-12-06 04:54:38', 0, NULL, 313, '2024-12-20 10:00:00'),
(425, '81-wxcm', 4, '81-xvth-12-2024', 81, 'Irvan Mohammad Regent', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-02 03:35:56', '2024-12-01 03:04:54', '2024-12-02 03:35:56', 0, NULL, 284, '2024-12-26 10:00:00'),
(426, '138-kb6n', 4, '138-xdbs-12-2024', 138, 'Robi Anggara', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-12 03:59:18', '2024-12-01 03:04:54', '2024-12-12 03:59:18', 0, NULL, 377, '2024-12-29 10:00:00'),
(427, '139-qktg', 4, '139-cmqa-12-2024', 139, 'Iis Nurjanah', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:55:05', '2024-12-01 03:04:54', '2024-12-01 03:55:05', 0, NULL, 267, '2024-12-25 10:00:00'),
(428, '145-fnfs', 4, '145-2w3m-12-2024', 145, 'Agus Rahmat (Azril)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-06 16:44:19', '2024-12-01 03:04:54', '2024-12-06 16:44:19', 0, NULL, 317, '2024-12-07 10:00:00'),
(429, '148-zak8', 4, '148-rnlh-12-2024', 148, 'Rinji Maulani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:51:20', '2024-12-01 03:04:54', '2024-12-01 03:51:20', 0, NULL, 264, '2024-12-29 10:00:00'),
(430, '149-n9rv', 4, '149-mijj-12-2024', 149, 'Iman Nurjamansyah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:51:53', '2024-12-01 03:04:54', '2024-12-01 03:51:53', 0, NULL, 265, '2024-12-29 10:00:00'),
(431, '150-2uby', 4, '150-dnvi-12-2024', 150, 'Dani Ramdani', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:50:15', '2024-12-01 03:04:54', '2024-12-01 03:50:15', 0, NULL, 261, '2024-12-29 10:00:00'),
(432, '151-zzhy', 4, '151-jdrh-12-2024', 151, 'Mujinah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:50:31', '2024-12-01 03:04:54', '2024-12-01 03:50:31', 0, NULL, 262, '2024-12-29 10:00:00'),
(433, '153-fgq2', 4, '153-l7u9-12-2024', 153, 'Gilang Gumilang', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:50:59', '2024-12-01 03:04:54', '2024-12-01 03:50:59', 0, NULL, 263, '2024-12-29 10:00:00'),
(434, '155-rd1a', 4, '155-ygjk-12-2024', 155, 'Mimin Rukmini', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-12-01 03:49:31', '2024-12-01 03:04:54', '2024-12-01 03:49:31', 0, NULL, 260, '2024-11-30 10:00:00'),
(435, '82-m0r9', 4, '82-mstw-12-2024', 82, 'Nuryani', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 01:17:23', '2024-12-01 03:04:55', '2024-12-09 01:17:23', 0, NULL, 341, '2024-12-05 10:00:00'),
(436, '83-hvzl', 4, '83-q5n8-12-2024', 83, 'Anggi', 'Basic 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-06 14:48:37', '2024-12-01 03:04:55', '2024-12-06 14:48:37', 0, NULL, 316, '2024-12-19 10:00:00'),
(437, '85-carm', 4, '85-2g0y-12-2024', 85, 'Siti Mariam', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-04 15:49:29', '2024-12-01 03:04:55', '2024-12-04 15:49:29', 0, NULL, 299, '2024-12-26 10:00:00'),
(438, '86-y258', 4, '86-uwwy-12-2024', 86, 'Desi Nurdianti', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-04 15:48:50', '2024-12-01 03:04:55', '2024-12-04 15:48:50', 0, NULL, 298, '2024-12-26 10:00:00'),
(439, '87-zrcx', 4, '87-xvjb-12-2024', 87, 'Iis Ismawati', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-04 15:50:04', '2024-12-01 03:04:55', '2024-12-04 15:50:04', 0, NULL, 300, '2024-12-26 10:00:00'),
(440, '88-p2d5', 4, '88-fxkh-12-2024', 88, 'Imar Sulaeman', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 23:47:56', '2024-12-01 03:04:56', '2024-12-09 23:47:56', 0, NULL, 360, '2024-12-27 10:00:00'),
(441, '89-gefw', 4, '89-gasv-12-2024', 89, 'Adis', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-02 00:32:53', '2024-12-01 03:04:56', '2024-12-02 00:32:53', 0, NULL, 282, '2024-12-27 10:00:00'),
(442, '90-ujji', 4, '90-bgkn-12-2024', 90, 'Satim Silegar', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-07 23:40:41', '2024-12-01 03:04:56', '2024-12-07 23:40:41', 0, NULL, 330, '2024-12-27 10:00:00'),
(443, '91-hdzc', 4, '91-eaqz-12-2024', 91, 'Heni', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 20:46:33', '2024-12-01 03:04:56', '2024-12-09 20:46:33', 0, NULL, 359, '2024-12-29 10:00:00'),
(444, '92-fp95', 4, '92-vtlp-12-2024', 92, 'Juju Juarsih', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-05 22:41:56', '2024-12-01 03:04:56', '2024-12-05 22:41:56', 0, NULL, 311, '2024-11-30 10:00:00'),
(445, '93-y4nr', 4, '93-m70k-12-2024', 93, 'Deni', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:56', '2024-12-01 03:04:56', 0, NULL, NULL, '2024-11-30 10:00:00'),
(446, '94-kora', 4, '94-vtsa-12-2024', 94, 'Julian Ratu Malik Kusum', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:57', '2024-12-01 03:04:57', 0, NULL, NULL, '2024-12-03 10:00:00'),
(447, '95-mlgf', 4, '95-unqj-12-2024', 95, 'Rosidah', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 17:02:38', '2024-12-01 03:04:57', '2024-12-09 17:02:38', 0, NULL, 351, '2024-12-19 10:00:00'),
(448, '96-oouq', 4, '96-6l1j-12-2024', 96, 'Cici', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 04:19:42', '2024-12-01 03:04:57', '2024-12-09 04:19:42', 0, NULL, 346, '2024-12-08 10:00:00'),
(449, '97-1uwi', 4, '97-zhlw-12-2024', 97, 'Pepen', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-10 02:10:22', '2024-12-01 03:04:57', '2024-12-10 02:10:22', 0, NULL, 362, '2024-12-09 10:00:00'),
(450, '98-hze1', 4, '98-ohf8-12-2024', 98, 'Wayanto', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 04:19:05', '2024-12-01 03:04:57', '2024-12-09 04:19:05', 0, NULL, 345, '2024-12-09 10:00:00'),
(451, '99-ezmb', 4, '99-70wx-12-2024', 99, 'Miung', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-06 23:40:27', '2024-12-01 03:04:57', '2024-12-06 23:40:27', 0, NULL, 322, '2024-12-13 10:00:00'),
(452, '100-nhim', 4, '100-ncmi-12-2024', 100, 'Dadan Hamdani', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 09:48:30', '2024-12-01 03:04:58', '2024-12-09 09:48:30', 0, NULL, 347, '2024-12-13 10:00:00'),
(453, '101-lbys', 4, '101-yp1k-12-2024', 101, 'Sule', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:04:58', '2024-12-01 03:04:58', 0, NULL, NULL, '2024-12-13 10:00:00'),
(454, '102-q1yk', 4, '102-mbgi-12-2024', 102, 'Heri Suandi', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-10 18:16:53', '2024-12-01 03:04:58', '2024-12-10 18:16:53', 0, NULL, 369, '2024-12-13 10:00:00'),
(455, '103-rtuy', 4, '103-qn14-12-2024', 103, 'Juhdi', 'Basic 1 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-07 04:37:20', '2024-12-01 03:04:58', '2024-12-07 04:37:20', 0, NULL, 324, '2024-11-30 10:00:00'),
(456, '104-zkoi', 4, '104-qfcx-12-2024', 104, 'RT Oji', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-06 23:40:53', '2024-12-01 03:04:58', '2024-12-06 23:40:53', 0, NULL, 323, '2024-12-14 10:00:00'),
(457, '105-bks7', 4, '105-wtvd-12-2024', 105, 'Suntari', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 17:02:06', '2024-12-01 03:04:58', '2024-12-09 17:02:06', 0, NULL, 350, '2024-12-17 10:00:00'),
(458, '106-mddi', 4, '106-ilga-12-2024', 106, 'Iqbal Budi Prasetya', 'Basic 1 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 16:37:14', '2024-12-01 03:04:58', '2024-12-08 16:37:14', 0, NULL, 337, '2024-12-03 10:00:00'),
(459, '107-mbb5', 4, '107-bfew-12-2024', 107, 'Engko', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-10 05:44:36', '2024-12-01 03:04:59', '2024-12-10 05:44:36', 0, NULL, 364, '2024-12-24 10:00:00'),
(460, '108-j52k', 4, '108-99r2-12-2024', 108, 'Arya Cahyana Putra', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 18:07:26', '2024-12-01 03:04:59', '2024-12-09 18:07:26', 0, NULL, 354, '2024-11-30 10:00:00'),
(461, '109-bpan', 4, '109-ucjn-12-2024', 109, 'Wida', 'Basic 1 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 16:37:31', '2024-12-01 03:04:59', '2024-12-08 16:37:31', 0, NULL, 338, '2024-12-02 10:00:00'),
(462, '110-lkyf', 4, '110-ftzu-12-2024', 110, 'H Imon', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 15:16:32', '2024-12-01 03:04:59', '2024-12-08 15:16:32', 0, NULL, 333, '2024-12-07 10:00:00'),
(463, '111-9xmu', 4, '111-w4pg-12-2024', 111, 'Julianto', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-11 05:32:44', '2024-12-01 03:04:59', '2024-12-11 05:32:44', 0, NULL, 374, '2024-12-11 10:00:00'),
(464, '112-hjws', 4, '112-vxgu-12-2024', 112, 'Okis', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 16:36:38', '2024-12-01 03:04:59', '2024-12-08 16:36:38', 0, NULL, 336, '2024-12-19 10:00:00'),
(465, '113-twfl', 4, '113-mosu-12-2024', 113, 'Neng Siti', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 16:53:02', '2024-12-01 03:05:00', '2024-12-09 16:53:02', 0, NULL, 349, '2024-12-21 10:00:00'),
(466, '114-6yzf', 4, '114-ssmi-12-2024', 114, 'Dedi Haerudi', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 15:17:10', '2024-12-01 03:05:00', '2024-12-08 15:17:10', 0, NULL, 334, '2024-12-21 10:00:00'),
(467, '115-jmkf', 4, '115-awk9-12-2024', 115, 'Amania Kusumah', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 16:32:14', '2024-12-01 03:05:00', '2024-12-08 16:32:14', 0, NULL, 335, '2024-12-28 10:00:00'),
(468, '116-tyta', 4, '116-f5nv-12-2024', 116, 'Nurul Aeni', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 20:26:47', '2024-12-01 03:05:00', '2024-12-08 20:26:47', 0, NULL, 339, '2024-12-28 10:00:00'),
(469, '117-7znm', 4, '117-c21o-12-2024', 117, 'Pak Ojak', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-01 23:11:04', '2024-12-01 03:05:00', '2024-12-01 23:11:04', 0, NULL, 280, '2024-11-30 10:00:00'),
(470, '118-kvwy', 4, '118-kbog-12-2024', 118, 'Redi Natisna', 'Basic 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-01 04:07:31', '2024-12-01 03:05:00', '2024-12-01 04:07:31', 0, NULL, 268, '2024-12-28 10:00:00'),
(471, '119-2gnv', 4, '119-pphv-12-2024', 119, 'Wahyu Maulana', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-03 19:25:07', '2024-12-01 03:05:00', '2024-12-03 19:25:07', 0, NULL, 292, '2024-12-29 10:00:00'),
(472, '120-oi3s', 4, '120-0h8x-12-2024', 120, 'Usman Abdul Sobari', 'Basic 1 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 01:16:56', '2024-12-01 03:05:01', '2024-12-09 01:16:56', 0, NULL, 340, '2024-12-03 10:00:00'),
(473, '121-30cz', 4, '121-qlkm-12-2024', 121, 'Bajuri Enci', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-10 15:45:32', '2024-12-01 03:05:01', '2024-12-10 15:45:32', 0, NULL, 366, '2024-12-03 10:00:00'),
(474, '123-b1yb', 4, '123-hbnh-12-2024', 123, 'Salmah Saleha', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-08 15:14:45', '2024-12-01 03:05:01', '2024-12-08 15:14:45', 0, NULL, 332, '2024-12-07 10:00:00'),
(475, '124-s8kc', 4, '124-ffdn-12-2024', 124, 'Neneng Suratni', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-05 21:01:57', '2024-12-01 03:05:01', '2024-12-05 21:01:57', 0, NULL, 310, '2024-12-27 10:00:00'),
(476, '125-dkhe', 4, '125-bbkl-12-2024', 125, 'Herman', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 03:42:19', '2024-12-01 03:05:01', '2024-12-09 03:42:19', 0, NULL, 344, '2024-12-06 10:00:00'),
(477, '126-ltzy', 4, '126-ntsg-12-2024', 126, 'Syarip Hidayatullah', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-10 01:44:32', '2024-12-01 03:05:01', '2024-12-10 01:44:32', 0, NULL, 361, '2024-12-11 10:00:00'),
(478, '127-8p3j', 4, '127-1fk0-12-2024', 127, 'Misbah Toko Bangunan', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-04 01:47:03', '2024-12-01 03:05:02', '2024-12-04 01:47:03', 0, NULL, 294, '2024-12-03 10:00:00'),
(479, '128-wmrh', 4, '128-gjv0-12-2024', 128, 'Jajang Salahudin', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-10 16:12:20', '2024-12-01 03:05:02', '2024-12-10 16:12:20', 0, NULL, 367, '2024-12-05 10:00:00'),
(480, '129-1csy', 4, '129-dlb6-12-2024', 129, 'Dadi Kusnadi', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 09:49:51', '2024-12-01 03:05:02', '2024-12-09 09:49:51', 0, NULL, 348, '2024-12-16 10:00:00'),
(481, '130-pgwf', 4, '130-pxvp-12-2024', 130, 'Darma Wirawan', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-03 20:07:38', '2024-12-01 03:05:02', '2024-12-03 20:07:38', 0, NULL, 293, '2024-12-12 10:00:00'),
(482, '131-tjvg', 4, '131-ibji-12-2024', 131, 'Sulis Toko', 'Basic 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 17:12:52', '2024-12-01 03:05:02', '2024-12-09 17:12:52', 0, NULL, 353, '2024-12-14 10:00:00'),
(483, '132-meem', 4, '132-kujl-12-2024', 132, 'Pak Agus', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 01:18:41', '2024-12-01 03:05:02', '2024-12-09 01:18:41', 0, NULL, 342, '2024-12-19 10:00:00'),
(484, '133-73eh', 4, '133-xkcn-12-2024', 133, 'Sulis Rumah', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-09 17:12:10', '2024-12-01 03:05:03', '2024-12-09 17:12:10', 0, NULL, 352, '2024-12-19 10:00:00'),
(485, '134-lvsc', 4, '134-mo24-12-2024', 134, 'Rika Rahmawati', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-03 00:52:57', '2024-12-01 03:05:03', '2024-12-03 00:52:57', 0, NULL, 291, '2024-12-26 10:00:00'),
(486, '135-sawz', 4, '135-yz3y-12-2024', 135, 'Yuyun', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-05 02:34:39', '2024-12-01 03:05:03', '2024-12-05 02:34:39', 0, NULL, 307, '2024-12-14 10:00:00'),
(487, '136-mgim', 4, '136-hyej-12-2024', 136, 'Wahid', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-12-01 03:05:03', '2024-12-01 03:05:03', 0, NULL, NULL, '2024-12-24 10:00:00'),
(488, '137-ldar', 4, '137-2ohm-12-2024', 137, 'Fera', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-04 15:20:59', '2024-12-01 03:05:03', '2024-12-04 15:20:59', 0, NULL, 297, '2024-12-29 10:00:00'),
(489, '140-oai6', 4, '140-oevs-12-2024', 140, 'Rifal Yudhi Pratama', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-01 05:56:53', '2024-12-01 03:05:03', '2024-12-01 05:56:53', 0, NULL, 271, '2024-12-25 10:00:00'),
(490, '146-j6wo', 4, '146-z93h-12-2024', 146, 'Ani Sumarni', 'Basic 2 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2024-12-01 05:58:02', '2024-12-01 03:05:03', '2024-12-01 05:58:02', 0, NULL, 272, '2024-12-29 10:00:00'),
(491, '156-n15h', 4, '156-1ixl-12-2024', 156, 'Rini Seminar', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-12-01 06:10:17', '2024-12-01 05:36:03', '2024-12-01 06:10:17', NULL, NULL, 273, NULL),
(492, '157-ew1x', 4, '157-ajtv-12-2024', 157, 'Siam Suroso', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-12-01 05:44:57', '2024-12-01 05:38:12', '2024-12-01 05:44:57', NULL, NULL, 270, NULL),
(493, '158-ykgs', 4, '158-cbwe-12-2024', 158, 'Tati Mulyati', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-12-01 05:41:58', '2024-12-01 05:41:45', '2024-12-01 05:41:58', NULL, NULL, 269, NULL),
(494, '159-oue7', 4, '159-56jy-12-2024', 159, 'Risna Suhertini', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-12-02 06:37:03', '2024-12-02 06:36:24', '2024-12-02 06:37:03', NULL, NULL, 285, NULL),
(495, '160-uxsw', 4, '160-efcg-12-2024', 160, 'Dewi Sutriani', 'ShaNet 0 (7 Mbps)', 100000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-12-02 08:04:09', '2024-12-02 08:03:49', '2024-12-02 08:04:09', NULL, NULL, 286, NULL),
(496, '161-plzd', 4, '161-wbt4-12-2024', 161, 'Mariati', 'ShaNet 2 (15 Mbps)', 150000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-12-04 07:13:58', '2024-12-04 07:13:47', '2024-12-04 07:13:58', NULL, NULL, 296, NULL),
(497, '162-g5hz', 4, '162-psum-12-2024', 162, 'Devi Sevtiawati', 'ShaNet 1 (10 Mbps)', 125000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-12-05 00:33:56', '2024-12-05 00:33:37', '2024-12-05 00:33:56', NULL, NULL, 304, NULL),
(498, '163-c5rf', 4, '163-m25d-12-2024', 163, 'Tatang Mulyana', 'ShaNet 2 (15 Mbps)', 150000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-12-07 19:53:58', '2024-12-07 19:53:41', '2024-12-07 19:53:58', NULL, NULL, 325, NULL),
(499, '164-jfls', 4, '164-caxq-12-2024', 164, 'Agus Hermanto', 'Basic 3 (30 Mbps)', 200000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-12-07 20:42:36', '2024-12-07 20:42:18', '2024-12-07 20:42:36', NULL, NULL, 326, NULL),
(500, '165-vxnn', 4, '165-qohj-12-2024', 165, 'Hendri Gunawan', 'Basic 2 (10 Mbps)', 150000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2024-12-07 23:13:54', '2024-12-07 23:13:42', '2024-12-07 23:13:54', NULL, NULL, 329, NULL),
(501, '167-obxs', 4, '167-trqd-12-2024', 167, 'Randika Nurfaizi', 'Basic 1 (10 Mbps)', 150000, 'Randika Nurfaizi 2', 'BL', NULL, NULL, '2024-12-30 06:50:29', '2024-12-30 06:50:29', NULL, NULL, NULL, NULL),
(502, '168-13fa', 4, '168-9tev-12-2024', 168, 'John Doe', 'ShaNet 2 (15 Mbps)', 200000, 'Randika Nurfaizi 2', 'BL', NULL, NULL, '2025-01-01 09:05:21', '2025-01-01 09:05:21', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `billing_periodes`
--

CREATE TABLE `billing_periodes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `month` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing_periodes`
--

INSERT INTO `billing_periodes` (`id`, `name`, `created_at`, `updated_at`, `year`, `month`) VALUES
(1, 'October 2024', '2024-09-30 22:58:49', '2024-09-30 22:58:49', '2024', 'October'),
(3, 'November 2024', '2024-11-01 19:13:55', '2024-11-01 19:13:55', '2024', 'November'),
(4, 'December 2024', '2024-12-01 03:04:45', '2024-12-01 03:04:45', '2024', 'December');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `paket_id` bigint(20) UNSIGNED NOT NULL,
  `mikrotik_id` bigint(20) UNSIGNED NOT NULL,
  `internet_service` varchar(255) DEFAULT NULL,
  `secret_id` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password_ppp` varchar(255) DEFAULT NULL,
  `ppp_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `simpleque_id` varchar(255) DEFAULT NULL,
  `simpleque_name` varchar(255) DEFAULT NULL,
  `ip_static` varchar(255) DEFAULT NULL,
  `mac_address` varchar(255) DEFAULT NULL,
  `interface` varchar(255) DEFAULT NULL,
  `arp_id` varchar(255) DEFAULT NULL,
  `address_list_id` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact` text DEFAULT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'true',
  `activation` enum('false','true') NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `primary_server` bigint(20) DEFAULT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `customer_id` varchar(255) DEFAULT NULL,
  `activation_date` timestamp NULL DEFAULT NULL,
  `caller_id` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `internet_service`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `simpleque_id`, `simpleque_name`, `ip_static`, `mac_address`, `interface`, `arp_id`, `address_list_id`, `address`, `contact`, `disabled`, `activation`, `created_at`, `updated_at`, `deleted_at`, `primary_server`, `discount`, `customer_id`, `activation_date`, `caller_id`, `user_id`) VALUES
(1, 'Muh Subhan', '100210121-at-subhan-evqx', '$2y$10$Tns8PO6nzj4HgOhdUl1AnOPdQ8toalTjitI7xtHVC4Hcs59OVUNfe', 'xranonymous.xr@gmail.com', 2, 1, 'ppp', '*2', '2021012101@subhan', '2021012101', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6281384729066', 'false', 'true', '2024-09-30 12:55:48', '2024-12-10 00:11:20', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, '2024-12-30 09:03:05', NULL, NULL, 0.00, NULL, NULL, NULL, 1),
(2, 'Iis Sugiarti', '100210212-at-iissugiarti-gsvh', '$2y$10$vYaFzeJPZi3.oyStjK0SPOZnkOKzH9WHcmDLWzXzmf0MyJi9yIAua', 'dikanurfaizi.rn@gmail.com', 2, 1, 'ppp', '*3', '2021021201@iissugiarti', '2021021201', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '087779954399', 'false', 'true', '2024-09-30 12:55:48', '2024-12-09 13:13:59', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, '2025-01-04 08:16:40', NULL, NULL, 1.00, NULL, '2024-10-01 04:10:08', NULL, 1),
(3, 'Asep Nasihin', '100210220-at-niaagustin-hjwc', '$2y$10$b4ycM5YZm3TWeLTDvFsvduN.L2DW0JNhYNQ2wLO9yaoQNfhso0eBa', NULL, 2, 1, 'ppp', '*4', '2021022001@asep', '2021022001', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '081321875971', 'false', 'true', '2024-09-30 12:55:48', '2024-12-09 13:15:05', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Pak Surya', '150210411-at-surya-fijt', '$2y$10$e2H6/W9HYHxUXPstfr.4w.p3z72k4yI137o/SWIDQZeTZYsxUL77G', NULL, 4, 1, 'ppp', '*5', '2021041101@surya', '2021041101', 5, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '081282400741', 'false', 'true', '2024-09-30 12:55:48', '2024-12-10 00:30:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Koesnadi', '150210712-at-koesnadi-wcwq', '$2y$10$RomzLgOI5/9xYGUdQnmEQOZU.Wol2QYzUomfTO..Jr25/nLbiyRgO', NULL, 4, 1, 'ppp', '*6', '2021071201@koesnadi', '2021071201', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '08974848114', 'false', 'true', '2024-09-30 12:55:48', '2024-12-10 00:36:28', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Cucu Suryani', '150211002-at-cucu-cx5l', '$2y$10$2NnspMYyHs7AqyQiHiAv/Ousoowh98pbKUst57sUWR.8kRo.0r3O6', 'innayahazizah00@gmail.com', 4, 1, 'ppp', '*7', '2021100201@cucu', '2021100201', 5, 'Kp. Sukamaju RT 009/007 No. 37 Ciseureuh Purwakarta', '082299409442', 'false', 'true', '2024-09-30 12:55:49', '2024-12-10 00:29:00', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Sumarni', '100220430-at-dinda-aunf', '$2y$10$rZRuJFOA2e0XH.lS.EXSceuR9fcahfpEkd4h.mRJvZPppFbLQH1KS', 'sum402791@gmail.com', 2, 1, 'ppp', '*8', '2022043001@sumarni', '2022043001', 5, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '089531041230', 'false', 'true', '2024-09-30 12:55:49', '2024-12-09 13:16:26', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Teh Darsih', '100220918-at-darsih-esil', '$2y$10$JsVycMEZpicybGDKZmgxyuAeZPccRqPEyVyqnv3bWfLAW0NgN5ZIW', NULL, 2, 1, 'ppp', '*A', '2022091801@darsih', '2022091801', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '085798509660', 'false', 'true', '2024-09-30 12:55:49', '2024-12-09 13:17:25', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'Pak Eko', '100221009-at-eko-tlec', '$2y$10$VC93qjb2uStD9QVNe2uSl.aZYLQu4VvpRzVS1o9E9/rvDxUZtDYYW', NULL, 2, 1, 'ppp', '*B', '2022100901@eko', '2022100901', 5, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '0895360111123', 'false', 'true', '2024-09-30 12:55:49', '2024-12-09 13:18:26', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'Ali Akbar', '100221020-at-ustdali-24ay', '$2y$10$T7KK1mLY44Kms8/CPF0dFOLtuaFYvYrSUqvlz/Jm4JT/G9W62jrjC', NULL, 2, 1, 'ppp', '*D', '2022102001@ustdali', '2022102001', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '083836212877', 'false', 'true', '2024-09-30 12:55:49', '2024-12-09 13:36:15', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'Rita Susanti', '100221029-at-rita-xrci', '$2y$10$TUepkxXfJZY7tOwpq8Jmvu7eBLlbaA4IrnffYJFlDlzAUSzjMkWzS', NULL, 2, 1, 'ppp', '*F', '100221029@rita', '100221029', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '085759233749', 'false', 'true', '2024-09-30 12:55:50', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'Pak Suud', '150240118-at-suud-n4gt', '$2y$10$N2Ez7t.HXLE.ZuuM3yjINumdsKfpGI333v0E0vf5254JhrJTRD5lG', NULL, 4, 1, 'ppp', '*10', '2024011801@suud', '2024011801', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '62818209745', 'false', 'true', '2024-09-30 12:55:50', '2024-12-09 12:24:55', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'Mamah Anggi', '100221107-at-anggi-5jgx', '$2y$10$R4sXPo7qm5wUmr4Xkc0Hfe.55OT1mpA2P6Xv7U/xBE4jGsEvYpmBW', NULL, 2, 1, 'ppp', '*12', '2022110701@anggi', '2022110701', 5, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '08995430767', 'false', 'true', '2024-09-30 12:55:50', '2024-12-09 13:41:48', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Ai Solihah', '150221120-at-adhiga-dtrv', '$2y$10$Vl5pJniag./lYDHyRUzPZeIR0j163e68Nfr1d0lTu.aUs4yvZurVq', NULL, 4, 1, 'ppp', '*14', '2022112001@adhiga', '2022112001', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '085220677026', 'false', 'true', '2024-09-30 12:55:50', '2024-12-10 00:27:39', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Armiyanti', '200211114-at-armiyanti-6zyz', '$2y$10$l072gt6yQ9qHlmRRllm9iOoYLe/9Lh1wLqbEsuqkV5MylbUToR0.m', NULL, 5, 1, 'ppp', '*15', '2021111401@armiyanti', '2021111401', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '081385195803', 'false', 'true', '2024-09-30 12:55:50', '2024-12-09 00:06:43', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'Riska Azizah', '100230102-at-rdcell-yk5h', '$2y$10$wCAvavoTUm4SVOTpUe0ZweHuS3XFPthkwly8SXRMZaCF9wO7TCb.q', NULL, 2, 1, 'ppp', '*18', '2023010201@rizka', '2023010201', 5, 'Perum Griya Asri Blok K7 No. 7 RT 010/011 Ciseureuh Purwakarta', '085647808733', 'false', 'true', '2024-09-30 12:55:51', '2024-12-10 00:18:43', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'Iip', '150230402-at-iip-ijky', '$2y$10$85gBTUGVRMAQuXKOyn4srOzz0Ab9viG87sjE3C1/WR8tjf0LC4VgK', NULL, 4, 1, 'ppp', '*1B', '2023040201@iip', '2023040201', 5, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '08382843852', 'false', 'true', '2024-09-30 12:55:51', '2024-12-09 23:56:32', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Ahmad Saepuloh', '150230408-at-ahmadraja-p5t1', '$2y$10$dLvva7iTiueomOstGDkSaePyBNhGoD0fCkkhEXH/XqHLg5VKrtOqC', 'ahmadsae13@gmail.com', 4, 1, 'ppp', '*1C', '2023040801@ahmadraja', '2023040801', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '089691150846', 'false', 'true', '2024-09-30 12:55:51', '2024-12-09 23:55:24', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Pak Lili', '150230503-at-lamjaya-7cfl', '$2y$10$Ibtu3T9NpzTCFrHYM4s0ReB4/.FkHPqRMPc2AOXrTfZqWuO0BXG8i', NULL, 4, 1, 'ppp', '*1E', '2023050301@lamjaya', '2023050301', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '087825791053', 'false', 'true', '2024-09-30 12:55:51', '2024-12-09 23:53:42', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'Ibu Ayu', '150230717-at-ibuayu-3mqu', '$2y$10$/WFlOfeT6zJIliuX0OFQleR.MoyRzsqUk1.e.xtL0bkcCDWW.c1Q.', NULL, 4, 1, 'ppp', '*20', '2023071701@ibuayu', '2023071701', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '087779845917', 'false', 'true', '2024-09-30 12:55:52', '2024-12-09 23:52:05', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'Ratih Aisyah', '100230729-at-jojo-0bau', '$2y$10$OZdoJ9pnDlnI3.yrHM5iD.5DOLR1LXimifuGiR2gdaQA9W8gwqTOq', NULL, 2, 1, 'ppp', '*22', '2023072901@ratih', '2023072901', 5, 'Jl. Veteran RT 003/002 Ciseureuh Purwakarta', '0895405332171', 'false', 'true', '2024-09-30 12:55:52', '2024-12-10 00:38:36', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'AA Ramli', '150230804-at-rafka-ee5h', '$2y$10$xYLrRSRSUltqiE6Hgs8Z6en7fq16oJjix.3C9GSE7fEFEl0Kt77hC', NULL, 4, 1, 'ppp', '*23', '2023080401@rafka', '2023080401', 5, 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', '085210226726', 'false', 'true', '2024-09-30 12:55:52', '2024-12-09 23:48:49', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, '2025-01-01 09:00:54', NULL, NULL, 0.00, NULL, NULL, NULL, 2),
(24, 'Neni Nurhaeni', '150231101-at-neni-irxa', '$2y$10$GXH1GwFxXKW79sX2S0Nh1.KPlvBElBBZyFYexe/H60/1WWIMrFBvu', 'neninurhaeni1990@gmail.com', 4, 1, 'ppp', '*2A', '2023110101@neni', '2023110101', 5, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '089684514949', 'false', 'true', '2024-09-30 12:55:52', '2024-12-09 23:20:05', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 'Etik Aprilia Ratna Sari', '150231115-at-heriyanto-4rwf', '$2y$10$hSS2kKR3SdvIT9Z2IkRMfe7tcQJPMsev5YndP8NQR.p.7hLkhVq42', 'etikaprilia8@gmail.com', 4, 1, 'ppp', '*2C', '2023111501@aprilia', '2023111501', 5, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '085727463177', 'false', 'true', '2024-09-30 12:55:52', '2024-12-09 23:17:38', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 'Narsiah', '075230101-at-meisya-kqgp', '$2y$10$A0J2OD9jcrwpJ7omjFyjje/WbxQlwCtWYNm5nyEypuGjk38WfLsny', 'narsiiahnay@gmail.com', 2, 1, 'ppp', '*2D', '2023010101@narsiah', '2023010101', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6289608997773', 'false', 'true', '2024-09-30 12:55:52', '2024-12-10 00:13:10', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'Novi Eka Purwaningsih', '100231205-at-novi-qoy2', '$2y$10$zkcDZQ5NhzP3K9Cki9cisuT/2rFf2B8NGYPs0bvsyiFzz/0XloS3q', NULL, 2, 1, 'ppp', '*2E', '2023120501@novi', '2023120501', 5, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '083892406496', 'false', 'true', '2024-09-30 12:55:53', '2024-12-10 00:20:30', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'Hasma Wati', '100231210-at-hasma-dmlc', '$2y$10$0JrkINervzXjGBFMLYDRZ.pMhrMFP3pFz1sXAfH5wq//SVoIOb7Yi', NULL, 2, 1, 'ppp', '*2F', '2023121001@hasma', '2023121001', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '081808073633', 'false', 'true', '2024-09-30 12:55:53', '2024-12-10 00:23:05', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'Euis Ratnasari', '150231216-at-euis-nbhl', '$2y$10$Kx4uTvk4BAPBAWPdTEmWj.bCQOKLIz2h3Z.bYgpu1wOxkQJQhJcee', 'euisratnasari@gmail.com', 4, 1, 'ppp', '*30', '2023121601@euis', '2023121601', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '089698432244', 'false', 'true', '2024-09-30 12:55:53', '2024-12-09 23:13:20', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 'Adi Amoy', '100231217-at-amoy-smqx', '$2y$10$m2965pW/9cPJMi4OI1fLWukEOG54h3UgLfsflWIGnDDvybpAEdJ.W', NULL, 2, 1, 'ppp', '*31', '2023121701@amoy', '2023121701', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '085715468128', 'false', 'true', '2024-09-30 12:55:53', '2024-12-10 00:25:37', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 'A Zaky Alattas', '150240211-at-pakzaky-xwld', '$2y$10$JHIE7fRFx4fNYvT3TNalOuSgQd8oMvGEGNltalWVaJC7hgs0aRfua', NULL, 4, 1, 'ppp', '*3D', '2024021101@pakzaky', '2024021101', 5, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '08111972299', 'false', 'true', '2024-09-30 12:55:53', '2024-12-09 13:10:33', NULL, 'Jl. Test', '6285156573596', 'false', 'true', NULL, '2025-01-01 09:29:13', NULL, NULL, 0.00, NULL, NULL, NULL, 2),
(32, 'Teh Ai', '100240219-at-tehai-posk', '$2y$10$RQDYsMUunaPZrlizveJyF.ND6BFScaya8PenyQUkFA/xxYurqviAu', NULL, 2, 1, 'ppp', '*42', '2024021901@tehai', '2024021901', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '0895405325313', 'false', 'true', '2024-09-30 12:55:54', '2024-12-10 03:01:18', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 'Pak Slamet', '150240311-at-slamet-numk', '$2y$10$6UT81J/qEug2tuiBDJwMwOty0bxS53nGXzl.lbYRHhsTqdBpC53A6', NULL, 4, 1, 'ppp', '*4F', '2024031101@slamet', '2024031101', 5, 'Jl. Sukamaju RT. 009/007 Kelurahan Ciseureuh', '089671475636', 'false', 'true', '2024-09-30 12:55:54', '2024-12-09 13:02:21', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 'Debbie Febriana', '170231220-at-iburuli-wldt', '$2y$10$KtfMy2hf511pH7wGHDKQfuFwyBjdTcbTmYk3enq3qjUDbndZ6pOj2', 'fdebi707@gmail.com', 4, 1, 'ppp', '*51', '2023122001@debbie', '2023122001', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '083862780951', 'false', 'true', '2024-09-30 12:55:54', '2024-12-09 23:07:45', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 'Hafis', '100221019-at-hafis-zgcg', '$2y$10$WYeq3GAJj64RmP.O0cK8IOAm7Io7e3vjYzu7RVd5S5BMrfTNk/Fr2', NULL, 2, 1, 'ppp', '*61', '2022101901@hafis', '2022101901', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '08818253308', 'false', 'true', '2024-09-30 12:55:54', '2024-12-09 13:33:14', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 'Triyanti', '200240417-at-yanti-bmum', '$2y$10$aGK1z3uCDe52sstaCjVGtu3ldkolqxiL4bbxM/7xqkWkMB0RMEwaC', 'zakipwk2144@gmail.com', 5, 1, 'ppp', '*65', '2024041701@yanti', '2024041701', 5, 'Gg. Beringin RT. 077/008 Nagrikaler Purwakarta', '083124358737', 'false', 'true', '2024-09-30 12:55:54', '2024-12-08 07:37:43', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'Yuan Firmansyah', '150240125-at-warungbali-haqj', '$2y$10$gRxBjL9tLQfJwYUwHTgO0O5GhqVc7DcR/rHgusO8FGL4YliAY8ega', NULL, 4, 1, 'ppp', '*6E', '2024012501@yuan', '2024012501', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '085793309918', 'false', 'true', '2024-09-30 12:55:55', '2024-12-10 03:03:25', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'Dede Suherman', '100210120-at-hexsa-1dnj', '$2y$10$pwvlYfcfOYA6lCSiTKW3te5SeB369HBm7J2F2H72931SpcpOcCTPe', 'djhonexsekutipmuda41@gmail.com', 2, 1, 'ppp', '*70', '2021012001@dede', '2021012001', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '081909404442', 'false', 'true', '2024-09-30 12:55:55', '2024-12-09 13:12:28', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'Ida Siti', '100240501-at-siti-vsgv', '$2y$10$q.E8uVZhjEIgyegs/EMMq.K2/l4DYlI2vsiGVqMvlPM/ZtgHlnhdi', NULL, 2, 1, 'ppp', '*71', '2024050101@siti', '2024050101', 5, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '082210830975', 'false', 'true', '2024-09-30 12:55:55', '2024-12-10 03:04:49', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'Wandi Suhendi', '100231112-at-wandi-becg', '$2y$10$QGs0JyG3YMJqJ1j.i7VvEe.0yjBadLLT78Zjh9cnUQvfC11Wcpqse', NULL, 2, 1, 'ppp', '*72', '2023111201@wandi', '2023111201', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '087779838230', 'false', 'true', '2024-09-30 12:55:55', '2024-12-10 03:00:08', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'Muhammad Fahrizal', '100240503-at-rizal-a8vw', '$2y$10$igl4UtY5f8OqNl4jA5wpWuC.BkbRO86KfN.OVYaR9GbaYAzU5ypcO', 'muhammadfahrijal707@gmail.com', 4, 1, 'ppp', '*75', '2024050301@fahrizal', '2024050301', 5, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '6283135700855', 'false', 'true', '2024-09-30 12:55:55', '2024-12-09 12:58:58', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, 'Edi Suswanto', '150240510-at-pakedi-d1ew', '$2y$10$C4PulOYkH06Ys7X5MX6H3u6nhwPNu9oofRK6PiSlpElA8HHXmEV5K', NULL, 4, 1, 'ppp', '*76', '2024051001@pakedi', '2024051001', 5, 'Kp. Sukamaju RT 009/007 Green Sapir Kelurahan Ciseureuh', '081904094579', 'false', 'true', '2024-09-30 12:55:56', '2024-12-09 12:57:13', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'Tri Nurhidayati', '150221217-at-furqon-59at', '$2y$10$qqoHq3wBaArJBbUVvKBzX.7rI65vKh0jI5RUxtFrbHxT8WfE.FXRK', 'trinur0501@gmail.com', 4, 1, 'ppp', '*78', '2022121701@nurhidayati', '2022121701', 5, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '089698033060', 'false', 'true', '2024-09-30 12:55:56', '2024-12-10 00:04:05', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, 'Pak Aam', '150221106-at-aam-skhy', '$2y$10$ueXEO54WLoeOWhVihXPiRe5LrNudhEK//X4zjZcvbZQ2FiLZcUQ5.', NULL, 4, 1, 'ppp', '*79', '2022110601@aam', '2022110601', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '087719301665', 'false', 'true', '2024-09-30 12:55:56', '2024-12-10 00:00:24', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'Rohani Rusmiati', '150210312-at-sikefi-x5vs', '$2y$10$pfFhyVIg7AX9q/sjIiLnBepiSV2QWlIejOiT/wdNxyIjm13FsbGuG', 'kebulisikefipurwakarta@gmail.com', 4, 1, 'ppp', '*7A', '2021031201@rohani', '2021031201', 5, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '087722273673', 'false', 'true', '2024-09-30 12:55:56', '2024-12-10 02:28:16', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 'Mulyana', '150231029-at-mulya-fvgl', '$2y$10$StZ0gNA3sPfGhNx6kwmQg.vaSFZqDeFJL66/jAn0LLKjsidCaNFf6', 'd_ardi@ymail.com', 4, 1, 'ppp', '*7C', '2023102901@mulya', '2023102901', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '083817367818', 'false', 'true', '2024-09-30 12:55:56', '2024-12-09 23:45:33', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'Sri Sulastri', '150240710-at-sulastri-mfvp', '$2y$10$HsnDqCc6SqSJj8oA.3hIC.QQZOP1zh21CqegXROje.R7KD8SpamAS', NULL, 4, 1, 'ppp', '*89', '2024071001@sulastri', '2024071001', 5, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '085624127735', 'false', 'true', '2024-09-30 12:55:57', '2024-12-09 12:20:21', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'Hendrizal T (Geprek Jogja)', '150240621-at-miayamgeprek-7lfy', '$2y$10$wz0lPs5WAcGhkGAcupTAfewqQ6GKf4e/Ccyv63mTNLXKvrOOUy.0K', NULL, 4, 1, 'ppp', '*8B', '2024062101@hendrizal', '2024062101', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6287820284786', 'false', 'true', '2024-09-30 12:55:57', '2024-12-10 03:28:57', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 'Ferry Kurniawan', '150230725-at-ustdfery-sxed', '$2y$10$T6AAoPBPu9psOtR.8fLn4.8r44ywcMQvzuiKqzdfop7KX.S9uxaE.', NULL, 4, 1, 'ppp', '*8C', '2023072501@ustdfery', '2023072501', 5, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '087825303864', 'false', 'true', '2024-09-30 12:55:57', '2024-12-09 23:50:39', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, 'Nok Reni', '150240711-at-tehenok-k0f3', '$2y$10$7/keG7v/z7zuW0JKCYWM1.1/AyktC.a8ZnXyklyI4p6XTm/BcyVLu', NULL, 4, 1, 'ppp', '*8D', '2024071101@nokreni', '2024071101', 5, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '083175503164', 'false', 'true', '2024-09-30 12:55:57', '2024-12-09 12:18:54', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'Ahmad Sugianto', '150240725-at-ahmad-ybf5', '$2y$10$w7EuRmCTmE6J9VgfRUwd8OsAUAgfMkzFqwBqcUX6/b4Z6kxvn.r8e', NULL, 4, 1, 'ppp', '*93', '2024072501@ahmad', '2024072501', 5, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '087860734025', 'false', 'true', '2024-09-30 12:55:57', '2024-12-09 12:16:21', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 'Maharani Widyaningrum', '150230913-at-maharani-twro', '$2y$10$c2kg2MYWp8Yu5cRnfcKG0uAeqJoxwZMi61pZi4kFaMnn/WsF5jnbm', NULL, 4, 1, 'ppp', '*96', '2023091301@maharani', '2023091301', 5, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '081912512312', 'false', 'true', '2024-09-30 12:55:58', '2024-12-09 23:47:13', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 'Linda', '120240310-at-linda-xfw7', '$2y$10$h4oUcZ.jxvVV6WLl76tGKuNC8ttA9ICiji4G8gs.Xxusk9xjMOYIC', NULL, 3, 1, 'ppp', '*97', '2024031001@linda', '2024031001', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '083853508908', 'false', 'true', '2024-09-30 12:55:58', '2024-12-10 02:35:41', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 'Agista Utari', '100240225-at-agis-coa7', '$2y$10$afF.elelamWV5JfAXz1/ae8ebnS/re1ZhIcGtyEkF35AykhF1tKVK', 'utariagista@gmail.com', 2, 1, 'ppp', '*98', '2024022501@agis', '2024022501', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '083113359456', 'false', 'true', '2024-09-30 12:55:58', '2024-12-10 00:40:17', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'Agustian Setiadi', '150240807-at-nayunda-vahg', '$2y$10$j3zb9D2SAx4EstIrFwRVFetKDGW4TPut8uuYtzYVIWc.fBzRKWvaW', 'agustiansetiadi703@gmail.com', 4, 1, 'ppp', '*99', '2024080701@agustian', '2024080701', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '085285092449', 'false', 'true', '2024-09-30 12:55:58', '2024-12-09 12:22:55', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'Pak Iyan', '200230910-at-iyan-nsls', '$2y$10$y/fzHfMTsKJJDQW13O5QdOn0S294UuPDGDvVV/Cqtl3PqOBhcF3ym', NULL, 5, 1, 'ppp', '*9A', '2023091001@iyan', '2023091001', 5, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '087879990597', 'false', 'true', '2024-09-30 12:55:58', '2024-12-09 00:05:07', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, 'Nur Fakriah Putri', '150240811-at-nurfakriah-wkj0', '$2y$10$nfEMj3YP8HGZWVmWwj4pnO.X1ZFidQz9LeF2n7qU4cuHiugkz6puC', 'nurfakriahp@gmail.com', 4, 1, 'ppp', '*9B', '2024081101@nurfakriah', '2024081101', 5, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '0882000336411', 'false', 'true', '2024-09-30 12:55:59', '2024-12-09 12:37:58', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'Pak Jepri', '150240612-at-jepri-np2z', '$2y$10$2Kg/JofVjcaYfFpanGDm7ey8YxBQ1x54D8FFJrbzOzj5twRYMjxim', NULL, 4, 1, 'ppp', '*9D', '2024061201@jepri', '2024061201', 5, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '082125598764', 'false', 'true', '2024-09-30 12:55:59', '2024-12-09 12:50:17', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, 'Bu Ani', '150240817-at-ibuani-rlmf', '$2y$10$U4Sqhf2I5I79OvHctXDrSOG9zebM0XOKR./1ZNcR.p8yDeDpV7tyG', NULL, 4, 1, 'ppp', '*9F', '2024081701@ibuani', '2024081701', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '087794516139', 'false', 'true', '2024-09-30 12:55:59', '2024-12-09 12:06:59', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, 'Yeyet Abdulrachman', '250230601-at-abdulrahman-pvrj', '$2y$10$wTafupVFxz5xsPNj1JyJ.OmrbsJDJsFZssGgGkXrGXLEsbl2mnywC', NULL, 6, 1, 'ppp', '*A0', '2023060101@abdulrahman', '2023060101', 5, 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', '087879678412', 'false', 'true', '2024-09-30 12:55:59', '2024-12-08 07:40:59', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, 'Pak Dendi', '150240824-at-dendi-86fd', '$2y$10$cjyyQ7xQXeiITQe1LcXlze0Jaq6ZgFbPu.DTJgJrXiqlhZnhuIlwa', NULL, 4, 1, 'ppp', '*A2', '2024082401@dendi', '2024082401', 5, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '081385192239', 'false', 'true', '2024-09-30 12:56:00', '2024-12-09 11:20:13', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 'Marya Ningsih', '100240901-at-bunehe-zqpe', '$2y$10$Aa5gGs/UAuUM2YPZYYdsweq0qRwSsZqEFGW1eEgbihwQpW/cIAL2e', 'maryaningsih@gmail.com', 2, 1, 'ppp', '*A4', '2024090101@ningsih', '2024090101', 5, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '087840530823', 'false', 'true', '2024-09-30 12:56:00', '2024-12-10 02:46:32', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, 'Pak Kurdi', '150240901-at-pakkurdi-glwd', '$2y$10$OdrngqvUr7pmpiEh1qb/c.LQXGiwWr00CsEDP.LK1G9RJOVI1qeoa', NULL, 4, 1, 'ppp', '*A5', '2024090101@pakkurdi', '2024090101', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '082217713000', 'false', 'true', '2024-09-30 12:56:00', '2024-12-09 04:22:31', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'Kodrat (GOR ADHIGA)', '150240305-at-goradhigha-dbai', '$2y$10$BNAI6fljFbwxR.FZlqkLnuderFWkQp9Lh1RrrYc3CY6GmaSHtbb2y', NULL, 4, 1, 'ppp', '*A6', '2024030501@goradhigha', '2024030501', 5, 'Jl. Sukamaju RT. 009/007 Kelurahan Ciseureuh', NULL, 'false', 'true', '2024-09-30 12:56:00', '2024-12-09 23:05:21', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'Lulu Andriani', '150240908-at-lulu-s8ho', '$2y$10$xRkYdz0D2w.RoffP2it7WO4ritKEWwUtlgWuJlp0JIDJ58XgtxI5a', NULL, 4, 1, 'ppp', '*A7', '2024090801@lulu', '2024090801', 5, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6283103801464', 'false', 'true', '2024-09-30 12:56:00', '2024-12-11 11:32:26', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, 'Ma Aisah', '150240908-at-aisah-13j1', '$2y$10$4uqbm.E521ybA29HyjEmnOCUzY/6gRMM4pxLuBrPqpbVNFKLguFHy', NULL, 4, 1, 'ppp', '*A8', '2024090802@aisah', '2024090802', 5, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '0895806314100', 'false', 'true', '2024-09-30 12:56:01', '2024-12-09 00:10:38', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, 'Alya Iklima Hidayah', '150240908-at-alhikam-c8c2', '$2y$10$HAr5GZgSNxXuui6aibSq2O/eM6zHWk4A4gEiLHqB2dr1TxUZb1ou6', 'alyaiklima@gmail.com', 4, 1, 'ppp', '*A9', '2024090803@alhikam', '2024090803', 5, 'Perum Panorama Indah B2-4 RT 013/013 Purwakarta', '085926103222', 'false', 'true', '2024-09-30 12:56:01', '2024-12-09 00:12:57', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 'Pak Kurdi 2', '150240912-at-pakkurdi-3euz', '$2y$10$oPigjP2IgIxbWkch5zxTou5QZ7HxMt0mjXPmk9LEi1vRMT4W5VfDy', NULL, 4, 1, 'ppp', '*AC', '2024090102@pakkurdi', '2024090102', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', NULL, 'false', 'true', '2024-09-30 12:56:01', '2024-12-09 04:24:33', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 'Teh Enen', '100240917-at-enen-u7xn', '$2y$10$IojVi5rPNe0AGgoCRU1rme1zaqcca5EmDNCSjgu0IJ22VqeQLfXF6', NULL, 2, 1, 'ppp', '*AD', '2024091701@enen', '2024091701', 5, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '087879788949', 'false', 'true', '2024-09-30 12:56:01', '2024-12-10 02:58:01', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 'Rina Konter BMCell', '100240715-at-bmcell-xe7d', '$2y$10$z466HiVVDZ4uvZlATioSBuQooJahSsMJZB15bec2gfq9bY1JsOzfS', NULL, 2, 1, 'ppp', '*AE', '2024071501@bmcell', '2024071501', 5, 'Jl. Sukamaju RT. 005/007 Ciseureuh Purwakarta', NULL, 'false', 'true', '2024-09-30 12:56:01', '2024-12-10 02:49:39', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 'Asep Topik Hidayat', '100240922-at-asep-hw73', '$2y$10$wB1RxbDa5RO6Ywh2fjmuFuGJC2aOKEPT5Bkr6v20lujh/LB8n4NKu', NULL, 2, 1, 'ppp', '*AF', '2024092201@topik', '2024092201', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '081992515445', 'false', 'true', '2024-09-30 12:56:01', '2024-12-10 02:40:40', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, 'Egy Laksma Suandi', '150240924-at-egy-zndc', '$2y$10$ydDsC1cUoXRphkUkk9t74eNil.z8pphCf1nH.qJtNIZ/JXy131xk6', NULL, 4, 1, 'ppp', '*B0', '2024092401@egy', '2024092401', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '089635533013', 'false', 'true', '2024-09-30 12:56:02', '2024-12-09 04:20:34', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, 'H. M Abu Rasyid. AS. SH', '300230902-at-cahayaqolbu-xkqe', '$2y$10$lnSw0HVTrpC.p4vA5GSPG.s0gubinyGAxRbjZDU7yagmuoufbxFIO', NULL, 5, 1, 'ppp', '*6D', '2024110101@cahayaqolbu', '2024110101', 5, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '081316153848', 'false', 'true', '2024-10-01 10:13:45', '2024-12-08 07:45:27', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, 'Nazri Nasier', 'nazri-14yg4l', '$2y$10$0Ge3w0n6yb8y7ojVRdt5gOs5W/9ypkwF7jZ7thSQ9IqV81Hgjs7g2', 'nasiernazri@gmail.com', 4, 1, 'ppp', '*B5', '2024100401@nazri', '2024100401', 5, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '085210433997', 'false', 'true', '2024-10-04 12:20:17', '2024-12-09 04:10:51', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, 'Moh. Gian Firtyanza', 'moh-qdm7pn', '$2y$10$ATMOpS2HTifv8Ra3uZR8neIZsdF20pk3kIn5UifjrIjwWnNkfQvpa', NULL, 4, 1, 'ppp', '*B7', '2024100901@gian', '2024100901', 5, 'PerumGriya Asri Blok D2 No. 15 Ciseureuh Purwakarta', '082122332067', 'false', 'true', '2024-10-09 20:04:42', '2024-12-09 12:14:33', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(79, 'Intan Ayu Sri Wahyuni', 'intan-vmvffx', '$2y$10$qBQNXfdHxeXUf9T.R8GE/.c6/aHPqMpfz2ocpwzurb0ULOww5NEOC', 'can.cc660@gmail.com', 4, 1, 'ppp', '*B8', '2024101701@intan', '2024101701', 5, 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', '6289661700462', 'false', 'true', '2024-10-17 12:25:35', '2024-12-09 04:05:50', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(80, 'Nurhayati', 'nurhayati-o6xht7', '$2y$10$A7K./Yu5M5j3OkkHXlJ0ROGZcwBtj6s5clG6D955m7aMxJoPO0Vby', 'nurhayati@shanetfiber.my.id', 4, 1, 'ppp', '*B9', '2024102001@nurhayati', '2024102001', 5, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '089510273231', 'false', 'true', '2024-10-20 19:25:50', '2024-12-09 04:02:00', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(81, 'Irvan Mohammad Regent', 'irvan-rbchni', '$2y$10$rxBs1QY7fuS5S/x5lak2wegdZtEXLH06oKD3.jhqIVVXAnCJAU.pe', 'irvanregent@gmail.com', 4, 1, 'ppp', '*BA', '2024102701@irvan', '2024102701', 5, 'Perum Griya Asri Blok K1 No. 10 Ciseureuh Purwakarta', '089622707203', 'false', 'true', '2024-10-27 09:52:51', '2024-12-09 01:01:37', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(82, 'Nuryani', '125230606-at-yance-jhtl', '$2y$10$MvWFfR4J/gwK04bUL/oY3O4hJ4WJw3HWccZlcxww0ZnjBBGLHMO.G', NULL, 21, 18, 'ppp', '*2', '125230606@yance', '125230606', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '085213732627', 'false', 'true', '2024-10-28 20:10:52', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, 'Anggi', '200240420-at-anggi-5rzv', '$2y$10$Tiwa5s6OKvynrkAsMxgomuGvTxf33be8R1T15I.zeebfVyhmYKRRK', NULL, 23, 18, 'ppp', '*3', '200240420@anggi', '200240420', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '081399008999', 'false', 'true', '2024-10-28 20:10:53', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, 'Siti Mariam', '125240427-at-amar-qnsq', '$2y$10$D4oMKh4yq.DXLFEkXXSJyO2.zmow/jF0GcApO3GTI6iih3cR6/nrm', NULL, 21, 18, 'ppp', '*9', '125240427@amar', '125240427', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '089509783282', 'false', 'true', '2024-10-28 20:10:55', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(86, 'Desi Nurdianti', '125240427-at-salim-zw1g', '$2y$10$Xs5ERAiUciuBS.nrMOAppOaVjtLM25LD0t4An7lvaVf9jubNY0P9y', NULL, 21, 18, 'ppp', '*A', '125240427@salim', '125240427', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '083827502565', 'false', 'true', '2024-10-28 20:10:56', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, 'Iis Ismawati', '125240427-at-iis-ptfu', '$2y$10$s3DUDonAw7Zx0qnu3WCq5Oirmopx6rnbXrMbnJs9XAb6vh78LhE1K', NULL, 21, 18, 'ppp', '*B', '125240427@iis', '125240427', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '0895374776212', 'false', 'true', '2024-10-28 20:10:56', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, 'Imar Sulaeman', '125240428-at-sulaeman-tckb', '$2y$10$aiBoTEdg5Q7hElazd2kpvO5y5CTxqmuszNaBwemyrXt.ywl2j0lfq', 'imarsulaeman04@gmail.com', 21, 18, 'ppp', '*C', '125240428@sulaeman', '125240428', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '085210763586', 'false', 'true', '2024-10-28 20:10:57', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(89, 'Adis', '125240428-at-adis-7mbq', '$2y$10$3gQ6VsxCSyZIlJLo7hctdu4b1z3.jVq4MNt8y7ZyLodatU1jaVA4a', NULL, 21, 18, 'ppp', '*D', '125240428@adis', '125240428', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '087869634616', 'false', 'true', '2024-10-28 20:10:58', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(90, 'Satim Silegar', '125240428-at-paksatim-fhoc', '$2y$10$Y6FEqMyFK6cOc.7jOvygkOe9uEfdUZDdEVO15KPJNeLc3FHhlFF6W', 'susisusilaw1410@gmail.com', 21, 18, 'ppp', '*E', '125240428@paksatim', '125240428', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '085943092738', 'false', 'true', '2024-10-28 20:10:59', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(91, 'Heni', '125240430-at-heni-fdu7', '$2y$10$09VrjCiDq6UVV8pA7j0d/u3L3gVc3JTx5q5ahAS6h0yrGjPebLyAO', NULL, 21, 18, 'ppp', '*F', '125240430@heni', '125240430', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '087836276215', 'false', 'true', '2024-10-28 20:11:00', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, 'Juju Juarsih', '125240501-at-juju-vrc5', '$2y$10$ImC3CShS7WSWkUdMDGLsZ.H2SuYnW5mdFjN2RIUgY1mzGd/moPwFy', NULL, 21, 18, 'ppp', '*10', '125240501@juju', '125240501', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '089698054300', 'false', 'true', '2024-10-28 20:11:01', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(93, 'Deni', '125240501-at-deni-pn9s', '$2y$10$gh92qR0w0zVWnuuXNTbbMuOGpQ7rkYgFCsqAv06n5uo45aGgKdQFG', NULL, 21, 18, 'ppp', '*11', '125240501@deni', '125240501', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '0881011228286', 'false', 'true', '2024-10-28 20:11:02', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(94, 'Julian Ratu Malik Kusum', '125240504-at-ratu-rj1m', '$2y$10$Un/6xmcZzOW3.dMbA7eBB.1LDd4w9j6Nu4u8h6Zt3AwM4bzWYezdm', NULL, 21, 18, 'ppp', '*13', '125240504@ratu', '125240504', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '0859109735399', 'false', 'true', '2024-10-28 20:11:03', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, 'Rosidah', '125240420-at-rosidah-goas', '$2y$10$L/t8dn/JvyAA91dQxH.fbenCI7l1NbzNhrZE3gchtHceWLbyRO46K', NULL, 21, 18, 'ppp', '*14', '125240420@rosidah', '125240420', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '089652111141', 'false', 'true', '2024-10-28 20:11:04', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, 'Cici', '125240509-at-cici-urae', '$2y$10$Yk7SxBqeDCSuSorsw0PfpOsqGpSQkULn4BoSjZTm/CsDjz9LJf9yG', NULL, 22, 18, 'ppp', '*15', '125240509@cici', '125240509', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6287828059654', 'false', 'true', '2024-10-28 20:11:05', '2024-12-09 11:27:19', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, 'Pepen', '125240510-at-pepen-yog8', '$2y$10$UeL2OwJpKT8YLRsD3uqP.u6o9747gPeIZ.ci0OhJDRdNTiTxsjpk2', NULL, 21, 18, 'ppp', '*16', '125240510@pepen', '125240510', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '082123801066', 'false', 'true', '2024-10-28 20:11:06', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, 'Wayanto', '125240510-at-wayanto-rtx1', '$2y$10$C7U78dsQgfBMxrT9yOblYudkB.IX.D9SB7ASeAmddG5cl7hhSzWMO', NULL, 21, 18, 'ppp', '*17', '125240510@wayanto', '125240510', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '089529839040', 'false', 'true', '2024-10-28 20:11:07', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, 'Miung', '125240514-at-miung-xzds', '$2y$10$lWix3uKL/di3gKOn2YNUR.MhVWdfxxa8uDylMWiRpt.U3SKvAg.tO', NULL, 21, 18, 'ppp', '*18', '125240514@miung', '125240514', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '087794377878', 'false', 'true', '2024-10-28 20:11:08', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 'Dadan Hamdani', '125240514-at-rtahong-t5gm', '$2y$10$omiJROYGdlaI0Q6glB9V1ufHH.vBIvgOAZvYJPOiVOaYdFJrPx1nW', NULL, 21, 18, 'ppp', '*19', '125240514@rtahong', '125240514', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '081399714844', 'false', 'true', '2024-10-28 20:11:09', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 'Sule', '125240514-at-sule-pglq', '$2y$10$2D9vBMc2ZTXRcwDV8aT2reflqgAKEvybzR/edslo4krmA372ovS8i', NULL, 21, 18, 'ppp', '*1A', '125240514@sule', '125240514', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '083148496911', 'false', 'true', '2024-10-28 20:11:10', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, 'Heri Suandi', '125240514-at-heri-hujl', '$2y$10$Yec8xLYTtwG.VApc4abgv.nwOg8TAGvj9BTdCgduhdIuGCM5OYlBa', NULL, 21, 18, 'ppp', '*1B', '125240514@heri', '125240514', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '087899566027', 'false', 'true', '2024-10-28 20:11:12', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 'Juhdi', '125240501-at-zudenk-wz0w', '$2y$10$ImLfLg/Huo3aJPJLHKDE..IPiJ/rf4I8JhfWtqrnQRFqIP2EZnVie', 'juhdianjuhdi@gmail.com', 21, 18, 'ppp', '*1E', '125240501@zudenk', '125240501', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '08886243932', 'false', 'true', '2024-10-28 20:11:12', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(104, 'RT Oji', '150240515-at-rtoji-abjg', '$2y$10$5zikt3.kxMEaj.RUZ8h22udo9r/cyboV7Abzg7CIw1HWZ7mzYJDs6', NULL, 22, 18, 'ppp', '*1F', '150240515@rtoji', '150240515', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '0895811360899', 'false', 'true', '2024-10-28 20:11:14', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(105, 'Suntari', '150240518-at-suntari-ttzy', '$2y$10$Jzs/fK8DFVNAtqs3UW4Q3ufvuKosJJ0HYYlpAfh80L6Fa1DGENhIC', NULL, 22, 18, 'ppp', '*20', '150240518@suntari', '150240518', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '081909474994', 'false', 'true', '2024-10-28 20:11:14', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(106, 'Iqbal Budi Prasetya', '100230304-at-iqbal-vcxz', '$2y$10$BlBvFlGJB/Dbk9QXfWvT3.YbL/ynHC7m/.tQNj8g4TT7AnY.gadbO', NULL, 21, 18, 'ppp', '*22', '100230304@iqbal', '100230304', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '089632966676', 'false', 'true', '2024-10-28 20:11:15', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(107, 'Engko', '150240525-at-engko-c50l', '$2y$10$hAtl4qCuvraYouHw3t670.fA4HbW2Ew2G3RzYC5Nj2zhWvbxagngW', 'mmdrizky0905@gmail.com', 22, 18, 'ppp', '*23', '150240525@engko', '150240525', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '081317532556', 'false', 'true', '2024-10-28 20:11:16', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(108, 'Arya Cahyana Putra', '150240601-at-arya-h7ho', '$2y$10$N6p4rDCL1wVtrHoUZE9/qeIIGfr7xLXtugTH3NzspTvKcm3ucInl6', NULL, 22, 18, 'ppp', '*24', '150240601@arya', '150240601', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '081218931945', 'false', 'true', '2024-10-28 20:11:17', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(109, 'Wida', '100230303-at-wida-luck', '$2y$10$k2uah.v88fVOO1fvfNU8sudYTNFOyUZuXi0AO8kzJORM.8ipWICjW', NULL, 21, 18, 'ppp', '*25', '100230303@wida', '100230303', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '08988560485', 'false', 'true', '2024-10-28 20:11:18', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(110, 'H Imon', '150240608-at-himon-dxr1', '$2y$10$Xs6TLpDU8B4SJ0wnojudIOuZuCGSdl0G550mrvmXiJH2vtk2XdtQe', NULL, 22, 18, 'ppp', '*27', '150240608@h.imon', '150240608', 5, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '087779500883', 'false', 'true', '2024-10-28 20:11:19', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(111, 'Julianto', '150240612-at-julianto-cnrn', '$2y$10$wmf0N01jHS9m2SRPW/JdGuxIxX57ftRZa1PT6LGDxyTzK4MiOU0AW', NULL, 22, 18, 'ppp', '*28', '150240612@julianto', '150240612', 5, 'kp.kadumekar', '083867217015', 'false', 'true', '2024-10-28 20:11:20', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(112, 'Okis', '150240420-at-okis-kzjs', '$2y$10$yvKXHl5tGWZHheUTsaTj0u/5hkZnaEjsPnoNj9a8OWIzt6xGgzbQe', NULL, 22, 18, 'ppp', '*29', '150240420@okis', '150240420', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '082123324181', 'false', 'true', '2024-10-28 20:11:21', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(113, 'Neng Siti', '150240622-at-nengsiti-be5n', '$2y$10$akulYt8YmxkaQuPDfjR5Lu5ShY.p0lkB19Nsz7ntgVsn2fxI7mtDu', NULL, 22, 18, 'ppp', '*2A', '150240622@nengsiti', '150240622', 5, 'Kp. Krajan RT. 007/002 Desa Maracang', '085798800407', 'false', 'true', '2024-10-28 20:11:23', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(114, 'Dedi Haerudi', '150240622-at-idoy-scot', '$2y$10$UPXRU7YFrVJVk/vBDxL/1.L2/gh997eX4WEkCdmkAbva7q1eYVVd6', NULL, 22, 18, 'ppp', '*2B', '150240622@idoy', '150240622', 5, 'Kp. Krajan RT. 007/002 Desa Maracang', '087785185102', 'false', 'true', '2024-10-28 20:11:23', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(115, 'Amania Kusumah', '150240629-at-amania-wmj5', '$2y$10$HVS1RldUuC6i9tJiWNDMGuf.VLWNrmjLA14HkmccYkWSKn09iYDdm', NULL, 22, 18, 'ppp', '*2C', '150240629@amania', '150240629', 5, 'Kp. Krajan RT. 008/002 Desa Maracang', '08979309951', 'false', 'true', '2024-10-28 20:11:24', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(116, 'Nurul Aeni', '150240629-at-nurul-6gzn', '$2y$10$s59vmia58OYz/Z8Wq3mtXeoemPKpceOoslRkP0aq5sNEQ9Svyh522', NULL, 22, 18, 'ppp', '*2D', '150240629@nurul', '150240629', 5, 'Kp. Krajan RT. 008/002 Desa Maracang', '089609996790', 'false', 'true', '2024-10-28 20:11:25', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(117, 'Pak Ojak', '150240701-at-ojak-aumh', '$2y$10$1105asqVxOfsm9.t7ns4Hu4Kr4o3IByKTo2dqTJmn8.aTqcIdB.TS', NULL, 22, 18, 'ppp', '*2E', '150240701@ojak', '150240701', 5, 'Kp. Krajan RT. 008/002 Desa Maracang', '087778703112', 'false', 'true', '2024-10-28 20:11:26', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(118, 'Redi Natisna', '200240629-at-redi-rpgp', '$2y$10$2Dv5ulws359arpmnF0BFJugDjDLbLLkfAZZuyuyZu6ENzvz1cDBuC', NULL, 23, 18, 'ppp', '*2F', '200240629@redi', '200240629', 5, 'Kp. Krajan RT. 010/003 Desa Maracang', '083128938001', 'false', 'true', '2024-10-28 20:11:27', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(119, 'Wahyu Maulana', '150240630-at-wahyu-at06', '$2y$10$pe2TX7HgqRybHKY0hzW/s.vXk01lg1wL2gvsPV2DjvHnjfuscyLIe', NULL, 22, 18, 'ppp', '*30', '150240630@wahyu', '150240630', 5, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '085890005327', 'false', 'true', '2024-10-28 20:11:28', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(120, 'Usman Abdul Sobari', '100231204-at-usman-t4v1', '$2y$10$ikD1T0.7NBCrI8QGe8vYieAjT6m9WqeYl.NFdCd70puZMqDRz6tU2', NULL, 21, 18, 'ppp', '*31', '100231204@usman', '100231204', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '083193222232', 'false', 'true', '2024-10-28 20:11:29', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(121, 'Bajuri Enci', '150240704-at-bajuri-1it3', '$2y$10$HRXh6suQ8DmaUPs3QE9ofO5i4mQdcnda03bC9moFAg2g06rz0lbee', NULL, 22, 18, 'ppp', '*32', '150240704@bajuri', '150240704', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '085603273195', 'false', 'true', '2024-10-28 20:11:30', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(123, 'Salmah Saleha', '150240708-at-salmah-n7wq', '$2y$10$YMyaDht6gzXXKieL1yx3AubFjOQQTeldcnXNnQQS8lgYkzGdLwmQS', NULL, 22, 18, 'ppp', '*34', '150240708@salmah', '150240708', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '089515117637', 'false', 'true', '2024-10-28 20:11:32', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, 'Neneng Suratni', '150240828-at-neneng-l9b1', '$2y$10$2GcYXBZ4vxFiywFcxv0oauKBuUZgvHEbK6iiy9rmJBNVk2B1Ymaqu', 'nenengsuratni.99@gmail.com', 22, 18, 'ppp', '*3E', '150240828@neneng', '150240828', 5, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '087778957694', 'false', 'true', '2024-10-28 20:11:33', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, 'Herman', '150240907-at-eman-fush', '$2y$10$mmyG831bpT.bEpTEqBmIbusScpGb3fy9uywGXf1WCC5X2Fv6QwdSy', NULL, 22, 18, 'ppp', '*3F', '150240907@eman', '150240907', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '085216510760', 'false', 'true', '2024-10-28 20:11:35', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, 'Syarip Hidayatullah', '150240912-at-syarip-2kig', '$2y$10$9fjDv2FPRPv9ipmXaLvNVuhYTn03e9qh27kUYkZGR95UJhNn3FcbS', NULL, 22, 18, 'ppp', '*40', '150240912@syarip', '150240912', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '0895348840602', 'false', 'true', '2024-10-28 20:11:35', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `internet_service`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `simpleque_id`, `simpleque_name`, `ip_static`, `mac_address`, `interface`, `arp_id`, `address_list_id`, `address`, `contact`, `disabled`, `activation`, `created_at`, `updated_at`, `deleted_at`, `primary_server`, `discount`, `customer_id`, `activation_date`, `caller_id`, `user_id`) VALUES
(127, 'Misbah Toko Bangunan', '150241004-at-misbah-pvfc', '$2y$10$/yzteZmYOGuOfp8HZP1DYuOEcMo32Y0BiEM1f0afC8s/Mqc9Acnim', NULL, 22, 18, 'ppp', '*41', '150241004@misbah', '150241004', 5, 'Kp. Krajan RT. 008/002 Desa Maracang', '087778624000', 'false', 'true', '2024-10-28 20:11:37', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, 'Jajang Salahudin', '150241006-at-jajang-85yh', '$2y$10$J7wJOYMyIWwiFHxeW7urI.FlYPt.GWuAgl6DBtT.f/EAxz/xrttmq', NULL, 22, 18, 'ppp', '*42', '150241006@jajang', '150241006', 5, 'Kp. Krajan RT. 003/001 Desa Maracang', '0895333787739', 'false', 'true', '2024-10-28 20:11:37', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, 'Dadi Kusnadi', '150241017-at-dadi-gtna', '$2y$10$SADSxToCkjU4DMCSlxlXreAyGkNyuYqkS0pgqZMaUsscPrcsXfr6S', NULL, 22, 18, 'ppp', '*43', '150241017@dadi', '150241017', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '087718592331', 'false', 'true', '2024-10-28 20:11:39', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 'Darma Wirawan', '150240713-at-darma-axvt', '$2y$10$DfK8CQZ7KWo9C6aNL3pd/uyDHbA/Sx6Dkq1wE6LZEVZVCyRegZZke', 'irawanindra1911@gmail.com', 22, 18, 'ppp', '*35', '150240713@darma', '150240713', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '087837145242', 'false', 'true', '2024-10-28 20:15:32', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, 'Sulis Toko', '200240715-at-sulis-zofc', '$2y$10$YufXDMBN0gvxSVLFfOTcIeul0OTg7kwCkPBLZnXcJYKyewZ1iSqL6', NULL, 23, 18, 'ppp', '*36', '200240715@sulis', '200240715', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '081945501926', 'false', 'true', '2024-10-28 20:15:32', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 'Pak Agus', '150240720-at-agus-qf9a', '$2y$10$1L04NDisBLbUuR88afRAdeHYZRuw0hzYxQiNPEy5AspMfkznFVyNa', NULL, 22, 18, 'ppp', '*37', '150240720@agus', '150240720', 5, 'kp.bakanjati rw06 ds. maracang', '085860251672', 'false', 'true', '2024-10-28 20:15:33', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, 'Sulis Rumah', '150240720-at-sulis-yyk7', '$2y$10$Ps0Z/X3sVMdJv.igt.8j2O7F4KMe9KLsinGq0CQKQ9pwmkp9XQd9K', NULL, 22, 18, 'ppp', '*39', '150240720@sulis', '150240720', 5, 'Kp. Rawabolang RT. 023/007 Desa Maracang', NULL, 'false', 'true', '2024-10-28 20:15:34', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(134, 'Rika Rahmawati', '150240727-at-rika-qn5d', '$2y$10$WbqbDZEaEvALBTWz.BgJU.yuIFJAr4jINWdvuy.WNiRoIE4REXVZK', NULL, 22, 18, 'ppp', '*3A', '150240727@rika', '150240727', 5, 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', '081317000715', 'false', 'true', '2024-10-28 20:15:35', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(135, 'Yuyun', '150240815-at-yuyun-0sdu', '$2y$10$c8LF1thItBVdNGk2DAeqB.2SodEiymCV.6pkwTINO..5LaNVZ3OO.', NULL, 22, 18, 'ppp', '*3C', '150240815@yuyun', '150240815', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '08882687989', 'false', 'true', '2024-10-28 20:15:36', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(136, 'Wahid', '150240825-at-wahid-jdwg', '$2y$10$Vnxb.CG8t4K/2weBx/fz5O44MkxZuOQ22z9eVj4UrRJWkK9SdugJ2', NULL, 22, 18, 'ppp', '*3D', '150240825@wahid', '150240825', 5, 'Kp. Babakan Jati Desa Maracang', '085794748005', 'false', 'true', '2024-10-28 20:15:37', '2024-11-27 12:12:03', NULL, 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, 'Fera', 'fera-s1xwst', '$2y$10$M8PtAr95YZdWDkmWFs6Yle.Qq84/nZxTVTl962hObxi8hWlp7q1Ee', 'fera141120@gmail.com', 22, 18, 'ppp', '*45', '150241030@fera', '150241030', 5, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '0895392820036', 'false', 'true', '2024-10-30 08:12:30', '2024-11-27 12:12:03', '18', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(138, 'Robi Anggara', 'robi-zbdepz', '$2y$10$CUEP9duqVEOyWu5tjeq5g.8qsPZfv075kmj9BmeX95bbDgSXkdvCq', 'robi702.ra@gmail.com', 4, 1, 'ppp', '*BD', '2024103001@robi', '2024103001', 5, 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', '087879621757', 'false', 'true', '2024-10-30 10:14:39', '2024-12-09 00:15:52', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, 'Iis Nurjanah', 'iis-cp09xj', '$2y$10$jo1PaelDYFiQm4hN0bx5VeS66MsvFeAL3.n9BwNZCIQkDIzGDtM1.', 'utengiis@gmail.com', 3, 1, 'ppp', '*C0', '2024112601@iisnurjanah', '2024112601', 5, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '087718095891', 'false', 'true', '2024-11-26 08:10:32', '2024-12-10 02:33:32', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, 'Rifal Yudhi Pratama', 'rifal-mdoeco', '$2y$10$ZzB3UK2OkzFhAmfhwF3reeovlxsWClmN2HPiQCg06v9xYQEJntTXa', 'pratamarifal81@gmail.com', 22, 18, 'ppp', '*46', '150241126@rifal', '150241126', 5, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '087891528346', 'false', 'true', '2024-11-26 09:39:06', '2024-11-27 12:12:03', '18', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 'Agus Rahmat (Azril)', 'agus-f1j3jd', '$2y$10$GIjdamRqKLqesMlW3JlIWOEcLKtdLCh6iZX87V1VeKYhB.PCXC0LO', NULL, 4, 1, 'ppp', '*C5', '2023020801@azril', '2023020801', 5, 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', '6285795716348', 'false', 'true', '2024-11-29 08:44:57', '2024-12-09 23:58:33', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, 'Ani Sumarni', 'ani-wjwnxb', '$2y$10$Vb2XEv5pSETdhwM7pRmOeOPbtQfQxUPZl1jSwCjXfGRIFrZfRsIr2', 'ani653874@gmail.com', 22, 18, 'ppp', '*47', '150241130@anisumarni', '150241130', 5, 'Kp. Rawabolang RT. 025/007 Maracang Babakancikao', '081289921468', 'false', 'true', '2024-11-30 02:57:13', '2024-11-30 03:00:04', '18', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 'Neng Helawati', 'neng-nnljzy', '$2y$10$/hdSmltCqyy6sX4i3Mtm5.1o94iDjVw7FUBgp/ou43/3aanpQ/UJu', 'nenghelawati59@gmail.com', 4, 1, 'ppp', '*C6', '2024120601@helawati', '2024120601', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '6285794547149', 'false', 'true', '2024-11-30 09:11:01', '2024-12-08 23:52:14', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 'Rinji Maulani', 'rinji-1hvwlt', '$2y$10$lNx3LGstuv2Qj9uWTEDAA.YEQZTeYyQWw8b0AU78iM.FPlTCzT2Hi', 'rinjimaulani900@gmail.com', 4, 1, 'ppp', '*C7', '2024113004@rinji', '2024113004', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '6281945958019', 'false', 'true', '2024-11-30 10:13:33', '2024-12-08 23:44:08', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 'Iman Nurjamansyah', 'iman-etpyhh', '$2y$10$oBBK.dLON06V7byHtXLoAe5HULkx6i/KxGprrK.LnbWNJeHwG6X9.', 'nurjaman@gmail.com', 2, 1, 'ppp', '*C8', '2024113002@iman', '2024113002', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '0895424273100', 'false', 'true', '2024-11-30 10:25:57', '2024-12-08 23:40:29', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 'Dani Ramdani', 'dani-o9yk0h', '$2y$10$faeX3xJqxJ9dHgy1zXeNkeZ3/RjwRujDcoLedPnVy59mDYrrR/glW', 'CHApliN10@gmail.com', 2, 1, 'ppp', '*C9', '2024113001@dani', '2024113001', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '083816206823', 'false', 'true', '2024-11-30 10:31:43', '2024-12-08 07:51:01', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 'Mujinah', 'mujinah-l3zbsh', '$2y$10$j8Mtn2fTnS4q4Ej9yGoH8.YVpNpfMOhE6w2B03VOvmi.WCnJS1g2m', 'argazihan70@gmail.com', 2, 1, 'ppp', '*CA', '2024113005@mujinah', '2024113005', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '089659280003', 'false', 'true', '2024-11-30 10:32:44', '2024-12-08 23:45:57', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 'Rodiah', 'rodiah-hphfrh', '$2y$10$mlUWj10gY2yeDlSU81IeROgMTF7xBWP.CyjKtumOZeHRj9f9ZoQhO', 'raisanisan56@gmail.com', 2, 1, 'ppp', '*CB', '2024120102@rodiah', '2024120102', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '6289503852046', 'false', 'true', '2024-11-30 10:34:10', '2024-12-08 23:49:54', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 'Gilang Gumilang', 'tika-ezkozv', '$2y$10$vEMUOd0G4ejbns4OCIGm2uGx4.tN/085LalbjCf.SYKcxAJtxp7yq', 'gumilangg208@gmail.com', 3, 1, 'ppp', '*CC', '2024113003@gilang', '2024113003', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '6289687854003', 'false', 'true', '2024-11-30 11:38:18', '2024-12-08 23:42:28', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, 'Fadilah Nurhasanah', 'fadilah-ynwndo', '$2y$10$BewTdnf95WqQdEgS0tPVeeMlBzCiee38inUIBdbkMYF1RF8K.Egp.', 'fadilahnurhasanah133@gmail.com', 3, 1, 'ppp', '*CD', '2024120101@fadilah', '2024120101', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '6285974706228', 'false', 'true', '2024-12-01 07:46:17', '2024-12-08 09:43:16', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 'Mimin Rukmini', 'mimin-dxyiwu', '$2y$10$x877JVAgdlEOXhuPn.MZQOFrHlvpXDru19xf.3gNMwSo23MQYrjrC', 'adulema0@gmail.com', 2, 1, 'ppp', '*CE', '2024120103@mimin', '2024120103', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '6287879752212', 'false', 'true', '2024-12-01 07:48:40', '2024-12-10 02:38:12', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 'Rini Seminar', 'rini-e1mksu', '$2y$10$3Cl9oRP/rcuhKlqjvUoY3eaG.ZoP2dHSz.G8ZujDmXNdif5wbV/Oa', 'hideungbodas69@gmail.com', 2, 1, 'ppp', '*D1', '2024121001@rini', '2024121001', 5, 'Kp. Sukamaju RT. 009/007 Ciseureuh Purwakarta', '6287822655767', 'false', 'true', '2024-12-01 12:36:03', '2024-12-10 03:18:31', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(157, 'Siam Suroso', 'siam-ygptmm', '$2y$10$lXIct295eI3GRpS3KXgREeo2wAk5KFVejK4zcXF0HPMjGFPLl9YCq', NULL, 2, 1, 'ppp', '*D0', '2024120701@siam', '2024120701', 5, 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', '6281386757581', 'false', 'true', '2024-12-01 12:38:12', '2024-12-09 04:17:22', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, 'Tati Mulyati', 'tati-1x2epk', '$2y$10$yhBAU8Yz5mzb76HsD1BghOT4Ae17aewcpAGhwNCh2zVOrUAQUK.S2', 'martamarta3942@gmail.com', 2, 1, 'ppp', '*CF', '2024120802@tati', '2024120802', 5, 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', '6285967977930', 'false', 'true', '2024-12-01 12:41:45', '2024-12-08 09:45:43', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(159, 'Risna Suhertini', 'risna-l8oe9d', '$2y$10$Aw3DSoMBk4gbsjDW/hNon.dGom4K36AADpdlE23ChAT8cFjj55FBC', 'summeryuri45@gmail.com', 2, 1, 'ppp', '*D2', '2024120202@risna', '2024120202', 5, 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', '089639512085', 'false', 'true', '2024-12-02 13:36:24', '2024-12-09 00:01:03', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(160, 'Dewi Sutriani', 'dewi-cbxpke', '$2y$10$oYSIXvYGqTp6Ykon0PnkiOhVqdiwGqt63STO/lqgQdqX4UCsOPzAC', 'destirahayu443@gmail.com', 2, 1, 'ppp', '*D3', '2024120201@dewi', '2024120201', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '081287271594', 'false', 'true', '2024-12-02 15:03:49', '2024-12-08 23:59:09', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(161, 'Mariati', 'mariati-bq4kkn', '$2y$10$.PiJyEnK3.CZqxGHvVgTCOKoNbWDJq4YyjBInAl0gVoup3mBqtHIS', 'mariatimariati609@gmail.com', 4, 1, 'ppp', '*D4', '2024120602@mariati', '2024120602', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '6283131898860', 'false', 'true', '2024-12-04 14:13:47', '2024-12-08 23:55:00', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(162, 'Devi Sevtiawati', 'devi-3ormrb', '$2y$10$8m7glC5T/pF6TVwR9/WqZOjIUNxc7kgBx5lEfGaV6mO1AgRxk9UHe', 'devii.setia18@gmail.com', 3, 1, 'ppp', '*D5', '2024120501@devi', '2024120501', 5, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '089527460013', 'false', 'true', '2024-12-05 07:33:37', '2024-12-10 02:31:46', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(163, 'Tatang Mulyana', 'tatang-reiwyy', '$2y$10$ZK6BzHQz.9.8qmM4mSoMMu4vzs87UjrLPbx/eCHC5G6Cx2G0A8c2a', NULL, 4, 1, 'ppp', '*D9', '2024120801@tatang', '2024120801', 5, 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', NULL, 'false', 'true', '2024-12-08 02:53:41', '2024-12-08 02:55:01', '1', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(164, 'Agus Hermanto', 'agus-ktfmpf', '$2y$10$4gMH0xQy.1BkW2IJGPB1LuyM/90v4NDchwakCOIVdYdRkw9wrUpDW', 'bagasbagus01175@gmail.com', 23, 18, 'ppp', '*48', '200241208@agus', '200241208', 5, 'Kp. Krajan RT. 010/003 Maracang Babakancikao', '083894458546', 'false', 'true', '2024-12-08 03:42:18', '2024-12-08 03:43:18', '18', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(165, 'Hendri Gunawan', 'hendri-yh767g', '$2y$10$/yVh9WW2j9jTlsZWBfAzAuNyxCntI/Hkmqdqlu/waylRV8paQiZoS', 'hg0753407@gmail.com', 22, 18, 'ppp', '*49', '150241208@hendri', '150241208', 5, 'Kp. Rawabolang RT. 025/007 Maracang Babakancikao', '082241946794', 'false', 'true', '2024-12-08 06:13:42', '2024-12-08 06:14:43', '18', 'Jl. Test', '628983249628', 'true', 'true', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'Dock Dock Test', 'randika-wss3wm', '$2y$10$LgJr2q2cAI9E4CYjRSW5i.hntZs4PYqutofGjZzd9uNRNfQV/z5W2', 'randika.nurfaizi470@gmail.com', 22, 18, 'ppp', NULL, 'randika-podourip-pyhe', 'OffiKr', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Test', '628983249628', 'true', 'false', '2024-12-30 06:50:29', '2025-01-04 08:17:26', NULL, 18, 0.00, 'SNF18-167', NULL, NULL, 1),
(168, 'John Doe', 'john-jy3x8l', '$2y$10$wucoCDvK7lb83rK03D4gZeLRWEpqxc3n8q4//pFzpp5crx7fuQ6FW', 'john.doe@gmail.com', 5, 1, 'ppp', NULL, 'john-test-address-xskz', 'JWTDmY', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Test Address', '628983249628', 'true', 'false', '2025-01-01 09:05:21', '2025-01-04 08:16:12', NULL, 1, 0.00, 'SNF01-168', NULL, NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `customer_onts`
--

CREATE TABLE `customer_onts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `merk_ont` varchar(255) DEFAULT NULL,
  `vlan_id` int(11) DEFAULT NULL,
  `type_ont` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `port` varchar(255) DEFAULT '80',
  `mac_address` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_onts`
--

INSERT INTO `customer_onts` (`id`, `customer_id`, `merk_ont`, `vlan_id`, `type_ont`, `username`, `password`, `port`, `mac_address`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:48', '2024-10-06 12:33:19'),
(2, 2, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:48', '2024-10-06 12:50:52'),
(3, 3, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:48', '2024-10-14 08:30:42'),
(4, 4, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:48', '2024-10-15 23:26:27'),
(5, 5, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:48', '2024-10-16 00:54:41'),
(6, 6, 'Tenda', NULL, NULL, 'admin', 'shanum250117', '80', NULL, NULL, '2024-09-30 05:55:49', '2024-10-01 08:35:40'),
(7, 7, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:49', '2024-10-14 03:46:50'),
(8, 8, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:49', '2024-09-30 05:55:49'),
(9, 9, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:49', '2024-10-15 13:23:23'),
(10, 10, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:49', '2024-10-09 06:15:45'),
(11, 11, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:50', '2024-10-15 13:24:33'),
(12, 12, 'TP-Link', NULL, NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:50', '2024-10-16 00:45:31'),
(13, 13, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:50', '2024-10-15 13:55:43'),
(14, 14, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:50', '2024-10-15 13:38:27'),
(15, 15, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:50', '2024-10-16 00:31:36'),
(16, 16, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:51', '2024-10-19 10:14:00'),
(18, 18, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:51', '2024-09-30 05:55:51'),
(19, 19, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:51', '2024-10-01 02:56:57'),
(20, 20, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:51', '2024-10-14 08:38:23'),
(21, 21, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:52', '2024-10-16 00:53:05'),
(22, 22, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:52', '2024-10-07 05:31:14'),
(23, 23, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:43:63:8d', NULL, '2024-09-30 05:55:52', '2024-10-01 00:46:53'),
(24, 24, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:52', '2024-10-01 02:16:40'),
(25, 25, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:34:07:b1', '1c568e3407b1', '2024-09-30 05:55:52', '2024-10-01 02:54:32'),
(26, 26, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:52', '2024-09-30 09:24:43'),
(27, 27, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:53', '2024-10-03 11:26:44'),
(28, 28, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:53', '2024-09-30 05:55:53'),
(29, 29, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:53', '2024-10-02 08:51:23'),
(30, 30, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:53', '2024-10-15 12:56:13'),
(31, 31, 'Huawei', 123, 'HG8245H', 'admin', 'admin', '80', '192.123.123.123', 'Test', '2024-09-30 05:55:53', '2024-12-30 09:36:20'),
(32, 32, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:54', '2024-10-16 00:50:39'),
(33, 33, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:54', '2024-10-15 23:16:55'),
(34, 34, 'TP-Link', NULL, 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:54', '2024-10-06 12:23:03'),
(35, 35, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:54', '2024-09-30 05:55:54'),
(36, 36, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:54', '2024-10-02 02:59:20'),
(38, 38, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'admintelecom', '80', NULL, NULL, '2024-09-30 05:55:55', '2024-10-06 12:49:09'),
(39, 39, 'ZTE', NULL, 'F660', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:55', '2024-10-09 02:56:54'),
(40, 40, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:55', '2024-10-15 14:01:57'),
(41, 41, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'admintelecom', '80', NULL, NULL, '2024-09-30 05:55:55', '2024-10-15 23:11:33'),
(42, 42, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:3d:d9:c1', '1c568e3dd9c1', '2024-09-30 05:55:56', '2024-10-01 06:34:07'),
(43, 43, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:56', '2024-09-30 05:55:56'),
(44, 44, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:56', '2024-10-09 02:59:09'),
(45, 45, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:56', '2024-10-15 23:28:03'),
(46, 46, 'ZTE', NULL, 'F663NV3A', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:56', '2024-10-07 05:34:52'),
(47, 47, 'ZTE', NULL, 'F663NV3a', NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:56', '2024-11-29 16:38:10'),
(48, 48, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:57', '2024-10-06 12:28:33'),
(49, 49, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:57', '2024-09-30 05:55:57'),
(50, 50, 'ZTE', NULL, 'F663NV3A', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:57', '2024-10-06 12:41:08'),
(51, 51, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:57', '2024-10-11 06:10:56'),
(52, 52, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:57', '2024-10-06 12:44:49'),
(53, 53, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:3f:07:09', 'ZIONCOM', '2024-09-30 05:55:58', '2024-09-30 13:11:06'),
(54, 54, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:58', '2024-10-15 13:49:35'),
(55, 55, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:58', '2024-10-16 08:24:40'),
(56, 56, 'ZTE', NULL, 'F663NV3A', 'admin', 'berkah25', '80', 'B0:53:65:0A:E1:3F', 'CIOT030AE13F', '2024-09-30 05:55:58', '2024-10-01 02:26:30'),
(57, 57, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:55:58', '2024-09-30 05:55:58'),
(58, 58, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', 'D8:32:14:69:0A:08', NULL, '2024-09-30 05:55:59', '2024-10-01 03:52:31'),
(60, 60, 'Huawei', NULL, 'HS8145C5', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:59', '2024-10-15 23:20:09'),
(61, 61, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:59', '2024-10-16 00:40:51'),
(62, 62, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:55:59', '2024-10-14 22:44:12'),
(63, 63, 'Tenda', NULL, NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:56:00', '2024-10-18 06:09:52'),
(64, 64, 'TP-Link', NULL, 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:56:00', '2024-10-18 06:05:20'),
(65, 65, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:56:00', '2024-10-16 00:34:59'),
(66, 66, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-30 05:56:00', '2024-09-30 05:56:00'),
(67, 67, 'ZTE', NULL, 'F663NV3A', 'admin', 'berkah25', '80', '78:2E:56:4D:A0:17', 'ZICG074DA017', '2024-09-30 05:56:00', '2024-09-30 12:41:33'),
(68, 68, 'ZTE', NULL, 'F663NV3A', 'admin', 'berkah25', '80', '1C:78:4E:6D:4F:31', 'ZICG146D4F31', '2024-09-30 05:56:01', '2024-09-30 12:56:56'),
(69, 69, 'ZTE', NULL, 'F663NV3A', 'admin', 'berkah25', '80', '78:2E:56:34:9F:E2', 'ZICG07349FE2', '2024-09-30 05:56:01', '2024-10-01 08:24:46'),
(70, 70, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:56:01', '2024-10-16 00:36:08'),
(71, 71, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:56:01', '2024-10-11 03:19:54'),
(72, 72, 'TP-Link', NULL, NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-09-30 05:56:01', '2024-10-16 14:11:27'),
(73, 73, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '2C:33:41:44:29:9A', 'ZICG2544299A', '2024-09-30 05:56:02', '2024-09-30 12:55:49'),
(74, 74, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '2C:33:41:35:A2:58', 'ZICG2535A258', '2024-09-30 05:56:02', '2024-09-30 13:05:38'),
(75, 75, 'Huawei', NULL, 'HG8245H', 'telecomadmin', 'admintelecom', '80', NULL, NULL, '2024-10-01 03:13:45', '2024-10-09 03:15:20'),
(77, 77, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-04 05:20:17', '2024-10-04 05:22:28'),
(78, 78, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-09 13:04:42', '2024-10-09 13:09:39'),
(79, 79, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-17 05:25:35', '2024-10-17 05:33:11'),
(80, 80, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-20 12:25:51', '2024-10-20 12:25:51'),
(81, 81, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 02:52:51', '2024-10-27 04:30:08'),
(82, 82, 'TP-Link', NULL, 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:10:53', '2024-10-30 19:36:42'),
(83, 83, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:10:53', '2024-10-28 13:10:53'),
(85, 85, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:10:55', '2024-10-31 00:16:34'),
(86, 86, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:10:56', '2024-10-30 05:53:43'),
(87, 87, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:10:57', '2024-10-30 06:53:39'),
(88, 88, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:10:58', '2024-10-30 00:47:41'),
(89, 89, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:10:59', '2024-11-01 09:08:11'),
(90, 90, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:10:59', '2024-10-30 20:19:49'),
(91, 91, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:00', '2024-11-01 09:12:34'),
(92, 92, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:02', '2024-10-31 05:36:36'),
(93, 93, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:02', '2024-11-01 09:09:54'),
(94, 94, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:03', '2024-11-01 03:41:59'),
(95, 95, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:11:05', '2024-10-28 13:11:05'),
(96, 96, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:06', '2024-11-02 01:25:49'),
(97, 97, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:07', '2024-11-01 09:11:16'),
(98, 98, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:07', '2024-11-01 15:24:42'),
(99, 99, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:09', '2024-11-01 15:26:17'),
(100, 100, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:10', '2024-10-30 05:42:39'),
(101, 101, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:11:11', '2024-10-28 13:11:11'),
(102, 102, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:12', '2024-10-30 05:48:43'),
(103, 103, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:13', '2024-10-28 22:59:29'),
(104, 104, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:14', '2024-11-01 15:25:30'),
(105, 105, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:14', '2024-11-02 01:24:45'),
(106, 106, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:15', '2024-10-30 05:46:58'),
(107, 107, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:16', '2024-10-28 23:05:11'),
(108, 108, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:17', '2024-10-30 06:56:00'),
(109, 109, 'TP-Link', NULL, 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:18', '2024-10-31 19:51:23'),
(110, 110, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:19', '2024-11-02 06:08:48'),
(111, 111, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:20', '2024-11-02 05:58:21'),
(112, 112, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:11:22', '2024-10-28 13:11:22'),
(113, 113, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:23', '2024-11-02 05:59:16'),
(114, 114, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:24', '2024-10-30 05:56:30'),
(115, 115, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:25', '2024-10-28 20:37:12'),
(116, 116, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:26', '2024-10-28 21:04:26'),
(117, 117, 'Huawei', NULL, 'HG8245H', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:26', '2024-11-02 06:00:41'),
(118, 118, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:28', '2024-10-28 20:57:33'),
(119, 119, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:28', '2024-10-28 21:01:42'),
(120, 120, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:30', '2024-10-30 21:01:05'),
(121, 121, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:11:30', '2024-10-28 13:11:30'),
(123, 123, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:33', '2024-10-30 05:44:41'),
(124, 124, 'ZTE', NULL, 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:34', '2024-10-28 20:46:36'),
(125, 125, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:35', '2024-10-30 06:02:29'),
(126, 126, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:11:36', '2024-10-28 13:11:36'),
(127, 127, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:11:37', '2024-10-28 13:11:37'),
(128, 128, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:38', '2024-10-28 20:43:46'),
(129, 129, 'TP-Link', NULL, NULL, NULL, 'berkah25', '80', NULL, NULL, '2024-10-28 13:11:39', '2024-10-28 20:33:35'),
(130, 130, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:15:32', '2024-10-30 15:04:07'),
(131, 131, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:15:33', '2024-11-02 05:53:15'),
(132, 132, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:15:34', '2024-11-02 06:03:08'),
(133, 133, 'TP-Link', NULL, NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:15:34', '2024-11-02 05:56:15'),
(134, 134, 'Huawei', NULL, 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-28 13:15:36', '2024-10-28 20:40:02'),
(135, 135, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:15:36', '2024-10-28 13:15:36'),
(136, 136, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-28 13:15:38', '2024-10-28 13:15:38'),
(137, 137, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-30 01:12:30', '2024-10-30 01:14:58'),
(138, 138, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-30 03:14:39', '2024-10-30 03:16:26'),
(139, 139, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-11-26 01:10:32', '2024-11-26 07:21:13'),
(140, 140, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-11-26 02:39:06', '2024-11-26 07:21:36'),
(143, 145, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-11-29 01:44:57', '2024-11-29 01:44:57'),
(144, 146, 'Totolink', NULL, 'N200RE', 'admin', 'berkah25 ', '80', NULL, NULL, '2024-11-29 19:57:13', '2024-11-29 20:00:46'),
(145, 147, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', 'E0:E0:C2:AE:7A:67', NULL, '2024-11-30 02:11:01', '2024-11-30 17:01:20'),
(146, 148, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '94:FF:61:2F:AB:5F', NULL, '2024-11-30 03:13:33', '2024-11-30 06:57:56'),
(147, 149, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', 'AC:8B:6A:A4:7E:3A', NULL, '2024-11-30 03:25:57', '2024-11-30 05:48:27'),
(148, 150, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '94:FF:61:29:C5:DE', NULL, '2024-11-30 03:31:43', '2024-11-30 05:59:08'),
(149, 151, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '94:FF:61:3E:CD:C1', NULL, '2024-11-30 03:32:44', '2024-11-30 06:09:36'),
(150, 152, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', 'AC:8B:6A:B6:3C:C5', NULL, '2024-11-30 03:34:10', '2024-11-30 06:25:27'),
(151, 153, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '94:ff:61:1b:56:0c', NULL, '2024-11-30 04:38:18', '2024-11-30 06:36:48'),
(152, 154, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', 'e0:e0:c2:cd:6c:fe', NULL, '2024-12-01 00:46:17', '2024-12-03 04:52:15'),
(153, 155, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-01 00:48:40', '2024-12-01 00:48:40'),
(154, 156, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-01 05:36:03', '2024-12-01 05:36:03'),
(155, 157, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-01 05:38:12', '2024-12-01 05:38:12'),
(156, 158, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-01 05:41:45', '2024-12-01 05:41:45'),
(157, 159, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '2c:33:41:45:f4:40', NULL, '2024-12-02 06:36:24', '2024-12-03 04:56:24'),
(158, 160, 'ZTE', NULL, 'F650', 'admin', 'berkah25', '80', '2C:33:41:44:39:D9', NULL, '2024-12-02 08:03:49', '2024-12-03 05:09:03'),
(159, 161, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-04 07:13:47', '2024-12-04 07:13:47'),
(160, 162, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-05 00:33:37', '2024-12-05 00:33:37'),
(161, 163, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-07 19:53:41', '2024-12-07 19:53:41'),
(162, 164, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-07 20:42:18', '2024-12-07 20:42:18'),
(163, 165, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-07 23:13:42', '2024-12-07 23:13:42'),
(164, 167, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-30 06:50:29', '2024-12-30 06:50:29'),
(165, 168, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-01 09:05:21', '2025-01-01 09:05:21');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_customers`
--

CREATE TABLE `deleted_customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `paket_id` varchar(255) DEFAULT NULL,
  `mikrotik_id` varchar(255) DEFAULT NULL,
  `secret_id` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password_ppp` varchar(255) DEFAULT NULL,
  `ppp_type_id` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact` text DEFAULT NULL,
  `activation_date` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deleted_customers`
--

INSERT INTO `deleted_customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `address`, `contact`, `activation_date`, `created_at`, `updated_at`) VALUES
(1, 'test', 'test-wd3hs3', '$2y$10$5.ADiLVOW4vU3UfSLcL9Gub9wVKdqea6k8MYL/vSLJykbD.bhRPQq', NULL, 'Expire', 'Shanet Fiber', '*B1', 'test', 'test', 'pppoe', NULL, NULL, NULL, '2024-09-30 17:13:09', '2024-09-30 17:13:09'),
(2, 'Nazri Nasier', 'maemunah-yk3mhd', '$2y$10$.9siJR4LokZNBRlmmf7GZ.iFSgb1k65lbVvzdrqXHhOf1Kff7QWHa', 'nasiernazri@gmail.com', 'Paket 2 (15 Mbps)', 'Shanet Fiber', '*B3', '200241004@maemunah', '200241004', 'pppoe', 'KP. Sukamaju RT. 05/07 Ciseureuh Purwakarta', '085210433997', NULL, '2024-10-03 08:15:11', '2024-10-03 08:15:11'),
(3, 'Dian Ardiansyah', 'dian-bhrazm', '$2y$10$k2CLyM.ixS6jtYC.RajULesWs8FR7ROHDmbuawDGUDhrwbX7TK.h.', 'otonks.34ar@gmail.com', 'Basic 3 (20Mbps)', 'Irsyad Network', '*45', 'dian-jl-sukamaju-no-49-rt-0907-kelurahan-ciseureuh-wsqt', 'pG1xKb', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-26 10:50:32', '2024-10-26 10:50:32'),
(4, 'Dian Ardiansyah', 'dian-x2ghft', '$2y$10$9U28Z1O2V.TMA70UsCKAGe19OW1IUlo0z2VoDDE9splH8GGWS7w7.', 'oto', 'ShaNet 0 (7 Mbps)', 'ShaNet Fiber', '*BB', 'dian-jl-sukamaju-no-49-rt-0907-kelurahan-ciseureuh-i14d', '2BJOpR', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-27 09:31:14', '2024-10-27 09:31:14'),
(5, 'Dian Ardiansyah', 'dian-fuy5nl', '$2y$10$gkN/9.mYw0mbsgbQfvkzGO.BysmhG9DKp./Hu2LTufacPOJc28mr.', 'otonks.34ar@gmail.com', 'ShaNet 0 (5 Mbps)', 'ShaNet Fiber', '*BC', '15616116szgsgasg', 'ashs1h56h', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-27 09:49:59', '2024-10-27 09:49:59'),
(6, 'Dian Ardiansyah', 'dian-v7vnu5', '$2y$10$7V8L4yZvFyg5cF90g.d2JuLAK.nGQxR6YxQY7SBXF6A2L2mkEslD6', 'otonks.34ar@gmail.com', 'Basic 0 (7 Mbps)', 'Irsyad Network', '*44', 'dian-jl-sukamaju-no-49-rt-0907-kelurahan-ciseureuh-yjuo', 'IgoLi8', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-27 20:25:48', '2024-10-27 20:25:48'),
(7, '150240705@adinda', '150240705-at-adinda-bq0s', '$2y$10$Vq/zP/cHoKlHKn1UBQ4VkeJI1MdU8Qp.D4fVCG1jcyD0SqCbTz7uC', NULL, 'Isolir', 'Irsyad Network', '*33', '150240705@adinda', '150240705', 'pppoe', NULL, NULL, NULL, '2024-10-30 23:12:18', '2024-10-30 23:12:18'),
(8, '125240427@rtkarma', '125240427-at-rtkarma-1bme', '$2y$10$UAtiFEG1ATZzzXlm6LV.0eYGRJPjIHK5PuD2xlFWrbV.Pi9LYEccW', NULL, 'Isolir', 'Irsyad Network', '*8', '125240427@rtkarma', '125240427', 'pppoe', NULL, NULL, NULL, '2024-10-30 23:24:23', '2024-10-30 23:24:23');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `failed_jobs`
--

INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(1, '84419a71-eac0-4afd-8cae-4273150f9ea2', 'database', 'default', '{\"uuid\":\"84419a71-eac0-4afd-8cae-4273150f9ea2\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'Error: Object of class Illuminate\\Database\\Eloquent\\Builder could not be converted to string in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:92\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 {main}', '2024-11-23 22:55:30'),
(2, 'd3409346-cf12-445c-8dea-08aad6b732b9', 'database', 'default', '{\"uuid\":\"d3409346-cf12-445c-8dea-08aad6b732b9\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'RuntimeException: You must specify an orderBy clause when using this function. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php:2960\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(34): Illuminate\\Database\\Query\\Builder->enforceOrderBy()\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(127): Illuminate\\Database\\Query\\Builder->chunk(50, Object(Closure))\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(108): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Database\\Query\\Builder), \'warning_bill\')\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 {main}', '2024-11-24 01:10:22'),
(3, '301a973a-4e19-41a0-8e3e-7897fe8be51f', 'database', 'default', '{\"uuid\":\"301a973a-4e19-41a0-8e3e-7897fe8be51f\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'RuntimeException: You must specify an orderBy clause when using this function. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php:2960\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(34): Illuminate\\Database\\Query\\Builder->enforceOrderBy()\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(127): Illuminate\\Database\\Query\\Builder->chunk(50, Object(Closure))\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(108): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Database\\Query\\Builder), \'warning_bill\')\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 {main}', '2024-11-24 01:10:25'),
(4, '8d5bfa47-273c-451d-b9de-7af8fa1d5701', 'database', 'default', '{\"uuid\":\"8d5bfa47-273c-451d-b9de-7af8fa1d5701\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'Exception: Property [status] does not exist on this collection instance. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Collections\\Traits\\EnumeratesValues.php:1003\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(132): Illuminate\\Support\\Collection->__get(\'status\')\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(53): App\\Jobs\\SendPaymentReminderJob->App\\Jobs\\{closure}(Object(Illuminate\\Database\\Eloquent\\Collection), 1)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(128): Illuminate\\Database\\Eloquent\\Builder->chunk(50, Object(Closure))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(115): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Database\\Eloquent\\Builder), \'deadline_bill\')\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-11-24 01:16:44'),
(5, 'c1eb1d8a-5f5b-4330-b59d-f34f41278746', 'database', 'default', '{\"uuid\":\"c1eb1d8a-5f5b-4330-b59d-f34f41278746\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Undefined property: stdClass::$id in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:176\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(254): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Undefined prope...\', \'C:\\\\xampp\\\\htdocs...\', 176)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(176): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Undefined prope...\', \'C:\\\\xampp\\\\htdocs...\', 176)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(152): App\\Jobs\\SendPaymentReminderJob->sendMessagesInBatch(Array, \'warning_bill\')\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(99): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\')\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-11-24 01:24:44'),
(6, '830eb609-1339-41ee-8243-0ffe0177ef5d', 'database', 'default', '{\"uuid\":\"830eb609-1339-41ee-8243-0ffe0177ef5d\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\SendPaymentReminderJob has been attempted too many times. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php:785\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(519): Illuminate\\Queue\\Worker->maxAttemptsExceededException(Object(Illuminate\\Queue\\Jobs\\DatabaseJob))\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(428): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), 1)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#19 {main}', '2024-11-24 02:05:11');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(7, '4c31487f-76a1-4706-82fb-ae7c256571d6', 'database', 'default', '{\"uuid\":\"4c31487f-76a1-4706-82fb-ae7c256571d6\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Attempt to read property \"customer\" on null in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:203\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(255): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 203)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(203): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 203)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(134): App\\Jobs\\SendPaymentReminderJob->manipulateContact(NULL)\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(119): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-12-15 06:00:44'),
(8, 'd073b7e6-0d02-4cf2-bb04-87eeefaceeda', 'database', 'default', '{\"uuid\":\"d073b7e6-0d02-4cf2-bb04-87eeefaceeda\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Attempt to read property \"customer\" on null in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:230\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(255): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 230)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(230): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 230)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(161): App\\Jobs\\SendPaymentReminderJob->manipulateContact(NULL)\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(146): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-12-15 06:18:18'),
(9, '77ca26fe-53a4-4649-aa33-0945b4c8740e', 'database', 'default', '{\"uuid\":\"77ca26fe-53a4-4649-aa33-0945b4c8740e\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Attempt to read property \"customer\" on null in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:230\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(255): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 230)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(230): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 230)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(161): App\\Jobs\\SendPaymentReminderJob->manipulateContact(NULL)\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(146): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-12-15 06:20:07'),
(10, '00808bd0-f21f-4bc6-b995-40ce3b208f3d', 'database', 'default', '{\"uuid\":\"00808bd0-f21f-4bc6-b995-40ce3b208f3d\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Array to string conversion in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:183\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(255): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Array to string...\', \'C:\\\\xampp\\\\htdocs...\', 183)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(183): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Array to string...\', \'C:\\\\xampp\\\\htdocs...\', 183)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(177): App\\Jobs\\SendPaymentReminderJob->sendMessagesInBatch(Array, \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(129): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-12-15 06:53:44'),
(11, '18651d5b-3f19-4890-9352-3f161c74bd22', 'database', 'default', '{\"uuid\":\"18651d5b-3f19-4890-9352-3f161c74bd22\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Attempt to read property \"customer\" on null in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:231\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(255): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 231)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(231): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 231)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(161): App\\Jobs\\SendPaymentReminderJob->manipulateContact(NULL)\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(145): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-12-15 07:12:53');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(12, '71ee7e95-5e90-461b-8ad0-0284073acf89', 'database', 'default', '{\"uuid\":\"71ee7e95-5e90-461b-8ad0-0284073acf89\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Attempt to read property \"customer\" on null in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:232\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(255): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 232)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(232): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Attempt to read...\', \'C:\\\\xampp\\\\htdocs...\', 232)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(162): App\\Jobs\\SendPaymentReminderJob->manipulateContact(NULL)\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(145): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-12-15 07:14:39'),
(13, '613e654e-b602-456b-8037-3308faa51801', 'database', 'default', '{\"uuid\":\"613e654e-b602-456b-8037-3308faa51801\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'PDOException: SQLSTATE[42000]: Syntax error or access violation: 1055 \'shanetfiber_mgmt.billings.id\' isn\'t in GROUP BY in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:486\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(486): PDO->prepare(\'select `billing...\')\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(816): Illuminate\\Database\\Connection->Illuminate\\Database\\{closure}(\'select `billing...\', Array)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(783): Illuminate\\Database\\Connection->runQueryCallback(\'select `billing...\', Array, Object(Closure))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(477): Illuminate\\Database\\Connection->run(\'select `billing...\', Array, Object(Closure))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3107): Illuminate\\Database\\Connection->cursor(\'select `billing...\', Array, true)\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Collections\\LazyCollection.php(789): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(165): Illuminate\\Support\\LazyCollection->Illuminate\\Support\\{closure}()\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(154): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#38 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#39 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#40 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42000]: Syntax error or access violation: 1055 \'shanetfiber_mgmt.billings.id\' isn\'t in GROUP BY (Connection: mysql, SQL: select `billings`.`id`, `billings`.`customer_id`, `customers`.`name` as `customer_name`, `customers`.`contact` as `customer_contact`, COUNT(billings.id) as total_bills, SUM(billings.paket_price) as total_amount from `billings` inner join `customers` on `billings`.`customer_id` = `customers`.`id` where `customers`.`contact` is not null and `customers`.`contact` !=  and (`billings`.`status` = BL and `billings`.`due_date` is not null and date(`billings`.`due_date`) < 2024-12-15 or (`billings`.`status` = PL and `billings`.`pay_later` is not null and date(`billings`.`pay_later`) < 2024-12-15)) group by `billings`.`customer_id`, `customers`.`name`, `customers`.`contact`) in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:829\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(783): Illuminate\\Database\\Connection->runQueryCallback(\'select `billing...\', Array, Object(Closure))\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(477): Illuminate\\Database\\Connection->run(\'select `billing...\', Array, Object(Closure))\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3107): Illuminate\\Database\\Connection->cursor(\'select `billing...\', Array, true)\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Collections\\LazyCollection.php(789): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(165): Illuminate\\Support\\LazyCollection->Illuminate\\Support\\{closure}()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(154): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#38 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#39 {main}', '2024-12-15 07:49:27'),
(14, '90dfbaa8-8e6f-44d8-9631-1b204370f8f5', 'database', 'default', '{\"uuid\":\"90dfbaa8-8e6f-44d8-9631-1b204370f8f5\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Undefined variable $billingsUpcoming in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:182\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(255): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Undefined varia...\', \'C:\\\\xampp\\\\htdocs...\', 182)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(182): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Undefined varia...\', \'C:\\\\xampp\\\\htdocs...\', 182)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 {main}', '2024-12-15 09:31:01');
INSERT INTO `failed_jobs` (`id`, `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`) VALUES
(15, '6c8f46ab-a551-4b0f-bf6b-aa1f44b11382', 'database', 'default', '{\"uuid\":\"6c8f46ab-a551-4b0f-bf6b-aa1f44b11382\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'PDOException: SQLSTATE[42000]: Syntax error or access violation: 1055 \'shanetfiber_mgmt.billings.due_date\' isn\'t in GROUP BY in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:486\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(486): PDO->prepare(\'select `billing...\')\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(816): Illuminate\\Database\\Connection->Illuminate\\Database\\{closure}(\'select `billing...\', Array)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(783): Illuminate\\Database\\Connection->runQueryCallback(\'select `billing...\', Array, Object(Closure))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(477): Illuminate\\Database\\Connection->run(\'select `billing...\', Array, Object(Closure))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3107): Illuminate\\Database\\Connection->cursor(\'select `billing...\', Array, true)\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Collections\\LazyCollection.php(789): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(158): Illuminate\\Support\\LazyCollection->Illuminate\\Support\\{closure}()\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(149): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'reminder_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#38 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#39 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#40 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#41 {main}\n\nNext Illuminate\\Database\\QueryException: SQLSTATE[42000]: Syntax error or access violation: 1055 \'shanetfiber_mgmt.billings.due_date\' isn\'t in GROUP BY (Connection: mysql, SQL: select `billings`.`customer_id`, `customers`.`name` as `customer_name`, `customers`.`contact` as `customer_contact`, `customers`.`address` as `customer_address`, COUNT(billings.id) as total_bills, SUM(billings.paket_price) as total_amount, DATEDIFF(billings.due_date, CURDATE()) as remaining_days from `billings` inner join `customers` on `billings`.`customer_id` = `customers`.`id` where `customers`.`contact` is not null and `customers`.`contact` !=  and ((`billings`.`status` = BL and `billings`.`due_date` is not null and date(`billings`.`due_date`) >= 2024-12-15) or (`billings`.`status` = PL and `billings`.`pay_later` is not null and date(`billings`.`pay_later`) >= 2024-12-15)) group by `billings`.`customer_id`, `customers`.`name`, `customers`.`contact`, `customers`.`address` order by `customers`.`name` asc) in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php:829\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(783): Illuminate\\Database\\Connection->runQueryCallback(\'select `billing...\', Array, Object(Closure))\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php(477): Illuminate\\Database\\Connection->run(\'select `billing...\', Array, Object(Closure))\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php(3107): Illuminate\\Database\\Connection->cursor(\'select `billing...\', Array, true)\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Collections\\LazyCollection.php(789): Illuminate\\Database\\Query\\Builder->Illuminate\\Database\\Query\\{closure}()\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(158): Illuminate\\Support\\LazyCollection->Illuminate\\Support\\{closure}()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(149): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'reminder_bill\', Object(App\\Models\\WhatsappGateway\\WhatsappGateway))\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(144): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(119): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(102): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1096): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(324): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(175): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#38 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#39 {main}', '2024-12-15 09:42:45');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merk_routers`
--

CREATE TABLE `merk_routers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `merk_routers`
--

INSERT INTO `merk_routers` (`id`, `name`, `disabled`, `created_at`, `updated_at`) VALUES
(1, 'ZTE', 'false', '2024-09-29 12:07:45', '2024-09-29 12:07:45'),
(2, 'Huawei', 'false', '2024-09-29 12:15:29', '2024-09-29 12:15:29'),
(3, 'Fiberhome', 'false', '2024-09-29 12:15:46', '2024-09-29 12:15:46'),
(4, 'Totolink', 'false', '2024-09-29 12:16:40', '2024-09-29 12:16:40'),
(5, 'TP-Link', 'false', '2024-09-29 12:16:52', '2024-09-29 12:16:52'),
(6, 'Tenda', 'false', '2024-09-29 12:17:05', '2024-09-29 12:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `message_logs`
--

CREATE TABLE `message_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `message_type` varchar(255) NOT NULL,
  `gateway_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `midtrans`
--

CREATE TABLE `midtrans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `midtrans_merchant_id` varchar(255) DEFAULT NULL,
  `midtrans_client_key` varchar(255) DEFAULT NULL,
  `midtrans_server_key` varchar(255) DEFAULT NULL,
  `midtrans_sandbox_client_key` varchar(255) DEFAULT NULL,
  `midtrans_sandbox_server_key` varchar(255) DEFAULT NULL,
  `midtrans_is_production` enum('false','true') NOT NULL DEFAULT 'false',
  `disabled` enum('false','true') NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `midtrans`
--

INSERT INTO `midtrans` (`id`, `slug`, `name`, `midtrans_merchant_id`, `midtrans_client_key`, `midtrans_server_key`, `midtrans_sandbox_client_key`, `midtrans_sandbox_server_key`, `midtrans_is_production`, `disabled`, `created_at`, `updated_at`) VALUES
(1, 'midtrans', 'Midtrans', NULL, NULL, NULL, NULL, NULL, 'false', 'true', '2024-12-10 09:46:44', '2024-12-10 09:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2023_09_05_010440_create_permission_tables', 1),
(7, '2023_09_05_012746_create_mikrotiks_table', 1),
(8, '2023_09_05_012757_create_jobs_table', 1),
(9, '2023_09_05_012807_create_add_picture_to_users', 1),
(10, '2023_09_07_085135_create_ppp_types_table', 1),
(11, '2023_09_07_132721_create_pakets_table', 1),
(12, '2023_09_08_001126_create_billings_table', 1),
(13, '2023_09_11_113634_create_websystems_table', 1),
(14, '2023_09_16_065350_create_auto_isolirs_table', 1),
(15, '2023_09_25_032020_create_customer_onts_table', 1),
(16, '2023_09_25_043838_create_deleted_customers_table', 1),
(17, '2023_09_26_033541_create_routers_table', 1),
(18, '2023_10_10_064153_create_orders_table', 1),
(19, '2023_10_24_035129_update_v_1_0_7b', 1),
(20, '2023_10_29_230905_update_v_1_0_8b', 1),
(21, '2023_11_04_214802_create_ppp_profiles_table', 1),
(22, '2024_09_29_211752_create_whatsapp_gateways_table', 1),
(23, '2024_10_05_123334_insert_data_into_whatsapp_gateways', 1),
(24, '2024_10_05_193123_create_api_urls_table', 1),
(25, '2024_11_16_000000_alter_pakets_and_customers_table', 1),
(26, '2024_11_16_113610_create_whatsapp_numbers_table', 1),
(27, '2024_11_16_113611_create_whatsapp_messages_table', 1),
(28, '2024_11_16_113616_create_whatsapp_gateways_table', 1),
(29, '2024_11_16_131636_drop_api_urls_table', 1),
(30, '2024_11_16_132015_alter_auto_isolir_table', 1),
(31, '2024_11_16_132830_update_v_1_0_9b', 1),
(32, '2024_11_27_162153_create_message_logs_table', 1),
(33, '2024_11_16_113600_create_whatsapp_numbers_table', 2),
(34, '2024_11_16_113601_create_whatsapp_gateways_table', 2),
(35, '2024_11_16_113602_create_whatsapp_messages_table', 2),
(36, '2024_12_15_223411_recreate_websystems_table', 2),
(37, '2024_12_28_103343_add_user_id_to_customers_table', 3),
(38, '2024_12_30_162724_add_vlan_id_to_customer_onts', 4);

-- --------------------------------------------------------

--
-- Table structure for table `mikrotiks`
--

CREATE TABLE `mikrotiks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `port` bigint(20) NOT NULL,
  `merk_router` varchar(255) DEFAULT NULL,
  `type_router` varchar(255) DEFAULT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ros_version_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mikrotiks`
--

INSERT INTO `mikrotiks` (`id`, `name`, `slug`, `host`, `username`, `password`, `port`, `merk_router`, `type_router`, `disabled`, `created_at`, `updated_at`, `ros_version_id`) VALUES
(1, 'ShaNet Fiber', 'shanet-fiber-xk2z', 'shanet.my.id', 'shanum.net', 'shanum250117', 1011, NULL, NULL, 'false', '2024-09-29 08:51:44', '2024-11-15 02:28:50', '2'),
(18, 'Irsyad Network', 'irsyad-network-4sl1', '152.42.243.226', 'irsyad.net', 'root1234', 1021, NULL, NULL, 'false', '2024-10-27 03:55:28', '2024-11-15 02:28:50', '2');

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `number` varchar(16) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_status` enum('1','2','3','4') NOT NULL COMMENT '1=menunggu pembayaran, 2=sudah dibayar, 3=kadaluarsa, 4=batal',
  `snap_token` varchar(36) DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `billing_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT NULL,
  `payment_time` timestamp NULL DEFAULT NULL,
  `pdf_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pakets`
--

CREATE TABLE `pakets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mikrotik_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `profile` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pakets`
--

INSERT INTO `pakets` (`id`, `mikrotik_id`, `name`, `profile`, `slug`, `description`, `price`, `disabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 1, 'ShaNet 0 (Upto 7 Mbps)', 'ShaNet_0_5Mbps', 'shanet-0-5mbps-kz7k', 'Speed up to 7 Mbps (Broadband)', 100000, 'false', '2024-09-29 08:52:23', '2024-10-27 07:39:24', NULL),
(3, 1, 'ShaNet 0 (7 Mbps)', 'ShaNet_0_7Mbps', 'shanet-0-7mbps-kzs5', 'Speed 7 Mbps', 120000, 'false', '2024-09-29 08:52:23', '2024-10-27 07:39:24', NULL),
(4, 1, 'ShaNet 1 (10 Mbps)', 'ShaNet_1_10Mbps', 'shanet-1-10mbps-6hxs', 'Speed 10 Mbps', 150000, 'false', '2024-09-29 08:52:23', '2024-10-27 07:39:24', NULL),
(5, 1, 'ShaNet 2 (15 Mbps)', 'ShaNet_2_15Mbps', 'shanet-2-15mbps-m9b8', 'Speed 15 Mbps', 200000, 'false', '2024-09-29 08:52:23', '2024-10-27 07:39:24', NULL),
(6, 1, 'ShaNet 3 (20 Mbps)', 'ShaNet_3_20Mbps', 'shanet-3-20mbps-xizi', 'Speed 20 Mbps', 250000, 'false', '2024-09-29 08:52:23', '2024-10-27 07:39:24', NULL),
(7, 1, 'ShaNet 4 (30 Mbps)', 'ShaNet_4_30Mbps', 'shanet-4-30mbps-4ty1', 'Speed 30 Mbps', 300000, 'false', '2024-09-29 08:52:23', '2024-10-27 09:44:08', NULL),
(14, 1, 'Expire', 'Expire', 'expire-4wur', 'Expire', 0, 'false', '2024-09-29 11:25:40', '2024-10-27 07:39:24', NULL),
(21, 18, 'Basic 0 (7 Mbps)', 'Basic 7 Mbps', 'basic-7-mbps-bdon', 'Server-2 Speed 7 Mbps', 125000, 'false', '2024-10-27 08:29:27', '2024-10-27 08:29:27', NULL),
(22, 18, 'Basic 1 (10 Mbps)', 'Basic 10 Mbps', 'basic-10-mbps-b2rn', 'Server-2 Speed 7 Mbps', 150000, 'false', '2024-10-27 08:29:27', '2024-10-27 08:29:27', NULL),
(23, 18, 'Basic 2 (15 Mbps)', 'Basic 15 Mbps', 'basic-15-mbps-8wec', 'Server-2 Speed 15 Mbps', 200000, 'false', '2024-10-27 08:29:27', '2024-10-27 08:29:27', NULL),
(24, 18, 'Basic 3 (20 Mbps)', 'Basic 20 Mbps', 'basic-20-mbps-ew8c', 'Server-2 Speed 20 Mbps', 250000, 'false', '2024-10-27 08:29:27', '2024-10-27 08:29:27', NULL),
(25, 18, 'Isolir', 'Isolir', 'isolir-egst', 'Isolir', 0, 'false', '2024-10-27 08:29:27', '2024-10-27 08:29:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'false',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `slug`, `disabled`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Midtrans', 'midtrans', 'false', 'Midtrans Payment Gateway', '2024-12-10 09:46:45', '2024-12-10 09:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `title`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', NULL, 0, '2024-12-10 09:46:45', '2024-12-10 09:46:45'),
(2, 'is-admin', 'web', 'Is Admin', 1, '2024-12-10 09:46:45', '2024-12-10 09:46:45'),
(3, 'can-update', 'web', 'Can Update', 1, '2024-12-10 09:46:45', '2024-12-10 09:46:45'),
(4, 'Server', 'web', NULL, 0, '2024-12-10 09:46:50', '2024-12-10 09:46:50'),
(5, 'Role', 'web', NULL, 0, '2024-12-10 09:46:50', '2024-12-10 09:46:50'),
(6, 'Packet', 'web', NULL, 0, '2024-12-10 09:46:50', '2024-12-10 09:46:50'),
(7, 'PPP', 'web', NULL, 0, '2024-12-10 09:46:50', '2024-12-10 09:46:50'),
(8, 'User', 'web', NULL, 0, '2024-12-10 09:46:50', '2024-12-10 09:46:50'),
(9, 'Customer', 'web', NULL, 0, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(10, 'Billing', 'web', NULL, 0, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(11, 'server-list', 'web', 'Server List', 4, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(12, 'server-create', 'web', 'Add Server', 4, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(13, 'server-edit', 'web', 'Edit Server', 4, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(14, 'server-delete', 'web', 'Delete Server', 4, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(15, 'role-list', 'web', 'Role List', 5, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(16, 'role-create', 'web', 'Create Role', 5, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(17, 'role-edit', 'web', 'Edit Role', 5, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(18, 'role-delete', 'web', 'Delete Role', 5, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(19, 'packet-list', 'web', 'Packet List', 6, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(20, 'packet-create', 'web', 'Add Packet', 6, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(21, 'packet-edit', 'web', 'Edit Packet', 6, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(22, 'packet-delete', 'web', 'Delete Packet', 6, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(23, 'secret-list', 'web', 'PPP Secret List', 7, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(24, 'secret-create', 'web', 'Add PPP Secret', 7, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(25, 'secret-edit', 'web', 'Edit PPP Secret', 7, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(26, 'secret-delete', 'web', 'Delete PPP Secret', 7, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(27, 'secret-disable', 'web', 'Disable/Enable PPP Secret', 7, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(28, 'user-list', 'web', 'User List', 8, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(29, 'user-create', 'web', 'Add User', 8, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(30, 'user-edit', 'web', 'Edit User', 8, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(31, 'user-delete', 'web', 'Delete User', 8, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(32, 'customer-list', 'web', 'Customer List', 9, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(33, 'customer-create', 'web', 'Add Customer', 9, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(34, 'customer-edit', 'web', 'Edit Customer', 9, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(35, 'customer-delete', 'web', 'Delete Customer', 9, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(36, 'billing-list', 'web', 'Billing List', 10, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(37, 'billing-create', 'web', 'Create Billing', 10, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(38, 'billing-edit', 'web', 'Edit Billing', 10, '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(39, 'billing-delete', 'web', 'Delete Billing', 10, '2024-12-10 09:46:51', '2024-12-10 09:46:51');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppp_profiles`
--

CREATE TABLE `ppp_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mikrotik_id` bigint(20) UNSIGNED NOT NULL,
  `profile_id` varchar(255) DEFAULT NULL,
  `profile_name` varchar(255) DEFAULT NULL,
  `local_address` varchar(255) DEFAULT NULL,
  `remote_address` varchar(255) DEFAULT NULL,
  `dns_server` varchar(255) DEFAULT NULL,
  `use_ipv6` enum('no','yes','required','default') NOT NULL DEFAULT 'yes',
  `use_mpls` enum('no','yes','required','default') NOT NULL DEFAULT 'default',
  `use_encryption` enum('no','yes','required','default') NOT NULL DEFAULT 'default',
  `use_compression` enum('no','yes','default') NOT NULL DEFAULT 'default',
  `max_upload` varchar(255) DEFAULT NULL,
  `max_download` varchar(255) DEFAULT NULL,
  `min_upload` varchar(255) DEFAULT NULL,
  `min_download` varchar(255) DEFAULT NULL,
  `burst_upload` varchar(255) DEFAULT NULL,
  `burst_download` varchar(255) DEFAULT NULL,
  `burst_threshold_upload` varchar(255) DEFAULT NULL,
  `burst_threshold_download` varchar(255) DEFAULT NULL,
  `burst_time_upload` varchar(255) DEFAULT NULL,
  `burst_time_download` varchar(255) DEFAULT NULL,
  `priority` enum('1','2','3','4','5','6','7','8') NOT NULL DEFAULT '8',
  `rasio_cir` varchar(255) DEFAULT NULL,
  `session_timeout` varchar(255) DEFAULT NULL,
  `idle_timeout` varchar(255) DEFAULT NULL,
  `only_one` enum('no','yes','default') NOT NULL DEFAULT 'default',
  `insert_queue_before` varchar(255) DEFAULT NULL,
  `parent_queue` varchar(255) DEFAULT NULL,
  `queue_type` varchar(255) DEFAULT NULL,
  `script_on_up` varchar(255) DEFAULT NULL,
  `script_on_down` varchar(255) DEFAULT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppp_types`
--

CREATE TABLE `ppp_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ppp_types`
--

INSERT INTO `ppp_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'any', '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(2, 'async', '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(3, 'l2tp', '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(4, 'ovpn', '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(5, 'pppoe', '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(6, 'pptp', '2024-12-10 09:46:51', '2024-12-10 09:46:51'),
(7, 'sstp', '2024-12-10 09:46:51', '2024-12-10 09:46:51');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `periode` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `billing_number` varchar(255) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `paket_name` varchar(255) NOT NULL,
  `paket_price` int(11) NOT NULL,
  `tax` int(11) DEFAULT NULL,
  `created_name` varchar(255) NOT NULL,
  `teller_name` varchar(255) DEFAULT NULL,
  `payment_time` timestamp NULL DEFAULT NULL,
  `payment_methode` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `deposit_bank_name` varchar(255) DEFAULT NULL,
  `deposit_account_name` varchar(255) DEFAULT NULL,
  `deposit_account_number` varchar(255) DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `slug`, `periode`, `number`, `billing_number`, `customer_id`, `customer_name`, `customer_address`, `paket_name`, `paket_price`, `tax`, `created_name`, `teller_name`, `payment_time`, `payment_methode`, `bank_name`, `account_name`, `account_number`, `deposit_bank_name`, `deposit_account_name`, `deposit_account_number`, `reference_id`, `created_at`, `updated_at`) VALUES
(1, '15-xura', 'October 2024', 'KWT-00001-October-2024', '15-pfum-10-2024', 15, '200211114@armiyanti', NULL, 'Paket 2 (15 Mbps)', 200000, 0, 'Admin', 'Admin', '2024-09-30 23:00:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 23:00:03', '2024-09-30 23:00:03'),
(2, '23-5jkg', 'October 2024', 'KWT-00002-October-2024', '23-m8v1-10-2024', 23, '150230804@rafka', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Admin', 'Admin', '2024-09-30 23:03:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 23:03:12', '2024-09-30 23:03:12'),
(3, '4-tqly', 'October 2024', 'KWT-00003-October-2024', '4-71zw-10-2024', 4, '150210411@surya', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Admin', 'Admin', '2024-09-30 23:03:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 23:03:27', '2024-09-30 23:03:27'),
(4, '3-a20n', 'October 2024', 'KWT-00004-October-2024', '3-l4m8-10-2024', 3, '100210220@niaagustin', NULL, 'Paket 0.1 (7Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-01 00:52:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-01 00:52:04', '2024-10-01 00:52:04'),
(6, '50-tgyz', 'October 2024', 'KWT-00001-October-2024', '50-hgqu-10-2024', 50, '150230725@ustdfery', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-01 10:51:28', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-01 10:51:28', '2024-10-01 10:51:28'),
(7, '6-5wd4', 'October 2024', 'KWT-00002-October-2024', '6-yynv-10-2024', 6, 'Cucu Suryani', 'Kp. Sukamaju RT 10/07 No. 37 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-01 10:53:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-01 10:53:22', '2024-10-01 10:53:22'),
(8, '69-oob6', 'October 2024', 'KWT-00003-October-2024', '69-bs6v-10-2024', 69, 'Alya Iklima Hidayah', 'Perum Panorama Indah B2-4 RT 013/013 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-01 22:35:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-01 22:35:59', '2024-10-01 22:35:59'),
(9, '29-z5et', 'October 2024', 'KWT-00004-October-2024', '29-upjm-10-2024', 29, 'Euis Ratnasari', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 14:13:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 14:13:05', '2024-10-02 14:13:05'),
(10, '27-4aq2', 'October 2024', 'KWT-00005-October-2024', '27-brvn-10-2024', 27, 'Novi Eka Purwaningsih', 'Kp. Sukamaju RT 09/07 Green Sapir Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 14:13:15', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 14:13:15', '2024-10-02 14:13:15'),
(11, '35-om5x', 'October 2024', 'KWT-00006-October-2024', '35-10nm-10-2024', 35, '100221019@hafis', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 14:13:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 14:13:53', '2024-10-02 14:13:53'),
(12, '17-z6w4', 'October 2024', 'KWT-00007-October-2024', '17-eqvs-10-2024', 17, '150230208@azril', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 14:14:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 14:14:01', '2024-10-02 14:14:01'),
(13, '74-9qb9', 'October 2024', 'KWT-00008-October-2024', '74-cvve-10-2024', 74, 'Egy Laksma Suandi', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 14:14:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 14:14:59', '2024-10-02 14:14:59'),
(14, '18-zc5k', 'October 2024', 'KWT-00009-October-2024', '18-rzgx-10-2024', 18, '150230402@iip', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 23:51:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 23:51:08', '2024-10-02 23:51:08'),
(15, '1-qtcg', 'October 2024', 'KWT-00010-October-2024', '1-mrgi-10-2024', 1, '100210121@subhan', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-03 15:23:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-03 15:23:27', '2024-10-03 15:23:27'),
(16, 'inv-77-wh2l', 'October 2024', '00011-KWT-October-2024', '77-ymlm-10-2024', 77, 'Nazri Nasier', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-04 05:21:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-04 05:21:33', '2024-10-04 05:21:33'),
(17, '56-ggr6', 'October 2024', 'KWT-00001-October-2024', '56-fw6k-10-2024', 56, 'Agustian Setiadi', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 02:28:46', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 02:28:46', '2024-10-05 02:28:46'),
(18, '62-zalm', 'October 2024', 'KWT-00002-October-2024', '62-nxjg-10-2024', 62, '250230601@abdulrahman', NULL, 'Paket 3 (20 Mbps)', 250000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 23:01:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 23:01:07', '2024-10-05 23:01:07'),
(19, '20-jhup', 'October 2024', 'KWT-00003-October-2024', '20-nisa-10-2024', 20, '150230503@lamjaya', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 00:38:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 00:38:44', '2024-10-06 00:38:44'),
(20, '61-pcpn', 'October 2024', 'KWT-00004-October-2024', '61-9plz-10-2024', 61, '150240817@ibuani', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 12:07:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 12:07:03', '2024-10-06 12:07:03'),
(21, '5-e0ho', 'October 2024', 'KWT-00005-October-2024', '5-dlov-10-2024', 5, '150210712@koesnadi', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 12:14:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 12:14:07', '2024-10-06 12:14:07'),
(22, '5-e0ho', 'October 2024', 'KWT-00006-October-2024', '5-dlov-10-2024', 5, '150210712@koesnadi', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 12:14:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 12:14:09', '2024-10-06 12:14:09'),
(23, '48-9htp', 'October 2024', 'KWT-00007-October-2024', '48-bz2d-10-2024', 48, 'Sri Sulastri', 'KP. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 12:29:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 12:29:23', '2024-10-06 12:29:23'),
(26, '46-dxdt', 'October 2024', 'KWT-00010-October-2024', '46-jdks-10-2024', 46, 'Rohani Rusmiati', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 05:35:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 05:35:57', '2024-10-07 05:35:57'),
(31, '13-ce0x', 'October 2024', 'KWT-00012-October-2024', '13-cpsx-10-2024', 13, '100221107@anggi', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 06:54:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 06:54:50', '2024-10-07 06:54:50'),
(33, '22-pdup', 'October 2024', 'KWT-00013-October-2024', '22-m7in-10-2024', 22, 'Ratih Aisyah', 'Jl. Veteran RT 003/002 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 07:59:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 07:59:57', '2024-10-07 07:59:57'),
(36, '44-cl5s', 'October 2024', 'KWT-00014-October-2024', '44-fqr3-10-2024', 44, '150221217@furqon', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 08:23:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 08:23:23', '2024-10-07 08:23:23'),
(37, '39-odak', 'October 2024', 'KWT-00015-October-2024', '39-xekf-10-2024', 39, '100210120@hexsa', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 04:20:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 04:20:01', '2024-10-08 04:20:01'),
(38, '57-jxct', 'October 2024', 'KWT-00016-October-2024', '57-uhdj-10-2024', 57, '200230910@iyan', NULL, 'Paket 2 (15 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 05:26:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 05:26:50', '2024-10-08 05:26:50'),
(39, '31-rxu0', 'October 2024', 'KWT-00017-October-2024', '31-dqf3-10-2024', 31, '150240211@pakzaky', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 05:35:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 05:35:58', '2024-10-08 05:35:58'),
(41, '73-d29v', 'October 2024', 'KWT-00019-October-2024', '73-cxuj-10-2024', 73, 'Asep Topik Hidayat', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 09:36:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 09:36:03', '2024-10-08 09:36:03'),
(42, '67-jlue', 'October 2024', 'KWT-00020-October-2024', '67-eice-10-2024', 67, 'Lulu Andriani', 'Kp. Sukamaju RT 10/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-09 02:45:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 02:45:06', '2024-10-09 02:45:06'),
(43, '68-dpyo', 'October 2024', 'KWT-00021-October-2024', '68-10jr-10-2024', 68, 'Ma Aisah', 'Kp. Sukamaju RT 10/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-09 02:50:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 02:50:05', '2024-10-09 02:50:05'),
(44, '45-ydmk', 'October 2024', 'KWT-00022-October-2024', '45-mbmp-10-2024', 45, '150221106@aam', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-09 10:34:17', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 10:34:17', '2024-10-09 10:34:17'),
(45, 'inv-78-hgvh', 'October 2024', '00023-KWT-October-2024', '78-sil8-10-2024', 78, 'Moh. Gian Firtyanza', 'Perum Pesona Griya Asri Blok D2 No. 15 Ciseureuh', 'Paket 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-09 13:04:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 13:04:59', '2024-10-09 13:04:59'),
(46, '66-9rk2', 'October 2024', 'KWT-00001-October-2024', '66-o7dx-10-2024', 66, '150240305@goradhigha', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 01:42:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 01:42:41', '2024-10-10 01:42:41'),
(47, '71-rpnq', 'October 2024', 'KWT-00002-October-2024', '71-lhrc-10-2024', 71, '100240917@enen', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 13:02:32', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 13:02:32', '2024-10-10 13:02:32'),
(48, '55-d3gz', 'October 2024', 'KWT-00003-October-2024', '55-eg1k-10-2024', 55, '100240225@agis', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 13:02:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 13:02:44', '2024-10-10 13:02:44'),
(49, '63-qpbs', 'October 2024', 'KWT-00004-October-2024', '63-78f3-10-2024', 63, '150240824@dendi', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 16:00:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 16:00:34', '2024-10-10 16:00:34'),
(50, '34-omj3', 'October 2024', 'KWT-00005-October-2024', '34-ej6y-10-2024', 34, 'Debbie Febriana', 'KP. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-11 03:24:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-11 03:24:26', '2024-10-11 03:24:26'),
(51, '51-jg1t', 'October 2024', 'KWT-00006-October-2024', '51-d8xq-10-2024', 51, '150240711@tehenok', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-11 06:05:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-11 06:05:12', '2024-10-11 06:05:12'),
(52, '64-ckry', 'October 2024', 'KWT-00007-October-2024', '64-mv5g-10-2024', 64, '100240901@bunehe', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-11 10:51:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-11 10:51:08', '2024-10-11 10:51:08'),
(53, '53-kx3r', 'October 2024', 'KWT-00008-October-2024', '53-o8wp-10-2024', 53, 'Maharani Widyaningrum', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-11 10:51:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-11 10:51:27', '2024-10-11 10:51:27'),
(54, '19-akuh', 'October 2024', 'KWT-00009-October-2024', '19-11cq-10-2024', 19, 'Ahmad Saepuloh', 'Kp. Sukamaju RT 09/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-11 11:58:11', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-11 11:58:11', '2024-10-11 11:58:11'),
(55, '26-czlm', 'October 2024', 'KWT-00010-October-2024', '26-iiw3-10-2024', 26, 'Narsiah', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 02:33:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 02:33:08', '2024-10-13 02:33:08'),
(56, '9-vpav', 'October 2024', 'KWT-00011-October-2024', '9-fezd-10-2024', 9, '100221009@eko', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 04:15:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 04:15:50', '2024-10-13 04:15:50'),
(57, '42-lyzo', 'October 2024', 'KWT-00012-October-2024', '42-lhkm-10-2024', 42, 'Muhammad Fahrizal', 'Kp. Sukamaju RT 09/07 Gg. Arimbi Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 11:17:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 11:17:53', '2024-10-13 11:17:53'),
(58, '2-j742', 'October 2024', 'KWT-00013-October-2024', '2-qkrr-10-2024', 2, 'Iis Sugiarti', 'KP. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 14:17:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 14:17:08', '2024-10-13 14:17:08'),
(59, '36-tslu', 'October 2024', 'KWT-00014-October-2024', '36-gqol-10-2024', 36, 'Triyanti', 'Gg. Beringin RT 077/008 Nagrikaler Purwakarta', 'Paket 2 (15 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 23:16:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 23:16:59', '2024-10-13 23:16:59'),
(60, '33-wt0q', 'October 2024', 'KWT-00015-October-2024', '33-tr6c-10-2024', 33, '150240311@slamet', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 02:37:49', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 02:37:49', '2024-10-14 02:37:49'),
(61, '7-hrq7', 'October 2024', 'KWT-00016-October-2024', '7-izge-10-2024', 7, 'Sumarni', 'KP. Sukamaju RT 09/07 Kelurahan Ciseureuh', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 03:47:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 03:47:42', '2024-10-14 03:47:42'),
(62, '58-cbdt', 'October 2024', 'KWT-00017-October-2024', '58-aqr3-10-2024', 58, 'Nur Fakriah Putri', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 12:20:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 12:20:00', '2024-10-14 12:20:00'),
(63, '28-731i', 'October 2024', 'KWT-00018-October-2024', '28-cgg8-10-2024', 28, '100231210@hasma', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 22:25:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 22:25:33', '2024-10-14 22:25:33'),
(64, '38-afqj', 'October 2024', 'KWT-00019-October-2024', '38-mfwl-10-2024', 38, 'Yuan Firmansyah', 'KP. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 22:57:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 22:57:44', '2024-10-14 22:57:44'),
(65, '24-ci2r', 'October 2024', 'KWT-00020-October-2024', '24-xol2-10-2024', 24, 'Neni Nurhaeni', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 00:36:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 00:36:33', '2024-10-15 00:36:33'),
(66, '40-tnwv', 'October 2024', 'KWT-00021-October-2024', '40-erql-10-2024', 40, '100240501@siti', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 02:37:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 02:37:35', '2024-10-15 02:37:35'),
(67, '21-pu4z', 'October 2024', 'KWT-00022-October-2024', '21-ttf1-10-2024', 21, '150230717@ibuayu', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 03:18:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 03:18:08', '2024-10-15 03:18:08'),
(68, '10-nmsn', 'October 2024', 'KWT-00023-October-2024', '10-sv7p-10-2024', 10, 'Ali Akbar', 'KP. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 04:46:11', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 04:46:11', '2024-10-15 04:46:11'),
(70, '25-9wf7', 'October 2024', 'KWT-00024-October-2024', '25-ceiz-10-2024', 25, 'Etik Aprilia Ratna Sari', 'Kp. Sukamaju RT 05/07 Gg. Paraji Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 08:02:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 08:02:43', '2024-10-15 08:02:43'),
(71, '59-nsz8', 'October 2024', 'KWT-00025-October-2024', '59-z0xb-10-2024', 59, 'Amni Fitria', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 11:32:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 11:32:23', '2024-10-15 11:32:23'),
(72, '49-ucyx', 'October 2024', 'KWT-00026-October-2024', '49-iu4n-10-2024', 49, '150240621@miayamgeprek', NULL, 'ShaNet 1 (10 Mbps)', 75000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 11:32:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 11:32:29', '2024-10-15 11:32:29'),
(73, '32-vyc5', 'October 2024', 'KWT-00027-October-2024', '32-8mvw-10-2024', 32, '100240219@tehai', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 11:32:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 11:32:47', '2024-10-15 11:32:47'),
(74, '54-5enx', 'October 2024', 'KWT-00028-October-2024', '54-mxv3-10-2024', 54, '120240310@linda', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 12:53:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 12:53:47', '2024-10-15 12:53:47'),
(75, '30-ygl8', 'October 2024', 'KWT-00029-October-2024', '30-3yvw-10-2024', 30, '100231217@amoy', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 12:54:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 12:54:42', '2024-10-15 12:54:42'),
(76, '60-el5z', 'October 2024', 'KWT-00030-October-2024', '60-zokf-10-2024', 60, '150240612@jepri', NULL, 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 22:26:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 22:26:21', '2024-10-15 22:26:21'),
(78, '41-ikam', 'October 2024', 'KWT-00031-October-2024', '41-xfht-10-2024', 41, 'Wandi Suhendi', 'KP. Sukamaju RT 05/07 Kelurahan Ciseureuh', 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 00:17:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 00:17:31', '2024-10-16 00:17:31'),
(79, '70-ysxb', 'October 2024', 'KWT-00032-October-2024', '70-nzgg-10-2024', 70, 'Pak Kurdi 2', NULL, 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 00:37:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 00:37:52', '2024-10-16 00:37:52'),
(80, '65-l5du', 'October 2024', 'KWT-00033-October-2024', '65-psah-10-2024', 65, 'Pak Kurdi 1', NULL, 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 00:37:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 00:37:57', '2024-10-16 00:37:57'),
(81, '72-xq6x', 'October 2024', 'KWT-00034-October-2024', '72-r558-10-2024', 72, '100240715@bmcell', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 01:11:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 01:11:52', '2024-10-16 01:11:52'),
(82, '11-tg3c', 'October 2024', 'KWT-00035-October-2024', '11-nuca-10-2024', 11, 'Teh Rita', 'KP. Sukamaju RT 09/07 Kelurahan Ciseureuh', 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 04:24:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 04:24:27', '2024-10-16 04:24:27'),
(83, '43-pwwu', 'October 2024', 'KWT-00036-October-2024', '43-1agz-10-2024', 43, 'Pak Edi', 'Kp. Sukamaju RT 009/007 Green Sapir Kelurahan Ciseureuh', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 08:33:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 08:33:09', '2024-10-16 08:33:09'),
(84, '16-iflw', 'October 2024', 'KWT-00037-October-2024', '16-zqoq-10-2024', 16, '100230102@rdcell', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 08:47:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 08:47:20', '2024-10-16 08:47:20'),
(85, '14-kclc', 'October 2024', 'KWT-00038-October-2024', '14-l6j9-10-2024', 14, 'Ai Solihah', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 10:22:40', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 10:22:40', '2024-10-16 10:22:40'),
(86, 'inv-79-ufiq', 'October 2024', '00039-KWT-October-2024', '79-znlv-10-2024', 79, 'Intan Ayu Sri Wahyuni', 'Jl. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 127500, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-17 07:45:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-17 07:45:35', '2024-10-17 07:45:35'),
(87, '12-rjwc', 'October 2024', 'KWT-00001-October-2024', '12-fo6j-10-2024', 12, 'Pak Suud', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-18 05:48:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-18 05:48:14', '2024-10-18 05:48:14'),
(90, 'inv-80-aywv', 'October 2024', '00002-KWT-October-2024', '80-ccub-10-2024', 80, 'Nurhayati', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-20 12:34:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-20 12:34:23', '2024-10-20 12:34:23'),
(92, '52-coep', 'October 2024', 'KWT-00001-October-2024', '52-ezsd-10-2024', 52, 'Ahmad Sugianto', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-25 09:05:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-25 09:05:01', '2024-10-25 09:05:01'),
(93, 'inv-81-kup4', 'October 2024', '00002-KWT-October-2024', '81-e7x4-10-2024', 81, 'Irvan Mohammad Regent', 'Perum Griya Asri Block K1 No. 10 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-27 03:01:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-27 03:01:54', '2024-10-27 03:01:54'),
(94, '8-zffk', 'October 2024', 'KWT-00001-October-2024', '8-hcka-10-2024', 8, 'Teh Darsih', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-10-28 00:05:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-28 00:05:31', '2024-10-28 00:05:31'),
(95, 'inv-137-qpom', 'October 2024', '00002-KWT-October-2024', '137-yz1p-10-2024', 137, 'Fera', 'Kp. Rawabolang RT 025/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-30 01:18:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-30 01:18:39', '2024-10-30 01:18:39'),
(96, 'inv-138-z36y', 'October 2024', '00001-KWT-October-2024', '138-yqkj-10-2024', 138, 'Robi Anggara', 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-30 03:21:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-30 03:21:52', '2024-10-30 03:21:52'),
(98, '20-hy9f', 'November 2024', 'KWT-00002-November-2024', '20-z0ha-11-2024', 20, 'Pak Lili', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 19:20:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 19:20:43', '2024-11-01 19:20:43'),
(99, '75-eayk', 'November 2024', 'KWT-00003-November-2024', '75-rpo9-11-2024', 75, 'H. M Abu Rasyid. AS. SH', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 19:21:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 19:21:56', '2024-11-01 19:21:56'),
(100, '69-yujj', 'November 2024', 'KWT-00004-November-2024', '69-hixd-11-2024', 69, 'Alya Iklima Hidayah', 'Perum Panorama Indah B2-4 RT 013/013 Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 19:23:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 19:23:31', '2024-11-01 19:23:31'),
(101, '81-6a8z', 'November 2024', 'KWT-00005-November-2024', '81-kbqz-11-2024', 81, 'Irvan Mohammad Regent', 'Perum Griya Asri Block K1 No. 10 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 19:26:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 19:26:12', '2024-11-01 19:26:12'),
(102, '6-nwtz', 'November 2024', 'KWT-00006-November-2024', '6-gdel-11-2024', 6, 'Cucu Suryani', 'Kp. Sukamaju RT 009/007 No. 37 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 19:28:46', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 19:28:46', '2024-11-01 19:28:46'),
(103, '79-fdhp', 'November 2024', 'KWT-00007-November-2024', '79-xwky-11-2024', 79, 'Intan Ayu Sri Wahyuni', 'Jl. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 19:32:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 19:32:03', '2024-11-01 19:32:03'),
(104, '4-ymng', 'November 2024', 'KWT-00008-November-2024', '4-hcod-11-2024', 4, 'Pak Surya', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 19:33:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 19:33:36', '2024-11-01 19:33:36'),
(105, '18-5sgy', 'November 2024', 'KWT-00009-November-2024', '18-nx8q-11-2024', 18, 'Iip', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 00:06:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 00:06:47', '2024-11-02 00:06:47'),
(106, '117-834k', 'November 2024', 'KWT-00010-November-2024', '117-4vfc-11-2024', 117, 'Pak Ojak', 'kp', 'Basic 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 16:09:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 16:09:31', '2024-11-02 16:09:31'),
(107, '106-ocis', 'November 2024', 'KWT-00011-November-2024', '106-kldl-11-2024', 106, 'Iqbal Budi Prasetya', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 16:09:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 16:09:52', '2024-11-02 16:09:52'),
(108, '48-q5sy', 'November 2024', 'KWT-00012-November-2024', '48-e4lh-11-2024', 48, 'Sri Sulastri', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-02 18:15:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 18:15:36', '2024-11-02 18:15:36'),
(109, '89-w9lx', 'November 2024', 'KWT-00013-November-2024', '89-qcc8-11-2024', 89, 'Adis', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 19:04:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 19:04:59', '2024-11-02 19:04:59'),
(110, '92-piuq', 'November 2024', 'KWT-00014-November-2024', '92-33wx-11-2024', 92, 'Juju Juarsih', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 19:05:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 19:05:56', '2024-11-02 19:05:56'),
(111, '120-yy5m', 'November 2024', 'KWT-00015-November-2024', '120-cps7-11-2024', 120, 'Usman Abdul Sobari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 19:06:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 19:06:34', '2024-11-02 19:06:34'),
(112, '3-k5hw', 'November 2024', 'KWT-00016-November-2024', '3-r0xw-11-2024', 3, 'Asep Nasihin', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-02 20:17:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 20:17:39', '2024-11-02 20:17:39'),
(113, '56-2oy9', 'November 2024', 'KWT-00017-November-2024', '56-dhev-11-2024', 56, 'Agustian Setiadi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-02 23:53:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 23:53:55', '2024-11-02 23:53:55'),
(114, '44-gcdo', 'November 2024', 'KWT-00018-November-2024', '44-myok-11-2024', 44, 'Tri Nurhidayati', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-03 00:49:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-03 00:49:01', '2024-11-03 00:49:01'),
(117, '80-21tq', 'November 2024', 'KWT-00019-November-2024', '80-jhj8-11-2024', 80, 'Nurhayati', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-03 03:58:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-03 03:58:58', '2024-11-03 03:58:58'),
(118, '13-lgpg', 'November 2024', 'KWT-00020-November-2024', '13-va2n-11-2024', 13, 'Mamah Anggi', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-03 06:37:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-03 06:37:04', '2024-11-03 06:37:04'),
(119, '15-9m9o', 'November 2024', 'KWT-00021-November-2024', '15-vsgl-11-2024', 15, 'Armiyanti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 200000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-03 16:04:16', 'transfer', 'Bank Mandiri', 'Dian Ardiansyah', '1730012695905', 'Bank Mandiri', 'Armiyanti', '1730012690715', '2411031122070696562', '2024-11-03 16:04:16', '2024-11-03 16:04:16'),
(120, '53-c8b4', 'November 2024', 'KWT-00022-November-2024', '53-td4p-11-2024', 53, 'Maharani Widyaningrum', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-03 17:38:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-03 17:38:29', '2024-11-03 17:38:29'),
(121, '62-fgwi', 'November 2024', 'KWT-00023-November-2024', '62-qpue-11-2024', 62, 'Yeyet Abdulrachman', 'Jl. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 3 (20 Mbps)', 200000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-03 20:05:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-03 20:05:57', '2024-11-03 20:05:57'),
(122, '50-bn5o', 'November 2024', 'KWT-00024-November-2024', '50-gzip-11-2024', 50, 'Ferry Kurniawan', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-03 20:07:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-03 20:07:54', '2024-11-03 20:07:54'),
(123, '29-sehu', 'November 2024', 'KWT-00025-November-2024', '29-ai5p-11-2024', 29, 'Euis Ratnasari', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-04 00:46:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-04 00:46:26', '2024-11-04 00:46:26'),
(124, '74-kjhe', 'November 2024', 'KWT-00026-November-2024', '74-zcar-11-2024', 74, 'Egy Laksma Suandi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-04 01:48:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-04 01:48:13', '2024-11-04 01:48:13'),
(125, '61-w1ex', 'November 2024', 'KWT-00027-November-2024', '61-l8uy-11-2024', 61, 'Bu Ani', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-04 04:12:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-04 04:12:55', '2024-11-04 04:12:55'),
(126, '35-pftv', 'November 2024', 'KWT-00028-November-2024', '35-fivd-11-2024', 35, 'Hafis', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-04 16:43:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-04 16:43:09', '2024-11-04 16:43:09'),
(127, '17-shqz', 'November 2024', 'KWT-00029-November-2024', '17-pjbc-11-2024', 17, 'Azriel', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-04 16:43:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-04 16:43:53', '2024-11-04 16:43:53'),
(128, '5-fivv', 'November 2024', 'KWT-00030-November-2024', '5-olvh-11-2024', 5, 'Koesnadi', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-04 19:34:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-04 19:34:09', '2024-11-04 19:34:09'),
(129, '22-ypzf', 'November 2024', 'KWT-00031-November-2024', '22-zdnw-11-2024', 22, 'Ratih Aisyah', 'Jl. Veteran RT 003/002 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-04 19:36:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-04 19:36:56', '2024-11-04 19:36:56'),
(130, '78-yiah', 'November 2024', 'KWT-00032-November-2024', '78-439n-11-2024', 78, 'Moh. Gian Firtyanza', 'PerumGriya Asri Blok D2 No. 15 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-05 14:29:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:29:31', '2024-11-05 14:29:31'),
(131, '113-4eij', 'November 2024', 'KWT-00033-November-2024', '113-k78f-11-2024', 113, 'Neng Siti', NULL, 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:39:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:39:31', '2024-11-05 14:39:31'),
(132, '132-6ujw', 'November 2024', 'KWT-00034-November-2024', '132-tavi-11-2024', 132, 'Pak Agus', NULL, 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:43:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:43:58', '2024-11-05 14:43:58'),
(133, '133-c3bx', 'November 2024', 'KWT-00035-November-2024', '133-3xxc-11-2024', 133, 'Sulis Rumah', NULL, 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:44:51', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:44:51', '2024-11-05 14:44:51'),
(134, '127-unpt', 'November 2024', 'KWT-00036-November-2024', '127-6ul6-11-2024', 127, 'Misbah', NULL, 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:45:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:45:29', '2024-11-05 14:45:29'),
(135, '83-jbp6', 'November 2024', 'KWT-00037-November-2024', '83-jjde-11-2024', 83, 'Anggi', NULL, 'Basic 2 (15 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:46:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:46:05', '2024-11-05 14:46:05'),
(136, '131-rjhx', 'November 2024', 'KWT-00038-November-2024', '131-ey0h-11-2024', 131, 'Sulis Toko', NULL, 'Basic 2 (15 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:46:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:46:31', '2024-11-05 14:46:31'),
(137, '97-geym', 'November 2024', 'KWT-00039-November-2024', '97-kuvz-11-2024', 97, 'Pepen', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:47:25', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:47:25', '2024-11-05 14:47:25'),
(138, '99-e9ff', 'November 2024', 'KWT-00040-November-2024', '99-yubd-11-2024', 99, 'Miung', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:48:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:48:20', '2024-11-05 14:48:20'),
(139, '123-gxjd', 'November 2024', 'KWT-00041-November-2024', '123-7ol3-11-2024', 123, 'Salmah Saleha', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:49:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:49:13', '2024-11-05 14:49:13'),
(140, '87-ewcd', 'November 2024', 'KWT-00042-November-2024', '87-4saf-11-2024', 87, 'Iis Ismawati', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:50:45', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:50:45', '2024-11-05 14:50:45'),
(141, '118-xevn', 'November 2024', 'KWT-00043-November-2024', '118-ygw8-11-2024', 118, 'Redi Natisna', 'Kp. Krajan RT. 010/003 Desa Maracang', 'Basic 2 (15 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:51:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:51:24', '2024-11-05 14:51:24'),
(142, '124-cndz', 'November 2024', 'KWT-00044-November-2024', '124-5ek2-11-2024', 124, 'Neneng Suratni', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:51:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:51:50', '2024-11-05 14:51:50'),
(143, '137-p94i', 'November 2024', 'KWT-00045-November-2024', '137-h5py-11-2024', 137, 'Fera', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:53:11', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:53:11', '2024-11-05 14:53:11'),
(144, '88-w7fo', 'November 2024', 'KWT-00046-November-2024', '88-tmyq-11-2024', 88, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:53:45', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:53:45', '2024-11-05 14:53:45'),
(145, '119-mvwj', 'November 2024', 'KWT-00047-November-2024', '119-p6ru-11-2024', 119, 'Wahyu Maulana', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 14:55:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 14:55:24', '2024-11-05 14:55:24'),
(146, '86-cldg', 'November 2024', 'KWT-00048-November-2024', '86-wo9w-11-2024', 86, 'Desi Nurdianti', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 15:03:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 15:03:44', '2024-11-05 15:03:44'),
(147, '85-xhxk', 'November 2024', 'KWT-00049-November-2024', '85-lcos-11-2024', 85, 'Siti Mariam', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 15:05:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 15:05:39', '2024-11-05 15:05:39'),
(148, '130-vmyz', 'November 2024', 'KWT-00050-November-2024', '130-eouu-11-2024', 130, 'Darma Wirawan', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 15:07:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 15:07:24', '2024-11-05 15:07:24'),
(149, '1-hahb', 'November 2024', 'KWT-00051-November-2024', '1-mbm0-11-2024', 1, 'Muh Subhan', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-05 18:03:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 18:03:10', '2024-11-05 18:03:10'),
(150, '73-tphs', 'November 2024', 'KWT-00052-November-2024', '73-vpym-11-2024', 73, 'Asep Topik Hidayat', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-05 18:25:16', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 18:25:16', '2024-11-05 18:25:16'),
(151, '27-m8wy', 'November 2024', 'KWT-00053-November-2024', '27-axct-11-2024', 27, 'Novi Eka Purwaningsih', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-05 18:37:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 18:37:33', '2024-11-05 18:37:33'),
(152, '116-wq63', 'November 2024', 'KWT-00054-November-2024', '116-3kav-11-2024', 116, 'Nurul Aeni', 'Kp. Krajan RT. 008/002 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 20:12:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 20:12:23', '2024-11-05 20:12:23'),
(153, '125-xih2', 'November 2024', 'KWT-00055-November-2024', '125-hnim-11-2024', 125, 'Herman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-05 22:41:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-05 22:41:41', '2024-11-05 22:41:41'),
(154, '11-dwvj', 'November 2024', 'KWT-00056-November-2024', '11-nnva-11-2024', 11, 'Rita Susanti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-06 03:00:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-06 03:00:22', '2024-11-06 03:00:22'),
(155, '110-hwct', 'November 2024', 'KWT-00057-November-2024', '110-oy0b-11-2024', 110, 'H Imon', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-06 12:46:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-06 12:46:27', '2024-11-06 12:46:27'),
(156, '104-i9nm', 'November 2024', 'KWT-00058-November-2024', '104-ikpa-11-2024', 104, 'RT Oji', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-06 17:43:16', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-06 17:43:16', '2024-11-06 17:43:16'),
(157, '98-33re', 'November 2024', 'KWT-00059-November-2024', '98-oqfi-11-2024', 98, 'Wayanto', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-06 17:43:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-06 17:43:38', '2024-11-06 17:43:38'),
(158, '95-uq2j', 'November 2024', 'KWT-00060-November-2024', '95-ime6-11-2024', 95, 'Rosidah', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-06 19:56:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-06 19:56:50', '2024-11-06 19:56:50'),
(159, '112-gacb', 'November 2024', 'KWT-00061-November-2024', '112-3hui-11-2024', 112, 'Okis', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-06 19:57:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-06 19:57:07', '2024-11-06 19:57:07'),
(160, '23-gieb', 'November 2024', 'KWT-00062-November-2024', '23-iwy7-11-2024', 23, 'AA Ramli', 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-06 20:10:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-06 20:10:10', '2024-11-06 20:10:10'),
(161, '66-kcjt', 'November 2024', 'KWT-00063-November-2024', '66-pyhi-11-2024', 66, 'Kodrat (GOR ADHIGA)', 'Jl. Sukamaju RT 009/007 Kelurahan Ciseureuh', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-07 05:29:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 05:29:03', '2024-11-07 05:29:03'),
(162, '126-iqmx', 'November 2024', 'KWT-00064-November-2024', '126-8b5n-11-2024', 126, 'Syarip', NULL, 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 05:54:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 05:54:34', '2024-11-07 05:54:34'),
(163, '128-b5xs', 'November 2024', 'KWT-00065-November-2024', '128-tssx-11-2024', 128, 'Jajang Salahudin', 'Kp. Krajan RT. 003/001 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 05:54:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 05:54:58', '2024-11-07 05:54:58'),
(164, '96-rg7z', 'November 2024', 'KWT-00066-November-2024', '96-uzx3-11-2024', 96, 'Cici', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 05:57:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 05:57:10', '2024-11-07 05:57:10'),
(165, '129-thct', 'November 2024', 'KWT-00067-November-2024', '129-1tat-11-2024', 129, 'Dadi Kusnadi', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 05:57:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 05:57:35', '2024-11-07 05:57:35'),
(166, '82-5qbn', 'November 2024', 'KWT-00068-November-2024', '82-5zh5-11-2024', 82, 'Nuryani', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 05:58:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 05:58:00', '2024-11-07 05:58:00'),
(167, '103-qzrr', 'November 2024', 'KWT-00069-November-2024', '103-zxem-11-2024', 103, 'Juhdi', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 05:58:30', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 05:58:30', '2024-11-07 05:58:30'),
(168, '114-hcjk', 'November 2024', 'KWT-00070-November-2024', '114-soki-11-2024', 114, 'Dedi Haerudi', 'Kp. Krajan RT. 007/002 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 13:20:40', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 13:20:40', '2024-11-07 13:20:40'),
(169, '114-hcjk', 'November 2024', 'KWT-00071-November-2024', '114-soki-11-2024', 114, 'Dedi Haerudi', 'Kp. Krajan RT. 007/002 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 13:24:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 13:24:36', '2024-11-07 13:24:36'),
(170, '109-dujn', 'November 2024', 'KWT-00072-November-2024', '109-id8h-11-2024', 109, 'Wida', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 13:40:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 13:40:26', '2024-11-07 13:40:26'),
(171, '31-euns', 'November 2024', 'KWT-00073-November-2024', '31-wh7k-11-2024', 31, 'A Zaky Alattas', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-07 18:32:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 18:32:27', '2024-11-07 18:32:27'),
(172, '105-magk', 'November 2024', 'KWT-00074-November-2024', '105-elfs-11-2024', 105, 'Suntari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 18:58:46', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 18:58:46', '2024-11-07 18:58:46');
INSERT INTO `receipts` (`id`, `slug`, `periode`, `number`, `billing_number`, `customer_id`, `customer_name`, `customer_address`, `paket_name`, `paket_price`, `tax`, `created_name`, `teller_name`, `payment_time`, `payment_methode`, `bank_name`, `account_name`, `account_number`, `deposit_bank_name`, `deposit_account_name`, `deposit_account_number`, `reference_id`, `created_at`, `updated_at`) VALUES
(173, '105-magk', 'November 2024', 'KWT-00075-November-2024', '105-elfs-11-2024', 105, 'Suntari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-07 19:07:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-07 19:07:13', '2024-11-07 19:07:13'),
(174, '90-bpr1', 'November 2024', 'KWT-00076-November-2024', '90-msjd-11-2024', 90, 'Satim Silegar', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-08 04:45:25', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 04:45:25', '2024-11-08 04:45:25'),
(175, '100-iy4n', 'November 2024', 'KWT-00077-November-2024', '100-vosp-11-2024', 100, 'Dadan Hamdani', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-08 04:45:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 04:45:53', '2024-11-08 04:45:53'),
(176, '107-61ln', 'November 2024', 'KWT-00078-November-2024', '107-se07-11-2024', 107, 'Engko', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-08 04:46:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 04:46:08', '2024-11-08 04:46:08'),
(177, '71-eboq', 'November 2024', 'KWT-00079-November-2024', '71-ljaw-11-2024', 71, 'Teh Enen', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-08 04:46:40', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 04:46:40', '2024-11-08 04:46:40'),
(178, '135-mpbu', 'November 2024', 'KWT-00080-November-2024', '135-jczr-11-2024', 135, 'Yuyun', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-08 04:48:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 04:48:35', '2024-11-08 04:48:35'),
(179, '60-6rst', 'November 2024', 'KWT-00081-November-2024', '60-0yge-11-2024', 60, 'Pak Jepri', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-08 18:38:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 18:38:42', '2024-11-08 18:38:42'),
(180, '57-l4oy', 'November 2024', 'KWT-00082-November-2024', '57-byxe-11-2024', 57, 'Pak Iyan', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 200000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-08 18:40:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 18:40:53', '2024-11-08 18:40:53'),
(181, '7-qbpg', 'November 2024', 'KWT-00083-November-2024', '7-2lhx-11-2024', 7, 'Sumarni', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-08 18:42:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 18:42:10', '2024-11-08 18:42:10'),
(182, '134-y01f', 'November 2024', 'KWT-00084-November-2024', '134-vpxx-11-2024', 134, 'Rika Rahmawati', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-08 23:11:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-08 23:11:39', '2024-11-08 23:11:39'),
(183, '91-hpsk', 'November 2024', 'KWT-00085-November-2024', '91-llvk-11-2024', 91, 'Heni', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-09 02:01:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-09 02:01:59', '2024-11-09 02:01:59'),
(184, '45-3gip', 'November 2024', 'KWT-00086-November-2024', '45-tzlo-11-2024', 45, 'Pak Aam', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-09 06:13:15', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-09 06:13:15', '2024-11-09 06:13:15'),
(185, '51-v146', 'November 2024', 'KWT-00087-November-2024', '51-tzei-11-2024', 51, 'Nok Reni', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-09 07:02:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-09 07:02:00', '2024-11-09 07:02:00'),
(186, '64-kmfg', 'November 2024', 'KWT-00088-November-2024', '64-jtq9-11-2024', 64, 'Marya Ningsih', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-09 19:43:30', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-09 19:43:30', '2024-11-09 19:43:30'),
(187, '121-omf2', 'November 2024', 'KWT-00089-November-2024', '121-6npt-11-2024', 121, 'Bajuri/enci', 'kp.rawabolang rt23rw07 ds.maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-09 22:27:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-09 22:27:33', '2024-11-09 22:27:33'),
(188, '108-gl3k', 'November 2024', 'KWT-00090-November-2024', '108-vryj-11-2024', 108, 'Arya Cahyana Putra', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-09 22:27:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-09 22:27:59', '2024-11-09 22:27:59'),
(189, '138-ndig', 'November 2024', 'KWT-00091-November-2024', '138-mk68-11-2024', 138, 'Robi Anggara', 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-10 03:01:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-10 03:01:56', '2024-11-10 03:01:56'),
(190, '115-r0kf', 'November 2024', 'KWT-00092-November-2024', '115-gxad-11-2024', 115, 'Amania Kusumah', 'Kp. Krajan RT. 008/002 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-10 07:54:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-10 07:54:06', '2024-11-10 07:54:06'),
(191, '111-q983', 'November 2024', 'KWT-00093-November-2024', '111-1e6a-11-2024', 111, 'Julianto', 'kp.kadumekar', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-10 07:55:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-10 07:55:07', '2024-11-10 07:55:07'),
(192, '102-s9ff', 'November 2024', 'KWT-00094-November-2024', '102-x9ht-11-2024', 102, 'Heri Suandi', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-10 19:29:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-10 19:29:05', '2024-11-10 19:29:05'),
(193, '46-fzie', 'November 2024', 'KWT-00095-November-2024', '46-7gb1-11-2024', 46, 'Rohani Rusmiati', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-11 04:23:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-11 04:23:38', '2024-11-11 04:23:38'),
(194, '28-vxka', 'November 2024', 'KWT-00096-November-2024', '28-047u-11-2024', 28, 'Hasma Wati', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-11 06:10:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-11 06:10:22', '2024-11-11 06:10:22'),
(195, '16-16rt', 'November 2024', 'KWT-00097-November-2024', '16-hrzm-11-2024', 16, 'Riska Azizah', 'Perum Griya Asri Blok K7 No. 7 RT 010/011 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-11 17:38:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-11 17:38:59', '2024-11-11 17:38:59'),
(196, '54-rtcj', 'November 2024', 'KWT-00098-November-2024', '54-gywm-11-2024', 54, 'Linda', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 120000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-11 20:40:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-11 20:40:19', '2024-11-11 20:40:19'),
(197, '94-79fc', 'November 2024', 'KWT-00099-November-2024', '94-cucq-11-2024', 94, 'Julian Ratu Malik Kusum', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-11 21:58:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-11 21:58:44', '2024-11-11 21:58:44'),
(198, '19-rocw', 'November 2024', 'KWT-00100-November-2024', '19-qjc8-11-2024', 19, 'Ahmad Saepuloh', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 00:33:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 00:33:12', '2024-11-12 00:33:12'),
(199, '26-w2en', 'November 2024', 'KWT-00101-November-2024', '26-plut-11-2024', 26, 'Narsiah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 75000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 02:53:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 02:53:26', '2024-11-12 02:53:26'),
(200, '40-uoft', 'November 2024', 'KWT-00102-November-2024', '40-xy79-11-2024', 40, 'Ida Siti', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 03:17:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 03:17:36', '2024-11-12 03:17:36'),
(201, '55-mwnz', 'November 2024', 'KWT-00103-November-2024', '55-fwon-11-2024', 55, 'Agista Utari', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 05:11:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 05:11:38', '2024-11-12 05:11:38'),
(202, '63-emra', 'November 2024', 'KWT-00104-November-2024', '63-xjj7-11-2024', 63, 'Pak Dendi', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 06:14:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 06:14:00', '2024-11-12 06:14:00'),
(203, '67-zb2e', 'November 2024', 'KWT-00105-November-2024', '67-yll5-11-2024', 67, 'Lulu Andriani', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 19:35:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 19:35:10', '2024-11-12 19:35:10'),
(204, '68-nmsy', 'November 2024', 'KWT-00106-November-2024', '68-m1z1-11-2024', 68, 'Ma Aisah', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 19:36:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 19:36:04', '2024-11-12 19:36:04'),
(205, '43-sggr', 'November 2024', 'KWT-00107-November-2024', '43-9ots-11-2024', 43, 'Edi Suswanto', 'Kp. Sukamaju RT 009/007 Green Sapir Kelurahan Ciseureuh', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-12 23:34:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-12 23:34:59', '2024-11-12 23:34:59'),
(206, '33-v8rn', 'November 2024', 'KWT-00108-November-2024', '33-gbjq-11-2024', 33, 'Pak Slamet', 'Jl. Sukamaju RT 009/007 Kelurahan Ciseureuh', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-13 05:15:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-13 05:15:04', '2024-11-13 05:15:04'),
(207, '42-0rez', 'November 2024', 'KWT-00109-November-2024', '42-ys4l-11-2024', 42, 'Muhammad Fahrizal', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-13 05:55:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-13 05:55:27', '2024-11-13 05:55:27'),
(208, '30-qrak', 'November 2024', 'KWT-00110-November-2024', '30-vtp0-11-2024', 30, 'Adi Amoy', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-13 20:52:48', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-13 20:52:48', '2024-11-13 20:52:48'),
(209, '36-n7fe', 'November 2024', 'KWT-00111-November-2024', '36-dlfs-11-2024', 36, 'Triyanti', 'Gg. Beringin RT 077/008 Nagrikaler Purwakarta', 'ShaNet 2 (20 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-13 21:26:27', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', '2990407315', '1231655146', '2024-11-13 21:26:27', '2024-11-13 21:26:27'),
(210, '58-0v5q', 'November 2024', 'KWT-00112-November-2024', '58-scmx-11-2024', 58, 'Nur Fakriah Putri', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-14 05:11:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-14 05:11:02', '2024-11-14 05:11:02'),
(211, '77-gefm', 'November 2024', 'KWT-00113-November-2024', '77-upnd-11-2024', 77, 'Nazri Nasier', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-14 14:52:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-14 14:52:55', '2024-11-14 14:52:55'),
(212, '39-v82l', 'November 2024', 'KWT-00114-November-2024', '39-ls64-11-2024', 39, 'Dede Suherman', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-14 16:16:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-14 16:16:00', '2024-11-14 16:16:00'),
(213, '10-whsd', 'November 2024', 'KWT-00115-November-2024', '10-lyzh-11-2024', 10, 'Ali Akbar', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 01:44:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 01:44:41', '2024-11-15 01:44:41'),
(214, '21-ojfi', 'November 2024', 'KWT-00116-November-2024', '21-psof-11-2024', 21, 'Ibu Ayu', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 01:58:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 01:58:58', '2024-11-15 01:58:58'),
(215, '24-24in', 'November 2024', 'KWT-00117-November-2024', '24-qvwe-11-2024', 24, 'Neni Nurhaeni', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 02:49:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 02:49:05', '2024-11-15 02:49:05'),
(216, '49-qnho', 'November 2024', 'KWT-00118-November-2024', '49-ckru-11-2024', 49, 'Mie Ayam & Geprek', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 75000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 04:43:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 04:43:42', '2024-11-15 04:43:42'),
(217, '9-5z4m', 'November 2024', 'KWT-00119-November-2024', '9-rvks-11-2024', 9, 'Pak Eko', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 17:23:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 17:23:52', '2024-11-15 17:23:52'),
(218, '38-jbwy', 'November 2024', 'KWT-00120-November-2024', '38-oz0x-11-2024', 38, 'Yuan Firmansyah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 18:52:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 18:52:06', '2024-11-15 18:52:06'),
(219, '65-gsju', 'November 2024', 'KWT-00121-November-2024', '65-ez2l-11-2024', 65, 'Pak Kurdi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 20:14:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 20:14:14', '2024-11-15 20:14:14'),
(220, '70-7esu', 'November 2024', 'KWT-00122-November-2024', '70-weoh-11-2024', 70, 'Pak Kurdi 2', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 100500, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 20:14:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 20:14:19', '2024-11-15 20:14:19'),
(221, '2-ydhf', 'November 2024', 'KWT-00123-November-2024', '2-c07u-11-2024', 2, 'Iis Sugiarti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-15 20:48:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 20:48:03', '2024-11-15 20:48:03'),
(222, '32-s5y8', 'November 2024', 'KWT-00124-November-2024', '32-aedr-11-2024', 32, 'Teh Ai', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 21:15:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 21:15:31', '2024-11-15 21:15:31'),
(223, '72-jobe', 'November 2024', 'KWT-00125-November-2024', '72-liyq-11-2024', 72, 'Rina Konter BMCell', 'Jl. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 22:30:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 22:30:14', '2024-11-15 22:30:14'),
(224, '25-hsqq', 'November 2024', 'KWT-00126-November-2024', '25-pjp7-11-2024', 25, 'Etik Aprilia Ratna Sari', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-15 23:36:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-15 23:36:39', '2024-11-15 23:36:39'),
(225, '12-q2z0', 'November 2024', 'KWT-00127-November-2024', '12-c19f-11-2024', 12, 'Pak Suud', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-16 05:52:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-16 05:52:44', '2024-11-16 05:52:44'),
(226, '41-xvu0', 'November 2024', 'KWT-00128-November-2024', '41-6znk-11-2024', 41, 'Wandi Suhendi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-17 06:24:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-17 06:24:43', '2024-11-17 06:24:43'),
(227, '52-ywal', 'November 2024', 'KWT-00129-November-2024', '52-5tk8-11-2024', 52, 'Ahmad Sugianto', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-17 14:40:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-17 14:40:23', '2024-11-17 14:40:23'),
(228, '136-38vh', 'November 2024', 'KWT-00130-November-2024', '136-6bsc-11-2024', 136, 'Wahid', 'Kp. Babakan Jati Desa Maracang', 'Basic 1 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-11-18 21:27:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-18 21:27:31', '2024-11-18 21:27:31'),
(229, '34-rrs8', 'November 2024', 'KWT-00131-November-2024', '34-p4xd-11-2024', 34, 'Debbie Febriana', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-19 04:10:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-19 04:10:52', '2024-11-19 04:10:52'),
(230, '14-on5g', 'November 2024', 'KWT-00132-November-2024', '14-4fyq-11-2024', 14, 'Ai Solihah', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-19 05:18:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-19 05:18:06', '2024-11-19 05:18:06'),
(231, '8-00ey', 'November 2024', 'KWT-00133-November-2024', '8-hdun-11-2024', 8, 'Teh Darsih', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-20 16:23:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-20 16:23:10', '2024-11-20 16:23:10'),
(232, 'inv-139-a1zt', 'November 2024', '00134-KWT-November-2024', '139-fwpy-11-2024', 139, 'Iis Nurjanah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (7 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-26 01:16:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-26 01:16:41', '2024-11-26 01:16:41'),
(233, 'inv-140-ugru', 'November 2024', '00001-KWT-November-2024', '140-z8pn-11-2024', 140, 'Rifal Yudhi Pratama', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-26 02:55:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-26 02:55:44', '2024-11-26 02:55:44'),
(234, '47-rg8i', 'November 2024', 'KWT-00001-November-2024', '47-cwpb-11-2024', 47, 'Mulyana', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-26 23:39:18', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-26 23:39:18', '2024-11-26 23:39:18'),
(235, '47-hivz', 'October 2024', 'KWT-00002-November-2024', '47-p4em-10-2024', 47, 'Mulyana', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-26 23:39:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-26 23:39:21', '2024-11-26 23:39:21'),
(236, 'inv-141-ey6e', 'November 2024', '00003-KWT-November-2024', '141-qmlx-11-2024', 141, 'Dian Ardiansyah', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', 'ShaNet 5 (50 Mbps)', 300000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-27 21:33:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-27 21:33:10', '2024-11-27 21:33:10'),
(245, '144-sf1j', 'November 2024', 'KWT-00001-November-2024', '144-7v9j-11-2024', 144, 'Dian Ardiansyah', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', 'ShaNet 5 (50 Mbps)', 300000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-28 00:03:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-28 00:03:23', '2024-11-28 00:03:23'),
(246, 'inv-145-h8vo', 'November 2024', '00002-KWT-November-2024', '145-qsjq-11-2024', 145, 'Agus Rahmat (Azril)', 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-29 01:45:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-29 01:45:13', '2024-11-29 01:45:13'),
(247, 'inv-146-qndv', 'November 2024', '00001-KWT-November-2024', '146-rgqb-11-2024', 146, 'Ani Sumarni', 'Kp. Rawabolang RT. 025/007 Maracang Babakancikao', 'Basic 2 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-29 19:57:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-29 19:57:43', '2024-11-29 19:57:43'),
(248, 'inv-147-f0oo', 'November 2024', '00001-KWT-November-2024', '147-3enj-11-2024', 147, 'Neng Helawati', 'Kp. Sukamaju RT 010/007', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-30 02:13:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-30 02:13:14', '2024-11-30 02:13:14'),
(249, 'inv-148-bfnt', 'November 2024', '00001-KWT-November-2024', '148-efyh-11-2024', 148, 'Rinji Maulani', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-30 03:14:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-30 03:14:44', '2024-11-30 03:14:44'),
(250, 'inv-149-mhhy', 'November 2024', '00001-KWT-November-2024', '149-6igr-11-2024', 149, 'Iman Nurjamansyah', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-30 03:26:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-30 03:26:10', '2024-11-30 03:26:10'),
(251, 'inv-150-dywk', 'November 2024', '00001-KWT-November-2024', '150-o9fk-11-2024', 150, 'Dani Ramdani', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-30 03:34:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-30 03:34:35', '2024-11-30 03:34:35'),
(252, 'inv-151-kztg', 'November 2024', '00001-KWT-November-2024', '151-xqfq-11-2024', 151, 'Mujinah', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-30 03:35:49', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-30 03:35:49', '2024-11-30 03:35:49'),
(253, 'inv-152-apvd', 'November 2024', '00001-KWT-November-2024', '152-s3au-11-2024', 152, 'Rodiah', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-30 03:36:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-30 03:36:04', '2024-11-30 03:36:04'),
(254, 'inv-153-bygv', 'November 2024', '00001-KWT-November-2024', '153-l6wd-11-2024', 153, 'Tika Rachmawati', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-30 04:38:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-30 04:38:29', '2024-11-30 04:38:29'),
(255, 'inv-154-uh7b', 'November 2024', '00001-KWT-December-2024', '154-1qgs-11-2024', 154, 'Fadilah Nurhasanah', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 01:19:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 01:19:07', '2024-12-01 01:19:07'),
(256, 'inv-155-x9nm', 'November 2024', '00001-KWT-December-2024', '155-4jvo-11-2024', 155, 'Mimin Rukmini', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 01:19:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 01:19:13', '2024-12-01 01:19:13'),
(257, '39-a6gc', 'December 2024', 'KWT-00001-December-2024', '39-3sg5-12-2024', 39, 'Dede Suherman', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 03:12:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:12:05', '2024-12-01 03:12:05'),
(258, '61-jqts', 'December 2024', 'KWT-00002-December-2024', '61-4yd5-12-2024', 61, 'Bu Ani', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 03:12:37', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:12:37', '2024-12-01 03:12:37'),
(259, '20-n6ni', 'December 2024', 'KWT-00003-December-2024', '20-2w1k-12-2024', 20, 'Pak Lili', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:46:28', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:46:28', '2024-12-01 03:46:28'),
(260, '155-rd1a', 'December 2024', 'KWT-00004-December-2024', '155-ygjk-12-2024', 155, 'Mimin Rukmini', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:49:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:49:31', '2024-12-01 03:49:31'),
(261, '150-2uby', 'December 2024', 'KWT-00005-December-2024', '150-dnvi-12-2024', 150, 'Dani Ramdani', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:50:15', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:50:15', '2024-12-01 03:50:15'),
(262, '151-zzhy', 'December 2024', 'KWT-00006-December-2024', '151-jdrh-12-2024', 151, 'Mujinah', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:50:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:50:31', '2024-12-01 03:50:31'),
(263, '153-fgq2', 'December 2024', 'KWT-00007-December-2024', '153-l7u9-12-2024', 153, 'Gilang Gumilang', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:50:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:50:59', '2024-12-01 03:50:59'),
(264, '148-zak8', 'December 2024', 'KWT-00008-December-2024', '148-rnlh-12-2024', 148, 'Rinji Maulani', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:51:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:51:20', '2024-12-01 03:51:20'),
(265, '149-n9rv', 'December 2024', 'KWT-00009-December-2024', '149-mijj-12-2024', 149, 'Iman Nurjamansyah', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:51:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:51:53', '2024-12-01 03:51:53'),
(266, '69-ljc0', 'December 2024', 'KWT-00010-December-2024', '69-youi-12-2024', 69, 'Alya Iklima Hidayah', 'Perum Panorama Indah B2-4 RT 013/013 Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:53:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:53:01', '2024-12-01 03:53:01'),
(267, '139-qktg', 'December 2024', 'KWT-00011-December-2024', '139-cmqa-12-2024', 139, 'Iis Nurjanah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 03:55:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 03:55:05', '2024-12-01 03:55:05'),
(268, '118-kvwy', 'December 2024', 'KWT-00012-December-2024', '118-kbog-12-2024', 118, 'Redi Natisna', 'Kp. Krajan RT. 010/003 Desa Maracang', 'Basic 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-01 04:07:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 04:07:31', '2024-12-01 04:07:31'),
(269, 'inv-158-9opv', 'December 2024', '00013-KWT-December-2024', '158-cbwe-12-2024', 158, 'Tati Mulyati', 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 05:41:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 05:41:58', '2024-12-01 05:41:58'),
(270, 'inv-157-ktvo', 'December 2024', '00001-KWT-December-2024', '157-ajtv-12-2024', 157, 'Siam Suroso', 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 05:44:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 05:44:57', '2024-12-01 05:44:57'),
(271, '140-oai6', 'December 2024', 'KWT-00001-December-2024', '140-oevs-12-2024', 140, 'Rifal Yudhi Pratama', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-01 05:56:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 05:56:53', '2024-12-01 05:56:53'),
(272, '146-j6wo', 'December 2024', 'KWT-00002-December-2024', '146-z93h-12-2024', 146, 'Ani Sumarni', 'Kp. Rawabolang RT. 025/007 Maracang Babakancikao', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-01 05:58:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 05:58:02', '2024-12-01 05:58:02'),
(273, 'inv-156-emkk', 'December 2024', '00003-KWT-December-2024', '156-1ixl-12-2024', 156, 'Rini Seminar', 'Kp. Sukamaju RT. 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 06:10:17', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 06:10:17', '2024-12-01 06:10:17'),
(274, '47-fmht', 'December 2024', 'KWT-00001-December-2024', '47-lddu-12-2024', 47, 'Mulyana', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 06:13:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 06:13:52', '2024-12-01 06:13:52'),
(275, '79-wayf', 'December 2024', 'KWT-00002-December-2024', '79-rilf-12-2024', 79, 'Intan Ayu Sri Wahyuni', 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-12-01 06:15:25', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 06:15:25', '2024-12-01 06:15:25'),
(276, '23-4u5o', 'December 2024', 'KWT-00003-December-2024', '23-uwq9-12-2024', 23, 'AA Ramli', 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 17:50:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 17:50:39', '2024-12-01 17:50:39'),
(277, '44-5ehz', 'December 2024', 'KWT-00004-December-2024', '44-y72w-12-2024', 44, 'Tri Nurhidayati', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 18:36:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 18:36:05', '2024-12-01 18:36:05'),
(278, '29-ohqg', 'December 2024', 'KWT-00005-December-2024', '29-xivo-12-2024', 29, 'Euis Ratnasari', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 19:44:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 19:44:55', '2024-12-01 19:44:55'),
(279, '56-5skj', 'December 2024', 'KWT-00006-December-2024', '56-pqwe-12-2024', 56, 'Agustian Setiadi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 19:47:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 19:47:27', '2024-12-01 19:47:27'),
(280, '117-7znm', 'December 2024', 'KWT-00007-December-2024', '117-c21o-12-2024', 117, 'Pak Ojak', 'Kp. Krajan RT. 008/002 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-01 23:11:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 23:11:04', '2024-12-01 23:11:04'),
(281, '3-t3j5', 'December 2024', 'KWT-00008-December-2024', '3-hszf-12-2024', 3, 'Asep Nasihin', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-01 23:50:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-01 23:50:07', '2024-12-01 23:50:07'),
(282, '89-gefw', 'December 2024', 'KWT-00009-December-2024', '89-gasv-12-2024', 89, 'Adis', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-02 00:32:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 00:32:53', '2024-12-02 00:32:53'),
(283, '6-2vfk', 'December 2024', 'KWT-00010-December-2024', '6-ji8b-12-2024', 6, 'Cucu Suryani', 'Kp. Sukamaju RT 009/007 No. 37 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 03:06:25', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 03:06:25', '2024-12-02 03:06:25'),
(284, '81-wxcm', 'December 2024', 'KWT-00011-December-2024', '81-xvth-12-2024', 81, 'Irvan Mohammad Regent', 'Perum Griya Asri Blok K1 No. 10 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 03:35:56', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-02 03:35:56', '2024-12-02 03:35:56'),
(285, 'inv-159-mvix', 'December 2024', '00012-KWT-December-2024', '159-56jy-12-2024', 159, 'Risna Suhertini', 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 06:37:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 06:37:03', '2024-12-02 06:37:03'),
(286, 'inv-160-inhe', 'December 2024', '00001-KWT-December-2024', '160-efcg-12-2024', 160, 'Dewi Sutriani', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 08:04:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 08:04:09', '2024-12-02 08:04:09'),
(287, '27-ebkb', 'December 2024', 'KWT-00001-December-2024', '27-094h-12-2024', 27, 'Novi Eka Purwaningsih', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 17:57:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 17:57:35', '2024-12-02 17:57:35'),
(288, '73-ah5w', 'December 2024', 'KWT-00002-December-2024', '73-4ohb-12-2024', 73, 'Asep Topik Hidayat', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 20:07:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 20:07:58', '2024-12-02 20:07:58'),
(289, '4-a7km', 'December 2024', 'KWT-00003-December-2024', '4-4kvf-12-2024', 4, 'Pak Surya', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 21:18:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 21:18:53', '2024-12-02 21:18:53'),
(290, '1-twpx', 'December 2024', 'KWT-00004-December-2024', '1-z4am-12-2024', 1, 'Muh Subhan', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-02 22:22:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-02 22:22:58', '2024-12-02 22:22:58'),
(291, '134-lvsc', 'December 2024', 'KWT-00005-December-2024', '134-mo24-12-2024', 134, 'Rika Rahmawati', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-03 00:52:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-03 00:52:57', '2024-12-03 00:52:57'),
(292, '119-2gnv', 'December 2024', 'KWT-00006-December-2024', '119-pphv-12-2024', 119, 'Wahyu Maulana', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-03 19:25:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-03 19:25:07', '2024-12-03 19:25:07'),
(293, '130-pgwf', 'December 2024', 'KWT-00007-December-2024', '130-pxvp-12-2024', 130, 'Darma Wirawan', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-03 20:07:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-03 20:07:38', '2024-12-03 20:07:38'),
(294, '127-8p3j', 'December 2024', 'KWT-00008-December-2024', '127-1fk0-12-2024', 127, 'Misbah Toko Bangunan', 'Kp. Krajan RT. 008/002 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-04 01:47:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 01:47:03', '2024-12-04 01:47:03'),
(295, '11-asyi', 'December 2024', 'KWT-00009-December-2024', '11-xkrp-12-2024', 11, 'Rita Susanti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-04 03:01:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 03:01:09', '2024-12-04 03:01:09'),
(296, 'inv-161-r6aw', 'December 2024', '00010-KWT-December-2024', '161-wbt4-12-2024', 161, 'Mariati', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-04 07:13:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 07:13:58', '2024-12-04 07:13:58'),
(297, '137-ldar', 'December 2024', 'KWT-00001-December-2024', '137-2ohm-12-2024', 137, 'Fera', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-04 15:20:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 15:20:59', '2024-12-04 15:20:59'),
(298, '86-y258', 'December 2024', 'KWT-00002-December-2024', '86-uwwy-12-2024', 86, 'Desi Nurdianti', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-04 15:48:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 15:48:50', '2024-12-04 15:48:50'),
(299, '85-carm', 'December 2024', 'KWT-00003-December-2024', '85-2g0y-12-2024', 85, 'Siti Mariam', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-04 15:49:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 15:49:29', '2024-12-04 15:49:29'),
(300, '87-zrcx', 'December 2024', 'KWT-00004-December-2024', '87-xvjb-12-2024', 87, 'Iis Ismawati', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-04 15:50:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 15:50:04', '2024-12-04 15:50:04'),
(301, '22-aet5', 'December 2024', 'KWT-00005-December-2024', '22-1h0w-12-2024', 22, 'Ratih Aisyah', 'Jl. Veteran RT 003/002 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-04 17:06:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-04 17:06:22', '2024-12-04 17:06:22'),
(302, '50-flzz', 'December 2024', 'KWT-00006-December-2024', '50-2pqx-12-2024', 50, 'Ferry Kurniawan', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-04 20:34:37', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'Bank BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-04 20:34:37', '2024-12-04 20:34:37'),
(303, '18-o2c5', 'December 2024', 'KWT-00007-December-2024', '18-ccfw-12-2024', 18, 'Iip', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-04 21:01:23', 'transfer', 'Bank Mandiri', 'Dian Ardiansyah', '1730012695905', 'Mandiri', 'Dian Ardiansyah', NULL, NULL, '2024-12-04 21:01:23', '2024-12-04 21:01:23'),
(304, 'inv-162-wjzs', 'December 2024', '00008-KWT-December-2024', '162-psum-12-2024', 162, 'Devi Sevtiawati', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-05 00:33:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-05 00:33:56', '2024-12-05 00:33:56'),
(305, '16-4vtx', 'December 2024', 'KWT-00001-December-2024', '16-wfdh-12-2024', 16, 'Riska Azizah', 'Perum Griya Asri Blok K7 No. 7 RT 010/011 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-05 01:39:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-05 01:39:55', '2024-12-05 01:39:55'),
(306, '5-d4ot', 'December 2024', 'KWT-00002-December-2024', '5-offx-12-2024', 5, 'Koesnadi', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-05 02:22:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-05 02:22:33', '2024-12-05 02:22:33'),
(307, '135-sawz', 'December 2024', 'KWT-00003-December-2024', '135-yz3y-12-2024', 135, 'Yuyun', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-05 02:34:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-05 02:34:39', '2024-12-05 02:34:39'),
(308, '78-afkw', 'December 2024', 'KWT-00004-December-2024', '78-ug6f-12-2024', 78, 'Moh. Gian Firtyanza', 'PerumGriya Asri Blok D2 No. 15 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-05 06:57:28', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'Bank BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-05 06:57:28', '2024-12-05 06:57:28'),
(309, '15-ren9', 'December 2024', 'KWT-00005-December-2024', '15-glwi-12-2024', 15, 'Armiyanti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-05 06:58:29', 'transfer', 'Bank Mandiri', 'Dian Ardiansyah', '1730012695905', 'Bank Mandiri', 'Dian Ardiansyah', NULL, NULL, '2024-12-05 06:58:29', '2024-12-05 06:58:29'),
(310, '124-s8kc', 'December 2024', 'KWT-00006-December-2024', '124-ffdn-12-2024', 124, 'Neneng Suratni', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-05 21:01:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-05 21:01:56', '2024-12-05 21:01:56'),
(311, '92-fp95', 'December 2024', 'KWT-00007-December-2024', '92-vtlp-12-2024', 92, 'Juju Juarsih', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-05 22:41:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-05 22:41:56', '2024-12-05 22:41:56'),
(312, '66-7qfx', 'December 2024', 'KWT-00008-December-2024', '66-r3dg-12-2024', 66, 'Kodrat (GOR ADHIGA)', 'Jl. Sukamaju RT. 009/007 Kelurahan Ciseureuh', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 03:33:16', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 03:33:16', '2024-12-06 03:33:16'),
(313, '80-ap1p', 'December 2024', 'KWT-00009-December-2024', '80-ezeo-12-2024', 80, 'Nurhayati', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 04:54:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 04:54:38', '2024-12-06 04:54:38'),
(314, '42-gxv9', 'December 2024', 'KWT-00010-December-2024', '42-ksvu-12-2024', 42, 'Muhammad Fahrizal', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 06:26:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 06:26:03', '2024-12-06 06:26:03'),
(315, '83-hvzl', 'December 2024', 'KWT-00011-December-2024', '83-q5n8-12-2024', 83, 'Anggi', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-06 14:47:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 14:47:55', '2024-12-06 14:47:55'),
(316, '83-hvzl', 'December 2024', 'KWT-00012-December-2024', '83-q5n8-12-2024', 83, 'Anggi', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-06 14:48:37', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 14:48:37', '2024-12-06 14:48:37'),
(317, '145-fnfs', 'December 2024', 'KWT-00013-December-2024', '145-2w3m-12-2024', 145, 'Agus Rahmat (Azril)', 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 16:44:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 16:44:19', '2024-12-06 16:44:19'),
(318, '35-07br', 'December 2024', 'KWT-00014-December-2024', '35-aa1f-12-2024', 35, 'Hafis', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 16:44:32', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 16:44:32', '2024-12-06 16:44:32'),
(319, '60-qcdi', 'December 2024', 'KWT-00015-December-2024', '60-rnsm-12-2024', 60, 'Pak Jepri', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 17:49:22', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-06 17:49:22', '2024-12-06 17:49:22'),
(320, '62-1emb', 'December 2024', 'KWT-00016-December-2024', '62-amiw-12-2024', 62, 'Yeyet Abdulrachman', 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', 'ShaNet 4 (40 Mbps)', 250000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 19:05:46', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-06 19:05:46', '2024-12-06 19:05:46'),
(321, '48-irv2', 'December 2024', 'KWT-00017-December-2024', '48-wkig-12-2024', 48, 'Sri Sulastri', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-06 19:12:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 19:12:59', '2024-12-06 19:12:59'),
(322, '99-ezmb', 'December 2024', 'KWT-00018-December-2024', '99-70wx-12-2024', 99, 'Miung', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-06 23:40:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 23:40:27', '2024-12-06 23:40:27'),
(323, '104-zkoi', 'December 2024', 'KWT-00019-December-2024', '104-qfcx-12-2024', 104, 'RT Oji', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-06 23:40:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-06 23:40:53', '2024-12-06 23:40:53');
INSERT INTO `receipts` (`id`, `slug`, `periode`, `number`, `billing_number`, `customer_id`, `customer_name`, `customer_address`, `paket_name`, `paket_price`, `tax`, `created_name`, `teller_name`, `payment_time`, `payment_methode`, `bank_name`, `account_name`, `account_number`, `deposit_bank_name`, `deposit_account_name`, `deposit_account_number`, `reference_id`, `created_at`, `updated_at`) VALUES
(324, '103-rtuy', 'December 2024', 'KWT-00020-December-2024', '103-qn14-12-2024', 103, 'Juhdi', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-07 04:37:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-07 04:37:20', '2024-12-07 04:37:20'),
(325, 'inv-163-hj56', 'December 2024', '00021-KWT-December-2024', '163-m25d-12-2024', 163, 'Tatang Mulyana', 'Kp. Sukamaju RT. 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-07 19:53:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-07 19:53:58', '2024-12-07 19:53:58'),
(326, 'inv-164-q4vv', 'December 2024', '00001-KWT-December-2024', '164-caxq-12-2024', 164, 'Agus Hermanto', 'Kp. Krajan RT. 010/003 Maracang Babakancikao', 'Basic 3 (30 Mbps)', 200000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-07 20:42:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-07 20:42:36', '2024-12-07 20:42:36'),
(327, '26-fsmt', 'December 2024', 'KWT-00001-December-2024', '26-78dq-12-2024', 26, 'Narsiah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-07 21:35:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-07 21:35:43', '2024-12-07 21:35:43'),
(328, '74-ca6i', 'December 2024', 'KWT-00002-December-2024', '74-fslj-12-2024', 74, 'Egy Laksma Suandi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-07 23:06:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-07 23:06:59', '2024-12-07 23:06:59'),
(329, 'inv-165-klzk', 'December 2024', '00003-KWT-December-2024', '165-qohj-12-2024', 165, 'Hendri Gunawan', 'Kp. Rawabolang RT. 025/007 Maracang Babakancikao', 'Basic 2 (10 Mbps)', 150000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-07 23:13:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-07 23:13:54', '2024-12-07 23:13:54'),
(330, '90-ujji', 'December 2024', 'KWT-00001-December-2024', '90-bgkn-12-2024', 90, 'Satim Silegar', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-07 23:40:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-07 23:40:41', '2024-12-07 23:40:41'),
(331, '57-r7yc', 'December 2024', 'KWT-00002-December-2024', '57-xgaf-12-2024', 57, 'Pak Iyan', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-08 04:59:59', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-08 04:59:59', '2024-12-08 04:59:59'),
(332, '123-b1yb', 'December 2024', 'KWT-00003-December-2024', '123-hbnh-12-2024', 123, 'Salmah Saleha', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 15:14:45', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 15:14:45', '2024-12-08 15:14:45'),
(333, '110-lkyf', 'December 2024', 'KWT-00004-December-2024', '110-ftzu-12-2024', 110, 'H Imon', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 15:16:32', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 15:16:32', '2024-12-08 15:16:32'),
(334, '114-6yzf', 'December 2024', 'KWT-00005-December-2024', '114-ssmi-12-2024', 114, 'Dedi Haerudi', 'Kp. Krajan RT. 007/002 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 15:17:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 15:17:10', '2024-12-08 15:17:10'),
(335, '115-jmkf', 'December 2024', 'KWT-00006-December-2024', '115-awk9-12-2024', 115, 'Amania Kusumah', 'Kp. Krajan RT. 008/002 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 16:32:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 16:32:14', '2024-12-08 16:32:14'),
(336, '112-hjws', 'December 2024', 'KWT-00007-December-2024', '112-vxgu-12-2024', 112, 'Okis', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 16:36:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 16:36:38', '2024-12-08 16:36:38'),
(337, '106-mddi', 'December 2024', 'KWT-00008-December-2024', '106-ilga-12-2024', 106, 'Iqbal Budi Prasetya', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 16:37:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 16:37:14', '2024-12-08 16:37:14'),
(338, '109-bpan', 'December 2024', 'KWT-00009-December-2024', '109-ucjn-12-2024', 109, 'Wida', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 16:37:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 16:37:31', '2024-12-08 16:37:31'),
(339, '116-tyta', 'December 2024', 'KWT-00010-December-2024', '116-f5nv-12-2024', 116, 'Nurul Aeni', 'Kp. Krajan RT. 008/002 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-08 20:26:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-08 20:26:47', '2024-12-08 20:26:47'),
(340, '120-oi3s', 'December 2024', 'KWT-00011-December-2024', '120-0h8x-12-2024', 120, 'Usman Abdul Sobari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 01:16:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 01:16:56', '2024-12-09 01:16:56'),
(341, '82-m0r9', 'December 2024', 'KWT-00012-December-2024', '82-mstw-12-2024', 82, 'Nuryani', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 01:17:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 01:17:23', '2024-12-09 01:17:23'),
(342, '132-meem', 'December 2024', 'KWT-00013-December-2024', '132-kujl-12-2024', 132, 'Pak Agus', 'kp.bakanjati rw06 ds. maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 01:18:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 01:18:41', '2024-12-09 01:18:41'),
(343, '13-jyun', 'December 2024', 'KWT-00014-December-2024', '13-iuyl-12-2024', 13, 'Mamah Anggi', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-09 01:54:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 01:54:12', '2024-12-09 01:54:12'),
(344, '125-dkhe', 'December 2024', 'KWT-00015-December-2024', '125-bbkl-12-2024', 125, 'Herman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 03:42:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 03:42:19', '2024-12-09 03:42:19'),
(345, '98-hze1', 'December 2024', 'KWT-00016-December-2024', '98-ohf8-12-2024', 98, 'Wayanto', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 04:19:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 04:19:05', '2024-12-09 04:19:05'),
(346, '96-oouq', 'December 2024', 'KWT-00017-December-2024', '96-6l1j-12-2024', 96, 'Cici', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 04:19:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 04:19:42', '2024-12-09 04:19:42'),
(347, '100-nhim', 'December 2024', 'KWT-00018-December-2024', '100-ncmi-12-2024', 100, 'Dadan Hamdani', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 09:48:30', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 09:48:30', '2024-12-09 09:48:30'),
(348, '129-1csy', 'December 2024', 'KWT-00019-December-2024', '129-dlb6-12-2024', 129, 'Dadi Kusnadi', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 09:49:51', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 09:49:51', '2024-12-09 09:49:51'),
(349, '113-twfl', 'December 2024', 'KWT-00020-December-2024', '113-mosu-12-2024', 113, 'Neng Siti', 'Kp. Krajan RT. 007/002 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 16:53:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 16:53:02', '2024-12-09 16:53:02'),
(350, '105-bks7', 'December 2024', 'KWT-00021-December-2024', '105-wtvd-12-2024', 105, 'Suntari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 17:02:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 17:02:06', '2024-12-09 17:02:06'),
(351, '95-mlgf', 'December 2024', 'KWT-00022-December-2024', '95-unqj-12-2024', 95, 'Rosidah', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 17:02:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 17:02:38', '2024-12-09 17:02:38'),
(352, '133-73eh', 'December 2024', 'KWT-00023-December-2024', '133-xkcn-12-2024', 133, 'Sulis Rumah', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 17:12:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 17:12:10', '2024-12-09 17:12:10'),
(353, '131-tjvg', 'December 2024', 'KWT-00024-December-2024', '131-ibji-12-2024', 131, 'Sulis Toko', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 17:12:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 17:12:52', '2024-12-09 17:12:52'),
(354, '108-j52k', 'December 2024', 'KWT-00025-December-2024', '108-99r2-12-2024', 108, 'Arya Cahyana Putra', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 18:07:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 18:07:26', '2024-12-09 18:07:26'),
(355, '7-knr8', 'December 2024', 'KWT-00026-December-2024', '7-yrpv-12-2024', 7, 'Sumarni', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-09 19:29:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 19:29:34', '2024-12-09 19:29:34'),
(356, '51-ey9i', 'December 2024', 'KWT-00027-December-2024', '51-2o3a-12-2024', 51, 'Nok Reni', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-09 19:42:53', 'transfer', 'Bank Mandiri', 'Dian Ardiansyah', '1730012695905', 'Mandiri', 'Dian Ardiansyah', NULL, NULL, '2024-12-09 19:42:53', '2024-12-09 19:42:53'),
(357, '91-hdzc', 'December 2024', 'KWT-00028-December-2024', '91-eaqz-12-2024', 91, 'Heni', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 20:46:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 20:46:29', '2024-12-09 20:46:29'),
(358, '91-hdzc', 'December 2024', 'KWT-00029-December-2024', '91-eaqz-12-2024', 91, 'Heni', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 20:46:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 20:46:31', '2024-12-09 20:46:31'),
(359, '91-hdzc', 'December 2024', 'KWT-00030-December-2024', '91-eaqz-12-2024', 91, 'Heni', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 20:46:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 20:46:33', '2024-12-09 20:46:33'),
(360, '88-p2d5', 'December 2024', 'KWT-00031-December-2024', '88-fxkh-12-2024', 88, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-09 23:47:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-09 23:47:56', '2024-12-09 23:47:56'),
(361, '126-ltzy', 'December 2024', 'KWT-00032-December-2024', '126-ntsg-12-2024', 126, 'Syarip Hidayatullah', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-10 01:44:32', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 01:44:32', '2024-12-10 01:44:32'),
(362, '97-1uwi', 'December 2024', 'KWT-00033-December-2024', '97-zhlw-12-2024', 97, 'Pepen', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-10 02:10:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 02:10:22', '2024-12-10 02:10:22'),
(363, '71-wnb6', 'December 2024', 'KWT-00034-December-2024', '71-c0l7-12-2024', 71, 'Teh Enen', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-10 05:05:45', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 05:05:45', '2024-12-10 05:05:45'),
(364, '107-mbb5', 'December 2024', 'KWT-00035-December-2024', '107-bfew-12-2024', 107, 'Engko', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-10 05:44:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 05:44:36', '2024-12-10 05:44:36'),
(365, '2-k9ew', 'December 2024', 'KWT-00036-December-2024', '2-jhtu-12-2024', 2, 'Iis Sugiarti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-10 06:58:00', 'transfer', 'Bank Mandiri', 'Dian Ardiansyah', '1730012695905', 'Mandiri', 'Dian Ardiansyah', NULL, NULL, '2024-12-10 06:58:00', '2024-12-10 06:58:00'),
(366, '121-30cz', 'December 2024', 'KWT-00037-December-2024', '121-qlkm-12-2024', 121, 'Bajuri Enci', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-10 15:45:32', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 15:45:32', '2024-12-10 15:45:32'),
(367, '128-wmrh', 'December 2024', 'KWT-00038-December-2024', '128-gjv0-12-2024', 128, 'Jajang Salahudin', 'Kp. Krajan RT. 003/001 Desa Maracang', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-10 16:12:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 16:12:20', '2024-12-10 16:12:20'),
(368, '63-xosn', 'December 2024', 'KWT-00039-December-2024', '63-fkqp-12-2024', 63, 'Pak Dendi', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-10 17:44:42', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-10 17:44:42', '2024-12-10 17:44:42'),
(369, '102-q1yk', 'December 2024', 'KWT-00040-December-2024', '102-mbgi-12-2024', 102, 'Heri Suandi', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'Basic 1 (7 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-10 18:16:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 18:16:53', '2024-12-10 18:16:53'),
(370, '67-j1ty', 'December 2024', 'KWT-00041-December-2024', '67-w0g0-12-2024', 67, 'Lulu Andriani', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 97500, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-10 19:11:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 19:11:03', '2024-12-10 19:11:03'),
(371, '68-pjbu', 'December 2024', 'KWT-00042-December-2024', '68-zo0u-12-2024', 68, 'Ma Aisah', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-10 19:12:48', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 19:12:48', '2024-12-10 19:12:48'),
(372, '45-pnma', 'December 2024', 'KWT-00043-December-2024', '45-zwnj-12-2024', 45, 'Pak Aam', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-10 19:35:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-10 19:35:01', '2024-12-10 19:35:01'),
(373, '111-9xmu', 'December 2024', 'KWT-00044-December-2024', '111-w4pg-12-2024', 111, 'Julianto', 'kp.kadumekar', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-11 05:32:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-11 05:32:42', '2024-12-11 05:32:42'),
(374, '111-9xmu', 'December 2024', 'KWT-00045-December-2024', '111-w4pg-12-2024', 111, 'Julianto', 'kp.kadumekar', 'Basic 2 (10 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2024-12-11 05:32:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-11 05:32:44', '2024-12-11 05:32:44'),
(375, '19-wwvq', 'December 2024', 'KWT-00046-December-2024', '19-synm-12-2024', 19, 'Ahmad Saepuloh', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-12 02:08:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-12 02:08:33', '2024-12-12 02:08:33'),
(376, '31-iraq', 'December 2024', 'KWT-00047-December-2024', '31-xath-12-2024', 31, 'A Zaky Alattas', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-12 02:51:55', 'transfer', 'Bank Mandiri', 'Dian Ardiansyah', '1730012695905', 'Mandiri', 'Dian Ardiansyah', NULL, NULL, '2024-12-12 02:51:55', '2024-12-12 02:51:55'),
(377, '138-kb6n', 'December 2024', 'KWT-00048-December-2024', '138-xdbs-12-2024', 138, 'Robi Anggara', 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-12 03:59:18', 'transfer', 'Bank Central Asia', 'PT. Global Sarana Elektronika', '2317111157', 'BCA', 'Robi Anggara', NULL, NULL, '2024-12-12 03:59:18', '2024-12-12 03:59:18'),
(378, '55-rxqs', 'December 2024', 'KWT-00049-December-2024', '55-yqqn-12-2024', 55, 'Agista Utari', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-12 06:30:32', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-12 06:30:32', '2024-12-12 06:30:32'),
(379, '28-nmxi', 'December 2024', 'KWT-00050-December-2024', '28-i1vh-12-2024', 28, 'Hasma Wati', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-12 06:43:51', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-12 06:43:51', '2024-12-12 06:43:51'),
(380, '40-cqgx', 'December 2024', 'KWT-00051-December-2024', '40-84jo-12-2024', 40, 'Ida Siti', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-12 20:06:49', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-12 20:06:49', '2024-12-12 20:06:49'),
(381, '53-cdlr', 'December 2024', 'KWT-00052-December-2024', '53-lw7r-12-2024', 53, 'Maharani Widyaningrum', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-13 03:24:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-13 03:24:20', '2024-12-13 03:24:20'),
(382, '43-z26e', 'December 2024', 'KWT-00053-December-2024', '43-udum-12-2024', 43, 'Edi Suswanto', 'Kp. Sukamaju RT 009/007 Green Sapir Kelurahan Ciseureuh', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-13 17:51:23', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-13 17:51:23', '2024-12-13 17:51:23'),
(383, '58-yvbu', 'December 2024', 'KWT-00054-December-2024', '58-at8h-12-2024', 58, 'Nur Fakriah Putri', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-13 19:58:49', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-13 19:58:49', '2024-12-13 19:58:49'),
(384, '21-1ny0', 'December 2024', 'KWT-00055-December-2024', '21-iwr9-12-2024', 21, 'Ibu Ayu', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-13 23:23:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-13 23:23:54', '2024-12-13 23:23:54'),
(385, '33-rlow', 'December 2024', 'KWT-00056-December-2024', '33-4eri-12-2024', 33, 'Pak Slamet', 'Jl. Sukamaju RT. 009/007 Kelurahan Ciseureuh', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-13 23:41:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-13 23:41:36', '2024-12-13 23:41:36'),
(386, '36-kyiy', 'December 2024', 'KWT-00057-December-2024', '36-plfm-12-2024', 36, 'Triyanti', 'Gg. Beringin RT. 077/008 Nagrikaler Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-14 01:25:18', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-14 01:25:18', '2024-12-14 01:25:18'),
(387, '25-rfcb', 'December 2024', 'KWT-00058-December-2024', '25-neob-12-2024', 25, 'Etik Aprilia Ratna Sari', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-14 05:18:55', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-14 05:18:55', '2024-12-14 05:18:55'),
(388, '38-f9vm', 'December 2024', 'KWT-00059-December-2024', '38-efvk-12-2024', 38, 'Yuan Firmansyah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-14 17:01:55', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-14 17:01:55', '2024-12-14 17:01:55'),
(389, '54-4p8f', 'December 2024', 'KWT-00060-December-2024', '54-lhyt-12-2024', 54, 'Linda', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 120000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-14 20:13:16', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'BCA', 'Dian Ardiansyah', NULL, NULL, '2024-12-14 20:13:16', '2024-12-14 20:13:16'),
(390, '10-euek', 'December 2024', 'KWT-00061-December-2024', '10-7zj1-12-2024', 10, 'Ali Akbar', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-12-14 20:19:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-12-14 20:19:42', '2024-12-14 20:19:42');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', '2024-12-10 09:47:06', '2024-12-10 09:47:06');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1);

-- --------------------------------------------------------

--
-- Table structure for table `type_routers`
--

CREATE TABLE `type_routers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `merk_router_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'false',
  `server_type` enum('false','true') NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `type_routers`
--

INSERT INTO `type_routers` (`id`, `merk_router_id`, `name`, `disabled`, `server_type`, `created_at`, `updated_at`) VALUES
(1, 1, 'F663NV3a', 'false', 'false', '2024-09-29 12:18:00', '2024-10-07 08:44:50'),
(2, 1, 'F660', 'false', 'false', '2024-09-29 12:18:48', '2024-09-29 12:18:48'),
(3, 1, 'F650', 'false', 'false', '2024-09-29 12:19:05', '2024-09-29 12:19:05'),
(4, 2, 'HS8145C5', 'false', 'false', '2024-09-29 12:19:40', '2024-09-29 12:19:40'),
(5, 2, 'HG8245H5', 'false', 'false', '2024-09-29 12:19:51', '2024-09-29 12:19:51'),
(6, 2, 'HG8245H', 'false', 'false', '2024-09-29 12:20:02', '2024-09-29 12:20:02'),
(7, 2, 'HG8245A', 'false', 'false', '2024-09-29 12:20:11', '2024-09-29 12:20:11'),
(8, 4, 'N200RE', 'false', 'false', '2024-09-29 12:20:45', '2024-09-29 12:20:45'),
(9, 4, 'N355RT', 'false', 'false', '2024-09-29 12:20:59', '2024-09-29 12:20:59'),
(10, 4, 'N300RT', 'false', 'false', '2024-09-29 12:21:12', '2024-09-29 12:21:12'),
(11, 5, 'TL-WR840N', 'false', 'false', '2024-10-05 15:22:30', '2024-10-05 15:22:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `disabled` enum('false','true') NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `disabled`, `created_at`, `updated_at`, `picture`) VALUES
(1, 'Randika Nurfaizi', 'randika@bni.co.id', NULL, '$2y$10$iJQ6bn9BxxyTddYg.EsckO9YMWo8K/g9FGCTdO02sZdxg6AVrr4uO', NULL, 'false', '2024-12-10 09:47:06', '2024-12-10 09:47:06', NULL),
(2, 'Randika Nurfaizi 2', 'randika.nurfaizi@bni.co.id', NULL, '$2y$10$BfuwHgazyaHHCIHxg5FDS.XY/nsZCh77K7ymr/7S8Nj1rLzHGk5OS', NULL, 'false', '2024-12-28 03:10:30', '2024-12-28 03:10:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `websystems`
--

CREATE TABLE `websystems` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `cpanel_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_gateway` enum('enable','disable') NOT NULL DEFAULT 'disable',
  `enable_tax` tinyint(1) NOT NULL DEFAULT 0,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `email` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `country_code` varchar(3) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `customer_code` varchar(255) DEFAULT NULL,
  `long_customer_code` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `websystems`
--

INSERT INTO `websystems` (`id`, `slug`, `title`, `version`, `cpanel_url`, `created_at`, `updated_at`, `payment_gateway`, `enable_tax`, `tax_rate`, `email`, `url`, `address`, `city`, `postal_code`, `country_code`, `phone`, `customer_code`, `long_customer_code`) VALUES
(1, 'websystem', 'ShaNet Fiber Management', '1.0.9b3', NULL, '2024-09-29 01:50:11', '2024-11-14 21:08:11', 'disable', 0, 11.00, 'd_ardi@ymail.com', NULL, 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', 'Kabupaten Purwakarta', '41118', 'IDN', '085795909904', 'SNF', 3);

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_gateways`
--

CREATE TABLE `whatsapp_gateways` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `remaining_day` int(11) NOT NULL DEFAULT 1,
  `schedule_time` int(11) NOT NULL DEFAULT 10,
  `whatsapp_number_id` bigint(20) UNSIGNED DEFAULT NULL,
  `country_code` int(11) DEFAULT 62,
  `description` varchar(255) DEFAULT NULL,
  `url_callback` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `cert_file` varchar(255) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_gateways`
--

INSERT INTO `whatsapp_gateways` (`id`, `slug`, `name`, `remaining_day`, `schedule_time`, `whatsapp_number_id`, `country_code`, `description`, `url_callback`, `api_key`, `cert_file`, `disabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 'gateway', 'WA Gateway', 0, 10, 3, 62, 'Official Contact Shanum Network Fiber', 'http://103.129.148.212:3000', 'yE1Q82tGjnB3xZveu50yqQ==', NULL, 0, '2024-12-31 03:03:40', '2024-12-31 03:03:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_messages`
--

CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `whatsapp_number_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `incoming_message` text DEFAULT NULL,
  `reply_message` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_messages`
--

INSERT INTO `whatsapp_messages` (`id`, `slug`, `whatsapp_number_id`, `name`, `incoming_message`, `reply_message`, `description`, `default`, `disabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(41, 'unknow_keyword', NULL, 'Unknow Keyword', 'unknow keyword', 'Mohon maaf, keywoard yang anda masukkan salah\n\nKeyword yang tersedia:\n%keywords%', 'Pesan yang dikirim saat pesan yang diterima tidak ada dalam keyword.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(42, 'payment', NULL, 'Pembayaran Lunas', 'payment', '===============================\n         SHANUM NETWORK FIBER\n          Receipt # : %billing_number%\n===============================\n\nPelanggan yang terhormat, Berikut kami sampaikan detail pembayaran anda:\n\n*👥 Nama			:* _Bpk/Ibu %name%_\n*🆔 Cust - ID		:* _%customer_id%_\n*📍 Alamat			:* _%address%_\n*📦 Paket			:* _%paket%_\n*📆 Periode			:* _%periode%_\n*💶 Nominal			:* *_%bill%_*\n\n*Teller				:* _%teller%_\n*Tanggal			:* _%payment_time%_\n*Metode pembayaran	:* _%payment_methode%_\n\n===============================\nTerima kasih telah mempercayakan kebutuhan internetnya kepada kami.\nKami berkomitmen untuk selalu memberikan yang terbaik.\nMohon maaf jika ada salah penulisan nama, Anda dapat menghubungi kami untuk pembetulan.\n===============================\n            *Shanum Network Fiber*\n_Pwrd by PT. Global Sarana Elektronika_\n===============================', 'Pesan yang dikirim saat pelanggan melakukan pembayaran.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(43, 'unknow_customer', NULL, 'Non Customer', 'unknow customer', 'Mohon maaf, anda belum terdaftar sebagai customer kami.\nSilahkan menghubungi 087818040024 untuk proses registrasi.', 'Pesan yang dikirim bukan customer.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(44, 'unpayment', NULL, 'Unpayment', 'unpayment', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nMaaf telah terjadi kesalahan system.\n\nPembayaran anda :\nNomor Tagihan : *%billing_number%*\nJumlah : *%bill%*\n\nTelah dibatalkan.\n\nTerima kasih telah menggunakan layanan internet kami.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat admin melakukan pembatalan pembayaran.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(45, 'no_paket', NULL, 'Customer Dont Have Paket', 'no paket', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nSaat ini anda sudah terdaftar di sistem kami, namun belum mempunyai paket.\n\nJika anda ingin menggunakan layanan kami, silahkan hubungi 0878-1804-0024.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*', 'Pesan yang dikirim saat pelanggan tidak memiliki paket internet.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(46, 'no_bill', NULL, 'No Bill', 'no bill', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nSaat ini anda belum memiliki tagihan.\n\nKami mengucapkan terima kasih karena anda tertib administrasi.\nIni sangat berguna untuk kemajuan kami dalam memperbaiki layanan.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*', 'Pesan yang dikirim saat pelanggan tidak memiliki tagihan.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(47, 'reminder_bill', NULL, 'Reminder Bill', 'warning bill', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\n%day% hari lagi adalah batas akhir pembayaran paket internet anda. Saat ini anda memiliki %count-bill% tagihan yang belum dibayar:\n\n%pakets%\n*Jumlah tagihan: %total-bill%*\n\nLakukan pembayaran sebelum jatuh tempo untuk menghindari isolir otomatis oleh system kami.\n\nAnda dapat melakukan pembayaran melalui:\n%account-bank%\n\nTerima kasih telah menggunakan layanan internet kami.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim untuk memberitahukan bahwa tagihan mereka akan jatuh tempo.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(48, 'deadline_bill', NULL, 'Deadline Bill', 'warning bill', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nHari ini adalah batas akhir pembayaran paket internet anda. Saat ini anda memiliki %count-bill% tagihan yang belum dibayar:\n\n%pakets%\n*✅ Jumlah tagihan : %total-bill%*\n\nSegera lakukan pembayaran sebelum jatuh tempo untuk menghindari isolir otomatis oleh system kami.\n\nAnda dapat melakukan pembayaran melalui:\n%account-bank%\n\nInformasikan kepada kami jika anda sudah melakukan pembayaran di nomor *WA 0878-1804-0024*\nTerima kasih telah menggunakan layanan internet kami.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim untuk memberitahukan bahwa tagihan mereka sudah jatuh tempo.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(49, 'past_due_warning', NULL, 'Past Due Warning', 'warning bill', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nKami ingin mengingatkan bahwa Anda memiliki %count-bill% tagihan paket internet yang belum dibayar hingga melewati jatuh tempo.\nBerikut adalah rincian tagihan Anda:\n\n%pakets%\n*✅ Jumlah tagihan keseluruhan : %total-bill%*\n\nPembayaran dapat dilakukan melalui:\n%account-bank%\n\nInformasikan kepada kami jika anda sudah melakukan pembayaran di nomor *WA 0878-1804-0024*\nKami berkomitmen memberikan layanan terbaik untuk Anda.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim untuk mengingatkan bahwa tagihan sudah melewati jatuh tempo.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(50, 'info_tagihan', NULL, 'Info Tagihan', 'info tagihan', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nBerikut info tagihan anda.\n%bills%\nJumlah tagihan : %total-bills%\n\nAnda dapat melakukan pembayaran melalui:\n%account-banks%\n\nTerima kasih telah mempercayakan kebutuhan internet anda kepada kami.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat pelanggan melakukan pengecekan tagihan.', 0, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(51, 'info_paket', NULL, 'Info Paket', 'info paket', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nBerikut kami sampaikan rincian paket anda:\n%pakets%\n\nTerima kasih telah mempercayakan layanan internet anda kepada kami. Kami berkomitmen untuk selalu memberikan pelayanan yang terbaik.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat pelanggan melakukan pengecekan paket.', 0, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(52, 'disable_paket', NULL, 'Disable Paket', 'disable paket', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPaket anda sudah dinon-aktifkan, kami tidak mengenakan biaya layanan selama paket anda dinon-aktifkan.\n\nInfo paket:\nPaket   : *%paket_name%*\nStatus : *%status%*\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat paket di non-aktifkan.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(53, 'enable_paket', NULL, 'Enable Paket', 'disable paket', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPaket anda sudah diaktifkan, selamat menikmati layanan internet kami kembali.\n\nInfo paket:\nPaket     : *%paket_name%*\nTagihan : *%bill%* per bulan\nStatus    : *%status%*\nTanggal Aktivasi : %activation_date%\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat paket di aktifkan.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(54, 'activation_paket', NULL, 'Activation Paket', 'activation paket', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPaket anda sudah berhasil diaktivasi, selamat menikmati layanan internet dari kami.\n\nMasa berlaku: %periode%\nBatas bayar: Setiap tanggal %deadline%\nNama CS: %cs_name%\nContact: %cs_contact%\n\nJika terjadi kendala pada jaringan, silahkan hubungi CS kami.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat aktivasi paket.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(55, 'add_paket', NULL, 'Add Paket', 'add paket', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPendaftaran paket berhasil, berikut info layanan paket anda:\n\nPaket: %paket_name%\nHarga: %paket_price%\n\nBiaya Langganan: %bill% per bulan\n\nsilahkan tunggu beberapa saat karna paket anda masih dalam proses aktivasi.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*', 'Pesan yang dikirim saat penambahan paket customer.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(56, 'deactivation_customer', NULL, 'Deactivation Customer', 'deactivation customer', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nDengan berat hati kami menonaktifkan akun anda. Jika tidak keberatan, silahkan kirim masukkan anda untuk perbaikan pada layanan kami.\n\nTerima kasih telah menjadi pelanggan kami selama ini.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._**', 'Pesan yang dikirim saat customer di non-aktifkan.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(57, 'activation_customer', NULL, 'Activation Customer', 'activation customer', 'Yth. Bpk/Ibu %name%,\n%address%\n\nAktivasi akun berhasil. Berikut detail informasi akun anda:\n\n*👥 Nama      :* _Bpk/Ibu %name%_\n*🆔 Cust - ID :* _%customer_id%_\n*📍 Alamat      :* _%address%_\n*📧 Email       :* _%email%_\n*👨🏼‍💼 Customer Service :* %customer_service%\n\nSimpan informasi ini untuk pengajuan layanan atau saat terjadi gangguan layanan.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat aktivasi customer.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(58, 'register_customer', NULL, 'Register Customer', 'register customer', 'Yth. Bpk/Ibu %name%,\n%address%\n\nTerima kasih telah melakukan pendaftaran di sistem kami.\nBerikut layanan yang anda pilih:\n\n%pakets%\n\nJika anda ingin menggunakan layanan kami, silahkan hubungi *WA 0878-1804-0024*.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat registrasi customer.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(59, 'create_new_bill', NULL, 'Create New bill', 'create new bill', 'Yth. Bpk/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nSaat ini anda sudah dapat membayar tagihan internet.\nBerikut rincian tagihan anda:\n\n*📦 Paket            :* %paket-name%\n*💶 Tagihan        :* %bill%\n*📆 Periode         :* %periode%\n*✅ Jatuh tempo :* %deadline%\n\nAnda dapat melakukan pembayaran melalui:\n%account-banks%\n\nInformasikan kepada kami jika anda sudah melakukan pembayaran di nomor *WA 0878-1804-0024*\nTerima kasih telah mempercayakan kebutuhan internet anda kepada kami.\n\n*Shanum Network Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*_Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini._*', 'Pesan yang dikirim saat tagihan dibuat.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL),
(60, 'paylater', NULL, 'Pay Later', 'paylater', '===============================\n         SHANUM NETWORK FIBER\n          Receipt # : %billing_number%\n===============================\n\nPelanggan yang terhormat, Berikut kami sampaikan detail pembayaran mundur anda:\n\n*👥 Nama			:* _Bpk/Ibu %name%_\n*🆔 Cust - ID		:* _%customer_id%_\n*📍 Alamat			:* _%address%_\n*📦 Paket			:* _%paket%_\n*📆 Periode			:* _%periode%_\n*💶 Nominal			:* *_%bill%_*\n\n*Teller				:* _%teller%_\n*Metode pembayaran	:* _%payment_methode%_\n*Jatuh Tempo		:* *%paylater%*\n\n===============================\nMohon agar membayar tagihan tepat waktu guna menghindari\nterisolirnya akun Anda, Terima Kasih.\n===============================\n            *Shanum Network Fiber*\n_Pwrd by PT. Global Sarana Elektronika_\n===============================', 'Pesan yang dikirim saat permintaan pay later.', 1, 0, '2024-12-31 03:03:46', '2024-12-31 03:03:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_numbers`
--

CREATE TABLE `whatsapp_numbers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `webhook_client_header` varchar(255) DEFAULT NULL,
  `webhook_client_secret` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_numbers`
--

INSERT INTO `whatsapp_numbers` (`id`, `slug`, `name`, `number`, `webhook_client_header`, `webhook_client_secret`, `description`, `disabled`, `default`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, '3d4zs', 'Official Contact', '6287818040024', 'Shanet-Signature', 'O5aEU9rSlNnHevb', 'Official Contact Shanum Network Fiber', 0, 0, '2024-12-31 03:03:40', '2024-12-31 03:03:40', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auto_isolirs`
--
ALTER TABLE `auto_isolirs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auto_isolirs_mikrotik_id_foreign` (`mikrotik_id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billings`
--
ALTER TABLE `billings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `billings_billing_periode_id_foreign` (`billing_periode_id`),
  ADD KEY `billings_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `billing_periodes`
--
ALTER TABLE `billing_periodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `billing_periodes_name_unique` (`name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_slug_unique` (`slug`),
  ADD UNIQUE KEY `customers_email_unique` (`email`),
  ADD UNIQUE KEY `customers_customer_id_unique` (`customer_id`),
  ADD KEY `customers_paket_id_foreign` (`paket_id`),
  ADD KEY `customers_mikrotik_id_foreign` (`mikrotik_id`),
  ADD KEY `customers_ppp_type_id_foreign` (`ppp_type_id`),
  ADD KEY `customers_user_id_foreign` (`user_id`);

--
-- Indexes for table `customer_onts`
--
ALTER TABLE `customer_onts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_onts_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `deleted_customers`
--
ALTER TABLE `deleted_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `merk_routers`
--
ALTER TABLE `merk_routers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message_logs`
--
ALTER TABLE `message_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_logs_customer_id_foreign` (`customer_id`),
  ADD KEY `message_logs_gateway_id_foreign` (`gateway_id`);

--
-- Indexes for table `midtrans`
--
ALTER TABLE `midtrans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `midtrans_slug_unique` (`slug`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mikrotiks`
--
ALTER TABLE `mikrotiks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_customer_id_foreign` (`customer_id`),
  ADD KEY `orders_billing_id_foreign` (`billing_id`);

--
-- Indexes for table `pakets`
--
ALTER TABLE `pakets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pakets_slug_unique` (`slug`),
  ADD KEY `pakets_mikrotik_id_foreign` (`mikrotik_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_gateways_slug_unique` (`slug`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `ppp_profiles`
--
ALTER TABLE `ppp_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppp_profiles_mikrotik_id_foreign` (`mikrotik_id`);

--
-- Indexes for table `ppp_types`
--
ALTER TABLE `ppp_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ppp_types_name_unique` (`name`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `type_routers`
--
ALTER TABLE `type_routers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_routers_merk_router_id_foreign` (`merk_router_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `websystems`
--
ALTER TABLE `websystems`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `websystems_slug_unique` (`slug`);

--
-- Indexes for table `whatsapp_gateways`
--
ALTER TABLE `whatsapp_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_gateways_slug_unique` (`slug`),
  ADD KEY `whatsapp_gateways_whatsapp_number_id_foreign` (`whatsapp_number_id`);

--
-- Indexes for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_messages_slug_unique` (`slug`),
  ADD KEY `whatsapp_messages_whatsapp_number_id_foreign` (`whatsapp_number_id`);

--
-- Indexes for table `whatsapp_numbers`
--
ALTER TABLE `whatsapp_numbers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_numbers_slug_unique` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auto_isolirs`
--
ALTER TABLE `auto_isolirs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `billings`
--
ALTER TABLE `billings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=503;

--
-- AUTO_INCREMENT for table `billing_periodes`
--
ALTER TABLE `billing_periodes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `customer_onts`
--
ALTER TABLE `customer_onts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `deleted_customers`
--
ALTER TABLE `deleted_customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=479;

--
-- AUTO_INCREMENT for table `merk_routers`
--
ALTER TABLE `merk_routers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `message_logs`
--
ALTER TABLE `message_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=901;

--
-- AUTO_INCREMENT for table `midtrans`
--
ALTER TABLE `midtrans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `mikrotiks`
--
ALTER TABLE `mikrotiks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pakets`
--
ALTER TABLE `pakets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppp_profiles`
--
ALTER TABLE `ppp_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppp_types`
--
ALTER TABLE `ppp_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=391;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `type_routers`
--
ALTER TABLE `type_routers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `websystems`
--
ALTER TABLE `websystems`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `whatsapp_gateways`
--
ALTER TABLE `whatsapp_gateways`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `whatsapp_numbers`
--
ALTER TABLE `whatsapp_numbers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auto_isolirs`
--
ALTER TABLE `auto_isolirs`
  ADD CONSTRAINT `auto_isolirs_mikrotik_id_foreign` FOREIGN KEY (`mikrotik_id`) REFERENCES `mikrotiks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `billings`
--
ALTER TABLE `billings`
  ADD CONSTRAINT `billings_billing_periode_id_foreign` FOREIGN KEY (`billing_periode_id`) REFERENCES `billing_periodes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `billings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_mikrotik_id_foreign` FOREIGN KEY (`mikrotik_id`) REFERENCES `mikrotiks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customers_paket_id_foreign` FOREIGN KEY (`paket_id`) REFERENCES `pakets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customers_ppp_type_id_foreign` FOREIGN KEY (`ppp_type_id`) REFERENCES `ppp_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_onts`
--
ALTER TABLE `customer_onts`
  ADD CONSTRAINT `customer_onts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_logs`
--
ALTER TABLE `message_logs`
  ADD CONSTRAINT `message_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_logs_gateway_id_foreign` FOREIGN KEY (`gateway_id`) REFERENCES `whatsapp_gateways` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_billing_id_foreign` FOREIGN KEY (`billing_id`) REFERENCES `billings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pakets`
--
ALTER TABLE `pakets`
  ADD CONSTRAINT `pakets_mikrotik_id_foreign` FOREIGN KEY (`mikrotik_id`) REFERENCES `mikrotiks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ppp_profiles`
--
ALTER TABLE `ppp_profiles`
  ADD CONSTRAINT `ppp_profiles_mikrotik_id_foreign` FOREIGN KEY (`mikrotik_id`) REFERENCES `mikrotiks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `type_routers`
--
ALTER TABLE `type_routers`
  ADD CONSTRAINT `type_routers_merk_router_id_foreign` FOREIGN KEY (`merk_router_id`) REFERENCES `merk_routers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `whatsapp_gateways`
--
ALTER TABLE `whatsapp_gateways`
  ADD CONSTRAINT `whatsapp_gateways_whatsapp_number_id_foreign` FOREIGN KEY (`whatsapp_number_id`) REFERENCES `whatsapp_numbers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  ADD CONSTRAINT `whatsapp_messages_whatsapp_number_id_foreign` FOREIGN KEY (`whatsapp_number_id`) REFERENCES `whatsapp_numbers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
