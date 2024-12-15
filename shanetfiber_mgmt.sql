-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 09:04 AM
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
(2, 'Notif_Pembayaran', 1, 'Expire', '*C60', '*262', NULL, 'false', '16', 'belum_lunas', 'lunas', 'false', '2024-09-29 18:45:50', '2024-10-27 14:39:16', NULL, '*3645', '172.10.10.2', 'Isolir-List', NULL, '*4', '25.25.25.0/24', '172.10.10.2/isolir', 'redirect', '*1252', NULL),
(3, 'Notification_Payment', 18, 'Isolir', '*2BE', '*13', NULL, 'false', '11', 'belum_lunas', 'lunas', 'false', '2024-10-27 23:23:14', '2024-10-27 23:23:14', NULL, '0', NULL, NULL, NULL, '0', NULL, NULL, 'deny', '0', NULL);

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
(1, 'bank-central-asia-zvpa', 'Dian Ardiansyah', 'Bank Central Asia', '2990407315', 'false', '2024-09-29 16:01:50', '2024-09-29 16:01:58'),
(2, 'bank-mandiri-9mq4', 'Dian Ardiansyah', 'Bank Mandiri', '1730012695905', 'false', '2024-09-29 16:03:29', '2024-09-29 16:03:33');

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
(1, '1-qtcg', 1, '1-mrgi-10-2024', 1, '100210121@subhan', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-03 01:23:27', '2024-09-30 08:58:49', '2024-10-03 01:23:27', 0, NULL, 15, '2024-10-29 03:00:00'),
(2, '2-j742', 1, '2-qkrr-10-2024', 2, '100210212@iissugiarti', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 00:17:08', '2024-09-30 08:58:49', '2024-10-13 00:17:08', 0, NULL, 58, '2024-10-29 03:00:00'),
(3, '3-a20n', 1, '3-l4m8-10-2024', 3, '100210220@niaagustin', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-09-30 10:52:04', '2024-09-30 08:58:49', '2024-09-30 10:52:04', 0, NULL, 4, '2024-10-29 03:00:00'),
(4, '4-tqly', 1, '4-71zw-10-2024', 4, '150210411@surya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Admin', '2024-09-30 09:03:27', '2024-09-30 08:58:49', '2024-09-30 09:03:27', 0, NULL, 3, '2024-10-29 03:00:00'),
(5, '5-e0ho', 1, '5-dlov-10-2024', 5, '150210712@koesnadi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-05 22:14:09', '2024-09-30 08:58:49', '2024-10-05 22:14:09', 0, NULL, 22, '2024-10-29 03:00:00'),
(6, '6-5wd4', 1, '6-yynv-10-2024', 6, '150211002@cucu', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-09-30 20:53:22', '2024-09-30 08:58:49', '2024-09-30 20:53:22', 0, NULL, 7, '2024-10-29 03:00:00'),
(7, '7-hrq7', 1, '7-izge-10-2024', 7, '100220430@dinda', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 13:47:42', '2024-09-30 08:58:49', '2024-10-13 13:47:42', 0, NULL, 61, '2024-10-29 03:00:00'),
(8, '8-zffk', 1, '8-hcka-10-2024', 8, '100220918@darsih', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Admin ShaNet Fiber', '2024-10-27 10:05:32', '2024-09-30 08:58:49', '2024-10-27 10:05:32', 0, '2024-10-25 03:00:00', 94, '2024-10-29 03:00:00'),
(9, '9-vpav', 1, '9-fezd-10-2024', 9, '100221009@eko', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-12 14:15:50', '2024-09-30 08:58:49', '2024-10-12 14:15:50', 0, NULL, 56, '2024-10-29 03:00:00'),
(10, '10-nmsn', 1, '10-sv7p-10-2024', 10, '100221020@ustdali', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 14:46:11', '2024-09-30 08:58:49', '2024-10-14 14:46:11', 0, NULL, 68, '2024-10-29 03:00:00'),
(11, '11-tg3c', 1, '11-nuca-10-2024', 11, '100221029@rita', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 14:24:27', '2024-09-30 08:58:49', '2024-10-15 14:24:27', 0, NULL, 82, '2024-10-29 03:00:00'),
(12, '12-rjwc', 1, '12-fo6j-10-2024', 12, '150240118@suud', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-17 15:48:15', '2024-09-30 08:58:49', '2024-10-17 15:48:15', 0, NULL, 87, '2024-10-29 03:00:00'),
(13, '13-ce0x', 1, '13-cpsx-10-2024', 13, '100221107@anggi', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 16:54:50', '2024-09-30 08:58:49', '2024-10-06 16:54:50', 0, NULL, 31, '2024-10-29 03:00:00'),
(14, '14-kclc', 1, '14-l6j9-10-2024', 14, '150221120@adhiga', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 20:22:40', '2024-09-30 08:58:49', '2024-10-15 20:22:40', 0, '2024-10-19 03:00:00', 85, '2024-10-29 03:00:00'),
(15, '15-xura', 1, '15-pfum-10-2024', 15, '200211114@armiyanti', 'Paket 2 (15 Mbps)', 200000, 'Admin', 'LS', 'Admin', '2024-09-30 09:00:03', '2024-09-30 08:58:49', '2024-09-30 09:00:03', 0, NULL, 1, '2024-10-29 03:00:00'),
(16, '16-iflw', 1, '16-zqoq-10-2024', 16, '100230102@rdcell', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 18:47:21', '2024-09-30 08:58:49', '2024-10-15 18:47:21', 0, NULL, 84, '2024-10-29 03:00:00'),
(17, '17-z6w4', 1, '17-eqvs-10-2024', 17, '150230208@azril', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 00:14:01', '2024-09-30 08:58:49', '2024-10-02 00:14:01', 0, NULL, 12, '2024-10-29 03:00:00'),
(18, '18-zc5k', 1, '18-rzgx-10-2024', 18, '150230402@iip', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 09:51:08', '2024-09-30 08:58:49', '2024-10-02 09:51:08', 0, NULL, 14, '2024-10-29 03:00:00'),
(19, '19-akuh', 1, '19-11cq-10-2024', 19, '150230408@ahmadraja', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 21:58:11', '2024-09-30 08:58:49', '2024-10-10 21:58:11', 0, NULL, 54, '2024-10-29 03:00:00'),
(20, '20-jhup', 1, '20-nisa-10-2024', 20, '150230503@lamjaya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-05 10:38:44', '2024-09-30 08:58:49', '2024-10-05 10:38:44', 0, NULL, 19, '2024-10-29 03:00:00'),
(21, '21-pu4z', 1, '21-ttf1-10-2024', 21, '150230717@ibuayu', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 13:18:08', '2024-09-30 08:58:49', '2024-10-14 13:18:08', 0, NULL, 67, '2024-10-29 03:00:00'),
(22, '22-pdup', 1, '22-m7in-10-2024', 22, '100230729@jojo', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 17:59:57', '2024-09-30 08:58:49', '2024-10-06 17:59:57', 0, NULL, 33, '2024-10-29 03:00:00'),
(23, '23-5jkg', 1, '23-m8v1-10-2024', 23, '150230804@rafka', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Admin', '2024-09-30 09:03:12', '2024-09-30 08:58:49', '2024-09-30 09:03:12', 0, NULL, 2, '2024-10-29 03:00:00'),
(24, '24-ci2r', 1, '24-xol2-10-2024', 24, '150231101@neni', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 10:36:33', '2024-09-30 08:58:49', '2024-10-14 10:36:33', 0, NULL, 65, '2024-10-29 03:00:00'),
(25, '25-9wf7', 1, '25-ceiz-10-2024', 25, '150231115@heriyanto', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 18:02:43', '2024-09-30 08:58:49', '2024-10-14 18:02:43', 0, NULL, 70, '2024-10-29 03:00:00'),
(26, '26-czlm', 1, '26-iiw3-10-2024', 26, '075230101@meisya', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-12 12:33:08', '2024-09-30 08:58:49', '2024-10-12 12:33:08', 0, NULL, 55, '2024-10-29 03:00:00'),
(27, '27-4aq2', 1, '27-brvn-10-2024', 27, '100231205@novi', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 00:13:15', '2024-09-30 08:58:50', '2024-10-02 00:13:15', 0, NULL, 10, '2024-10-29 03:00:00'),
(28, '28-731i', 1, '28-cgg8-10-2024', 28, '100231210@hasma', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 08:25:33', '2024-09-30 08:58:50', '2024-10-14 08:25:33', 0, NULL, 63, '2024-10-29 03:00:00'),
(29, '29-z5et', 1, '29-upjm-10-2024', 29, '150231216@euis', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 00:13:05', '2024-09-30 08:58:50', '2024-10-02 00:13:05', 0, NULL, 9, '2024-10-29 03:00:00'),
(30, '30-ygl8', 1, '30-3yvw-10-2024', 30, '100231217@amoy', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 22:54:42', '2024-09-30 08:58:50', '2024-10-14 22:54:42', 0, NULL, 75, '2024-10-29 03:00:00'),
(31, '31-rxu0', 1, '31-dqf3-10-2024', 31, '150240211@pakzaky', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 15:35:58', '2024-09-30 08:58:50', '2024-10-07 15:35:58', 0, NULL, 39, '2024-10-29 03:00:00'),
(32, '32-vyc5', 1, '32-8mvw-10-2024', 32, '100240219@tehai', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 21:32:47', '2024-09-30 08:58:50', '2024-10-14 21:32:47', 0, NULL, 73, '2024-10-29 03:00:00'),
(33, '33-wt0q', 1, '33-tr6c-10-2024', 33, '150240311@slamet', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 12:37:49', '2024-09-30 08:58:50', '2024-10-13 12:37:49', 0, NULL, 60, '2024-10-29 03:00:00'),
(34, '34-omj3', 1, '34-ej6y-10-2024', 34, '170231220@iburuli', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 13:24:26', '2024-09-30 08:58:50', '2024-10-10 13:24:26', 0, NULL, 50, '2024-10-29 03:00:00'),
(35, '35-om5x', 1, '35-10nm-10-2024', 35, '100221019@hafis', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 00:13:53', '2024-09-30 08:58:50', '2024-10-02 00:13:53', 0, NULL, 11, '2024-10-29 03:00:00'),
(36, '36-tslu', 1, '36-gqol-10-2024', 36, '200240417@yanti', 'Paket 2 (15 Mbps)', 200000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 09:16:59', '2024-09-30 08:58:50', '2024-10-13 09:16:59', 0, NULL, 59, '2024-10-29 03:00:00'),
(37, '38-afqj', 1, '38-mfwl-10-2024', 38, '150240125@warungbali', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 08:57:44', '2024-09-30 08:58:50', '2024-10-14 08:57:44', 0, NULL, 64, '2024-10-29 03:00:00'),
(38, '39-odak', 1, '39-xekf-10-2024', 39, '100210120@hexsa', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 14:20:01', '2024-09-30 08:58:50', '2024-10-07 14:20:01', 0, NULL, 37, '2024-10-29 03:00:00'),
(39, '40-tnwv', 1, '40-erql-10-2024', 40, '100240501@siti', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 12:37:35', '2024-09-30 08:58:50', '2024-10-14 12:37:35', 0, NULL, 66, '2024-10-29 03:00:00'),
(40, '41-ikam', 1, '41-xfht-10-2024', 41, '100231112@wandi', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 10:17:31', '2024-09-30 08:58:50', '2024-10-15 10:17:31', 0, NULL, 78, '2024-10-29 03:00:00'),
(41, '42-lyzo', 1, '42-lhkm-10-2024', 42, '100240503@rizal', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-12 21:17:53', '2024-09-30 08:58:50', '2024-10-12 21:17:53', 0, NULL, 57, '2024-10-29 03:00:00'),
(42, '43-pwwu', 1, '43-1agz-10-2024', 43, '150240510@pakedi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 18:33:09', '2024-09-30 08:58:50', '2024-10-15 18:33:09', 0, NULL, 83, '2024-10-29 03:00:00'),
(43, '44-cl5s', 1, '44-fqr3-10-2024', 44, '150221217@furqon', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 18:23:23', '2024-09-30 08:58:50', '2024-10-06 18:23:23', 0, NULL, 36, '2024-10-29 03:00:00'),
(44, '45-ydmk', 1, '45-mbmp-10-2024', 45, '150221106@aam', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-08 20:34:17', '2024-09-30 08:58:50', '2024-10-08 20:34:17', 0, NULL, 44, '2024-10-29 03:00:00'),
(45, '46-dxdt', 1, '46-jdks-10-2024', 46, '150210312@sikefi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-06 15:35:57', '2024-09-30 08:58:50', '2024-10-06 15:35:57', 0, NULL, 26, '2024-10-29 03:00:00'),
(46, '47-hivz', 1, '47-p4em-10-2024', 47, '150231029@mulya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'BL', 'Dian Ardiansyah', '2024-10-17 23:19:51', '2024-09-30 08:58:50', '2024-10-17 23:20:37', 0, '2024-10-19 03:00:00', NULL, '2024-10-29 03:00:00'),
(47, '48-9htp', 1, '48-bz2d-10-2024', 48, '150240710@sulastri', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-05 22:29:23', '2024-09-30 08:58:50', '2024-10-05 22:29:23', 0, NULL, 23, '2024-10-29 03:00:00'),
(48, '49-ucyx', 1, '49-iu4n-10-2024', 49, '150240621@miayamgeprek', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 21:32:29', '2024-09-30 08:58:50', '2024-10-14 21:32:29', 0, NULL, 72, '2024-10-29 03:00:00'),
(49, '50-tgyz', 1, '50-hgqu-10-2024', 50, '150230725@ustdfery', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-09-30 20:51:28', '2024-09-30 08:58:50', '2024-09-30 20:51:28', 0, NULL, 6, '2024-10-29 03:00:00'),
(50, '51-jg1t', 1, '51-d8xq-10-2024', 51, '150240711@tehenok', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 16:05:12', '2024-09-30 08:58:50', '2024-10-10 16:05:12', 0, NULL, 51, '2024-10-29 03:00:00'),
(51, '52-coep', 1, '52-ezsd-10-2024', 52, '150240725@ahmad', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-24 19:05:01', '2024-09-30 08:58:50', '2024-10-24 19:05:01', 0, NULL, 92, '2024-10-29 03:00:00'),
(52, '53-kx3r', 1, '53-o8wp-10-2024', 53, 'Marie Maharani Widyaningrum', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 20:51:27', '2024-09-30 08:58:50', '2024-10-10 20:51:27', 0, NULL, 53, '2024-10-12 03:00:00'),
(53, '54-5enx', 1, '54-mxv3-10-2024', 54, '120240310@linda', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 22:53:47', '2024-09-30 08:58:50', '2024-10-14 22:53:47', 0, NULL, 74, '2024-10-29 03:00:00'),
(54, '55-d3gz', 1, '55-eg1k-10-2024', 55, '100240225@agis', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-09 23:02:44', '2024-09-30 08:58:50', '2024-10-09 23:02:44', 0, NULL, 48, '2024-10-29 03:00:00'),
(55, '56-ggr6', 1, '56-fw6k-10-2024', 56, '150240807@nayunda', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-04 12:28:46', '2024-09-30 08:58:50', '2024-10-04 12:28:46', 0, NULL, 17, '2024-10-29 03:00:00'),
(56, '57-jxct', 1, '57-uhdj-10-2024', 57, '200230910@iyan', 'Paket 2 (15 Mbps)', 200000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 15:26:50', '2024-09-30 08:58:50', '2024-10-07 15:26:50', 0, NULL, 38, '2024-10-29 03:00:00'),
(57, '58-cbdt', 1, '58-aqr3-10-2024', 58, '150240811@nurfakriah', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-13 22:20:00', '2024-09-30 08:58:50', '2024-10-13 22:20:00', 0, NULL, 62, '2024-10-29 03:00:00'),
(58, '59-nsz8', 1, '59-z0xb-10-2024', 59, '120230419@fitria', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-14 21:32:24', '2024-09-30 08:58:50', '2024-10-14 21:32:24', 0, NULL, 71, '2024-10-29 03:00:00'),
(59, '60-el5z', 1, '60-zokf-10-2024', 60, '150240612@jepri', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 08:26:21', '2024-09-30 08:58:50', '2024-10-15 08:26:21', 0, NULL, 76, '2024-10-29 03:00:00'),
(60, '61-pcpn', 1, '61-9plz-10-2024', 61, '150240817@ibuani', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-05 22:07:03', '2024-09-30 08:58:50', '2024-10-05 22:07:03', 0, NULL, 20, '2024-10-29 03:00:00'),
(61, '62-zalm', 1, '62-nxjg-10-2024', 62, '250230601@abdulrahman', 'Paket 3 (20 Mbps)', 250000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-05 09:01:07', '2024-09-30 08:58:50', '2024-10-05 09:01:07', 0, NULL, 18, '2024-10-29 03:00:00'),
(62, '63-qpbs', 1, '63-78f3-10-2024', 63, '150240824@dendi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 02:00:34', '2024-09-30 08:58:50', '2024-10-10 02:00:34', 0, NULL, 49, '2024-10-29 03:00:00'),
(63, '64-ckry', 1, '64-mv5g-10-2024', 64, '100240901@bunehe', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-10 20:51:08', '2024-09-30 08:58:50', '2024-10-10 20:51:08', 0, NULL, 52, '2024-10-29 03:00:00'),
(64, '65-l5du', 1, '65-psah-10-2024', 65, '150240901@pakkurdi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 10:37:57', '2024-09-30 08:58:51', '2024-10-15 10:37:57', 0, NULL, 80, '2024-10-29 03:00:00'),
(65, '66-9rk2', 1, '66-o7dx-10-2024', 66, '150240305@goradhigha', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-09 11:42:41', '2024-09-30 08:58:51', '2024-10-09 11:42:41', 0, NULL, 46, '2024-10-29 03:00:00'),
(66, '67-jlue', 1, '67-eice-10-2024', 67, 'Lulu Andriani', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-08 12:45:06', '2024-09-30 08:58:51', '2024-10-08 12:45:06', 0, NULL, 42, '2024-10-07 03:00:00'),
(67, '68-dpyo', 1, '68-10jr-10-2024', 68, 'Ma Aisah', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-08 12:50:05', '2024-09-30 08:58:51', '2024-10-08 12:50:05', 0, NULL, 43, '2024-10-07 03:00:00'),
(68, '69-oob6', 1, '69-bs6v-10-2024', 69, '150240908@alhikam', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-01 08:35:59', '2024-09-30 08:58:51', '2024-10-01 08:35:59', 0, NULL, 8, '2024-10-29 03:00:00'),
(69, '70-ysxb', 1, '70-nzgg-10-2024', 70, '150240912@pakkurdi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 10:37:52', '2024-09-30 08:58:51', '2024-10-15 10:37:52', 0, NULL, 79, '2024-10-29 03:00:00'),
(70, '71-rpnq', 1, '71-lhrc-10-2024', 71, '100240917@enen', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-09 23:02:32', '2024-09-30 08:58:51', '2024-10-09 23:02:32', 0, NULL, 47, '2024-10-29 03:00:00'),
(71, '72-xq6x', 1, '72-r558-10-2024', 72, '100240715@bmcell', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-15 11:11:52', '2024-09-30 08:58:51', '2024-10-15 11:11:52', 0, NULL, 81, '2024-10-29 03:00:00'),
(72, '73-d29v', 1, '73-cxuj-10-2024', 73, 'Asep Topik Hidayat', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-07 19:36:03', '2024-09-30 08:58:51', '2024-10-07 19:36:03', 0, NULL, 41, '2024-10-21 03:00:00'),
(73, '74-9qb9', 1, '74-cvve-10-2024', 74, 'Egy Laksma Suandi', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-02 00:14:59', '2024-09-30 08:58:51', '2024-10-02 00:14:59', 0, NULL, 13, '2024-10-23 03:00:00'),
(75, '77-drev', 1, '77-ymlm-10-2024', 77, 'Nazri Nasier', 'Paket 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-03 15:21:33', '2024-10-03 15:20:17', '2024-10-03 15:21:33', NULL, NULL, 16, NULL),
(76, '78-aazb', 1, '78-sil8-10-2024', 78, 'Moh. Gian Firtyanza', 'Paket 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-08 23:04:59', '2024-10-08 23:04:42', '2024-10-08 23:04:59', NULL, NULL, 45, NULL),
(77, '79-g824', 1, '79-znlv-10-2024', 79, 'Intan Ayu Sri Wahyuni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-16 17:45:35', '2024-10-16 15:25:35', '2024-10-16 17:45:35', NULL, NULL, 86, NULL),
(78, '80-eq9i', 1, '80-ccub-10-2024', 80, 'Nurhayati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-19 22:34:23', '2024-10-19 22:25:51', '2024-10-19 22:34:23', NULL, NULL, 90, NULL),
(79, '81-ubun', 1, '81-e7x4-10-2024', 81, 'Irvan Mohammad Regent', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-26 13:01:54', '2024-10-26 12:52:51', '2024-10-26 13:01:54', NULL, NULL, 93, NULL),
(80, '137-t16k', 1, '137-yz1p-10-2024', 137, 'Fera', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-29 11:18:39', '2024-10-29 11:12:30', '2024-10-29 11:18:39', NULL, NULL, 95, NULL),
(81, '138-qqjz', 1, '138-yqkj-10-2024', 138, 'Robi Anggara', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-10-29 13:21:53', '2024-10-29 13:14:39', '2024-10-29 13:21:53', NULL, NULL, 96, NULL),
(200, '1-hahb', 3, '1-mbm0-11-2024', 1, 'Muh Subhan', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:13:57', '2024-11-01 05:13:57', 0, NULL, NULL, '2024-11-24 20:00:00'),
(201, '2-ydhf', 3, '2-c07u-11-2024', 2, 'Iis Sugiarti', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:13:57', '2024-11-01 05:13:57', 0, NULL, NULL, '2024-11-10 20:00:00'),
(202, '3-k5hw', 3, '3-r0xw-11-2024', 3, 'Asep Nasihin', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:13:58', '2024-11-01 05:13:58', 0, NULL, NULL, '2024-11-18 20:00:00'),
(203, '4-ymng', 3, '4-hcod-11-2024', 4, 'Pak Surya', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:33:36', '2024-11-01 05:13:59', '2024-11-01 05:33:36', 0, NULL, 104, '2024-11-09 20:00:00'),
(204, '5-fivv', 3, '5-olvh-11-2024', 5, 'Koesnadi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:13:59', '2024-11-01 05:13:59', 0, NULL, NULL, '2024-11-10 20:00:00'),
(205, '6-nwtz', 3, '6-gdel-11-2024', 6, 'Cucu Suryani', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:28:46', '2024-11-01 05:14:00', '2024-11-01 05:28:46', 0, NULL, 102, '2024-10-31 20:00:00'),
(206, '7-qbpg', 3, '7-2lhx-11-2024', 7, 'Sumarni', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:00', '2024-11-01 05:14:00', 0, NULL, NULL, '2024-11-28 20:00:00'),
(207, '8-00ey', 3, '8-hdun-11-2024', 8, 'Teh Darsih', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:01', '2024-11-01 05:14:01', 0, NULL, NULL, '2024-11-16 20:00:00'),
(208, '9-5z4m', 3, '9-rvks-11-2024', 9, 'Pak Eko', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:01', '2024-11-01 05:14:01', 0, NULL, NULL, '2024-11-07 20:00:00'),
(209, '10-whsd', 3, '10-lyzh-11-2024', 10, 'Ali Akbar', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:02', '2024-11-01 05:14:02', 0, NULL, NULL, '2024-11-18 20:00:00'),
(210, '11-dwvj', 3, '11-nnva-11-2024', 11, 'Rita Susanti', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:03', '2024-11-01 05:14:03', 0, NULL, NULL, '2024-11-27 20:00:00'),
(211, '12-q2z0', 3, '12-c19f-11-2024', 12, 'Pak Suud', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:03', '2024-11-01 05:14:03', 0, NULL, NULL, '2024-11-16 20:00:00'),
(212, '13-lgpg', 3, '13-va2n-11-2024', 13, 'Mamah Anggi', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:04', '2024-11-01 05:14:04', 0, NULL, NULL, '2024-11-05 20:00:00'),
(213, '14-on5g', 3, '14-4fyq-11-2024', 14, 'Ai Solihah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:05', '2024-11-01 05:14:05', 0, NULL, NULL, '2024-11-18 20:00:00'),
(214, '15-9m9o', 3, '15-vsgl-11-2024', 15, 'Armiyanti', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:05', '2024-11-01 05:14:05', 0, NULL, NULL, '2024-11-12 20:00:00'),
(215, '16-16rt', 3, '16-hrzm-11-2024', 16, 'Riska Azizah', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:06', '2024-11-01 05:14:06', 0, NULL, NULL, '2024-10-31 20:00:00'),
(216, '17-shqz', 3, '17-pjbc-11-2024', 17, 'Azriel', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:07', '2024-11-01 05:14:07', 0, NULL, NULL, '2024-11-06 20:00:00'),
(217, '18-5sgy', 3, '18-nx8q-11-2024', 18, 'Iip', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 10:06:47', '2024-11-01 05:14:07', '2024-11-01 10:06:47', 0, NULL, 105, '2024-10-31 20:00:00'),
(218, '19-rocw', 3, '19-qjc8-11-2024', 19, 'Ahmad Saepuloh', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:08', '2024-11-01 05:14:08', 0, NULL, NULL, '2024-11-06 20:00:00'),
(219, '20-hy9f', 3, '20-z0ha-11-2024', 20, 'Pak Lili', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:20:43', '2024-11-01 05:14:08', '2024-11-01 05:20:43', 0, NULL, 98, '2024-11-01 20:00:00'),
(220, '21-ojfi', 3, '21-psof-11-2024', 21, 'Ibu Ayu', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:09', '2024-11-01 05:14:09', 0, NULL, NULL, '2024-11-15 20:00:00'),
(221, '22-ypzf', 3, '22-zdnw-11-2024', 22, 'Ratih Aisyah', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:09', '2024-11-01 05:14:09', 0, NULL, NULL, '2024-11-27 20:00:00'),
(222, '23-gieb', 3, '23-iwy7-11-2024', 23, 'AA Ramli', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:10', '2024-11-01 05:14:10', 0, NULL, NULL, '2024-11-02 20:00:00'),
(223, '24-24in', 3, '24-qvwe-11-2024', 24, 'Neni Nurhaeni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:11', '2024-11-01 05:14:11', 0, NULL, NULL, '2024-10-30 20:00:00'),
(224, '25-hsqq', 3, '25-pjp7-11-2024', 25, 'Etik Aprilia Ratna Sari', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:11', '2024-11-01 05:14:11', 0, NULL, NULL, '2024-11-13 20:00:00'),
(225, '26-w2en', 3, '26-plut-11-2024', 26, 'Narsiah', 'ShaNet 0 (Upto 7 Mbps)', 75000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:13', '2024-11-01 05:14:13', 0, NULL, NULL, '2024-10-30 20:00:00'),
(226, '27-m8wy', 3, '27-axct-11-2024', 27, 'Novi Eka Purwaningsih', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:14', '2024-11-01 05:14:14', 0, NULL, NULL, '2024-11-03 20:00:00'),
(227, '28-vxka', 3, '28-047u-11-2024', 28, 'Hasma Wati', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:14', '2024-11-01 05:14:14', 0, NULL, NULL, '2024-11-08 20:00:00'),
(228, '29-sehu', 3, '29-ai5p-11-2024', 29, 'Euis Ratnasari', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:15', '2024-11-01 05:14:15', 0, NULL, NULL, '2024-11-14 20:00:00'),
(229, '30-qrak', 3, '30-vtp0-11-2024', 30, 'Adi Amoy', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:15', '2024-11-01 05:14:15', 0, NULL, NULL, '2024-11-15 20:00:00'),
(230, '31-euns', 3, '31-wh7k-11-2024', 31, 'A Zaky Alattas', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:16', '2024-11-01 05:14:16', 0, NULL, NULL, '2024-11-22 20:00:00'),
(231, '32-s5y8', 3, '32-aedr-11-2024', 32, 'Teh Ai', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:17', '2024-11-01 05:14:17', 0, NULL, NULL, '2024-11-17 20:00:00'),
(232, '33-v8rn', 3, '33-gbjq-11-2024', 33, 'Pak Slamet', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:17', '2024-11-01 05:14:17', 0, NULL, NULL, '2024-11-09 20:00:00'),
(233, '34-rrs8', 3, '34-p4xd-11-2024', 34, 'Debbie Febriana', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:18', '2024-11-01 05:14:18', 0, NULL, NULL, '2024-11-18 20:00:00'),
(234, '35-pftv', 3, '35-fivd-11-2024', 35, 'Hafis', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:19', '2024-11-01 05:14:19', 0, NULL, NULL, '2024-11-17 20:00:00'),
(235, '36-n7fe', 3, '36-dlfs-11-2024', 36, 'Triyanti', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:19', '2024-11-01 05:14:19', 0, NULL, NULL, '2024-11-15 20:00:00'),
(236, '38-jbwy', 3, '38-oz0x-11-2024', 38, 'Yuan Firmansyah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:20', '2024-11-01 05:14:20', 0, NULL, NULL, '2024-11-23 20:00:00'),
(237, '39-v82l', 3, '39-ls64-11-2024', 39, 'Dede Suherman', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:18:26', '2024-11-01 05:14:20', '2024-11-01 05:18:26', 0, NULL, 97, '2024-11-18 20:00:00'),
(238, '40-uoft', 3, '40-xy79-11-2024', 40, 'Ida Siti', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:21', '2024-11-01 05:14:21', 0, NULL, NULL, '2024-10-30 20:00:00'),
(239, '41-xvu0', 3, '41-6znk-11-2024', 41, 'Wandi Suhendi', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:22', '2024-11-01 05:14:22', 0, NULL, NULL, '2024-11-10 20:00:00'),
(240, '42-0rez', 3, '42-ys4l-11-2024', 42, 'Muhammad Fahrizal', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:22', '2024-11-01 05:14:22', 0, NULL, NULL, '2024-11-01 20:00:00'),
(241, '43-sggr', 3, '43-9ots-11-2024', 43, 'Edi Suswanto', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:23', '2024-11-01 05:14:23', 0, NULL, NULL, '2024-11-28 20:00:00'),
(242, '44-gcdo', 3, '44-myok-11-2024', 44, 'Tri Nurhidayati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:24', '2024-11-01 05:14:24', 0, NULL, NULL, '2024-11-15 20:00:00'),
(243, '45-3gip', 3, '45-tzlo-11-2024', 45, 'Pak Aam', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:24', '2024-11-01 05:14:24', 0, NULL, NULL, '2024-11-04 20:00:00'),
(244, '46-fzie', 3, '46-7gb1-11-2024', 46, 'Rohani Rusmiati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:25', '2024-11-01 05:14:25', 0, NULL, NULL, '2024-11-10 20:00:00'),
(245, '47-rg8i', 3, '47-cwpb-11-2024', 47, 'Mulyana', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:26', '2024-11-01 05:14:26', 0, NULL, NULL, '2024-11-27 20:00:00'),
(246, '48-q5sy', 3, '48-e4lh-11-2024', 48, 'Sri Sulastri', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2024-11-02 04:15:36', '2024-11-01 05:14:27', '2024-11-02 04:15:36', 0, NULL, 108, '2024-11-08 20:00:00'),
(247, '49-qnho', 3, '49-ckru-11-2024', 49, 'Mie Ayam & Geprek', 'ShaNet 1 (10 Mbps)', 75000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:28', '2024-11-01 05:14:28', 0, NULL, NULL, '2024-11-19 20:00:00'),
(248, '50-bn5o', 3, '50-gzip-11-2024', 50, 'Ferry Kurniawan', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:28', '2024-11-01 05:14:28', 0, NULL, NULL, '2024-11-23 20:00:00'),
(249, '51-v146', 3, '51-tzei-11-2024', 51, 'Nok Reni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:29', '2024-11-01 05:14:29', 0, NULL, NULL, '2024-11-09 20:00:00'),
(250, '52-ywal', 3, '52-5tk8-11-2024', 52, 'Ahmad Sugianto', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:30', '2024-11-01 05:14:30', 0, NULL, NULL, '2024-11-23 20:00:00'),
(251, '53-c8b4', 3, '53-td4p-11-2024', 53, 'Maharani Widyaningrum', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:30', '2024-11-01 05:14:30', 0, NULL, NULL, '2024-11-11 20:00:00'),
(252, '54-rtcj', 3, '54-gywm-11-2024', 54, 'Linda', 'ShaNet 0 (7 Mbps)', 120000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:31', '2024-11-01 05:14:31', 0, NULL, NULL, '2024-11-08 20:00:00'),
(253, '55-mwnz', 3, '55-fwon-11-2024', 55, 'Agista Utari', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:31', '2024-11-01 05:14:31', 0, NULL, NULL, '2024-11-23 20:00:00'),
(254, '56-2oy9', 3, '56-dhev-11-2024', 56, 'Agustian Setiadi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:32', '2024-11-01 05:14:32', 0, NULL, NULL, '2024-11-05 20:00:00'),
(255, '57-l4oy', 3, '57-byxe-11-2024', 57, 'Pak Iyan', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:33', '2024-11-01 05:14:33', 0, NULL, NULL, '2024-11-08 20:00:00'),
(256, '58-0v5q', 3, '58-scmx-11-2024', 58, 'Nur Fakriah Putri', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:33', '2024-11-01 05:14:33', 0, NULL, NULL, '2024-11-09 20:00:00'),
(257, '59-usto', 3, '59-ljah-11-2024', 59, 'Amni Fitria', 'ShaNet 0 (7 Mbps)', 120000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:34', '2024-11-01 05:14:34', 0, NULL, NULL, '2024-11-17 20:00:00'),
(258, '60-6rst', 3, '60-0yge-11-2024', 60, 'Pak Jepri', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:35', '2024-11-01 05:14:35', 0, NULL, NULL, '2024-11-10 20:00:00'),
(259, '61-w1ex', 3, '61-l8uy-11-2024', 61, 'Bu Ani', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:35', '2024-11-01 05:14:35', 0, NULL, NULL, '2024-11-15 20:00:00'),
(260, '62-fgwi', 3, '62-qpue-11-2024', 62, 'Yeyet Abdulrachman', 'ShaNet 3 (20 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:36', '2024-11-01 05:14:36', 0, NULL, NULL, '2024-10-30 20:00:00'),
(261, '63-emra', 3, '63-xjj7-11-2024', 63, 'Pak Dendi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:37', '2024-11-01 05:14:37', 0, NULL, NULL, '2024-11-22 20:00:00'),
(262, '64-kmfg', 3, '64-jtq9-11-2024', 64, 'Marya Ningsih', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:37', '2024-11-01 05:14:37', 0, NULL, NULL, '2024-10-30 20:00:00'),
(263, '65-gsju', 3, '65-ez2l-11-2024', 65, 'Pak Kurdi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:38', '2024-11-01 05:14:38', 0, NULL, NULL, '2024-10-30 20:00:00'),
(264, '66-kcjt', 3, '66-pyhi-11-2024', 66, 'Kodrat (GOR ADHIGA)', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:38', '2024-11-01 05:14:38', 0, NULL, NULL, '2024-11-03 20:00:00'),
(265, '67-zb2e', 3, '67-yll5-11-2024', 67, 'Lulu Andriani', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:39', '2024-11-01 05:14:39', 0, NULL, NULL, '2024-11-06 20:00:00'),
(266, '68-nmsy', 3, '68-m1z1-11-2024', 68, 'Ma Aisah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:39', '2024-11-01 05:14:39', 0, NULL, NULL, '2024-11-06 20:00:00'),
(267, '69-yujj', 3, '69-hixd-11-2024', 69, 'Alya Iklima Hidayah', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:23:31', '2024-11-01 05:14:40', '2024-11-01 05:23:31', 0, NULL, 100, '2024-11-06 20:00:00'),
(268, '70-7esu', 3, '70-weoh-11-2024', 70, 'Pak Kurdi 2', 'ShaNet 1 (10 Mbps)', 100500, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:40', '2024-11-01 05:14:40', 0, NULL, NULL, '2024-11-10 20:00:00'),
(269, '71-eboq', 3, '71-ljaw-11-2024', 71, 'Teh Enen', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:41', '2024-11-01 05:14:41', 0, NULL, NULL, '2024-11-04 20:00:00'),
(270, '72-jobe', 3, '72-liyq-11-2024', 72, 'Rina Konter BMCell', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:41', '2024-11-01 05:14:41', 0, NULL, NULL, '2024-11-13 20:00:00'),
(271, '73-tphs', 3, '73-vpym-11-2024', 73, 'Asep Topik Hidayat', 'ShaNet 0 (Upto 7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:42', '2024-11-01 05:14:42', 0, NULL, NULL, '2024-11-20 20:00:00'),
(272, '74-kjhe', 3, '74-zcar-11-2024', 74, 'Egy Laksma Suandi', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:42', '2024-11-01 05:14:42', 0, NULL, NULL, '2024-11-22 20:00:00'),
(273, '75-eayk', 3, '75-rpo9-11-2024', 75, 'H. M Abu Rasyid. AS. SH', 'ShaNet 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:21:56', '2024-11-01 05:14:43', '2024-11-01 05:21:56', 0, NULL, 99, '2024-10-31 20:00:00'),
(274, '77-gefm', 3, '77-upnd-11-2024', 77, 'Nazri Nasier', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:44', '2024-11-01 05:14:44', 0, NULL, NULL, '2024-11-02 20:00:00'),
(275, '78-yiah', 3, '78-439n-11-2024', 78, 'Moh. Gian Firtyanza', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:44', '2024-11-01 05:14:44', 0, NULL, NULL, '2024-11-08 20:00:00'),
(276, '79-fdhp', 3, '79-xwky-11-2024', 79, 'Intan Ayu Sri Wahyuni', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:32:03', '2024-11-01 05:14:45', '2024-11-01 05:32:03', 0, NULL, 103, '2024-11-15 20:00:00'),
(277, '80-21tq', 3, '80-jhj8-11-2024', 80, 'Nurhayati', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:45', '2024-11-01 05:14:45', 0, NULL, NULL, '2024-11-19 20:00:00'),
(278, '81-6a8z', 3, '81-kbqz-11-2024', 81, 'Irvan Mohammad Regent', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-01 05:26:12', '2024-11-01 05:14:46', '2024-11-01 05:26:12', 0, NULL, 101, '2024-11-25 20:00:00'),
(279, '138-ndig', 3, '138-mk68-11-2024', 138, 'Robi Anggara', 'ShaNet 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:46', '2024-11-01 05:14:46', 0, NULL, NULL, '2024-11-28 20:00:00'),
(280, '82-5qbn', 3, '82-5zh5-11-2024', 82, 'Nuryani', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:48', '2024-11-01 05:14:48', 0, NULL, NULL, '2024-11-04 20:00:00'),
(281, '83-jbp6', 3, '83-jjde-11-2024', 83, '200240420@anggi', 'Basic 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:49', '2024-11-01 05:14:49', 0, NULL, NULL, '2024-11-27 20:00:00'),
(282, '85-xhxk', 3, '85-lcos-11-2024', 85, 'Siti Mariam', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:50', '2024-11-01 05:14:50', 0, NULL, NULL, '2024-11-25 20:00:00'),
(283, '86-cldg', 3, '86-wo9w-11-2024', 86, 'Desi Nurdianti', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:50', '2024-11-01 05:14:50', 0, NULL, NULL, '2024-11-25 20:00:00'),
(284, '87-ewcd', 3, '87-4saf-11-2024', 87, 'Iis Ismawati', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:57', '2024-11-01 05:14:57', 0, NULL, NULL, '2024-11-25 20:00:00'),
(285, '88-w7fo', 3, '88-tmyq-11-2024', 88, 'Imar Sulaeman', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:57', '2024-11-01 05:14:57', 0, NULL, NULL, '2024-11-26 20:00:00'),
(286, '89-w9lx', 3, '89-qcc8-11-2024', 89, '125240428@adis', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 05:04:59', '2024-11-01 05:14:58', '2024-11-02 05:04:59', 0, NULL, 109, '2024-11-26 20:00:00'),
(287, '90-bpr1', 3, '90-msjd-11-2024', 90, 'Satim Silegar', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:14:59', '2024-11-01 05:14:59', 0, NULL, NULL, '2024-11-26 20:00:00'),
(288, '91-hpsk', 3, '91-llvk-11-2024', 91, '125240430@heni', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:00', '2024-11-01 05:15:00', 0, NULL, NULL, '2024-11-28 20:00:00'),
(289, '92-piuq', 3, '92-33wx-11-2024', 92, 'Juju Juarsih', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 05:05:56', '2024-11-01 05:15:00', '2024-11-02 05:05:56', 0, NULL, 110, '2024-10-30 20:00:00'),
(290, '93-mppo', 3, '93-5jlx-11-2024', 93, '125240501@deni', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:02', '2024-11-01 05:15:02', 0, NULL, NULL, '2024-10-30 20:00:00'),
(291, '94-79fc', 3, '94-cucq-11-2024', 94, 'Julian Ratu Malik Kusum', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:02', '2024-11-01 05:15:02', 0, NULL, NULL, '2024-11-02 20:00:00'),
(292, '95-uq2j', 3, '95-ime6-11-2024', 95, '125240420@rosidah', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:04', '2024-11-01 05:15:04', 0, NULL, NULL, '2024-11-18 20:00:00'),
(293, '96-rg7z', 3, '96-uzx3-11-2024', 96, '125240509@cici', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:05', '2024-11-01 05:15:05', 0, NULL, NULL, '2024-11-27 20:00:00'),
(294, '97-geym', 3, '97-kuvz-11-2024', 97, '125240510@pepen', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:05', '2024-11-01 05:15:05', 0, NULL, NULL, '2024-11-08 20:00:00'),
(295, '98-33re', 3, '98-oqfi-11-2024', 98, '125240510@wayanto', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:06', '2024-11-01 05:15:06', 0, NULL, NULL, '2024-11-08 20:00:00'),
(296, '99-e9ff', 3, '99-yubd-11-2024', 99, '125240514@miung', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:07', '2024-11-01 05:15:07', 0, NULL, NULL, '2024-11-12 20:00:00'),
(297, '100-iy4n', 3, '100-vosp-11-2024', 100, 'Dadan Hamdani', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:07', '2024-11-01 05:15:07', 0, NULL, NULL, '2024-11-12 20:00:00'),
(298, '101-fmnk', 3, '101-lvvq-11-2024', 101, '125240514@sule', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:08', '2024-11-01 05:15:08', 0, NULL, NULL, '2024-11-12 20:00:00'),
(299, '102-s9ff', 3, '102-x9ht-11-2024', 102, 'Heri Suandi', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:08', '2024-11-01 05:15:08', 0, NULL, NULL, '2024-11-12 20:00:00'),
(300, '103-qzrr', 3, '103-zxem-11-2024', 103, 'Juhdi', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:09', '2024-11-01 05:15:09', 0, NULL, NULL, '2024-10-30 20:00:00'),
(301, '104-i9nm', 3, '104-ikpa-11-2024', 104, '150240515@rtoji', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:11', '2024-11-01 05:15:11', 0, NULL, NULL, '2024-11-13 20:00:00'),
(302, '105-magk', 3, '105-elfs-11-2024', 105, '150240518@suntari', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:11', '2024-11-01 05:15:11', 0, NULL, NULL, '2024-11-27 20:00:00'),
(303, '106-ocis', 3, '106-kldl-11-2024', 106, 'Iqbal Budi Prasetya', 'Basic 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 02:09:52', '2024-11-01 05:15:12', '2024-11-02 02:09:52', 0, NULL, 107, '2024-11-02 20:00:00'),
(304, '107-61ln', 3, '107-se07-11-2024', 107, 'Engko', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:13', '2024-11-01 05:15:13', 0, NULL, NULL, '2024-11-23 20:00:00'),
(305, '108-gl3k', 3, '108-vryj-11-2024', 108, 'Arya Cahyana Putra', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:13', '2024-11-01 05:15:13', 0, NULL, NULL, '2024-10-30 20:00:00'),
(306, '109-dujn', 3, '109-id8h-11-2024', 109, 'Wida', 'Basic 0 (7 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:14', '2024-11-01 05:15:14', 0, NULL, NULL, '2024-11-01 20:00:00'),
(307, '110-hwct', 3, '110-oy0b-11-2024', 110, '150240608@h.imon', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:14', '2024-11-01 05:15:14', 0, NULL, NULL, '2024-11-27 20:00:00'),
(308, '111-q983', 3, '111-1e6a-11-2024', 111, '150240612@julianto', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:15', '2024-11-01 05:15:15', 0, NULL, NULL, '2024-11-27 20:00:00'),
(309, '112-gacb', 3, '112-3hui-11-2024', 112, '150240420@okis', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:17', '2024-11-01 05:15:17', 0, NULL, NULL, '2024-11-18 20:00:00'),
(310, '113-4eij', 3, '113-k78f-11-2024', 113, '150240622@nengsiti', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:17', '2024-11-01 05:15:17', 0, NULL, NULL, '2024-11-27 20:00:00'),
(311, '114-hcjk', 3, '114-soki-11-2024', 114, 'Dedi Haerudi', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:18', '2024-11-01 05:15:18', 0, NULL, NULL, '2024-11-20 20:00:00'),
(312, '115-r0kf', 3, '115-gxad-11-2024', 115, 'Amania Kusumah', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:19', '2024-11-01 05:15:19', 0, NULL, NULL, '2024-11-27 20:00:00'),
(313, '116-wq63', 3, '116-3kav-11-2024', 116, 'Nurul Aeni', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:19', '2024-11-01 05:15:19', 0, NULL, NULL, '2024-11-27 20:00:00'),
(314, '117-834k', 3, '117-4vfc-11-2024', 117, '150240701@ojak', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 02:09:31', '2024-11-01 05:15:20', '2024-11-02 02:09:31', 0, NULL, 106, '2024-11-27 20:00:00'),
(315, '118-xevn', 3, '118-ygw8-11-2024', 118, 'Redi Natisna', 'Basic 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:21', '2024-11-01 05:15:21', 0, NULL, NULL, '2024-11-27 20:00:00'),
(316, '119-mvwj', 3, '119-p6ru-11-2024', 119, 'Wahyu Maulana', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:21', '2024-11-01 05:15:21', 0, NULL, NULL, '2024-11-28 20:00:00'),
(317, '120-yy5m', 3, '120-cps7-11-2024', 120, 'Usman Abdul Sobari', 'Basic 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2024-11-02 05:06:34', '2024-11-01 05:15:22', '2024-11-02 05:06:34', 0, NULL, 111, '2024-11-02 20:00:00'),
(318, '121-omf2', 3, '121-6npt-11-2024', 121, '150240704@bajuri', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:23', '2024-11-01 05:15:23', 0, NULL, NULL, '2024-11-27 20:00:00'),
(319, '123-gxjd', 3, '123-7ol3-11-2024', 123, 'Salmah Saleha', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:24', '2024-11-01 05:15:24', 0, NULL, NULL, '2024-11-06 20:00:00'),
(320, '124-cndz', 3, '124-5ek2-11-2024', 124, 'Neneng Suratni', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:24', '2024-11-01 05:15:24', 0, NULL, NULL, '2024-11-26 20:00:00'),
(321, '125-xih2', 3, '125-hnim-11-2024', 125, 'Herman', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:25', '2024-11-01 05:15:25', 0, NULL, NULL, '2024-11-05 20:00:00'),
(322, '126-iqmx', 3, '126-8b5n-11-2024', 126, '150240912@syarip', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:25', '2024-11-01 05:15:25', 0, NULL, NULL, '2024-11-27 20:00:00'),
(323, '127-unpt', 3, '127-6ul6-11-2024', 127, '150241004@misbah', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:27', '2024-11-01 05:15:27', 0, NULL, NULL, '2024-11-27 20:00:00'),
(324, '128-b5xs', 3, '128-tssx-11-2024', 128, 'Jajang Salahudin', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:27', '2024-11-01 05:15:27', 0, NULL, NULL, '2024-11-04 20:00:00'),
(325, '129-thct', 3, '129-1tat-11-2024', 129, 'Dadi Kusnadi', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:28', '2024-11-01 05:15:28', 0, NULL, NULL, '2024-11-15 20:00:00'),
(326, '130-vmyz', 3, '130-eouu-11-2024', 130, 'Darma Wirawan', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:28', '2024-11-01 05:15:28', 0, NULL, NULL, '2024-11-11 20:00:00'),
(327, '131-rjhx', 3, '131-ey0h-11-2024', 131, '200240715@sulis', 'Basic 2 (15 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:29', '2024-11-01 05:15:29', 0, NULL, NULL, '2024-11-27 20:00:00'),
(328, '132-6ujw', 3, '132-tavi-11-2024', 132, '150240720@agus', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:30', '2024-11-01 05:15:30', 0, NULL, NULL, '2024-11-27 20:00:00'),
(329, '133-c3bx', 3, '133-3xxc-11-2024', 133, '150240720@sulis', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:31', '2024-11-01 05:15:31', 0, NULL, NULL, '2024-11-27 20:00:00'),
(330, '134-y01f', 3, '134-vpxx-11-2024', 134, 'Rika Rahmawati', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:32', '2024-11-01 05:15:32', 0, NULL, NULL, '2024-11-25 20:00:00'),
(331, '135-mpbu', 3, '135-jczr-11-2024', 135, '150240815@yuyun', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:32', '2024-11-01 05:15:32', 0, NULL, NULL, '2024-11-27 20:00:00'),
(332, '136-38vh', 3, '136-6bsc-11-2024', 136, '150240825@wahid', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:33', '2024-11-01 05:15:33', 0, NULL, NULL, '2024-11-27 20:00:00'),
(333, '137-p94i', 3, '137-h5py-11-2024', 137, 'Fera', 'Basic 1 (10 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2024-11-01 05:15:33', '2024-11-01 05:15:33', 0, NULL, NULL, '2024-11-28 20:00:00'),
(334, '1-qtcg', 1, '1-mrgi-10-2024', 1, '100210121@subhan', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-03 01:23:27', '2024-09-30 08:58:49', '2024-10-03 01:23:27', 0, NULL, 0, '2024-11-23 03:00:00'),
(335, '1-qtcg', 1, '1-mrgi-10-2024', 1, '100210121@subhan', 'Paket 0.1 (7Mbps)', 100000, 'Admin', 'LS', 'Dian Ardiansyah', '2024-10-03 01:23:27', '2024-09-30 08:58:49', '2024-10-03 01:23:27', 0, NULL, 15, '2024-11-23 03:00:00'),
(336, '47-hivz', 1, '47-p4em-10-2024', 47, '150231029@mulya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'BL', 'Dian Ardiansyah', '2024-10-17 23:19:51', '2024-09-30 08:58:50', '2024-10-17 23:20:37', 0, '0000-00-00 00:00:00', NULL, '2024-11-23 03:00:00'),
(337, '47-hivz', 1, '47-p4em-10-2024', 47, '150231029@mulya', 'Paket 1 (10 Mbps)', 150000, 'Admin', 'BL', 'Dian Ardiansyah', '2024-10-17 23:19:51', '2024-09-30 08:58:50', '2024-10-17 23:20:37', 0, NULL, NULL, '2024-11-23 03:00:00');

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
(1, 'October 2024', '2024-09-30 08:58:49', '2024-09-30 08:58:49', '2024', 'October'),
(2, 'November 2024', '2024-11-01 05:13:55', '2024-11-01 05:13:55', '2024', 'November'),
(3, 'December 2024', '2024-12-01 05:13:55', '2024-12-01 05:13:55', '2024', 'December');

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
  `caller_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `internet_service`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `simpleque_id`, `simpleque_name`, `ip_static`, `mac_address`, `interface`, `arp_id`, `address_list_id`, `address`, `contact`, `disabled`, `activation`, `created_at`, `updated_at`, `deleted_at`, `primary_server`, `discount`, `customer_id`, `activation_date`, `caller_id`) VALUES
(1, 'Muh Subhan', '100210121-at-subhan-evqx', '$2y$10$11EOOju.2ubicB21M6OyQeHd.eYCN2/xZHTkmZR0gElDlgCeBiL9G', NULL, 2, 1, 'ppp', '*2', '100210121@subhan', '100210121', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '628983249628', 'false', 'true', '2024-09-29 15:55:48', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-001', '2021-01-20 03:00:00', NULL),
(2, 'Iis Sugiarti', '100210212-at-iissugiarti-gsvh', '$2y$10$vYaFzeJPZi3.oyStjK0SPOZnkOKzH9WHcmDLWzXzmf0MyJi9yIAua', NULL, 2, 1, 'ppp', '*3', '100210212@iissugiarti', '100210212', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:48', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-002', '2021-02-11 03:00:00', NULL),
(3, 'Asep Nasihin', '100210220-at-niaagustin-hjwc', '$2y$10$b4ycM5YZm3TWeLTDvFsvduN.L2DW0JNhYNQ2wLO9yaoQNfhso0eBa', NULL, 2, 1, 'ppp', '*4', '100210220@asep', '100210220', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:48', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-003', '2021-02-19 03:00:00', NULL),
(4, 'Pak Surya', '150210411-at-surya-fijt', '$2y$10$e2H6/W9HYHxUXPstfr.4w.p3z72k4yI137o/SWIDQZeTZYsxUL77G', NULL, 4, 1, 'ppp', '*5', '150210411@surya', '150210411', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:48', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-004', '2021-04-10 03:00:00', NULL),
(5, 'Koesnadi', '150210712-at-koesnadi-wcwq', '$2y$10$RomzLgOI5/9xYGUdQnmEQOZU.Wol2QYzUomfTO..Jr25/nLbiyRgO', NULL, 4, 1, 'ppp', '*6', '150210712@koesnadi', '150210712', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:48', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-005', '2021-07-11 03:00:00', NULL),
(6, 'Cucu Suryani', '150211002-at-cucu-cx5l', '$2y$10$2NnspMYyHs7AqyQiHiAv/Ousoowh98pbKUst57sUWR.8kRo.0r3O6', 'innayahazizah00@gmail.com', 4, 1, 'ppp', '*7', '150211002@cucu', '150211002', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 No. 37 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:49', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-006', '2021-10-01 03:00:00', NULL),
(7, 'Sumarni', '100220430-at-dinda-aunf', '$2y$10$rZRuJFOA2e0XH.lS.EXSceuR9fcahfpEkd4h.mRJvZPppFbLQH1KS', 'sum402791@gmail.com', 2, 1, 'ppp', '*8', '100220430@sumarni', '100220430', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:49', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-007', '2022-04-29 03:00:00', NULL),
(8, 'Teh Darsih', '100220918-at-darsih-esil', '$2y$10$JsVycMEZpicybGDKZmgxyuAeZPccRqPEyVyqnv3bWfLAW0NgN5ZIW', NULL, 2, 1, 'ppp', '*A', '100220918@darsih', '100220918', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:49', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-008', '2022-09-17 03:00:00', NULL),
(9, 'Pak Eko', '100221009-at-eko-tlec', '$2y$10$VC93qjb2uStD9QVNe2uSl.aZYLQu4VvpRzVS1o9E9/rvDxUZtDYYW', NULL, 2, 1, 'ppp', '*B', '100221009@eko', '100221009', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:49', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-009', '2022-10-08 03:00:00', NULL),
(10, 'Ali Akbar', '100221020-at-ustdali-24ay', '$2y$10$T7KK1mLY44Kms8/CPF0dFOLtuaFYvYrSUqvlz/Jm4JT/G9W62jrjC', NULL, 2, 1, 'ppp', '*D', '100221020@ustdali', '100221020', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:49', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-010', '2022-10-19 03:00:00', NULL),
(11, 'Rita Susanti', '100221029-at-rita-xrci', '$2y$10$TUepkxXfJZY7tOwpq8Jmvu7eBLlbaA4IrnffYJFlDlzAUSzjMkWzS', NULL, 2, 1, 'ppp', '*F', '100221029@rita', '100221029', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:50', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-011', '2022-10-28 03:00:00', NULL),
(12, 'Pak Suud', '150240118-at-suud-n4gt', '$2y$10$N2Ez7t.HXLE.ZuuM3yjINumdsKfpGI333v0E0vf5254JhrJTRD5lG', NULL, 4, 1, 'ppp', '*10', '150240118@suud', '150240118', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:50', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-012', '2024-01-17 03:00:00', NULL),
(13, 'Mamah Anggi', '100221107-at-anggi-5jgx', '$2y$10$R4sXPo7qm5wUmr4Xkc0Hfe.55OT1mpA2P6Xv7U/xBE4jGsEvYpmBW', NULL, 2, 1, 'ppp', '*12', '100221107@anggi', '100221107', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:50', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-013', '2022-11-06 03:00:00', NULL),
(14, 'Ai Solihah', '150221120-at-adhiga-dtrv', '$2y$10$Vl5pJniag./lYDHyRUzPZeIR0j163e68Nfr1d0lTu.aUs4yvZurVq', NULL, 4, 1, 'ppp', '*14', '150221120@adhiga', '150221120', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:50', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-014', '2022-11-19 03:00:00', NULL),
(15, 'Armiyanti', '200211114-at-armiyanti-6zyz', '$2y$10$l072gt6yQ9qHlmRRllm9iOoYLe/9Lh1wLqbEsuqkV5MylbUToR0.m', NULL, 5, 1, 'ppp', '*15', '200211114@armiyanti', '200211114', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:50', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-015', '2021-11-13 03:00:00', NULL),
(16, 'Riska Azizah', '100230102-at-rdcell-yk5h', '$2y$10$wCAvavoTUm4SVOTpUe0ZweHuS3XFPthkwly8SXRMZaCF9wO7TCb.q', NULL, 2, 1, 'ppp', '*18', '100230102@rizka', '100230102', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Griya Asri Blok K7 No. 7 RT 010/011 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:51', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-016', '2023-01-01 03:00:00', NULL),
(17, 'Azriel', '150230208-at-azril-ek7n', '$2y$10$85eFjCPWqfn7Nokzp/wua.l7Q93FKpbTdQ7QdZljtcOF4Xp1I.F6.', NULL, 4, 1, 'ppp', '*1A', '150230208@azril', '150230208', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:51', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-017', '2023-02-07 03:00:00', NULL),
(18, 'Iip', '150230402-at-iip-ijky', '$2y$10$85gBTUGVRMAQuXKOyn4srOzz0Ab9viG87sjE3C1/WR8tjf0LC4VgK', NULL, 4, 1, 'ppp', '*1B', '150230402@iip', '150230402', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:51', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-018', '2023-04-01 03:00:00', NULL),
(19, 'Ahmad Saepuloh', '150230408-at-ahmadraja-p5t1', '$2y$10$dLvva7iTiueomOstGDkSaePyBNhGoD0fCkkhEXH/XqHLg5VKrtOqC', 'ahmadsae13@gmail.com', 4, 1, 'ppp', '*1C', '150230408@ahmadraja', '150230408', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:51', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-019', '2023-04-07 03:00:00', NULL),
(20, 'Pak Lili', '150230503-at-lamjaya-7cfl', '$2y$10$Ibtu3T9NpzTCFrHYM4s0ReB4/.FkHPqRMPc2AOXrTfZqWuO0BXG8i', NULL, 4, 1, 'ppp', '*1E', '150230503@lamjaya', '150230503', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:51', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-020', '2023-05-02 03:00:00', NULL),
(21, 'Ibu Ayu', '150230717-at-ibuayu-3mqu', '$2y$10$/WFlOfeT6zJIliuX0OFQleR.MoyRzsqUk1.e.xtL0bkcCDWW.c1Q.', NULL, 4, 1, 'ppp', '*20', '150230717@ibuayu', '150230717', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:52', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-021', '2023-07-16 03:00:00', NULL),
(22, 'Ratih Aisyah', '100230729-at-jojo-0bau', '$2y$10$OZdoJ9pnDlnI3.yrHM5iD.5DOLR1LXimifuGiR2gdaQA9W8gwqTOq', NULL, 2, 1, 'ppp', '*22', '100230729@jojo', '100230729', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Veteran RT 003/002 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:52', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-022', '2023-07-28 03:00:00', NULL),
(23, 'AA Ramli', '150230804-at-rafka-ee5h', '$2y$10$xYLrRSRSUltqiE6Hgs8Z6en7fq16oJjix.3C9GSE7fEFEl0Kt77hC', NULL, 4, 1, 'ppp', '*23', '150230804@rafka', '150230804', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:52', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-023', '2023-08-03 03:00:00', NULL),
(24, 'Neni Nurhaeni', '150231101-at-neni-irxa', '$2y$10$GXH1GwFxXKW79sX2S0Nh1.KPlvBElBBZyFYexe/H60/1WWIMrFBvu', 'neninurhaeni1990@gmail.com', 4, 1, 'ppp', '*2A', '150231101@neni', '150231101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:52', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-024', '2023-10-31 03:00:00', NULL),
(25, 'Etik Aprilia Ratna Sari', '150231115-at-heriyanto-4rwf', '$2y$10$hSS2kKR3SdvIT9Z2IkRMfe7tcQJPMsev5YndP8NQR.p.7hLkhVq42', 'etikaprilia8@gmail.com', 4, 1, 'ppp', '*2C', '150231115@heriyanto', '150231115', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:52', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-025', '2023-11-14 03:00:00', NULL),
(26, 'Narsiah', '075230101-at-meisya-kqgp', '$2y$10$A0J2OD9jcrwpJ7omjFyjje/WbxQlwCtWYNm5nyEypuGjk38WfLsny', 'narsiiahnay@gmail.com', 2, 1, 'ppp', '*2D', '075230101@meisya', '075230101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:52', '2024-11-15 09:28:49', NULL, NULL, 25.00, 'SNF01-026', '2022-12-31 03:00:00', NULL),
(27, 'Novi Eka Purwaningsih', '100231205-at-novi-qoy2', '$2y$10$zkcDZQ5NhzP3K9Cki9cisuT/2rFf2B8NGYPs0bvsyiFzz/0XloS3q', NULL, 2, 1, 'ppp', '*2E', '100231205@novi', '100231205', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:53', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-027', '2023-12-04 03:00:00', NULL),
(28, 'Hasma Wati', '100231210-at-hasma-dmlc', '$2y$10$0JrkINervzXjGBFMLYDRZ.pMhrMFP3pFz1sXAfH5wq//SVoIOb7Yi', NULL, 2, 1, 'ppp', '*2F', '100231210@hasma', '100231210', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:53', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-028', '2023-12-09 03:00:00', NULL),
(29, 'Euis Ratnasari', '150231216-at-euis-nbhl', '$2y$10$Kx4uTvk4BAPBAWPdTEmWj.bCQOKLIz2h3Z.bYgpu1wOxkQJQhJcee', 'euisratnasari@gmail.com', 4, 1, 'ppp', '*30', '150231216@euis', '150231216', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:53', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-029', '2023-12-15 03:00:00', NULL),
(30, 'Adi Amoy', '100231217-at-amoy-smqx', '$2y$10$m2965pW/9cPJMi4OI1fLWukEOG54h3UgLfsflWIGnDDvybpAEdJ.W', NULL, 2, 1, 'ppp', '*31', '100231217@amoy', '100231217', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:53', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-030', '2023-12-16 03:00:00', NULL),
(31, 'A Zaky Alattas', '150240211-at-pakzaky-xwld', '$2y$10$JHIE7fRFx4fNYvT3TNalOuSgQd8oMvGEGNltalWVaJC7hgs0aRfua', NULL, 4, 1, 'ppp', '*3D', '150240211@pakzaky', '150240211', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:53', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-031', '2024-02-23 03:00:00', NULL),
(32, 'Teh Ai', '100240219-at-tehai-posk', '$2y$10$RQDYsMUunaPZrlizveJyF.ND6BFScaya8PenyQUkFA/xxYurqviAu', NULL, 2, 1, 'ppp', '*42', '100240219@tehai', '100240219', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:54', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-032', '2024-02-18 03:00:00', NULL),
(33, 'Pak Slamet', '150240311-at-slamet-numk', '$2y$10$6UT81J/qEug2tuiBDJwMwOty0bxS53nGXzl.lbYRHhsTqdBpC53A6', NULL, 4, 1, 'ppp', '*4F', '150240311@slamet', '150240311', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT 009/007 Kelurahan Ciseureuh', '', 'false', 'true', '2024-09-29 15:55:54', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-033', '2024-03-10 03:00:00', NULL),
(34, 'Debbie Febriana', '170231220-at-iburuli-wldt', '$2y$10$KtfMy2hf511pH7wGHDKQfuFwyBjdTcbTmYk3enq3qjUDbndZ6pOj2', 'fdebi707@gmail.com', 4, 1, 'ppp', '*51', '150231220@iburuli', '150231220', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:54', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-034', '2023-12-19 03:00:00', NULL),
(35, 'Hafis', '100221019-at-hafis-zgcg', '$2y$10$WYeq3GAJj64RmP.O0cK8IOAm7Io7e3vjYzu7RVd5S5BMrfTNk/Fr2', NULL, 2, 1, 'ppp', '*61', '100221019@hafis', '100221019', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:54', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-035', '2022-10-18 03:00:00', NULL),
(36, 'Triyanti', '200240417-at-yanti-bmum', '$2y$10$aGK1z3uCDe52sstaCjVGtu3ldkolqxiL4bbxM/7xqkWkMB0RMEwaC', 'zakipwk2144@gmail.com', 5, 1, 'ppp', '*65', '200240417@yanti', '200240417', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gg. Beringin RT 077/008 Nagrikaler Purwakarta', '', 'false', 'true', '2024-09-29 15:55:54', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-036', '2024-04-16 03:00:00', NULL),
(38, 'Yuan Firmansyah', '150240125-at-warungbali-haqj', '$2y$10$gRxBjL9tLQfJwYUwHTgO0O5GhqVc7DcR/rHgusO8FGL4YliAY8ega', NULL, 4, 1, 'ppp', '*6E', '150240125@warungbali', '150240125', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:55', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-038', '2024-01-24 03:00:00', NULL),
(39, 'Dede Suherman', '100210120-at-hexsa-1dnj', '$2y$10$pwvlYfcfOYA6lCSiTKW3te5SeB369HBm7J2F2H72931SpcpOcCTPe', 'djhonexsekutipmuda41@gmail.com', 2, 1, 'ppp', '*70', '100210120@hexsa', '100210120', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:55', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-039', '2021-01-19 03:00:00', NULL),
(40, 'Ida Siti', '100240501-at-siti-vsgv', '$2y$10$q.E8uVZhjEIgyegs/EMMq.K2/l4DYlI2vsiGVqMvlPM/ZtgHlnhdi', NULL, 2, 1, 'ppp', '*71', '100240501@siti', '100240501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:55', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-040', '2024-04-30 03:00:00', NULL),
(41, 'Wandi Suhendi', '100231112-at-wandi-becg', '$2y$10$QGs0JyG3YMJqJ1j.i7VvEe.0yjBadLLT78Zjh9cnUQvfC11Wcpqse', NULL, 2, 1, 'ppp', '*72', '100231112@wandi', '100231112', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:55', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-041', '2023-11-11 03:00:00', NULL),
(42, 'Muhammad Fahrizal', '100240503-at-rizal-a8vw', '$2y$10$igl4UtY5f8OqNl4jA5wpWuC.BkbRO86KfN.OVYaR9GbaYAzU5ypcO', 'muhammadfahrijal707@gmail.com', 2, 1, 'ppp', '*75', '100240503@fahrizal', '100240503', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:55', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-042', '2024-05-02 03:00:00', NULL),
(43, 'Edi Suswanto', '150240510-at-pakedi-d1ew', '$2y$10$C4PulOYkH06Ys7X5MX6H3u6nhwPNu9oofRK6PiSlpElA8HHXmEV5K', NULL, 4, 1, 'ppp', '*76', '150240510@pakedi', '150240510', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Kelurahan Ciseureuh', '', 'false', 'true', '2024-09-29 15:55:56', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-043', '2024-09-29 15:55:47', NULL),
(44, 'Tri Nurhidayati', '150221217-at-furqon-59at', '$2y$10$qqoHq3wBaArJBbUVvKBzX.7rI65vKh0jI5RUxtFrbHxT8WfE.FXRK', 'trinur0501@gmail.com', 4, 1, 'ppp', '*78', '150221217@furqon', '150221217', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:56', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-044', '2022-12-16 03:00:00', NULL),
(45, 'Pak Aam', '150221106-at-aam-skhy', '$2y$10$ueXEO54WLoeOWhVihXPiRe5LrNudhEK//X4zjZcvbZQ2FiLZcUQ5.', NULL, 4, 1, 'ppp', '*79', '150221106@aam', '150221106', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:56', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-045', '2022-11-05 03:00:00', NULL),
(46, 'Rohani Rusmiati', '150210312-at-sikefi-x5vs', '$2y$10$pfFhyVIg7AX9q/sjIiLnBepiSV2QWlIejOiT/wdNxyIjm13FsbGuG', 'kebulisikefipurwakarta@gmail.com', 4, 1, 'ppp', '*7A', '150210312@sikefi', '150210312', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:56', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-046', '2021-03-11 03:00:00', NULL),
(47, 'Mulyana', '150231029-at-mulya-fvgl', '$2y$10$StZ0gNA3sPfGhNx6kwmQg.vaSFZqDeFJL66/jAn0LLKjsidCaNFf6', 'd_ardi@ymail.com', 4, 1, 'ppp', '*7C', '150231029@mulya', '150231029', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '08983249628', 'false', 'true', '2024-09-29 15:55:56', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-047', '2023-10-28 03:00:00', NULL),
(48, 'Sri Sulastri', '150240710-at-sulastri-mfvp', '$2y$10$HsnDqCc6SqSJj8oA.3hIC.QQZOP1zh21CqegXROje.R7KD8SpamAS', NULL, 4, 1, 'ppp', '*89', '150240710@sulastri', '150240710', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:57', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-048', '2024-07-09 03:00:00', NULL),
(49, 'Mie Ayam & Geprek', '150240621-at-miayamgeprek-7lfy', '$2y$10$yAwqdWX30bbOFPIMo34EIeQuI6CBnIjKDnlKKNL8lNIxKpolDWRRe', NULL, 4, 1, 'ppp', '*8B', '150240621@miayamgeprek', '150240621', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:57', '2024-11-15 09:28:49', NULL, NULL, 50.00, 'SNF01-049', '2024-06-20 03:00:00', NULL),
(50, 'Ferry Kurniawan', '150230725-at-ustdfery-sxed', '$2y$10$T6AAoPBPu9psOtR.8fLn4.8r44ywcMQvzuiKqzdfop7KX.S9uxaE.', NULL, 4, 1, 'ppp', '*8C', '150230725@ustdfery', '150230725', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:57', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-050', '2023-07-24 03:00:00', NULL),
(51, 'Nok Reni', '150240711-at-tehenok-k0f3', '$2y$10$7/keG7v/z7zuW0JKCYWM1.1/AyktC.a8ZnXyklyI4p6XTm/BcyVLu', NULL, 4, 1, 'ppp', '*8D', '150240711@tehenok', '150240711', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:57', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-051', '2024-07-10 03:00:00', NULL),
(52, 'Ahmad Sugianto', '150240725-at-ahmad-ybf5', '$2y$10$w7EuRmCTmE6J9VgfRUwd8OsAUAgfMkzFqwBqcUX6/b4Z6kxvn.r8e', NULL, 4, 1, 'ppp', '*93', '150240725@ahmad', '150240725', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:57', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-052', '2024-07-24 03:00:00', NULL),
(53, 'Maharani Widyaningrum', '150230913-at-maharani-twro', '$2y$10$c2kg2MYWp8Yu5cRnfcKG0uAeqJoxwZMi61pZi4kFaMnn/WsF5jnbm', NULL, 4, 1, 'ppp', '*96', '150230913@maharani', '150230913', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:58', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-053', '2025-09-12 03:00:00', NULL),
(54, 'Linda', '120240310-at-linda-xfw7', '$2y$10$h4oUcZ.jxvVV6WLl76tGKuNC8ttA9ICiji4G8gs.Xxusk9xjMOYIC', NULL, 3, 1, 'ppp', '*97', '120240310@linda', '120240310', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:58', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-054', '2024-03-09 03:00:00', NULL),
(55, 'Agista Utari', '100240225-at-agis-coa7', '$2y$10$afF.elelamWV5JfAXz1/ae8ebnS/re1ZhIcGtyEkF35AykhF1tKVK', 'utariagista@gmail.com', 2, 1, 'ppp', '*98', '100240225@agis', '100240225', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:58', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-055', '2024-02-24 03:00:00', NULL),
(56, 'Agustian Setiadi', '150240807-at-nayunda-vahg', '$2y$10$j3zb9D2SAx4EstIrFwRVFetKDGW4TPut8uuYtzYVIWc.fBzRKWvaW', 'agustiansetiadi703@gmail.com', 4, 1, 'ppp', '*99', '150240807@nayunda', '150240807', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:58', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-056', '2024-08-06 03:00:00', NULL),
(57, 'Pak Iyan', '200230910-at-iyan-nsls', '$2y$10$y/fzHfMTsKJJDQW13O5QdOn0S294UuPDGDvVV/Cqtl3PqOBhcF3ym', NULL, 5, 1, 'ppp', '*9A', '200230910@iyan', '200230910', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:58', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-057', '2023-09-09 03:00:00', NULL),
(58, 'Nur Fakriah Putri', '150240811-at-nurfakriah-wkj0', '$2y$10$nfEMj3YP8HGZWVmWwj4pnO.X1ZFidQz9LeF2n7qU4cuHiugkz6puC', 'nurfakriahp@gmail.com', 4, 1, 'ppp', '*9B', '150240811@nurfakriah', '150240811', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:59', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-058', '2024-08-10 03:00:00', NULL),
(59, 'Amni Fitria', '120230419-at-fitria-czgx', '$2y$10$pHzMifDZ31DqXDFGpUQpbuQPT9zhwIr.1Hgb5OflOECaL1fOJEhpS', 'amnifitria778@gmail.com', 3, 1, 'ppp', '*9C', '120230419@fitria', '120230419', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:59', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-059', '2023-04-18 03:00:00', NULL),
(60, 'Pak Jepri', '150240612-at-jepri-np2z', '$2y$10$2Kg/JofVjcaYfFpanGDm7ey8YxBQ1x54D8FFJrbzOzj5twRYMjxim', NULL, 4, 1, 'ppp', '*9D', '150240612@jepri', '150240612', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:59', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-060', '2024-06-11 03:00:00', NULL),
(61, 'Bu Ani', '150240817-at-ibuani-rlmf', '$2y$10$U4Sqhf2I5I79OvHctXDrSOG9zebM0XOKR./1ZNcR.p8yDeDpV7tyG', NULL, 4, 1, 'ppp', '*9F', '150240817@ibuani', '150240817', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:59', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-061', '2024-08-16 03:00:00', NULL),
(62, 'Yeyet Abdulrachman', '250230601-at-abdulrahman-pvrj', '$2y$10$wTafupVFxz5xsPNj1JyJ.OmrbsJDJsFZssGgGkXrGXLEsbl2mnywC', NULL, 6, 1, 'ppp', '*A0', '250230601@abdulrahman', '250230601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:55:59', '2024-11-15 09:28:49', NULL, NULL, 20.00, 'SNF01-062', '2023-05-31 03:00:00', NULL),
(63, 'Pak Dendi', '150240824-at-dendi-86fd', '$2y$10$cjyyQ7xQXeiITQe1LcXlze0Jaq6ZgFbPu.DTJgJrXiqlhZnhuIlwa', NULL, 4, 1, 'ppp', '*A2', '150240824@dendi', '150240824', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:00', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-063', '2024-08-23 03:00:00', NULL),
(64, 'Marya Ningsih', '100240901-at-bunehe-zqpe', '$2y$10$Aa5gGs/UAuUM2YPZYYdsweq0qRwSsZqEFGW1eEgbihwQpW/cIAL2e', 'maryaningsih@gmail.com', 2, 1, 'ppp', '*A4', '100240901@ningsih', '100240901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:00', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-064', '2024-08-31 03:00:00', NULL),
(65, 'Pak Kurdi', '150240901-at-pakkurdi-glwd', '$2y$10$OdrngqvUr7pmpiEh1qb/c.LQXGiwWr00CsEDP.LK1G9RJOVI1qeoa', NULL, 4, 1, 'ppp', '*A5', '150240901@pakkurdi', '150240901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:00', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-065', '2024-08-31 03:00:00', NULL),
(66, 'Kodrat (GOR ADHIGA)', '150240305-at-goradhigha-dbai', '$2y$10$BNAI6fljFbwxR.FZlqkLnuderFWkQp9Lh1RrrYc3CY6GmaSHtbb2y', NULL, 4, 1, 'ppp', '*A6', '150240305@goradhigha', '150240305', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT 009/007 Kelurahan Ciseureuh', '', 'false', 'true', '2024-09-29 15:56:00', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-066', '2024-03-04 03:00:00', NULL),
(67, 'Lulu Andriani', '150240908-at-lulu-s8ho', '$2y$10$xRkYdz0D2w.RoffP2it7WO4ritKEWwUtlgWuJlp0JIDJ58XgtxI5a', NULL, 4, 1, 'ppp', '*A7', '150240908@lulu', '150240908', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:00', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-067', '2024-09-07 03:00:00', NULL),
(68, 'Ma Aisah', '150240908-at-aisah-13j1', '$2y$10$4uqbm.E521ybA29HyjEmnOCUzY/6gRMM4pxLuBrPqpbVNFKLguFHy', NULL, 4, 1, 'ppp', '*A8', '150240908@aisah', '150240908', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:01', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-068', '2024-09-07 03:00:00', NULL),
(69, 'Alya Iklima Hidayah', '150240908-at-alhikam-c8c2', '$2y$10$HAr5GZgSNxXuui6aibSq2O/eM6zHWk4A4gEiLHqB2dr1TxUZb1ou6', 'alyaiklima@gmail.com', 4, 1, 'ppp', '*A9', '150240908@alhikam', '150240908', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Panorama Indah B2-4 RT 013/013 Purwakarta', '', 'false', 'true', '2024-09-29 15:56:01', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-069', '2024-09-07 03:00:00', NULL),
(70, 'Pak Kurdi 2', '150240912-at-pakkurdi-3euz', '$2y$10$oPigjP2IgIxbWkch5zxTou5QZ7HxMt0mjXPmk9LEi1vRMT4W5VfDy', NULL, 4, 1, 'ppp', '*AC', '150240912@pakkurdi', '150240912', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:01', '2024-11-15 09:28:49', NULL, NULL, 33.00, 'SNF01-070', '2024-09-11 03:00:00', NULL),
(71, 'Teh Enen', '100240917-at-enen-u7xn', '$2y$10$IojVi5rPNe0AGgoCRU1rme1zaqcca5EmDNCSjgu0IJ22VqeQLfXF6', NULL, 2, 1, 'ppp', '*AD', '100240917@enen', '100240917', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:01', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-071', '2024-10-05 03:00:00', NULL),
(72, 'Rina Konter BMCell', '100240715-at-bmcell-xe7d', '$2y$10$z466HiVVDZ4uvZlATioSBuQooJahSsMJZB15bec2gfq9bY1JsOzfS', NULL, 2, 1, 'ppp', '*AE', '100240715@bmcell', '100240715', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:01', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-072', '2024-07-14 03:00:00', NULL),
(73, 'Asep Topik Hidayat', '100240922-at-asep-hw73', '$2y$10$wB1RxbDa5RO6Ywh2fjmuFuGJC2aOKEPT5Bkr6v20lujh/LB8n4NKu', NULL, 2, 1, 'ppp', '*AF', '100240922@asep', '100240922', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:01', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-073', '2024-09-21 03:00:00', NULL),
(74, 'Egy Laksma Suandi', '150240924-at-egy-zndc', '$2y$10$ydDsC1cUoXRphkUkk9t74eNil.z8pphCf1nH.qJtNIZ/JXy131xk6', NULL, 4, 1, 'ppp', '*B0', '150240924@egy', '150240924', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-29 15:56:02', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-074', '2024-09-23 03:00:00', NULL),
(75, 'H. M Abu Rasyid. AS. SH', '300230902-at-cahayaqolbu-xkqe', '$2y$10$lnSw0HVTrpC.p4vA5GSPG.s0gubinyGAxRbjZDU7yagmuoufbxFIO', NULL, 5, 1, 'ppp', '*6D', '200241101@cahayaqolbu', '200241101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '', 'false', 'true', '2024-09-30 13:13:45', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF01-075', '2023-09-01 03:00:00', NULL),
(77, 'Nazri Nasier', 'nazri-14yg4l', '$2y$10$0Ge3w0n6yb8y7ojVRdt5gOs5W/9ypkwF7jZ7thSQ9IqV81Hgjs7g2', 'nasiernazri@gmail.com', 4, 1, 'ppp', '*B5', '150241004@nazri', '150241004', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-10-03 15:20:17', '2024-11-15 09:28:49', NULL, 1, 0.00, 'SNF01-077', '2024-10-03 15:21:33', NULL),
(78, 'Moh. Gian Firtyanza', 'moh-qdm7pn', '$2y$10$ATMOpS2HTifv8Ra3uZR8neIZsdF20pk3kIn5UifjrIjwWnNkfQvpa', NULL, 4, 1, 'ppp', '*B7', '150241009@gian', '150241009', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PerumGriya Asri Blok D2 No. 15 Ciseureuh Purwakarta', '', 'false', 'true', '2024-10-08 23:04:42', '2024-11-15 09:28:49', NULL, 1, 0.00, 'SNF01-078', '2024-10-08 23:04:59', NULL),
(79, 'Intan Ayu Sri Wahyuni', 'intan-vmvffx', '$2y$10$qBQNXfdHxeXUf9T.R8GE/.c6/aHPqMpfz2ocpwzurb0ULOww5NEOC', 'can.cc660@gmail.com', 4, 1, 'ppp', '*B8', '150241017@intan', '150241017', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT 009/007 Ciseureuh Purwakarta', '', 'false', 'true', '2024-10-16 15:25:35', '2024-11-15 09:28:49', NULL, 1, 0.00, 'SNF01-079', '2024-10-16 17:45:35', NULL),
(80, 'Nurhayati', 'nurhayati-o6xht7', '$2y$10$A7K./Yu5M5j3OkkHXlJ0ROGZcwBtj6s5clG6D955m7aMxJoPO0Vby', 'nurhayati@shanetfiber.my.id', 4, 1, 'ppp', '*B9', '150241020@nurhayati', '150241020', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '', 'false', 'true', '2024-10-19 22:25:50', '2024-11-15 09:28:49', NULL, 1, 0.00, 'SNF01-080', '2024-10-19 22:34:23', NULL),
(81, 'Irvan Mohammad Regent', 'irvan-rbchni', '$2y$10$rxBs1QY7fuS5S/x5lak2wegdZtEXLH06oKD3.jhqIVVXAnCJAU.pe', 'irvanregent@gmail.com', 4, 1, 'ppp', '*BA', '150241027@irvan', '150241027', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Griya Asri Blok K1 No. 10 Ciseureuh Purwakarta', '', 'false', 'true', '2024-10-26 12:52:51', '2024-11-15 09:28:49', NULL, 1, 0.00, 'SNF01-081', '2024-10-26 13:01:54', NULL),
(82, 'Nuryani', '125230606-at-yance-jhtl', '$2y$10$MvWFfR4J/gwK04bUL/oY3O4hJ4WJw3HWccZlcxww0ZnjBBGLHMO.G', NULL, 21, 18, 'ppp', '*2', '125230606@yance', '125230606', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:10:52', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-082', '2023-06-04 20:00:00', NULL),
(83, 'Anggi', '200240420-at-anggi-5rzv', '$2y$10$Lmcdw006l45RqoLsQZ3cZ.SEGYbXHJ.CguFqT2Os.CKpZQQGmGybW', 'randika.nurfaizi470@gmail.com', 23, 18, 'ppp', '*3', '200240420@anggi', '200240420', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '628983249628', 'false', 'true', '2024-10-27 23:10:53', '2024-11-29 07:09:33', NULL, NULL, 0.00, 'SNF18-083', '2024-04-18 20:00:00', NULL),
(85, 'Siti Mariam', '125240427-at-amar-qnsq', '$2y$10$D4oMKh4yq.DXLFEkXXSJyO2.zmow/jF0GcApO3GTI6iih3cR6/nrm', NULL, 21, 18, 'ppp', '*9', '125240427@amar', '125240427', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:10:55', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-085', '2024-04-25 20:00:00', NULL),
(86, 'Desi Nurdianti', '125240427-at-salim-zw1g', '$2y$10$Xs5ERAiUciuBS.nrMOAppOaVjtLM25LD0t4An7lvaVf9jubNY0P9y', NULL, 21, 18, 'ppp', '*A', '125240427@salim', '125240427', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:10:56', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-086', '2024-04-25 20:00:00', NULL),
(87, 'Iis Ismawati', '125240427-at-iis-ptfu', '$2y$10$s3DUDonAw7Zx0qnu3WCq5Oirmopx6rnbXrMbnJs9XAb6vh78LhE1K', NULL, 21, 18, 'ppp', '*B', '125240427@iis', '125240427', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:10:56', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-087', '2024-04-25 20:00:00', NULL),
(88, 'Imar Sulaeman', '125240428-at-sulaeman-tckb', '$2y$10$aiBoTEdg5Q7hElazd2kpvO5y5CTxqmuszNaBwemyrXt.ywl2j0lfq', 'imarsulaeman04@gmail.com', 21, 18, 'ppp', '*C', '125240428@sulaeman', '125240428', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:10:57', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-088', '2024-04-26 20:00:00', NULL),
(89, 'Adis', '125240428-at-adis-7mbq', '$2y$10$3gQ6VsxCSyZIlJLo7hctdu4b1z3.jVq4MNt8y7ZyLodatU1jaVA4a', NULL, 21, 18, 'ppp', '*D', '125240428@adis', '125240428', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:10:58', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-089', '2024-04-26 20:00:00', NULL),
(90, 'Satim Silegar', '125240428-at-paksatim-fhoc', '$2y$10$Y6FEqMyFK6cOc.7jOvygkOe9uEfdUZDdEVO15KPJNeLc3FHhlFF6W', 'susisusilaw1410@gmail.com', 21, 18, 'ppp', '*E', '125240428@paksatim', '125240428', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:10:59', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-090', '2024-04-26 20:00:00', NULL),
(91, 'Heni', '125240430-at-heni-fdu7', '$2y$10$09VrjCiDq6UVV8pA7j0d/u3L3gVc3JTx5q5ahAS6h0yrGjPebLyAO', NULL, 21, 18, 'ppp', '*F', '125240430@heni', '125240430', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:00', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-091', '2024-04-28 20:00:00', NULL),
(92, 'Juju Juarsih', '125240501-at-juju-vrc5', '$2y$10$ImC3CShS7WSWkUdMDGLsZ.H2SuYnW5mdFjN2RIUgY1mzGd/moPwFy', NULL, 21, 18, 'ppp', '*10', '125240501@juju', '125240501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:01', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-092', '2024-04-29 20:00:00', NULL),
(93, 'Deni', '125240501-at-deni-pn9s', '$2y$10$gh92qR0w0zVWnuuXNTbbMuOGpQ7rkYgFCsqAv06n5uo45aGgKdQFG', NULL, 21, 18, 'ppp', '*11', '125240501@deni', '125240501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:02', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-093', '2024-05-30 20:00:00', NULL),
(94, 'Julian Ratu Malik Kusum', '125240504-at-ratu-rj1m', '$2y$10$Un/6xmcZzOW3.dMbA7eBB.1LDd4w9j6Nu4u8h6Zt3AwM4bzWYezdm', NULL, 21, 18, 'ppp', '*13', '125240504@ratu', '125240504', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:03', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-094', '2024-05-02 20:00:00', NULL),
(95, 'Rosidah', '125240420-at-rosidah-goas', '$2y$10$L/t8dn/JvyAA91dQxH.fbenCI7l1NbzNhrZE3gchtHceWLbyRO46K', NULL, 21, 18, 'ppp', '*14', '125240420@rosidah', '125240420', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:04', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-095', '2024-04-18 20:00:00', NULL),
(96, 'Cici', '125240509-at-cici-urae', '$2y$10$Yk7SxBqeDCSuSorsw0PfpOsqGpSQkULn4BoSjZTm/CsDjz9LJf9yG', NULL, 21, 18, 'ppp', '*15', '125240509@cici', '125240509', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:05', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-096', '2024-05-07 20:00:00', NULL),
(97, 'Pepen', '125240510-at-pepen-yog8', '$2y$10$UeL2OwJpKT8YLRsD3uqP.u6o9747gPeIZ.ci0OhJDRdNTiTxsjpk2', NULL, 21, 18, 'ppp', '*16', '125240510@pepen', '125240510', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:06', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-097', '2024-05-08 20:00:00', NULL),
(98, 'Wayanto', '125240510-at-wayanto-rtx1', '$2y$10$C7U78dsQgfBMxrT9yOblYudkB.IX.D9SB7ASeAmddG5cl7hhSzWMO', NULL, 21, 18, 'ppp', '*17', '125240510@wayanto', '125240510', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:07', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-098', '2024-05-08 20:00:00', NULL),
(99, 'Miung', '125240514-at-miung-xzds', '$2y$10$lWix3uKL/di3gKOn2YNUR.MhVWdfxxa8uDylMWiRpt.U3SKvAg.tO', NULL, 21, 18, 'ppp', '*18', '125240514@miung', '125240514', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:08', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-099', '2024-05-12 20:00:00', NULL),
(100, 'Dadan Hamdani', '125240514-at-rtahong-t5gm', '$2y$10$omiJROYGdlaI0Q6glB9V1ufHH.vBIvgOAZvYJPOiVOaYdFJrPx1nW', NULL, 21, 18, 'ppp', '*19', '125240514@rtahong', '125240514', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:09', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-100', '2024-05-12 20:00:00', NULL),
(101, 'Sule', '125240514-at-sule-pglq', '$2y$10$2D9vBMc2ZTXRcwDV8aT2reflqgAKEvybzR/edslo4krmA372ovS8i', NULL, 21, 18, 'ppp', '*1A', '125240514@sule', '125240514', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:10', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-101', '2024-05-12 20:00:00', NULL),
(102, 'Heri Suandi', '125240514-at-heri-hujl', '$2y$10$Yec8xLYTtwG.VApc4abgv.nwOg8TAGvj9BTdCgduhdIuGCM5OYlBa', NULL, 21, 18, 'ppp', '*1B', '125240514@heri', '125240514', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:12', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-102', '2024-05-12 20:00:00', NULL),
(103, 'Juhdi', '125240501-at-zudenk-wz0w', '$2y$10$ImLfLg/Huo3aJPJLHKDE..IPiJ/rf4I8JhfWtqrnQRFqIP2EZnVie', 'juhdianjuhdi@gmail.com', 21, 18, 'ppp', '*1E', '125240501@zudenk', '125240501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:12', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-103', '2024-04-30 03:00:00', NULL),
(104, 'RT Oji', '150240515-at-rtoji-abjg', '$2y$10$5zikt3.kxMEaj.RUZ8h22udo9r/cyboV7Abzg7CIw1HWZ7mzYJDs6', NULL, 22, 18, 'ppp', '*1F', '150240515@rtoji', '150240515', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:14', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-104', '2024-05-13 20:00:00', NULL),
(105, 'Suntari', '150240518-at-suntari-ttzy', '$2y$10$Jzs/fK8DFVNAtqs3UW4Q3ufvuKosJJ0HYYlpAfh80L6Fa1DGENhIC', NULL, 22, 18, 'ppp', '*20', '150240518@suntari', '150240518', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:14', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-105', '2024-05-16 20:00:00', NULL),
(106, 'Iqbal Budi Prasetya', '100230304-at-iqbal-vcxz', '$2y$10$BlBvFlGJB/Dbk9QXfWvT3.YbL/ynHC7m/.tQNj8g4TT7AnY.gadbO', NULL, 21, 18, 'ppp', '*22', '100230304@iqbal', '100230304', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:15', '2024-11-15 09:28:49', NULL, NULL, 20.00, 'SNF18-106', '2023-03-02 20:00:00', NULL),
(107, 'Engko', '150240525-at-engko-c50l', '$2y$10$hAtl4qCuvraYouHw3t670.fA4HbW2Ew2G3RzYC5Nj2zhWvbxagngW', 'mmdrizky0905@gmail.com', 22, 18, 'ppp', '*23', '150240525@engko', '150240525', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:16', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-107', '2024-05-24 03:00:00', NULL),
(108, 'Arya Cahyana Putra', '150240601-at-arya-h7ho', '$2y$10$N6p4rDCL1wVtrHoUZE9/qeIIGfr7xLXtugTH3NzspTvKcm3ucInl6', NULL, 22, 18, 'ppp', '*24', '150240601@arya', '150240601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:17', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-108', '2024-05-30 20:00:00', NULL),
(109, 'Wida', '100230303-at-wida-luck', '$2y$10$k2uah.v88fVOO1fvfNU8sudYTNFOyUZuXi0AO8kzJORM.8ipWICjW', NULL, 21, 18, 'ppp', '*25', '100230303@wida', '100230303', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:18', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-109', '2023-03-01 20:00:00', NULL),
(110, 'H Imon', '150240608-at-himon-dxr1', '$2y$10$ZMVytVd4B2gjbpsIw73suuJrbsneG5R7fXCmha98twCDFyovx5mki', NULL, 22, 18, 'ppp', '*27', '150240608@h.imon', '150240608', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:11:19', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-110', '2024-06-06 20:00:00', NULL),
(111, 'Julianto', '150240612-at-julianto-cnrn', '$2y$10$7rgbzcMEdY4joxsFfyQTJeeSCvs0aORKWyatzHkS3wpCixrWsFUPa', NULL, 22, 18, 'ppp', '*28', '150240612@julianto', '150240612', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:11:20', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-111', '2024-06-10 20:00:00', NULL),
(112, 'Okis', '150240420-at-okis-kzjs', '$2y$10$yvKXHl5tGWZHheUTsaTj0u/5hkZnaEjsPnoNj9a8OWIzt6xGgzbQe', NULL, 22, 18, 'ppp', '*29', '150240420@okis', '150240420', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:21', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-112', '2024-04-18 20:00:00', NULL),
(113, 'Neng Siti', '150240622-at-nengsiti-be5n', '$2y$10$YvpAg7fxTWJkI1hnrFhG0eDg8AGMDR4yCNNd0Re8ZDR8zGVEUeLLS', NULL, 22, 18, 'ppp', '*2A', '150240622@nengsiti', '150240622', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:11:23', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-113', '2024-06-20 20:00:00', NULL),
(114, 'Dedi Haerudi', '150240622-at-idoy-scot', '$2y$10$UPXRU7YFrVJVk/vBDxL/1.L2/gh997eX4WEkCdmkAbva7q1eYVVd6', NULL, 22, 18, 'ppp', '*2B', '150240622@idoy', '150240622', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 007/002 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:23', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-114', '2024-06-20 20:00:00', NULL),
(115, 'Amania Kusumah', '150240629-at-amania-wmj5', '$2y$10$HVS1RldUuC6i9tJiWNDMGuf.VLWNrmjLA14HkmccYkWSKn09iYDdm', NULL, 22, 18, 'ppp', '*2C', '150240629@amania', '150240629', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 008/002 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:24', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-115', '2024-06-27 20:00:00', NULL),
(116, 'Nurul Aeni', '150240629-at-nurul-6gzn', '$2y$10$s59vmia58OYz/Z8Wq3mtXeoemPKpceOoslRkP0aq5sNEQ9Svyh522', NULL, 22, 18, 'ppp', '*2D', '150240629@nurul', '150240629', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 008/002 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:25', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-116', '2024-06-27 20:00:00', NULL),
(117, 'Pak Ojak', '150240701-at-ojak-aumh', '$2y$10$1105asqVxOfsm9.t7ns4Hu4Kr4o3IByKTo2dqTJmn8.aTqcIdB.TS', NULL, 22, 18, 'ppp', '*2E', '150240701@ojak', '150240701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'kp', '', 'false', 'true', '2024-10-27 23:11:26', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-117', '2024-06-29 20:00:00', NULL),
(118, 'Redi Natisna', '200240629-at-redi-rpgp', '$2y$10$2Dv5ulws359arpmnF0BFJugDjDLbLLkfAZZuyuyZu6ENzvz1cDBuC', NULL, 23, 18, 'ppp', '*2F', '200240629@redi', '200240629', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 010/003 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:27', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-118', '2024-06-27 20:00:00', NULL),
(119, 'Wahyu Maulana', '150240630-at-wahyu-at06', '$2y$10$pe2TX7HgqRybHKY0hzW/s.vXk01lg1wL2gvsPV2DjvHnjfuscyLIe', NULL, 22, 18, 'ppp', '*30', '150240630@wahyu', '150240630', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '', 'false', 'true', '2024-10-27 23:11:28', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-119', '2024-06-28 20:00:00', NULL),
(120, 'Usman Abdul Sobari', '100231204-at-usman-t4v1', '$2y$10$ikD1T0.7NBCrI8QGe8vYieAjT6m9WqeYl.NFdCd70puZMqDRz6tU2', NULL, 21, 18, 'ppp', '*31', '100231204@usman', '100231204', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:29', '2024-11-15 09:28:49', NULL, NULL, 20.00, 'SNF18-120', '2023-12-02 20:00:00', NULL),
(121, 'Bajuri', '150240704-at-bajuri-1it3', '$2y$10$6YbtrNasTDhAY41M2yIqjudOP8BCNZT7Vww3IcTM7QU/CS3BS87TS', NULL, 22, 18, 'ppp', '*32', '150240704@bajuri', '150240704', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:11:30', '2024-11-16 23:37:27', NULL, NULL, 0.00, 'SNF18-121', '2024-07-02 20:00:00', NULL),
(123, 'Salmah Saleha', '150240708-at-salmah-n7wq', '$2y$10$YMyaDht6gzXXKieL1yx3AubFjOQQTeldcnXNnQQS8lgYkzGdLwmQS', NULL, 22, 18, 'ppp', '*34', '150240708@salmah', '150240708', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:32', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-123', '2024-07-06 20:00:00', NULL),
(124, 'Neneng Suratni', '150240828-at-neneng-l9b1', '$2y$10$2GcYXBZ4vxFiywFcxv0oauKBuUZgvHEbK6iiy9rmJBNVk2B1Ymaqu', 'nenengsuratni.99@gmail.com', 22, 18, 'ppp', '*3E', '150240828@neneng', '150240828', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '', 'false', 'true', '2024-10-27 23:11:33', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-124', '2024-08-26 20:00:00', NULL),
(125, 'Herman', '150240907-at-eman-fush', '$2y$10$mmyG831bpT.bEpTEqBmIbusScpGb3fy9uywGXf1WCC5X2Fv6QwdSy', NULL, 22, 18, 'ppp', '*3F', '150240907@eman', '150240907', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:35', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-125', '2024-09-05 20:00:00', NULL),
(126, 'Syarip', '150240912-at-syarip-2kig', '$2y$10$Oo1CIw7Rd2SzoJCn7pPDduSJ7zi3dl/ANweoc3OnzQ6JuFRheDzcG', NULL, 22, 18, 'ppp', '*40', '150240912@syarip', '150240912', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:11:35', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-126', '2024-09-10 20:00:00', NULL),
(127, 'Misbah', '150241004-at-misbah-pvfc', '$2y$10$T7FiVd/GmiNuVwF2WHJC0OXjN7PBkmxZ.cXkdX9IwrOW7gSMd1BZm', NULL, 22, 18, 'ppp', '*41', '150241004@misbah', '150241004', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:11:37', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-127', '2024-10-02 20:00:00', NULL),
(128, 'Jajang Salahudin', '150241006-at-jajang-85yh', '$2y$10$J7wJOYMyIWwiFHxeW7urI.FlYPt.GWuAgl6DBtT.f/EAxz/xrttmq', NULL, 22, 18, 'ppp', '*42', '150241006@jajang', '150241006', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 003/001 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:37', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-128', '2024-10-04 20:00:00', NULL),
(129, 'Dadi Kusnadi', '150241017-at-dadi-gtna', '$2y$10$SADSxToCkjU4DMCSlxlXreAyGkNyuYqkS0pgqZMaUsscPrcsXfr6S', NULL, 22, 18, 'ppp', '*43', '150241017@dadi', '150241017', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:11:39', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-129', '2024-10-15 20:00:00', NULL);
INSERT INTO `customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `internet_service`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `simpleque_id`, `simpleque_name`, `ip_static`, `mac_address`, `interface`, `arp_id`, `address_list_id`, `address`, `contact`, `disabled`, `activation`, `created_at`, `updated_at`, `deleted_at`, `primary_server`, `discount`, `customer_id`, `activation_date`, `caller_id`) VALUES
(130, 'Darma Wirawan', '150240713-at-darma-axvt', '$2y$10$DfK8CQZ7KWo9C6aNL3pd/uyDHbA/Sx6Dkq1wE6LZEVZVCyRegZZke', 'irawanindra1911@gmail.com', 22, 18, 'ppp', '*35', '150240713@darma', '150240713', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-27 23:15:32', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-130', '2024-07-11 20:00:00', NULL),
(131, 'Sulis Toko', '200240715-at-sulis-zofc', '$2y$10$D/A4UGXEU/E003BZxYuMMeGJ6CWKb.BhmuyAMunMLEOby4qRLDzi.', NULL, 23, 18, 'ppp', '*36', '200240715@sulis', '200240715', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:15:32', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-131', '2024-07-13 20:00:00', NULL),
(132, 'Pak Agus', '150240720-at-agus-qf9a', '$2y$10$0WJmwyH05xxyCd2vgQ6U0OzU8a7Du97yDEdnvocuE8ewAdG1jBuzS', NULL, 22, 18, 'ppp', '*37', '150240720@agus', '150240720', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:15:33', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-132', '2024-07-18 20:00:00', NULL),
(133, 'Sulis Rumah', '150240720-at-sulis-yyk7', '$2y$10$F1wJf7zHrVdt82ttT2XOa.yvaylfcTeOXFn9P.mevMDYvA8CVhnCO', NULL, 22, 18, 'ppp', '*39', '150240720@sulis', '150240720', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:15:34', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-133', '2024-07-18 20:00:00', NULL),
(134, 'Rika Rahmawati', '150240727-at-rika-qn5d', '$2y$10$WbqbDZEaEvALBTWz.BgJU.yuIFJAr4jINWdvuy.WNiRoIE4REXVZK', NULL, 22, 18, 'ppp', '*3A', '150240727@rika', '150240727', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', '', 'false', 'true', '2024-10-27 23:15:35', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-134', '2024-07-25 20:00:00', NULL),
(135, 'Yuyun', '150240815-at-yuyun-0sdu', '$2y$10$DpwmD7NkrHxzFJ5lqrN/neK6Dz7dDmXGT4Gzdmri3rLmq01LHDALq', NULL, 22, 18, 'ppp', '*3C', '150240815@yuyun', '150240815', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:15:36', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-135', '2024-08-13 20:00:00', NULL),
(136, 'Wahid', '150240825-at-wahid-jdwg', '$2y$10$GWMGIdBn1TtZkKTy6EL7sOKV9DFTQLjXytLelwvfQhFsMeZNAZZ/G', NULL, 22, 18, 'ppp', '*3D', '150240825@wahid', '150240825', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'false', 'true', '2024-10-27 23:15:37', '2024-11-15 09:28:49', NULL, NULL, 0.00, 'SNF18-136', '2024-08-23 20:00:00', NULL),
(137, 'Fera', 'fera-s1xwst', '$2y$10$M8PtAr95YZdWDkmWFs6Yle.Qq84/nZxTVTl962hObxi8hWlp7q1Ee', 'fera141120@gmail.com', 22, 18, 'ppp', '*45', '150241030@fera', '150241030', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '', 'false', 'true', '2024-10-29 11:12:30', '2024-11-15 09:28:49', NULL, 18, 0.00, 'SNF18-137', '2024-10-29 11:18:39', NULL),
(138, 'Robi Anggara', 'robi-zbdepz', '$2y$10$CUEP9duqVEOyWu5tjeq5g.8qsPZfv075kmj9BmeX95bbDgSXkdvCq', 'robi702.ra@gmail.com', 4, 1, 'ppp', '*BD', '150241030@robi', '150241030', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', '', 'false', 'true', '2024-10-29 13:14:39', '2024-11-15 09:28:49', NULL, 1, 0.00, 'SNF01-138', '2024-10-29 13:21:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_onts`
--

CREATE TABLE `customer_onts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `merk_ont` varchar(255) DEFAULT NULL,
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

INSERT INTO `customer_onts` (`id`, `customer_id`, `merk_ont`, `type_ont`, `username`, `password`, `port`, `mac_address`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:48', '2024-10-05 22:33:19'),
(2, 2, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:48', '2024-10-05 22:50:52'),
(3, 3, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:48', '2024-10-13 18:30:42'),
(4, 4, 'ZTE', 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:48', '2024-10-15 09:26:27'),
(5, 5, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:48', '2024-10-15 10:54:41'),
(6, 6, 'Tenda', NULL, 'admin', 'shanum250117', '80', NULL, NULL, '2024-09-29 15:55:49', '2024-09-30 18:35:40'),
(7, 7, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:49', '2024-10-13 13:46:50'),
(8, 8, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:49', '2024-09-29 15:55:49'),
(9, 9, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:49', '2024-10-14 23:23:23'),
(10, 10, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:49', '2024-10-08 16:15:45'),
(11, 11, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:50', '2024-10-14 23:24:33'),
(12, 12, 'TP-Link', NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:50', '2024-10-15 10:45:31'),
(13, 13, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:50', '2024-10-14 23:55:43'),
(14, 14, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:50', '2024-10-14 23:38:27'),
(15, 15, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:50', '2024-10-15 10:31:36'),
(16, 16, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:51', '2024-10-18 20:14:00'),
(17, 17, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:51', '2024-10-15 09:34:19'),
(18, 18, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:51', '2024-09-29 15:55:51'),
(19, 19, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:51', '2024-09-30 12:56:57'),
(20, 20, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:51', '2024-10-13 18:38:23'),
(21, 21, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:52', '2024-10-15 10:53:05'),
(22, 22, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:52', '2024-10-06 15:31:14'),
(23, 23, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:43:63:8d', NULL, '2024-09-29 15:55:52', '2024-09-30 10:46:53'),
(24, 24, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:52', '2024-09-30 12:16:40'),
(25, 25, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:34:07:b1', '1c568e3407b1', '2024-09-29 15:55:52', '2024-09-30 12:54:32'),
(26, 26, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:52', '2024-09-29 19:24:43'),
(27, 27, 'ZTE', 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:53', '2024-10-02 21:26:44'),
(28, 28, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:53', '2024-09-29 15:55:53'),
(29, 29, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:53', '2024-10-01 18:51:23'),
(30, 30, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:53', '2024-10-14 22:56:13'),
(31, 31, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:53', '2024-09-29 15:55:53'),
(32, 32, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:54', '2024-10-15 10:50:39'),
(33, 33, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:54', '2024-10-15 09:16:55'),
(34, 34, 'TP-Link', 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:54', '2024-10-05 22:23:03'),
(35, 35, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:54', '2024-09-29 15:55:54'),
(36, 36, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:54', '2024-10-01 12:59:20'),
(38, 38, 'Huawei', 'HS8145C5', 'telecomadmin', 'admintelecom', '80', NULL, NULL, '2024-09-29 15:55:55', '2024-10-05 22:49:09'),
(39, 39, 'ZTE', 'F660', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:55', '2024-10-08 12:56:54'),
(40, 40, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:55', '2024-10-15 00:01:57'),
(41, 41, 'Huawei', 'HS8145C5', 'telecomadmin', 'admintelecom', '80', NULL, NULL, '2024-09-29 15:55:55', '2024-10-15 09:11:33'),
(42, 42, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:3d:d9:c1', '1c568e3dd9c1', '2024-09-29 15:55:56', '2024-09-30 16:34:07'),
(43, 43, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:56', '2024-09-29 15:55:56'),
(44, 44, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:56', '2024-10-08 12:59:09'),
(45, 45, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:56', '2024-10-15 09:28:03'),
(46, 46, 'ZTE', 'F663NV3A', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:56', '2024-10-06 15:34:52'),
(47, 47, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:56', '2024-09-29 15:55:56'),
(48, 48, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:57', '2024-10-05 22:28:33'),
(49, 49, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:57', '2024-09-29 15:55:57'),
(50, 50, 'ZTE', 'F663NV3A', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:57', '2024-10-05 22:41:08'),
(51, 51, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:57', '2024-10-10 16:10:56'),
(52, 52, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:57', '2024-10-05 22:44:49'),
(53, 53, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', '1c:56:8e:3f:07:09', 'ZIONCOM', '2024-09-29 15:55:58', '2024-09-29 23:11:06'),
(54, 54, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:58', '2024-10-14 23:49:35'),
(55, 55, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:58', '2024-10-15 18:24:40'),
(56, 56, 'ZTE', 'F663NV3A', 'admin', 'berkah25', '80', 'B0:53:65:0A:E1:3F', 'CIOT030AE13F', '2024-09-29 15:55:58', '2024-09-30 12:26:30'),
(57, 57, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:55:58', '2024-09-29 15:55:58'),
(58, 58, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', 'D8:32:14:69:0A:08', NULL, '2024-09-29 15:55:59', '2024-09-30 13:52:31'),
(59, 59, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:59', '2024-09-30 12:50:17'),
(60, 60, 'Huawei', 'HS8145C5', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:59', '2024-10-15 09:20:09'),
(61, 61, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:59', '2024-10-15 10:40:51'),
(62, 62, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:55:59', '2024-10-14 08:44:12'),
(63, 63, 'Tenda', NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:56:00', '2024-10-17 16:09:52'),
(64, 64, 'TP-Link', 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:56:00', '2024-10-17 16:05:20'),
(65, 65, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:56:00', '2024-10-15 10:34:59'),
(66, 66, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-09-29 15:56:00', '2024-09-29 15:56:00'),
(67, 67, 'ZTE', 'F663NV3A', 'admin', 'berkah25', '80', '78:2E:56:4D:A0:17', 'ZICG074DA017', '2024-09-29 15:56:00', '2024-09-29 22:41:33'),
(68, 68, 'ZTE', 'F663NV3A', 'admin', 'berkah25', '80', '1C:78:4E:6D:4F:31', 'ZICG146D4F31', '2024-09-29 15:56:01', '2024-09-29 22:56:56'),
(69, 69, 'ZTE', 'F663NV3A', 'admin', 'berkah25', '80', '78:2E:56:34:9F:E2', 'ZICG07349FE2', '2024-09-29 15:56:01', '2024-09-30 18:24:46'),
(70, 70, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:56:01', '2024-10-15 10:36:08'),
(71, 71, 'ZTE', 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:56:01', '2024-10-10 13:19:54'),
(72, 72, 'TP-Link', NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-09-29 15:56:01', '2024-10-16 00:11:27'),
(73, 73, 'ZTE', 'F650', 'admin', 'berkah25', '80', '2C:33:41:44:29:9A', 'ZICG2544299A', '2024-09-29 15:56:02', '2024-09-29 22:55:49'),
(74, 74, 'ZTE', 'F650', 'admin', 'berkah25', '80', '2C:33:41:35:A2:58', 'ZICG2535A258', '2024-09-29 15:56:02', '2024-09-29 23:05:38'),
(75, 75, 'Huawei', 'HG8245H', 'telecomadmin', 'admintelecom', '80', NULL, NULL, '2024-09-30 13:13:45', '2024-10-08 13:15:20'),
(77, 77, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-03 15:20:17', '2024-10-03 15:22:28'),
(78, 78, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-08 23:04:42', '2024-10-08 23:09:39'),
(79, 79, 'ZTE', 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-16 15:25:35', '2024-10-16 15:33:11'),
(80, 80, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-19 22:25:51', '2024-10-19 22:25:51'),
(81, 81, 'ZTE', 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-26 12:52:51', '2024-10-26 14:30:08'),
(82, 82, 'TP-Link', 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:10:53', '2024-10-30 05:36:42'),
(83, 83, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:10:53', '2024-10-27 23:10:53'),
(85, 85, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:10:55', '2024-10-30 10:16:34'),
(86, 86, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:10:56', '2024-10-29 15:53:43'),
(87, 87, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:10:57', '2024-10-29 16:53:39'),
(88, 88, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:10:58', '2024-10-29 10:47:41'),
(89, 89, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:10:59', '2024-10-31 19:08:11'),
(90, 90, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:10:59', '2024-10-30 06:19:49'),
(91, 91, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:00', '2024-10-31 19:12:34'),
(92, 92, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:02', '2024-10-30 15:36:36'),
(93, 93, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:02', '2024-10-31 19:09:54'),
(94, 94, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:03', '2024-10-31 13:41:59'),
(95, 95, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:11:05', '2024-10-27 23:11:05'),
(96, 96, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:06', '2024-11-01 11:25:49'),
(97, 97, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:07', '2024-10-31 19:11:16'),
(98, 98, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:07', '2024-11-01 01:24:42'),
(99, 99, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:09', '2024-11-01 01:26:17'),
(100, 100, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:10', '2024-10-29 15:42:39'),
(101, 101, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:11:11', '2024-10-27 23:11:11'),
(102, 102, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:12', '2024-10-29 15:48:43'),
(103, 103, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:13', '2024-10-28 08:59:29'),
(104, 104, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:14', '2024-11-01 01:25:30'),
(105, 105, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:14', '2024-11-01 11:24:45'),
(106, 106, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:15', '2024-10-29 15:46:58'),
(107, 107, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:16', '2024-10-28 09:05:11'),
(108, 108, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:17', '2024-10-29 16:56:00'),
(109, 109, 'TP-Link', 'TL-WR840N', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:18', '2024-10-31 05:51:23'),
(110, 110, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:19', '2024-11-01 16:08:48'),
(111, 111, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:20', '2024-11-01 15:58:21'),
(112, 112, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:11:22', '2024-10-27 23:11:22'),
(113, 113, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:23', '2024-11-01 15:59:16'),
(114, 114, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:24', '2024-10-29 15:56:30'),
(115, 115, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:25', '2024-10-28 06:37:12'),
(116, 116, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:26', '2024-10-28 07:04:26'),
(117, 117, 'Huawei', 'HG8245H', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:26', '2024-11-01 16:00:41'),
(118, 118, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:28', '2024-10-28 06:57:33'),
(119, 119, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:28', '2024-10-28 07:01:42'),
(120, 120, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:30', '2024-10-30 07:01:05'),
(121, 121, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:11:30', '2024-10-27 23:11:30'),
(123, 123, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:33', '2024-10-29 15:44:41'),
(124, 124, 'ZTE', 'F663NV3a', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:34', '2024-10-28 06:46:36'),
(125, 125, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:35', '2024-10-29 16:02:29'),
(126, 126, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:11:36', '2024-10-27 23:11:36'),
(127, 127, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:11:37', '2024-10-27 23:11:37'),
(128, 128, 'ZTE', 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:38', '2024-10-28 06:43:46'),
(129, 129, 'TP-Link', NULL, NULL, 'berkah25', '80', NULL, NULL, '2024-10-27 23:11:39', '2024-10-28 06:33:35'),
(130, 130, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:15:32', '2024-10-30 01:04:07'),
(131, 131, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:15:33', '2024-11-01 15:53:15'),
(132, 132, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:15:34', '2024-11-01 16:03:08'),
(133, 133, 'TP-Link', NULL, 'admin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:15:34', '2024-11-01 15:56:15'),
(134, 134, 'Huawei', 'HS8145C5', 'telecomadmin', 'berkah25', '80', NULL, NULL, '2024-10-27 23:15:36', '2024-10-28 06:40:02'),
(135, 135, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:15:36', '2024-10-27 23:15:36'),
(136, 136, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-10-27 23:15:38', '2024-10-27 23:15:38'),
(137, 137, 'Totolink', 'N200RE', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-29 11:12:30', '2024-10-29 11:14:58'),
(138, 138, 'ZTE', 'F650', 'admin', 'berkah25', '80', NULL, NULL, '2024-10-29 13:14:39', '2024-10-29 13:16:26');

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
(1, 'test', 'test-wd3hs3', '$2y$10$5.ADiLVOW4vU3UfSLcL9Gub9wVKdqea6k8MYL/vSLJykbD.bhRPQq', NULL, 'Expire', 'Shanet Fiber', '*B1', 'test', 'test', 'pppoe', NULL, NULL, NULL, '2024-10-01 00:13:09', '2024-10-01 00:13:09'),
(2, 'Nazri Nasier', 'maemunah-yk3mhd', '$2y$10$.9siJR4LokZNBRlmmf7GZ.iFSgb1k65lbVvzdrqXHhOf1Kff7QWHa', 'nasiernazri@gmail.com', 'Paket 2 (15 Mbps)', 'Shanet Fiber', '*B3', '200241004@maemunah', '200241004', 'pppoe', 'KP. Sukamaju RT. 05/07 Ciseureuh Purwakarta', '085210433997', NULL, '2024-10-03 15:15:11', '2024-10-03 15:15:11'),
(3, 'Dian Ardiansyah', 'dian-bhrazm', '$2y$10$k2CLyM.ixS6jtYC.RajULesWs8FR7ROHDmbuawDGUDhrwbX7TK.h.', 'otonks.34ar@gmail.com', 'Basic 3 (20Mbps)', 'Irsyad Network', '*45', 'dian-jl-sukamaju-no-49-rt-0907-kelurahan-ciseureuh-wsqt', 'pG1xKb', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-26 17:50:32', '2024-10-26 17:50:32'),
(4, 'Dian Ardiansyah', 'dian-x2ghft', '$2y$10$9U28Z1O2V.TMA70UsCKAGe19OW1IUlo0z2VoDDE9splH8GGWS7w7.', 'oto', 'ShaNet 0 (7 Mbps)', 'ShaNet Fiber', '*BB', 'dian-jl-sukamaju-no-49-rt-0907-kelurahan-ciseureuh-i14d', '2BJOpR', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-27 16:31:14', '2024-10-27 16:31:14'),
(5, 'Dian Ardiansyah', 'dian-fuy5nl', '$2y$10$gkN/9.mYw0mbsgbQfvkzGO.BysmhG9DKp./Hu2LTufacPOJc28mr.', 'otonks.34ar@gmail.com', 'ShaNet 0 (5 Mbps)', 'ShaNet Fiber', '*BC', '15616116szgsgasg', 'ashs1h56h', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-27 16:49:59', '2024-10-27 16:49:59'),
(6, 'Dian Ardiansyah', 'dian-v7vnu5', '$2y$10$7V8L4yZvFyg5cF90g.d2JuLAK.nGQxR6YxQY7SBXF6A2L2mkEslD6', 'otonks.34ar@gmail.com', 'Basic 0 (7 Mbps)', 'Irsyad Network', '*44', 'dian-jl-sukamaju-no-49-rt-0907-kelurahan-ciseureuh-yjuo', 'IgoLi8', 'pppoe', 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', '085795909904', NULL, '2024-10-28 03:25:48', '2024-10-28 03:25:48'),
(7, '150240705@adinda', '150240705-at-adinda-bq0s', '$2y$10$Vq/zP/cHoKlHKn1UBQ4VkeJI1MdU8Qp.D4fVCG1jcyD0SqCbTz7uC', NULL, 'Isolir', 'Irsyad Network', '*33', '150240705@adinda', '150240705', 'pppoe', NULL, NULL, NULL, '2024-10-31 06:12:18', '2024-10-31 06:12:18'),
(8, '125240427@rtkarma', '125240427-at-rtkarma-1bme', '$2y$10$UAtiFEG1ATZzzXlm6LV.0eYGRJPjIHK5PuD2xlFWrbV.Pi9LYEccW', NULL, 'Isolir', 'Irsyad Network', '*8', '125240427@rtkarma', '125240427', 'pppoe', NULL, NULL, NULL, '2024-10-31 06:24:23', '2024-10-31 06:24:23');

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
(1, '84419a71-eac0-4afd-8cae-4273150f9ea2', 'database', 'default', '{\"uuid\":\"84419a71-eac0-4afd-8cae-4273150f9ea2\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'Error: Object of class Illuminate\\Database\\Eloquent\\Builder could not be converted to string in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:92\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 {main}', '2024-11-24 05:55:30'),
(2, 'd3409346-cf12-445c-8dea-08aad6b732b9', 'database', 'default', '{\"uuid\":\"d3409346-cf12-445c-8dea-08aad6b732b9\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'RuntimeException: You must specify an orderBy clause when using this function. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php:2960\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(34): Illuminate\\Database\\Query\\Builder->enforceOrderBy()\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(127): Illuminate\\Database\\Query\\Builder->chunk(50, Object(Closure))\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(108): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Database\\Query\\Builder), \'warning_bill\')\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 {main}', '2024-11-24 08:10:22'),
(3, '301a973a-4e19-41a0-8e3e-7897fe8be51f', 'database', 'default', '{\"uuid\":\"301a973a-4e19-41a0-8e3e-7897fe8be51f\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'RuntimeException: You must specify an orderBy clause when using this function. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Query\\Builder.php:2960\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(34): Illuminate\\Database\\Query\\Builder->enforceOrderBy()\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(127): Illuminate\\Database\\Query\\Builder->chunk(50, Object(Closure))\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(108): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Database\\Query\\Builder), \'warning_bill\')\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 {main}', '2024-11-24 08:10:25'),
(4, '8d5bfa47-273c-451d-b9de-7af8fa1d5701', 'database', 'default', '{\"uuid\":\"8d5bfa47-273c-451d-b9de-7af8fa1d5701\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'Exception: Property [status] does not exist on this collection instance. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Collections\\Traits\\EnumeratesValues.php:1003\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(132): Illuminate\\Support\\Collection->__get(\'status\')\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Concerns\\BuildsQueries.php(53): App\\Jobs\\SendPaymentReminderJob->App\\Jobs\\{closure}(Object(Illuminate\\Database\\Eloquent\\Collection), 1)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(128): Illuminate\\Database\\Eloquent\\Builder->chunk(50, Object(Closure))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(115): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Database\\Eloquent\\Builder), \'deadline_bill\')\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-11-24 08:16:44'),
(5, 'c1eb1d8a-5f5b-4330-b59d-f34f41278746', 'database', 'default', '{\"uuid\":\"c1eb1d8a-5f5b-4330-b59d-f34f41278746\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'ErrorException: Undefined property: stdClass::$id in C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php:176\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Bootstrap\\HandleExceptions.php(254): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->handleError(2, \'Undefined prope...\', \'C:\\\\xampp\\\\htdocs...\', 176)\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(176): Illuminate\\Foundation\\Bootstrap\\HandleExceptions->Illuminate\\Foundation\\Bootstrap\\{closure}(2, \'Undefined prope...\', \'C:\\\\xampp\\\\htdocs...\', 176)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(152): App\\Jobs\\SendPaymentReminderJob->sendMessagesInBatch(Array, \'warning_bill\')\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\app\\Jobs\\SendPaymentReminderJob.php(99): App\\Jobs\\SendPaymentReminderJob->processBillings(Object(Illuminate\\Support\\LazyCollection), \'warning_bill\')\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): App\\Jobs\\SendPaymentReminderJob->handle()\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(128): Illuminate\\Container\\Container->call(Array)\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Bus\\Dispatcher->Illuminate\\Bus\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Bus\\Dispatcher.php(132): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(123): Illuminate\\Bus\\Dispatcher->dispatchNow(Object(App\\Jobs\\SendPaymentReminderJob), false)\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(141): Illuminate\\Queue\\CallQueuedHandler->Illuminate\\Queue\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Pipeline\\Pipeline.php(116): Illuminate\\Pipeline\\Pipeline->Illuminate\\Pipeline\\{closure}(Object(App\\Jobs\\SendPaymentReminderJob))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(122): Illuminate\\Pipeline\\Pipeline->then(Object(Closure))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\CallQueuedHandler.php(70): Illuminate\\Queue\\CallQueuedHandler->dispatchThroughMiddleware(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(App\\Jobs\\SendPaymentReminderJob))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Jobs\\Job.php(98): Illuminate\\Queue\\CallQueuedHandler->call(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Array)\n#19 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(439): Illuminate\\Queue\\Jobs\\Job->fire()\n#20 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#21 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#22 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#23 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#24 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#25 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#26 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#27 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#28 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#29 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#30 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#31 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#32 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#33 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#34 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#35 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#36 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#37 {main}', '2024-11-24 08:24:44'),
(6, '830eb609-1339-41ee-8243-0ffe0177ef5d', 'database', 'default', '{\"uuid\":\"830eb609-1339-41ee-8243-0ffe0177ef5d\",\"displayName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPaymentReminderJob\",\"command\":\"O:31:\\\"App\\\\Jobs\\\\SendPaymentReminderJob\\\":1:{s:47:\\\"\\u0000App\\\\Jobs\\\\SendPaymentReminderJob\\u0000messageService\\\";O:35:\\\"App\\\\Services\\\\WhatsappMessageService\\\":0:{}}\"}}', 'Illuminate\\Queue\\MaxAttemptsExceededException: App\\Jobs\\SendPaymentReminderJob has been attempted too many times. in C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php:785\nStack trace:\n#0 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(519): Illuminate\\Queue\\Worker->maxAttemptsExceededException(Object(Illuminate\\Queue\\Jobs\\DatabaseJob))\n#1 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(428): Illuminate\\Queue\\Worker->markJobAsFailedIfAlreadyExceedsMaxAttempts(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), 1)\n#2 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(389): Illuminate\\Queue\\Worker->process(\'database\', Object(Illuminate\\Queue\\Jobs\\DatabaseJob), Object(Illuminate\\Queue\\WorkerOptions))\n#3 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Worker.php(176): Illuminate\\Queue\\Worker->runJob(Object(Illuminate\\Queue\\Jobs\\DatabaseJob), \'database\', Object(Illuminate\\Queue\\WorkerOptions))\n#4 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(137): Illuminate\\Queue\\Worker->daemon(\'database\', \'default\', Object(Illuminate\\Queue\\WorkerOptions))\n#5 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Queue\\Console\\WorkCommand.php(120): Illuminate\\Queue\\Console\\WorkCommand->runWorker(\'database\', \'default\')\n#6 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#7 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Util.php(41): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#8 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure(Object(Closure))\n#9 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\BoundMethod.php(35): Illuminate\\Container\\BoundMethod::callBoundMethod(Object(Illuminate\\Foundation\\Application), Array, Object(Closure))\n#10 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Container\\Container.php(662): Illuminate\\Container\\BoundMethod::call(Object(Illuminate\\Foundation\\Application), Array, Array, NULL)\n#11 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(211): Illuminate\\Container\\Container->call(Array)\n#12 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Command\\Command.php(326): Illuminate\\Console\\Command->execute(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#13 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Console\\Command.php(180): Symfony\\Component\\Console\\Command\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Illuminate\\Console\\OutputStyle))\n#14 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(1081): Illuminate\\Console\\Command->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#15 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(320): Symfony\\Component\\Console\\Application->doRunCommand(Object(Illuminate\\Queue\\Console\\WorkCommand), Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#16 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\symfony\\console\\Application.php(174): Symfony\\Component\\Console\\Application->doRun(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#17 C:\\xampp\\htdocs\\shanetfiber.my.id\\vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Console\\Kernel.php(201): Symfony\\Component\\Console\\Application->run(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#18 C:\\xampp\\htdocs\\shanetfiber.my.id\\artisan(35): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))\n#19 {main}', '2024-11-24 09:05:11');

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
(1, 'ZTE', 'false', '2024-09-29 19:07:45', '2024-09-29 19:07:45'),
(2, 'Huawei', 'false', '2024-09-29 19:15:29', '2024-09-29 19:15:29'),
(3, 'Fiberhome', 'false', '2024-09-29 19:15:46', '2024-09-29 19:15:46'),
(4, 'Totolink', 'false', '2024-09-29 19:16:40', '2024-09-29 19:16:40'),
(5, 'TP-Link', 'false', '2024-09-29 19:16:52', '2024-09-29 19:16:52'),
(6, 'Tenda', 'false', '2024-09-29 19:17:05', '2024-09-29 19:17:05');

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

--
-- Dumping data for table `message_logs`
--

INSERT INTO `message_logs` (`id`, `customer_id`, `message_type`, `gateway_id`, `created_at`, `updated_at`) VALUES
(12, 47, 'warning_bill', 1, '2024-11-24 10:54:50', '2024-11-24 10:54:50'),
(13, 1, 'reminder_bill', 1, '2024-11-24 10:54:50', '2024-11-24 10:54:50');

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
(1, 'midtrans', 'Midtrans', NULL, NULL, NULL, NULL, NULL, 'false', 'true', '2024-11-24 05:40:51', '2024-11-24 05:40:51');

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
(32, '2024_11_24_162037_create_message_logs_table', 2),
(33, '2024_11_24_162153_create_message_logs_table', 3);

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
(1, 'ShaNet Fiber', 'shanet-fiber-xk2z', 'shanet.my.id', 'shanum.net', 'shanum250117', 1011, NULL, NULL, 'false', '2024-09-29 15:51:44', '2024-11-15 09:28:50', '2'),
(18, 'Irsyad Network', 'irsyad-network-4sl1', '152.42.243.226', 'irsyad.net', 'root1234', 1021, NULL, NULL, 'false', '2024-10-27 10:55:28', '2024-11-15 09:28:50', '2');

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
(1, 'App\\Models\\User', 1);

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
(2, 1, 'ShaNet 0 (Upto 7 Mbps)', 'ShaNet_0_5Mbps', 'shanet-0-5mbps-kz7k', 'Speed up to 7 Mbps (Broadband)', 100000, 'false', '2024-09-29 15:52:23', '2024-10-27 14:39:24', NULL),
(3, 1, 'ShaNet 0 (7 Mbps)', 'ShaNet_0_7Mbps', 'shanet-0-7mbps-kzs5', 'Speed 7 Mbps', 120000, 'false', '2024-09-29 15:52:23', '2024-10-27 14:39:24', NULL),
(4, 1, 'ShaNet 1 (10 Mbps)', 'ShaNet_1_10Mbps', 'shanet-1-10mbps-6hxs', 'Speed 10 Mbps', 150000, 'false', '2024-09-29 15:52:23', '2024-10-27 14:39:24', NULL),
(5, 1, 'ShaNet 2 (15 Mbps)', 'ShaNet_2_15Mbps', 'shanet-2-15mbps-m9b8', 'Speed 15 Mbps', 200000, 'false', '2024-09-29 15:52:23', '2024-10-27 14:39:24', NULL),
(6, 1, 'ShaNet 3 (20 Mbps)', 'ShaNet_3_20Mbps', 'shanet-3-20mbps-xizi', 'Speed 20 Mbps', 250000, 'false', '2024-09-29 15:52:23', '2024-10-27 14:39:24', NULL),
(7, 1, 'ShaNet 4 (30 Mbps)', 'ShaNet_4_30Mbps', 'shanet-4-30mbps-4ty1', 'Speed 30 Mbps', 300000, 'false', '2024-09-29 15:52:23', '2024-10-27 16:44:08', NULL),
(14, 1, 'Expire', 'Expire', 'expire-4wur', 'Expire', 0, 'false', '2024-09-29 18:25:40', '2024-10-27 14:39:24', NULL),
(21, 18, 'Basic 0 (7 Mbps)', 'Basic 7 Mbps', 'basic-7-mbps-bdon', 'Server-2 Speed 7 Mbps', 125000, 'false', '2024-10-27 15:29:27', '2024-10-27 15:29:27', NULL),
(22, 18, 'Basic 1 (10 Mbps)', 'Basic 10 Mbps', 'basic-10-mbps-b2rn', 'Server-2 Speed 7 Mbps', 150000, 'false', '2024-10-27 15:29:27', '2024-10-27 15:29:27', NULL),
(23, 18, 'Basic 2 (15 Mbps)', 'Basic 15 Mbps', 'basic-15-mbps-8wec', 'Server-2 Speed 15 Mbps', 200000, 'false', '2024-10-27 15:29:27', '2024-10-27 15:29:27', NULL),
(24, 18, 'Basic 3 (20 Mbps)', 'Basic 20 Mbps', 'basic-20-mbps-ew8c', 'Server-2 Speed 20 Mbps', 250000, 'false', '2024-10-27 15:29:27', '2024-10-27 15:29:27', NULL),
(25, 18, 'Isolir', 'Isolir', 'isolir-egst', 'Isolir', 0, 'false', '2024-10-27 15:29:27', '2024-10-27 15:29:27', NULL);

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

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('randika@bni.co.id', '$2y$10$65GvuNnS1n5MljL0q6ia/ePGpPAfwVq2xWj388qvLrImg1l/agUae', '2024-11-24 11:05:43');

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
(1, 'Midtrans', 'midtrans', 'false', 'Midtrans Payment Gateway', '2024-11-24 05:40:51', '2024-11-24 05:40:51');

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
(1, 'Admin', 'web', NULL, 0, '2024-11-24 05:40:53', '2024-11-24 05:40:53'),
(2, 'is-admin', 'web', 'Is Admin', 1, '2024-11-24 05:40:53', '2024-11-24 05:40:53'),
(3, 'can-update', 'web', 'Can Update', 1, '2024-11-24 05:40:53', '2024-11-24 05:40:53'),
(4, 'Server', 'web', NULL, 0, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(5, 'Role', 'web', NULL, 0, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(6, 'Packet', 'web', NULL, 0, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(7, 'PPP', 'web', NULL, 0, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(8, 'User', 'web', NULL, 0, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(9, 'Customer', 'web', NULL, 0, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(10, 'Billing', 'web', NULL, 0, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(11, 'server-list', 'web', 'Server List', 4, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(12, 'server-create', 'web', 'Add Server', 4, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(13, 'server-edit', 'web', 'Edit Server', 4, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(14, 'server-delete', 'web', 'Delete Server', 4, '2024-11-24 05:40:59', '2024-11-24 05:40:59'),
(15, 'role-list', 'web', 'Role List', 5, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(16, 'role-create', 'web', 'Create Role', 5, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(17, 'role-edit', 'web', 'Edit Role', 5, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(18, 'role-delete', 'web', 'Delete Role', 5, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(19, 'packet-list', 'web', 'Packet List', 6, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(20, 'packet-create', 'web', 'Add Packet', 6, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(21, 'packet-edit', 'web', 'Edit Packet', 6, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(22, 'packet-delete', 'web', 'Delete Packet', 6, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(23, 'secret-list', 'web', 'PPP Secret List', 7, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(24, 'secret-create', 'web', 'Add PPP Secret', 7, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(25, 'secret-edit', 'web', 'Edit PPP Secret', 7, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(26, 'secret-delete', 'web', 'Delete PPP Secret', 7, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(27, 'secret-disable', 'web', 'Disable/Enable PPP Secret', 7, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(28, 'user-list', 'web', 'User List', 8, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(29, 'user-create', 'web', 'Add User', 8, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(30, 'user-edit', 'web', 'Edit User', 8, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(31, 'user-delete', 'web', 'Delete User', 8, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(32, 'customer-list', 'web', 'Customer List', 9, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(33, 'customer-create', 'web', 'Add Customer', 9, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(34, 'customer-edit', 'web', 'Edit Customer', 9, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(35, 'customer-delete', 'web', 'Delete Customer', 9, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(36, 'billing-list', 'web', 'Billing List', 10, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(37, 'billing-create', 'web', 'Create Billing', 10, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(38, 'billing-edit', 'web', 'Edit Billing', 10, '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(39, 'billing-delete', 'web', 'Delete Billing', 10, '2024-11-24 05:41:00', '2024-11-24 05:41:00');

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
(1, 'any', '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(2, 'async', '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(3, 'l2tp', '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(4, 'ovpn', '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(5, 'pppoe', '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(6, 'pptp', '2024-11-24 05:41:00', '2024-11-24 05:41:00'),
(7, 'sstp', '2024-11-24 05:41:00', '2024-11-24 05:41:00');

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
(1, '15-xura', 'October 2024', 'KWT-00001-October-2024', '15-pfum-10-2024', 15, '200211114@armiyanti', NULL, 'Paket 2 (15 Mbps)', 200000, 0, 'Admin', 'Admin', '2024-09-30 09:00:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 09:00:03', '2024-09-30 09:00:03'),
(2, '23-5jkg', 'October 2024', 'KWT-00002-October-2024', '23-m8v1-10-2024', 23, '150230804@rafka', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Admin', 'Admin', '2024-09-30 09:03:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 09:03:12', '2024-09-30 09:03:12'),
(3, '4-tqly', 'October 2024', 'KWT-00003-October-2024', '4-71zw-10-2024', 4, '150210411@surya', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Admin', 'Admin', '2024-09-30 09:03:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 09:03:27', '2024-09-30 09:03:27'),
(4, '3-a20n', 'October 2024', 'KWT-00004-October-2024', '3-l4m8-10-2024', 3, '100210220@niaagustin', NULL, 'Paket 0.1 (7Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-09-30 10:52:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 10:52:04', '2024-09-30 10:52:04'),
(6, '50-tgyz', 'October 2024', 'KWT-00001-October-2024', '50-hgqu-10-2024', 50, '150230725@ustdfery', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-09-30 20:51:28', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 20:51:28', '2024-09-30 20:51:28'),
(7, '6-5wd4', 'October 2024', 'KWT-00002-October-2024', '6-yynv-10-2024', 6, 'Cucu Suryani', 'Kp. Sukamaju RT 10/07 No. 37 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-09-30 20:53:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-09-30 20:53:22', '2024-09-30 20:53:22'),
(8, '69-oob6', 'October 2024', 'KWT-00003-October-2024', '69-bs6v-10-2024', 69, 'Alya Iklima Hidayah', 'Perum Panorama Indah B2-4 RT 013/013 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-01 08:35:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-01 08:35:59', '2024-10-01 08:35:59'),
(9, '29-z5et', 'October 2024', 'KWT-00004-October-2024', '29-upjm-10-2024', 29, 'Euis Ratnasari', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 00:13:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 00:13:05', '2024-10-02 00:13:05'),
(10, '27-4aq2', 'October 2024', 'KWT-00005-October-2024', '27-brvn-10-2024', 27, 'Novi Eka Purwaningsih', 'Kp. Sukamaju RT 09/07 Green Sapir Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 00:13:15', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 00:13:15', '2024-10-02 00:13:15'),
(11, '35-om5x', 'October 2024', 'KWT-00006-October-2024', '35-10nm-10-2024', 35, '100221019@hafis', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 00:13:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 00:13:53', '2024-10-02 00:13:53'),
(12, '17-z6w4', 'October 2024', 'KWT-00007-October-2024', '17-eqvs-10-2024', 17, '150230208@azril', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 00:14:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 00:14:01', '2024-10-02 00:14:01'),
(13, '74-9qb9', 'October 2024', 'KWT-00008-October-2024', '74-cvve-10-2024', 74, 'Egy Laksma Suandi', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 00:14:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 00:14:59', '2024-10-02 00:14:59'),
(14, '18-zc5k', 'October 2024', 'KWT-00009-October-2024', '18-rzgx-10-2024', 18, '150230402@iip', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-02 09:51:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-02 09:51:08', '2024-10-02 09:51:08'),
(15, '1-qtcg', 'October 2024', 'KWT-00010-October-2024', '1-mrgi-10-2024', 1, '100210121@subhan', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-03 01:23:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-03 01:23:27', '2024-10-03 01:23:27'),
(16, 'inv-77-wh2l', 'October 2024', '00011-KWT-October-2024', '77-ymlm-10-2024', 77, 'Nazri Nasier', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-03 15:21:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-03 15:21:33', '2024-10-03 15:21:33'),
(17, '56-ggr6', 'October 2024', 'KWT-00001-October-2024', '56-fw6k-10-2024', 56, 'Agustian Setiadi', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-04 12:28:46', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-04 12:28:46', '2024-10-04 12:28:46'),
(18, '62-zalm', 'October 2024', 'KWT-00002-October-2024', '62-nxjg-10-2024', 62, '250230601@abdulrahman', NULL, 'Paket 3 (20 Mbps)', 250000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 09:01:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 09:01:07', '2024-10-05 09:01:07'),
(19, '20-jhup', 'October 2024', 'KWT-00003-October-2024', '20-nisa-10-2024', 20, '150230503@lamjaya', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 10:38:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 10:38:44', '2024-10-05 10:38:44'),
(20, '61-pcpn', 'October 2024', 'KWT-00004-October-2024', '61-9plz-10-2024', 61, '150240817@ibuani', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 22:07:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 22:07:03', '2024-10-05 22:07:03'),
(21, '5-e0ho', 'October 2024', 'KWT-00005-October-2024', '5-dlov-10-2024', 5, '150210712@koesnadi', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 22:14:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 22:14:07', '2024-10-05 22:14:07'),
(22, '5-e0ho', 'October 2024', 'KWT-00006-October-2024', '5-dlov-10-2024', 5, '150210712@koesnadi', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 22:14:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 22:14:09', '2024-10-05 22:14:09'),
(23, '48-9htp', 'October 2024', 'KWT-00007-October-2024', '48-bz2d-10-2024', 48, 'Sri Sulastri', 'KP. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-05 22:29:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-05 22:29:23', '2024-10-05 22:29:23'),
(26, '46-dxdt', 'October 2024', 'KWT-00010-October-2024', '46-jdks-10-2024', 46, 'Rohani Rusmiati', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 15:35:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 15:35:57', '2024-10-06 15:35:57'),
(31, '13-ce0x', 'October 2024', 'KWT-00012-October-2024', '13-cpsx-10-2024', 13, '100221107@anggi', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 16:54:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 16:54:50', '2024-10-06 16:54:50'),
(33, '22-pdup', 'October 2024', 'KWT-00013-October-2024', '22-m7in-10-2024', 22, 'Ratih Aisyah', 'Jl. Veteran RT 003/002 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 17:59:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 17:59:57', '2024-10-06 17:59:57'),
(36, '44-cl5s', 'October 2024', 'KWT-00014-October-2024', '44-fqr3-10-2024', 44, '150221217@furqon', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-06 18:23:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-06 18:23:23', '2024-10-06 18:23:23'),
(37, '39-odak', 'October 2024', 'KWT-00015-October-2024', '39-xekf-10-2024', 39, '100210120@hexsa', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 14:20:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 14:20:01', '2024-10-07 14:20:01'),
(38, '57-jxct', 'October 2024', 'KWT-00016-October-2024', '57-uhdj-10-2024', 57, '200230910@iyan', NULL, 'Paket 2 (15 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 15:26:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 15:26:50', '2024-10-07 15:26:50'),
(39, '31-rxu0', 'October 2024', 'KWT-00017-October-2024', '31-dqf3-10-2024', 31, '150240211@pakzaky', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 15:35:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 15:35:58', '2024-10-07 15:35:58'),
(41, '73-d29v', 'October 2024', 'KWT-00019-October-2024', '73-cxuj-10-2024', 73, 'Asep Topik Hidayat', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-07 19:36:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-07 19:36:03', '2024-10-07 19:36:03'),
(42, '67-jlue', 'October 2024', 'KWT-00020-October-2024', '67-eice-10-2024', 67, 'Lulu Andriani', 'Kp. Sukamaju RT 10/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 12:45:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 12:45:06', '2024-10-08 12:45:06'),
(43, '68-dpyo', 'October 2024', 'KWT-00021-October-2024', '68-10jr-10-2024', 68, 'Ma Aisah', 'Kp. Sukamaju RT 10/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 12:50:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 12:50:05', '2024-10-08 12:50:05'),
(44, '45-ydmk', 'October 2024', 'KWT-00022-October-2024', '45-mbmp-10-2024', 45, '150221106@aam', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 20:34:17', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 20:34:17', '2024-10-08 20:34:17'),
(45, 'inv-78-hgvh', 'October 2024', '00023-KWT-October-2024', '78-sil8-10-2024', 78, 'Moh. Gian Firtyanza', 'Perum Pesona Griya Asri Blok D2 No. 15 Ciseureuh', 'Paket 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-08 23:04:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-08 23:04:59', '2024-10-08 23:04:59'),
(46, '66-9rk2', 'October 2024', 'KWT-00001-October-2024', '66-o7dx-10-2024', 66, '150240305@goradhigha', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-09 11:42:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 11:42:41', '2024-10-09 11:42:41'),
(47, '71-rpnq', 'October 2024', 'KWT-00002-October-2024', '71-lhrc-10-2024', 71, '100240917@enen', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-09 23:02:32', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 23:02:32', '2024-10-09 23:02:32'),
(48, '55-d3gz', 'October 2024', 'KWT-00003-October-2024', '55-eg1k-10-2024', 55, '100240225@agis', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-09 23:02:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-09 23:02:44', '2024-10-09 23:02:44'),
(49, '63-qpbs', 'October 2024', 'KWT-00004-October-2024', '63-78f3-10-2024', 63, '150240824@dendi', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 02:00:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 02:00:34', '2024-10-10 02:00:34'),
(50, '34-omj3', 'October 2024', 'KWT-00005-October-2024', '34-ej6y-10-2024', 34, 'Debbie Febriana', 'KP. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 13:24:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 13:24:26', '2024-10-10 13:24:26'),
(51, '51-jg1t', 'October 2024', 'KWT-00006-October-2024', '51-d8xq-10-2024', 51, '150240711@tehenok', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 16:05:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 16:05:12', '2024-10-10 16:05:12'),
(52, '64-ckry', 'October 2024', 'KWT-00007-October-2024', '64-mv5g-10-2024', 64, '100240901@bunehe', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 20:51:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 20:51:08', '2024-10-10 20:51:08'),
(53, '53-kx3r', 'October 2024', 'KWT-00008-October-2024', '53-o8wp-10-2024', 53, 'Maharani Widyaningrum', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 20:51:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 20:51:27', '2024-10-10 20:51:27'),
(54, '19-akuh', 'October 2024', 'KWT-00009-October-2024', '19-11cq-10-2024', 19, 'Ahmad Saepuloh', 'Kp. Sukamaju RT 09/07 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-10 21:58:11', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-10 21:58:11', '2024-10-10 21:58:11'),
(55, '26-czlm', 'October 2024', 'KWT-00010-October-2024', '26-iiw3-10-2024', 26, 'Narsiah', 'Kp. Sukamaju RT 05/07 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-12 12:33:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-12 12:33:08', '2024-10-12 12:33:08'),
(56, '9-vpav', 'October 2024', 'KWT-00011-October-2024', '9-fezd-10-2024', 9, '100221009@eko', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-12 14:15:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-12 14:15:50', '2024-10-12 14:15:50'),
(57, '42-lyzo', 'October 2024', 'KWT-00012-October-2024', '42-lhkm-10-2024', 42, 'Muhammad Fahrizal', 'Kp. Sukamaju RT 09/07 Gg. Arimbi Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-12 21:17:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-12 21:17:53', '2024-10-12 21:17:53'),
(58, '2-j742', 'October 2024', 'KWT-00013-October-2024', '2-qkrr-10-2024', 2, 'Iis Sugiarti', 'KP. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 00:17:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 00:17:08', '2024-10-13 00:17:08'),
(59, '36-tslu', 'October 2024', 'KWT-00014-October-2024', '36-gqol-10-2024', 36, 'Triyanti', 'Gg. Beringin RT 077/008 Nagrikaler Purwakarta', 'Paket 2 (15 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 09:16:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 09:16:59', '2024-10-13 09:16:59'),
(60, '33-wt0q', 'October 2024', 'KWT-00015-October-2024', '33-tr6c-10-2024', 33, '150240311@slamet', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 12:37:49', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 12:37:49', '2024-10-13 12:37:49'),
(61, '7-hrq7', 'October 2024', 'KWT-00016-October-2024', '7-izge-10-2024', 7, 'Sumarni', 'KP. Sukamaju RT 09/07 Kelurahan Ciseureuh', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 13:47:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 13:47:42', '2024-10-13 13:47:42'),
(62, '58-cbdt', 'October 2024', 'KWT-00017-October-2024', '58-aqr3-10-2024', 58, 'Nur Fakriah Putri', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-13 22:20:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-13 22:20:00', '2024-10-13 22:20:00'),
(63, '28-731i', 'October 2024', 'KWT-00018-October-2024', '28-cgg8-10-2024', 28, '100231210@hasma', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 08:25:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 08:25:33', '2024-10-14 08:25:33'),
(64, '38-afqj', 'October 2024', 'KWT-00019-October-2024', '38-mfwl-10-2024', 38, 'Yuan Firmansyah', 'KP. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 08:57:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 08:57:44', '2024-10-14 08:57:44'),
(65, '24-ci2r', 'October 2024', 'KWT-00020-October-2024', '24-xol2-10-2024', 24, 'Neni Nurhaeni', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 10:36:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 10:36:33', '2024-10-14 10:36:33'),
(66, '40-tnwv', 'October 2024', 'KWT-00021-October-2024', '40-erql-10-2024', 40, '100240501@siti', NULL, 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 12:37:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 12:37:35', '2024-10-14 12:37:35'),
(67, '21-pu4z', 'October 2024', 'KWT-00022-October-2024', '21-ttf1-10-2024', 21, '150230717@ibuayu', NULL, 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 13:18:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 13:18:08', '2024-10-14 13:18:08'),
(68, '10-nmsn', 'October 2024', 'KWT-00023-October-2024', '10-sv7p-10-2024', 10, 'Ali Akbar', 'KP. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'Paket 0.1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 14:46:11', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 14:46:11', '2024-10-14 14:46:11'),
(70, '25-9wf7', 'October 2024', 'KWT-00024-October-2024', '25-ceiz-10-2024', 25, 'Etik Aprilia Ratna Sari', 'Kp. Sukamaju RT 05/07 Gg. Paraji Ciseureuh Purwakarta', 'Paket 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 18:02:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 18:02:43', '2024-10-14 18:02:43'),
(71, '59-nsz8', 'October 2024', 'KWT-00025-October-2024', '59-z0xb-10-2024', 59, 'Amni Fitria', 'Kp. Sukamaju RT 09/07 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 21:32:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 21:32:23', '2024-10-14 21:32:23'),
(72, '49-ucyx', 'October 2024', 'KWT-00026-October-2024', '49-iu4n-10-2024', 49, '150240621@miayamgeprek', NULL, 'ShaNet 1 (10 Mbps)', 75000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 21:32:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 21:32:29', '2024-10-14 21:32:29'),
(73, '32-vyc5', 'October 2024', 'KWT-00027-October-2024', '32-8mvw-10-2024', 32, '100240219@tehai', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 21:32:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 21:32:47', '2024-10-14 21:32:47'),
(74, '54-5enx', 'October 2024', 'KWT-00028-October-2024', '54-mxv3-10-2024', 54, '120240310@linda', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 22:53:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 22:53:47', '2024-10-14 22:53:47'),
(75, '30-ygl8', 'October 2024', 'KWT-00029-October-2024', '30-3yvw-10-2024', 30, '100231217@amoy', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-14 22:54:42', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-14 22:54:42', '2024-10-14 22:54:42'),
(76, '60-el5z', 'October 2024', 'KWT-00030-October-2024', '60-zokf-10-2024', 60, '150240612@jepri', NULL, 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 08:26:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 08:26:21', '2024-10-15 08:26:21'),
(78, '41-ikam', 'October 2024', 'KWT-00031-October-2024', '41-xfht-10-2024', 41, 'Wandi Suhendi', 'KP. Sukamaju RT 05/07 Kelurahan Ciseureuh', 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 10:17:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 10:17:31', '2024-10-15 10:17:31'),
(79, '70-ysxb', 'October 2024', 'KWT-00032-October-2024', '70-nzgg-10-2024', 70, 'Pak Kurdi 2', NULL, 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 10:37:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 10:37:52', '2024-10-15 10:37:52'),
(80, '65-l5du', 'October 2024', 'KWT-00033-October-2024', '65-psah-10-2024', 65, 'Pak Kurdi 1', NULL, 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 10:37:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 10:37:57', '2024-10-15 10:37:57'),
(81, '72-xq6x', 'October 2024', 'KWT-00034-October-2024', '72-r558-10-2024', 72, '100240715@bmcell', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 11:11:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 11:11:52', '2024-10-15 11:11:52'),
(82, '11-tg3c', 'October 2024', 'KWT-00035-October-2024', '11-nuca-10-2024', 11, 'Teh Rita', 'KP. Sukamaju RT 09/07 Kelurahan Ciseureuh', 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 14:24:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 14:24:27', '2024-10-15 14:24:27'),
(83, '43-pwwu', 'October 2024', 'KWT-00036-October-2024', '43-1agz-10-2024', 43, 'Pak Edi', 'Kp. Sukamaju RT 009/007 Green Sapir Kelurahan Ciseureuh', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 18:33:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 18:33:09', '2024-10-15 18:33:09'),
(84, '16-iflw', 'October 2024', 'KWT-00037-October-2024', '16-zqoq-10-2024', 16, '100230102@rdcell', NULL, 'ShaNet 1 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 18:47:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 18:47:20', '2024-10-15 18:47:20'),
(85, '14-kclc', 'October 2024', 'KWT-00038-October-2024', '14-l6j9-10-2024', 14, 'Ai Solihah', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-15 20:22:40', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-15 20:22:40', '2024-10-15 20:22:40'),
(86, 'inv-79-ufiq', 'October 2024', '00039-KWT-October-2024', '79-znlv-10-2024', 79, 'Intan Ayu Sri Wahyuni', 'Jl. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 127500, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-16 17:45:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-16 17:45:35', '2024-10-16 17:45:35'),
(87, '12-rjwc', 'October 2024', 'KWT-00001-October-2024', '12-fo6j-10-2024', 12, 'Pak Suud', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-17 15:48:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-17 15:48:14', '2024-10-17 15:48:14'),
(90, 'inv-80-aywv', 'October 2024', '00002-KWT-October-2024', '80-ccub-10-2024', 80, 'Nurhayati', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-19 22:34:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-19 22:34:23', '2024-10-19 22:34:23'),
(92, '52-coep', 'October 2024', 'KWT-00001-October-2024', '52-ezsd-10-2024', 52, 'Ahmad Sugianto', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-24 19:05:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-24 19:05:01', '2024-10-24 19:05:01'),
(93, 'inv-81-kup4', 'October 2024', '00002-KWT-October-2024', '81-e7x4-10-2024', 81, 'Irvan Mohammad Regent', 'Perum Griya Asri Block K1 No. 10 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-26 13:01:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-26 13:01:54', '2024-10-26 13:01:54'),
(94, '8-zffk', 'October 2024', 'KWT-00001-October-2024', '8-hcka-10-2024', 8, 'Teh Darsih', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-10-27 10:05:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-27 10:05:31', '2024-10-27 10:05:31'),
(95, 'inv-137-qpom', 'October 2024', '00002-KWT-October-2024', '137-yz1p-10-2024', 137, 'Fera', 'Kp. Rawabolang RT 025/007 Desa Maracang', 'Basic 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-29 11:18:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-29 11:18:39', '2024-10-29 11:18:39'),
(96, 'inv-138-z36y', 'October 2024', '00001-KWT-October-2024', '138-yqkj-10-2024', 138, 'Robi Anggara', 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-10-29 13:21:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-10-29 13:21:52', '2024-10-29 13:21:52'),
(97, '39-v82l', 'November 2024', 'KWT-00001-November-2024', '39-ls64-11-2024', 39, 'Dede Suherman', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (Upto 7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:18:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:18:26', '2024-11-01 05:18:26'),
(98, '20-hy9f', 'November 2024', 'KWT-00002-November-2024', '20-z0ha-11-2024', 20, 'Pak Lili', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:20:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:20:43', '2024-11-01 05:20:43'),
(99, '75-eayk', 'November 2024', 'KWT-00003-November-2024', '75-rpo9-11-2024', 75, 'H. M Abu Rasyid. AS. SH', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:21:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:21:56', '2024-11-01 05:21:56'),
(100, '69-yujj', 'November 2024', 'KWT-00004-November-2024', '69-hixd-11-2024', 69, 'Alya Iklima Hidayah', 'Perum Panorama Indah B2-4 RT 013/013 Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:23:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:23:31', '2024-11-01 05:23:31'),
(101, '81-6a8z', 'November 2024', 'KWT-00005-November-2024', '81-kbqz-11-2024', 81, 'Irvan Mohammad Regent', 'Perum Griya Asri Block K1 No. 10 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:26:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:26:12', '2024-11-01 05:26:12'),
(102, '6-nwtz', 'November 2024', 'KWT-00006-November-2024', '6-gdel-11-2024', 6, 'Cucu Suryani', 'Kp. Sukamaju RT 009/007 No. 37 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:28:46', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:28:46', '2024-11-01 05:28:46'),
(103, '79-fdhp', 'November 2024', 'KWT-00007-November-2024', '79-xwky-11-2024', 79, 'Intan Ayu Sri Wahyuni', 'Jl. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:32:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:32:03', '2024-11-01 05:32:03'),
(104, '4-ymng', 'November 2024', 'KWT-00008-November-2024', '4-hcod-11-2024', 4, 'Pak Surya', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 05:33:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 05:33:36', '2024-11-01 05:33:36'),
(105, '18-5sgy', 'November 2024', 'KWT-00009-November-2024', '18-nx8q-11-2024', 18, 'Iip', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-01 10:06:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-01 10:06:47', '2024-11-01 10:06:47'),
(106, '117-834k', 'November 2024', 'KWT-00010-November-2024', '117-4vfc-11-2024', 117, 'Pak Ojak', 'kp', 'Basic 1 (10 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 02:09:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 02:09:31', '2024-11-02 02:09:31'),
(107, '106-ocis', 'November 2024', 'KWT-00011-November-2024', '106-kldl-11-2024', 106, 'Iqbal Budi Prasetya', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 02:09:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 02:09:52', '2024-11-02 02:09:52'),
(108, '48-q5sy', 'November 2024', 'KWT-00012-November-2024', '48-e4lh-11-2024', 48, 'Sri Sulastri', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2024-11-02 04:15:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 04:15:36', '2024-11-02 04:15:36'),
(109, '89-w9lx', 'November 2024', 'KWT-00013-November-2024', '89-qcc8-11-2024', 89, 'Adis', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 05:04:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 05:04:59', '2024-11-02 05:04:59'),
(110, '92-piuq', 'November 2024', 'KWT-00014-November-2024', '92-33wx-11-2024', 92, 'Juju Juarsih', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'Basic 0 (7 Mbps)', 125000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 05:05:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 05:05:56', '2024-11-02 05:05:56'),
(111, '120-yy5m', 'November 2024', 'KWT-00015-November-2024', '120-cps7-11-2024', 120, 'Usman Abdul Sobari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Basic 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2024-11-02 05:06:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-11-02 05:06:34', '2024-11-02 05:06:34');

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
(1, 'Admin', 'web', '2024-11-24 05:41:13', '2024-11-24 05:41:13');

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
(1, 1, 'F663NV3a', 'false', 'false', '2024-09-29 19:18:00', '2024-10-07 15:44:50'),
(2, 1, 'F660', 'false', 'false', '2024-09-29 19:18:48', '2024-09-29 19:18:48'),
(3, 1, 'F650', 'false', 'false', '2024-09-29 19:19:05', '2024-09-29 19:19:05'),
(4, 2, 'HS8145C5', 'false', 'false', '2024-09-29 19:19:40', '2024-09-29 19:19:40'),
(5, 2, 'HG8245H5', 'false', 'false', '2024-09-29 19:19:51', '2024-09-29 19:19:51'),
(6, 2, 'HG8245H', 'false', 'false', '2024-09-29 19:20:02', '2024-09-29 19:20:02'),
(7, 2, 'HG8245A', 'false', 'false', '2024-09-29 19:20:11', '2024-09-29 19:20:11'),
(8, 4, 'N200RE', 'false', 'false', '2024-09-29 19:20:45', '2024-09-29 19:20:45'),
(9, 4, 'N355RT', 'false', 'false', '2024-09-29 19:20:59', '2024-09-29 19:20:59'),
(10, 4, 'N300RT', 'false', 'false', '2024-09-29 19:21:12', '2024-09-29 19:21:12'),
(11, 5, 'TL-WR840N', 'false', 'false', '2024-10-05 22:22:30', '2024-10-05 22:22:30');

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
(1, 'Randika Nurfaizi', 'randika@bni.co.id', NULL, '$2y$10$ki9JB8zJVfH.kl4rtMIPW.b.Q6l2HO1reorM0F0qf859kyIdAwYyW', NULL, 'false', '2024-11-24 05:41:13', '2024-11-24 05:41:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `websystems`
--

CREATE TABLE `websystems` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `cpanel_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_gateway` enum('disable','enable') NOT NULL DEFAULT 'disable',
  `enable_tax` enum('false','true') NOT NULL DEFAULT 'false',
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `country_code` varchar(255) DEFAULT 'IDN',
  `phone` varchar(255) DEFAULT NULL,
  `customer_code` varchar(255) DEFAULT NULL,
  `long_customer_code` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `websystems`
--

INSERT INTO `websystems` (`id`, `slug`, `title`, `version`, `cpanel_url`, `created_at`, `updated_at`, `payment_gateway`, `enable_tax`, `tax_rate`, `email`, `url`, `address`, `city`, `postal_code`, `country_code`, `phone`, `customer_code`, `long_customer_code`) VALUES
(1, 'websystem', 'ShaNet Fiber Management', '1.0.9b3', NULL, '2024-09-29 15:50:11', '2024-11-15 11:08:11', 'disable', 'false', 11.00, 'd_ardi@ymail.com', NULL, 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', 'Kabupaten Purwakarta', '41118', 'IDN', '085795909904', 'SNF', 3);

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
(1, 'gateway', 'WA Gateway', 1, 10, 1, 62, 'Config WhatsApp Gateway', 'http://103.129.148.212:3000', 'yE1Q82tGjnB3xZveu50yqQ==', NULL, 0, '2024-11-16 21:43:30', '2024-11-24 05:50:29', NULL);

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
(1, 'unknow_keyword', NULL, 'Unknow Keyword', 'unknow keyword', 'Mohon maaf, keywoard yang anda masukkan salah\n\nKeyword yang tersedia:\n%keywords%', 'Pesan yang dikirim saat pesan yang diterima tidak ada dalam keyword.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(2, 'payment', NULL, 'Pembayaran Lunas', 'payment', '*ShaNet Fiber*\n=======================================\nPelanggan ShaNet Fiber yang terhormat,\nBerikut kami sampaikan detail pembayaran anda:\n\nAtas Nama : Bp/Ibu *%name%*\nCustomer-ID : %customer_id%\nAlamat : %address%\nNomor Tagihan: %billing_number%\nPaket Internet : *%paket%*\nPeriode : *%periode%*\nNominal : *%bill%*\nTeller : %teller%\nTanggal : %payment_time%\nMetode pembayaran: %payment_methode%\n\n=======================================\nTerima kasih telah mempercayakan kebutuhan internetnya kepada kami. Kami berkomitmen untuk selalu memberikan yang terbaik untuk pelanggan.\nMohon maaf jika ada penulisan nama yang salah. Anda dapat menghubungi kami untuk pembetulan nama.\n=======================================', 'Pesan yang dikirim saat pelanggan melakukan pembayaran.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(3, 'unknow_customer', NULL, 'Non Customer', 'unknow customer', 'Mohon maaf, anda belum terdaftar sebagai customer kami.\nSilahkan menghubungi 086726455588 untuk proses registrasi.', 'Pesan yang dikirim bukan customer.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(4, 'unpayment', NULL, 'Unpayment', 'unpayment', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nMaaf telah terjadi kesalahan system.\n\nPembayaran anda:\nNomor Tagihan: *%billing_number%*\nJumlah: *%bill%*\n\nTelah dibatalkan.\n\nTerima kasih telah menggunakan layanan internet kami.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat admin melakukan pembatalan pembayaran.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(5, 'no_paket', NULL, 'Customer Dont Have Paket', 'no paket', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nSaat ini anda sudah terdaftar di sistem kami, namun belum mempunyai paket.\n\nJika anda ingin menggunakan layanan kami, silahkan hubungi 085726455588.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat pelanggan tidak memiliki paket internet.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(6, 'no_bill', NULL, 'No Bill', 'no bill', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nSaat ini anda belum memiliki tagihan.\n\nKami mengucapkan terima kasih karena anda tertib administrasi.\nIni sangat berguna untuk kemajuan kami dalam memperbaiki layanan.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat pelanggan tidak memiliki tagihan.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(7, 'reminder_bill', NULL, 'Reminder Bill', 'warning bill', 'Yth. Bp/Ibu %name%,\r\n%address%\r\nCustomer ID: %customer_id%\r\n\r\n%day% hari lagi adalah batas akhir pembayaran paket %paket-name% internet anda. Saat ini anda memiliki %count-bill% tagihan yang belum dibayar:\r\n\r\n%pakets%\r\n*Periode: %periode%*\r\n*Jumlah tagihan: %total-bill%*\r\n\r\nLakukan pembayaran sebelum jatuh tempo untuk menghindari isolir otomatis oleh system kami.\r\n\r\nAnda dapat melakukan pembayaran melalui:\r\n%account-bank%\r\n\r\nTerima kasih telah menggunakan layanan internet kami.\r\n\r\n*ShaNet Fiber*\r\nJl. Sukamaju No. 49 RT 009/007\r\nCiseureuh, Purwakarta\r\n\r\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim untuk memberitahukan bahwa tagihan mereka akan jatuh tempo.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(8, 'deadline_bill', NULL, 'Deadline Bill', 'warning bill', 'Yth. Bp/Ibu %name%,\r\n%address%\r\nCustomer ID: %customer_id%\r\n\r\nHari ini adalah batas akhir pembayaran paket %paket-name% internet anda. Saat ini anda memiliki %count-bill% tagihan yang belum dibayar:\r\n\r\n%pakets%\r\n*Periode: %periode%*\r\n*Jumlah tagihan: %total-bill%*\r\n\r\nSegera lakukan pembayaran sebelum jatuh tempo untuk menghindari isolir otomatis oleh system kami.\r\n\r\nAnda dapat melakukan pembayaran melalui:\r\n%account-bank%\r\n\r\nTerima kasih telah menggunakan layanan internet kami.\r\n\r\n*ShaNet Fiber*\r\nJl. Sukamaju No. 49 RT 009/007\r\nCiseureuh, Purwakarta\r\n\r\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim untuk memberitahukan bahwa tagihan mereka sudah jatuh tempo.', 1, 0, '2024-11-24 05:41:00', '2024-11-24 05:41:00', NULL),
(9, 'past_due_warning', NULL, 'Past Due Warning', 'warning bill', 'Yth. Bp/Ibu %name%,\r\n%address%\r\nCustomer ID: %customer_id%\r\n\r\nKami ingin mengingatkan bahwa Anda memiliki %count-bill% tagihan paket internet %paket-name% yang belum dibayar hingga melewati jatuh tempo.\r\nBerikut adalah rincian tagihan Anda:\r\n\r\n%pakets%\r\n*Periode: %periode%*\r\n*Jumlah tagihan keseluruhan: %total-bill%*\r\n\r\nPembayaran dapat dilakukan melalui:\r\n%account-bank%\r\n\r\nTerima kasih telah menggunakan layanan internet ShaNet Fiber. Kami berkomitmen memberikan layanan terbaik untuk Anda.\r\n\r\n*ShaNet Fiber*\r\nJl. Sukamaju No. 49 RT 009/007\r\nCiseureuh, Purwakarta\r\n\r\n*Pesan ini dikirim secara otomatis oleh sistem, harap tidak membalas pesan ini.*', 'Pesan yang dikirim untuk mengingatkan bahwa tagihan sudah melewati jatuh tempo.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(10, 'info_tagihan', NULL, 'Info Tagihan', 'info tagihan', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nBerikut info tagihan anda.\n%bills%\nJumlah tagihan: %total-bills%\n\nAnda dapat melakukan pembayaran melalui:\n%account-banks%\n\nTerima kasih telah mempercayakan kebutuhan internet anda kepada kami.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat pelanggan melakukan pengecekan tagihan.', 0, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(11, 'info_paket', NULL, 'Info Paket', 'info paket', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nBerikut kami sampaikan rincian paket anda:\n%pakets%\n\nTerima kasih telah mempercayakan layanan internet anda kepada kami. Kami berkomitmen untuk selalu memberikan pelayanan yang terbaik.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat pelanggan melakukan pengecekan paket.', 0, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(12, 'disable_paket', NULL, 'Disable Paket', 'disable paket', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPaket anda sudah dinon-aktifkan, kami tidak mengenakan biaya layanan selama paket anda dinon-aktifkan.\n\nInfo paket:\nPaket: *%paket_name%*\nStatus: *%status%*\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat paket di non-aktifkan.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(13, 'enable_paket', NULL, 'Enable Paket', 'disable paket', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPaket anda sudah diaktifkan, selamat menikmati layanan internet kami kembali.\n\nInfo paket:\nPaket: *%paket_name%*\nTagihan: *%bill%* per bulan\nStatus: *%status%*\nTanggal Aktivasi: %activation_date%\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat paket di aktifkan.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(14, 'activation_paket', NULL, 'Activation Paket', 'activation paket', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPaket anda sudah berhasil diaktivasi, selamat menikmati layanan internet dari kami.\n\nMasa berlaku: %periode%\nBatas bayar: Setiap tanggal %deadline%\nNama CS: %cs_name%\nContact: %cs_contact%\n\nJika terjadi kendala pada jaringan, silahkan hubungi CS kami.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat aktivasi paket.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(15, 'add_paket', NULL, 'Add Paket', 'add paket', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nPendaftaran paket berhasil, berikut info layanan paket anda:\n\nPaket: %paket_name%\nHarga: %paket_price%\n\nBiaya Langganan: %bill% per bulan\n\nsilahkan tunggu beberapa saat karna paket anda masih dalam proses aktivasi.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat penambahan paket customer.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(16, 'deactivation_customer', NULL, 'Deactivation Customer', 'deactivation customer', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nDengan berat hati kami menonaktifkan akun anda. Jika tidak keberatan, silahkan kirim masukkan anda untuk perbaikan pada layanan kami.\n\nTerima kasih telah menjadi pelanggan kami selama ini.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat customer di non-aktifkan.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(17, 'activation_customer', NULL, 'Activation Customer', 'activation customer', 'Yth. Bp/Ibu %name%,\n%address%\n\nAktivasi akun berhasil. Berikut detail informasi akun anda:\n\nNama: %name%\nAlamat: %address%\nCustomer ID: %customer_id%\nEmail: %email%\nCustomer Service: %customer_service%\n\nSimpan informasi ini untuk pengajuan layanan atau saat terjadi gangguan layanan.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat aktivasi customer.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(18, 'register_customer', NULL, 'Register Customer', 'register customer', 'Yth. Bp/Ibu %name%,\n%address%\n\nTerima kasih telah melakukan pendaftaran di sistem kami.\nBerikut layanan yang dapat anda pilih:\n\n%pakets%\n\nJika anda ingin menggunakan layanan kami, silahkan hubungi 085726455588.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat registrasi customer.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(19, 'create_new_bill', NULL, 'Create New bill', 'create new bill', 'Yth. Bp/Ibu %name%,\n%address%\nCustomer ID: %customer_id%\n\nSaat ini anda sudah dapat membayar tagihan internet.\nBerikut rincian tagihan anda:\n\nPaket: %paket-name%\nTagihan: %bill%\nPeriode: %periode%\nJatuh tempo: %deadline%\n\nAnda dapat melakukan pembayaran melalui:\n%account-banks%\n\nInformasikan kepada kami jika anda sudah melakukan pembayaran di nomor WA 085726455588.\nTerima kasih telah mempercayakan kebutuhan internet anda kepada kami.\n\n*ShaNet Fiber*\nJl. Sukamaju No. 49 RT 009/007\nCiseureuh, Purwakarta\n\n*Pesan ini dikirim otomatis oleh system, anda tidak perlu membalasnya*', 'Pesan yang dikirim saat tagihan dibuat.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL),
(20, 'paylater', NULL, 'Pay Later', 'paylater', '*ShaNet Fiber*\n=======================================\nPelanggan ShaNet Fiber yang terhormat,\nBerikut kami sampaikan detail pembayaran mundur anda:\n\nAtas Nama : Bp/Ibu *%name%*\nCustomer-ID : %customer_id%\nAlamat : %address%\nNomor Tagihan: %billing_number%\nPaket Internet : *%paket%*\nPeriode : *%periode%*\nNominal : *%bill%*\nTeller : %teller%\nMetode pembayaran: *%payment_methode%*\nJatuh Tempo: *%paylater%*\n\n=======================================\nTerima kasih telah mempercayakan kebutuhan internetnya kepada kami. Kami berkomitmen untuk selalu memberikan yang terbaik untuk pelanggan.\nMohon maaf jika ada penulisan nama yang salah. Anda dapat menghubungi kami untuk pembetulan nama.\n=======================================', 'Pesan yang dikirim saat permintaan pay later.', 1, 0, '2024-11-24 05:41:01', '2024-11-24 05:41:01', NULL);

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
(1, 'bwfd8', 'Admin', '6287818040024', 'Shanet-Signature', 'BEGyu8uWKfnnkZe', 'ShaNet Fiber Admin', 0, 0, '2024-11-17 00:55:39', '2024-11-17 00:55:39', NULL);

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
  ADD KEY `customers_ppp_type_id_foreign` (`ppp_type_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `billings`
--
ALTER TABLE `billings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=338;

--
-- AUTO_INCREMENT for table `billing_periodes`
--
ALTER TABLE `billing_periodes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `customer_onts`
--
ALTER TABLE `customer_onts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `deleted_customers`
--
ALTER TABLE `deleted_customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1114;

--
-- AUTO_INCREMENT for table `merk_routers`
--
ALTER TABLE `merk_routers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `message_logs`
--
ALTER TABLE `message_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `midtrans`
--
ALTER TABLE `midtrans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `websystems`
--
ALTER TABLE `websystems`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `whatsapp_gateways`
--
ALTER TABLE `whatsapp_gateways`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `whatsapp_messages`
--
ALTER TABLE `whatsapp_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `whatsapp_numbers`
--
ALTER TABLE `whatsapp_numbers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  ADD CONSTRAINT `customers_ppp_type_id_foreign` FOREIGN KEY (`ppp_type_id`) REFERENCES `ppp_types` (`id`) ON DELETE CASCADE;

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
