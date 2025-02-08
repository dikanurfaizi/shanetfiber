-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 01, 2025 at 01:57 AM
-- Server version: 8.0.41-0ubuntu0.22.04.1
-- PHP Version: 8.1.2-1ubuntu2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shanetmgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `auto_isolirs`
--

CREATE TABLE `auto_isolirs` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mikrotik_id` bigint UNSIGNED NOT NULL,
  `profile_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `script_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_list_isolir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_date` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `due_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_unpayment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_payment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `nat_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nat_dst_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nat_src_address_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nat_dst_address_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proxy_access_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proxy_access_src_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proxy_access_action_data` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proxy_access_action` enum('redirect','deny','allow') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deny',
  `firewall_filter_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `address_list_non_isolir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auto_isolirs`
--

INSERT INTO `auto_isolirs` (`id`, `name`, `mikrotik_id`, `profile_id`, `script_id`, `schedule_id`, `address_list_isolir`, `activation_date`, `due_date`, `comment_unpayment`, `comment_payment`, `disabled`, `created_at`, `updated_at`, `deleted_at`, `nat_id`, `nat_dst_address`, `nat_src_address_list`, `nat_dst_address_list`, `proxy_access_id`, `proxy_access_src_address`, `proxy_access_action_data`, `proxy_access_action`, `firewall_filter_id`, `address_list_non_isolir`) VALUES
(1, 'auto_isolir_notification', 1, 'Expire', '*1156', '*278', 'Isolir-List', 'false', '16', 'unpayment', 'payment', 'false', '2025-01-02 18:01:26', '2025-01-02 18:01:26', NULL, '0', NULL, NULL, NULL, '0', NULL, NULL, 'deny', '0', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `slug`, `name`, `bank`, `account_number`, `disabled`, `created_at`, `updated_at`) VALUES
(1, 'bank-central-asia-mhtv', 'Dian Ardiansyah', 'BCA', '2990407315', 'false', '2025-01-02 15:59:37', '2025-01-14 10:32:44'),
(2, 'bank-bjb-vn4e', 'Dian Ardiansyah', 'Bank BJB', '0073901138100', 'true', '2025-01-02 16:00:42', '2025-01-02 16:00:42'),
(3, 'bank-mandiri-lagt', 'Dian Ardiansyah', 'Mandiri', '1730012695905', 'false', '2025-01-02 16:01:32', '2025-01-14 10:32:57'),
(5, 'bank-central-asia-nmv0', 'PT. Global Sarana Elektronik', 'Bank Central Asia', '2317111157', 'true', '2025-01-02 16:03:17', '2025-01-06 14:05:53'),
(6, 'bank-mandiri-vkqs', 'Budi Kosasih', 'Mandiri', '1730012656030', 'false', '2025-01-02 19:28:36', '2025-01-14 10:33:26'),
(7, 'dana-dzmo', 'Dian Ardiansyah', 'DANA', '085795909904', 'false', '2025-01-16 18:17:20', '2025-01-16 18:17:31'),
(8, 'dana-exiv', 'Budi Kosasih', 'DANA', '085710840198', 'false', '2025-01-14 10:34:08', '2025-01-14 10:34:27');

-- --------------------------------------------------------

--
-- Table structure for table `billings`
--

CREATE TABLE `billings` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_periode_id` bigint UNSIGNED NOT NULL,
  `billing_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paket_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paket_price` int NOT NULL,
  `created_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('BL','LS','PL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BL',
  `teller_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tax` int DEFAULT NULL,
  `pay_later` timestamp NULL DEFAULT NULL,
  `receipt_id` int DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billings`
--

INSERT INTO `billings` (`id`, `slug`, `billing_periode_id`, `billing_number`, `customer_id`, `customer_name`, `paket_name`, `paket_price`, `created_name`, `status`, `teller_name`, `payment_time`, `created_at`, `updated_at`, `tax`, `pay_later`, `receipt_id`, `due_date`) VALUES
(160, '2-9je3', 2, '2-ygfb-01-2025', 2, 'Iis Sugiarti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 11:26:13', '2025-01-02 18:51:54', '2025-01-12 11:26:13', 0, NULL, 138, '2025-01-12 00:00:00'),
(161, '3-gieb', 2, '3-5htw-01-2025', 3, 'Asep Nasihin', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-02 19:35:30', '2025-01-02 18:51:54', '2025-01-02 19:35:30', 0, NULL, 8, '2025-01-20 00:00:00'),
(162, '4-jxrx', 2, '4-1ztx-01-2025', 4, 'Pak Surya', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-02 19:34:24', '2025-01-02 18:51:54', '2025-01-02 19:34:24', 0, NULL, 7, '2025-01-11 00:00:00'),
(163, '5-bcj8', 2, '5-2nmj-01-2025', 5, 'Koesnadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-06 08:03:54', '2025-01-02 18:51:54', '2025-01-06 08:03:54', 0, NULL, 45, '2025-01-12 00:00:00'),
(164, '6-1ny7', 2, '6-ucgt-01-2025', 6, 'Cucu Suryani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-02 19:20:09', '2025-01-02 18:51:54', '2025-01-02 19:20:09', 0, NULL, 4, '2025-01-02 00:00:00'),
(165, '7-dr9a', 2, '7-dgrb-01-2025', 7, 'Sumarni', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-11 11:53:20', '2025-01-02 18:51:54', '2025-01-11 11:53:20', 0, NULL, 129, '2025-01-28 00:00:00'),
(166, '8-ryw5', 2, '8-fdxf-01-2025', 8, 'Teh Darsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-05 10:40:48', '2025-01-02 18:51:54', '2025-01-05 10:40:48', 0, NULL, 37, '2025-01-18 00:00:00'),
(167, '9-1vjx', 2, '9-2aua-01-2025', 9, 'Pak Eko', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-16 07:15:02', '2025-01-02 18:51:55', '2025-01-16 07:15:02', 0, NULL, 188, '2025-01-09 00:00:00'),
(168, '10-gcyy', 2, '10-uunt-01-2025', 10, 'Ustd. Ali Akbar', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 10:57:49', '2025-01-02 18:51:55', '2025-01-15 10:57:49', 0, NULL, 172, '2025-01-20 00:00:00'),
(169, '11-elpm', 2, '11-ptyl-01-2025', 11, 'Rita Susanti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-17 08:54:18', '2025-01-02 18:51:55', '2025-01-17 08:54:18', 0, '2025-01-20 00:00:00', 208, '2025-01-28 00:00:00'),
(170, '12-0iza', 2, '12-schs-01-2025', 12, 'Pak Suud', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-17 09:57:18', '2025-01-02 18:51:55', '2025-01-17 09:57:18', 0, NULL, 210, '2025-01-18 00:00:00'),
(171, '13-ckd4', 2, '13-oneq-01-2025', 13, 'Mamah Anggi', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-08 14:55:55', '2025-01-02 18:51:55', '2025-01-08 14:55:55', 0, NULL, 79, '2025-01-07 00:00:00'),
(172, '14-tjdw', 2, '14-2ruf-01-2025', 14, 'Ai Solihah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 12:25:25', '2025-01-02 18:51:55', '2025-01-15 12:25:25', 0, NULL, 173, '2025-01-20 00:00:00'),
(173, '15-pjca', 2, '15-lxnz-01-2025', 15, 'Armiyanti', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-14 07:19:31', '2025-01-02 18:51:55', '2025-01-14 07:19:31', 0, NULL, 160, '2025-01-14 00:00:00'),
(174, '16-vvbb', 2, '16-mzxq-01-2025', 16, 'Riska Azizah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 09:47:44', '2025-01-02 18:51:55', '2025-01-15 09:47:44', 0, NULL, 167, '2025-01-02 00:00:00'),
(175, '17-kn4y', 2, '17-oirq-01-2025', 17, 'Iip', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-02 19:26:09', '2025-01-02 18:51:55', '2025-01-02 19:26:09', 0, NULL, 6, '2025-01-02 00:00:00'),
(176, '18-pkvt', 2, '18-7c9u-01-2025', 18, 'Ahmad Saepuloh', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-09 12:47:51', '2025-01-02 18:51:55', '2025-01-09 12:47:51', 0, NULL, 87, '2025-01-08 00:00:00'),
(177, '19-rtrt', 2, '19-y1c7-01-2025', 19, 'Pak Lili', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-03 11:23:34', '2025-01-02 18:51:55', '2025-01-03 11:23:34', 0, NULL, 12, '2025-01-03 00:00:00'),
(178, '20-qeci', 2, '20-z1zq-01-2025', 20, 'Ibu Ayu', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-20 09:55:12', '2025-01-02 18:51:55', '2025-01-20 09:55:12', 0, '2025-01-20 00:00:00', 220, '2025-01-17 00:00:00'),
(179, '21-pmsq', 2, '21-yrcz-01-2025', 21, 'Ratih Aisyah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-05 12:19:27', '2025-01-02 18:51:55', '2025-01-05 12:19:27', 0, NULL, 40, '2025-01-28 00:00:00'),
(180, '22-cgb7', 2, '22-bp34-01-2025', 22, 'AA Ramli (Rafka)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-05 09:05:08', '2025-01-02 18:51:55', '2025-01-05 09:05:08', 0, NULL, 35, '2025-01-04 00:00:00'),
(181, '23-yszq', 2, '23-srdi-01-2025', 23, 'Neni Nurhaeni', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 15:55:19', '2025-01-02 18:51:55', '2025-01-15 15:55:19', 0, NULL, 177, '2025-01-01 00:00:00'),
(182, '24-yefh', 2, '24-lr28-01-2025', 24, 'Etik Aprilia Ratna Sari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 15:38:53', '2025-01-02 18:51:55', '2025-01-15 15:38:53', 0, NULL, 176, '2025-01-15 00:00:00'),
(183, '25-klxl', 2, '25-nbwd-01-2025', 25, 'Narsiah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-12 20:08:26', '2025-01-02 18:51:55', '2025-01-12 20:08:26', 0, NULL, 150, '2025-01-01 00:00:00'),
(184, '26-h6x5', 2, '26-fyhr-01-2025', 26, 'Novi Eka Purwaningsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-05 06:51:52', '2025-01-02 18:51:55', '2025-01-05 06:51:52', 0, NULL, 33, '2025-01-05 00:00:00'),
(185, '27-flsp', 2, '27-3idt-01-2025', 27, 'Hasma Wati', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-08 13:51:46', '2025-01-02 18:51:55', '2025-01-08 13:51:46', 0, NULL, 78, '2025-01-10 00:00:00'),
(186, '28-lllv', 2, '28-o6nl-01-2025', 28, 'Euis Ratnasari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-06 07:12:19', '2025-01-02 18:51:55', '2025-01-06 07:12:19', 0, NULL, 43, '2025-01-16 00:00:00'),
(187, '29-hdzg', 2, '29-uw32-01-2025', 29, 'Adi Amoy', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-16 16:31:57', '2025-01-02 18:51:56', '2025-01-16 16:31:57', 0, NULL, 193, '2025-01-17 00:00:00'),
(188, '30-9zk8', 2, '30-ooxb-01-2025', 30, 'A Zaky Alattas', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 07:13:40', '2025-01-02 18:51:56', '2025-01-15 07:13:40', 0, NULL, 164, '2025-01-11 00:00:00'),
(189, '31-tgzz', 2, '31-jupy-01-2025', 31, 'Teh Ai', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-16 16:31:10', '2025-01-02 18:51:56', '2025-01-16 16:31:10', 0, '2025-01-16 00:00:00', 192, '2025-01-19 00:00:00'),
(190, '32-rojn', 2, '32-y6vf-01-2025', 32, 'Pak Slamet', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 08:40:46', '2025-01-02 18:51:56', '2025-01-15 08:40:46', 0, NULL, 166, '2025-01-11 00:00:00'),
(191, '33-etls', 2, '33-bulg-01-2025', 33, 'Debbie Febriana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 08:42:17', '2025-01-02 18:51:56', '2025-01-11 08:42:17', 0, NULL, 122, '2025-01-20 00:00:00'),
(192, '34-rlwz', 2, '34-lnu3-01-2025', 34, 'Hafis', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-06 08:04:53', '2025-01-02 18:51:56', '2025-01-06 08:04:53', 0, NULL, 46, '2025-01-19 00:00:00'),
(193, '35-r0bo', 2, '35-waeu-01-2025', 35, 'Triyanti', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-14 21:18:02', '2025-01-02 18:51:56', '2025-01-14 21:18:02', 0, NULL, 163, '2025-01-17 00:00:00'),
(194, '36-4m1e', 2, '36-43qs-01-2025', 36, 'H. M Abu Rasyid. AS. SH', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-30 06:51:21', '2025-01-02 18:51:56', '2025-01-30 06:51:21', 0, NULL, 228, '2025-01-01 00:00:00'),
(195, '37-btrx', 2, '37-qitk-01-2025', 37, 'Yuan Firmansyah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-16 16:30:23', '2025-01-02 18:51:56', '2025-01-16 16:30:23', 0, NULL, 191, '2025-01-25 00:00:00'),
(196, '38-xwlb', 2, '38-znoj-01-2025', 38, 'Dede Suherman', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-05 06:41:07', '2025-01-02 18:51:56', '2025-01-05 06:41:07', 0, NULL, 31, '2025-01-20 00:00:00'),
(197, '39-vng6', 2, '39-1oqu-01-2025', 39, 'Ida Siti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-11 10:22:36', '2025-01-02 18:51:56', '2025-01-11 10:22:36', 0, NULL, 126, '2025-01-01 00:00:00'),
(198, '40-trzd', 2, '40-qzkp-01-2025', 40, 'Wandi Suhendi', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-16 05:41:36', '2025-01-02 18:51:56', '2025-01-16 05:41:36', 0, NULL, 184, '2025-01-12 00:00:00'),
(199, '41-knkv', 2, '41-q406-01-2025', 41, 'Muhammad Fahrizal', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-13 17:26:16', '2025-01-02 18:51:56', '2025-01-13 17:26:16', 0, NULL, 155, '2025-01-03 00:00:00'),
(200, '42-abxj', 2, '42-gddu-01-2025', 42, 'Edi Suswanto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-14 14:03:30', '2025-01-02 18:51:56', '2025-01-14 14:03:30', 0, NULL, 162, '2025-01-10 00:00:00'),
(201, '43-gj5a', 2, '43-a0b4-01-2025', 43, 'Tri Nurhidayati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-08 17:37:53', '2025-01-02 18:51:56', '2025-01-08 17:37:53', 0, NULL, 83, '2025-01-17 00:00:00'),
(202, '44-4ep7', 2, '44-1iv7-01-2025', 44, 'Pak Aam', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 16:50:55', '2025-01-02 18:51:56', '2025-01-12 16:50:55', 0, NULL, 146, '2025-01-06 00:00:00'),
(203, '45-y1kn', 2, '45-1k0q-01-2025', 45, 'Rohani Rusmiati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-17 07:42:07', '2025-01-02 18:51:56', '2025-01-17 07:42:07', 0, NULL, 207, '2025-01-12 00:00:00'),
(204, '46-i2dl', 2, '46-arcl-01-2025', 46, 'Mulyana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 18:06:35', '2025-01-02 18:51:56', '2025-01-15 18:06:35', 0, NULL, 183, '2025-01-28 00:00:00'),
(205, '47-yphw', 2, '47-8jez-01-2025', 47, 'Sri Sulastri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-10 15:37:09', '2025-01-02 18:51:57', '2025-01-10 15:37:09', 0, NULL, 105, '2025-01-10 00:00:00'),
(206, '48-cgiw', 2, '48-jnsc-01-2025', 48, 'Hendrizal T (Geprek Jogja)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-16 06:07:34', '2025-01-02 18:51:57', '2025-01-16 06:07:34', 0, NULL, 185, '2025-01-21 00:00:00'),
(207, '49-bd1m', 2, '49-6zag-01-2025', 49, 'Ferry Kurniawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-04 15:24:56', '2025-01-02 18:51:57', '2025-01-04 15:24:56', 0, NULL, 30, '2025-01-25 00:00:00'),
(208, '50-4wi8', 2, '50-xbfl-01-2025', 50, 'Nok Reni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-16 06:41:20', '2025-01-02 18:51:57', '2025-01-16 06:41:20', 0, NULL, 187, '2025-01-11 00:00:00'),
(209, '51-gavq', 2, '51-qtg9-01-2025', 51, 'Ahmad Sugianto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-19 10:18:40', '2025-01-02 18:51:57', '2025-01-19 10:18:40', 0, NULL, 216, '2025-01-25 00:00:00'),
(210, '52-le8u', 2, '52-vnpn-01-2025', 52, 'Maharani Widyaningrum', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-16 07:39:43', '2025-01-02 18:51:57', '2025-01-16 07:39:43', 0, NULL, 189, '2025-01-13 00:00:00'),
(211, '53-f6qr', 2, '53-ad66-01-2025', 53, 'Linda', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-16 18:14:51', '2025-01-02 18:51:57', '2025-01-16 18:14:51', 0, NULL, 203, '2025-01-10 00:00:00'),
(212, '54-wulk', 2, '54-vhlg-01-2025', 54, 'Agista Utari', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-08 19:06:09', '2025-01-02 18:51:57', '2025-01-08 19:06:09', 0, NULL, 85, '2025-01-25 00:00:00'),
(213, '55-7uoa', 2, '55-l7pk-01-2025', 55, 'Agustian Setiadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-03 09:04:34', '2025-01-02 18:51:57', '2025-01-03 09:04:34', 0, NULL, 10, '2025-01-07 00:00:00'),
(214, '56-kxvu', 2, '56-eo4a-01-2025', 56, 'Pak Iyan', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 08:41:56', '2025-01-02 18:51:57', '2025-01-11 08:41:56', 0, NULL, 121, '2025-01-10 00:00:00'),
(215, '57-55vn', 2, '57-awcb-01-2025', 57, 'Nur Fakriah Putri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 14:00:14', '2025-01-02 18:51:57', '2025-01-15 14:00:14', 0, NULL, 175, '2025-01-11 00:00:00'),
(216, '58-2jgq', 2, '58-yiqn-01-2025', 58, 'Pak Jepri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-10 19:36:23', '2025-01-02 18:51:57', '2025-01-10 19:36:23', 0, NULL, 117, '2025-01-12 00:00:00'),
(217, '59-wzop', 2, '59-aex6-01-2025', 59, 'Bu Ani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-06 06:33:44', '2025-01-02 18:51:57', '2025-01-06 06:33:44', 0, NULL, 42, '2025-01-17 00:00:00'),
(218, '60-ujr0', 2, '60-b5dl-01-2025', 60, 'Yeyet Abdulrachman', 'ShaNet 4 (50 Mbps)', 250000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-07 04:14:11', '2025-01-02 18:51:57', '2025-01-07 04:14:11', 0, NULL, 65, '2025-01-01 00:00:00'),
(219, '61-p5or', 2, '61-eufb-01-2025', 61, 'Pak Dendi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-16 06:08:56', '2025-01-02 18:51:57', '2025-01-16 06:08:56', 0, NULL, 186, '2025-01-24 00:00:00'),
(220, '62-dpel', 2, '62-a9cn-01-2025', 62, 'Marya Ningsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 16:15:06', '2025-01-02 18:51:57', '2025-01-15 16:15:06', 0, NULL, 178, '2025-01-01 00:00:00'),
(221, '63-farx', 2, '63-c1rq-01-2025', 63, 'Pak Kurdi (Rumah)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 17:44:30', '2025-01-02 18:51:57', '2025-01-15 17:44:30', 0, NULL, 180, '2025-01-01 00:00:00'),
(222, '64-zp3c', 2, '64-p4d0-01-2025', 64, 'Kodrat (GOR ADHIGA)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-03 09:25:58', '2025-01-02 18:51:57', '2025-01-03 09:25:58', 0, NULL, 11, '2025-01-05 00:00:00'),
(223, '65-lpdp', 2, '65-l5qd-01-2025', 65, 'Lulu Andriani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-10 08:42:23', '2025-01-02 18:51:57', '2025-01-10 08:42:23', 0, NULL, 95, '2025-01-08 00:00:00'),
(224, '66-k2uz', 2, '66-s5zs-01-2025', 66, 'Ma Aisah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-10 08:41:26', '2025-01-02 18:51:57', '2025-01-10 08:41:26', 0, NULL, 93, '2025-01-08 00:00:00'),
(225, '67-6w7x', 2, '67-surq-01-2025', 67, 'Alya Iklima Hidayah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 05:37:21', '2025-01-02 18:51:57', '2025-01-11 05:37:21', 0, NULL, 119, '2025-01-08 00:00:00'),
(226, '68-e6nv', 2, '68-kcb7-01-2025', 68, 'Pak Kurdi (Kontrakan)', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 17:44:39', '2025-01-02 18:51:58', '2025-01-15 17:44:39', 0, NULL, 181, '2025-01-01 00:00:00'),
(227, '69-rjmt', 2, '69-aka8-01-2025', 69, 'Teh Enen', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-11 08:00:20', '2025-01-02 18:51:58', '2025-01-11 08:00:20', 0, NULL, 120, '2025-01-17 00:00:00'),
(228, '71-w0xh', 2, '71-uux6-01-2025', 71, 'Asep Topik Hidayat', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-08 08:18:20', '2025-01-02 18:51:58', '2025-01-08 08:18:20', 0, NULL, 77, '2025-01-22 00:00:00'),
(229, '72-ypjn', 2, '72-d9qp-01-2025', 72, 'Egy Laksma Suandi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-09 18:53:31', '2025-01-02 18:51:58', '2025-01-09 18:53:31', 0, NULL, 90, '2025-01-24 00:00:00'),
(230, '73-frc1', 2, '73-eax1-01-2025', 73, 'Nazri Nasier', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-17 04:13:48', '2025-01-02 18:51:58', '2025-01-17 04:13:48', 0, NULL, 206, '2025-01-04 00:00:00'),
(231, '74-mwf6', 2, '74-i3uf-01-2025', 74, 'Moh. Gian Firtyanza', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-07 04:48:26', '2025-01-02 18:51:58', '2025-01-07 04:48:26', 0, NULL, 66, '2025-01-09 00:00:00'),
(232, '75-ubdm', 2, '75-6ou1-01-2025', 75, 'Intan Ayu Sri Wahyuni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-06 15:16:32', '2025-01-02 18:51:58', '2025-01-06 15:16:32', 0, NULL, 63, '2025-01-17 00:00:00'),
(233, '76-5swr', 2, '76-tvqt-01-2025', 76, 'Nurhayati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-05 06:51:41', '2025-01-02 18:51:58', '2025-01-05 06:51:41', 0, NULL, 32, '2025-01-20 00:00:00'),
(234, '77-dxvt', 2, '77-e8pg-01-2025', 77, 'Irvan Mohammad Regent', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-03 17:55:47', '2025-01-02 18:51:58', '2025-01-03 17:55:47', 0, NULL, 21, '2025-01-27 00:00:00'),
(235, '78-mh4h', 2, '78-6gv5-01-2025', 78, 'Robi Anggara', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-16 07:58:48', '2025-01-02 18:51:58', '2025-01-16 07:58:48', 0, NULL, 190, '2025-01-28 00:00:00'),
(237, '80-axhk', 2, '80-tfc1-01-2025', 80, 'Iis Nurjanah', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-09 12:42:44', '2025-01-02 18:51:58', '2025-01-09 12:42:44', 0, NULL, 86, '2025-01-26 00:00:00'),
(238, '81-pqnn', 2, '81-tzkf-01-2025', 81, 'Agus Rahmat (Azril)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-06 08:02:14', '2025-01-02 18:51:58', '2025-01-06 08:02:14', 0, NULL, 44, '2025-01-08 00:00:00'),
(239, '82-zinx', 2, '82-jmra-01-2025', 82, 'Neng Helawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-09 19:25:13', '2025-01-02 18:51:58', '2025-01-09 19:25:13', 0, NULL, 92, '2025-01-06 00:00:00'),
(240, '83-xyig', 2, '83-d58b-01-2025', 83, 'Rinji Maulani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-14 11:15:20', '2025-01-02 18:51:58', '2025-01-14 11:15:20', 0, NULL, 161, '2025-01-28 00:00:00'),
(241, '84-ozx6', 2, '84-1pol-01-2025', 84, 'Iman Nurjamansyah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-12 08:27:21', '2025-01-02 18:51:58', '2025-01-12 08:27:21', 0, NULL, 135, '2025-01-28 00:00:00'),
(242, '85-vl0e', 2, '85-pc9p-01-2025', 85, 'Dani Ramdani', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-10 08:42:04', '2025-01-02 18:51:58', '2025-01-10 08:42:04', 0, NULL, 94, '2025-01-28 00:00:00'),
(243, '86-qiy2', 2, '86-qwam-01-2025', 86, 'Mujinah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-03 04:42:58', '2025-01-02 18:51:58', '2025-01-03 04:42:58', 0, NULL, 9, '2025-01-28 00:00:00'),
(244, '87-iygh', 2, '87-3vpg-01-2025', 87, 'Rodiah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-09 13:17:02', '2025-01-02 18:51:58', '2025-01-09 13:17:02', 0, NULL, 88, '2025-01-01 00:00:00'),
(245, '88-axcf', 2, '88-aqoq-01-2025', 88, 'Gilang Gumilang', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-12 09:28:06', '2025-01-02 18:51:58', '2025-01-12 09:28:06', 0, NULL, 137, '2025-01-28 00:00:00'),
(246, '89-gpvk', 2, '89-dbxl-01-2025', 89, 'Fadilah Nurhasanah', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-03 13:09:23', '2025-01-02 18:51:59', '2025-01-03 13:09:23', 0, NULL, 17, '2025-01-01 00:00:00'),
(247, '90-j6g4', 2, '90-5sbo-01-2025', 90, 'Mimin Rukmini', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 12:45:40', '2025-01-02 18:51:59', '2025-01-15 12:45:40', 0, NULL, 174, '2025-01-01 00:00:00'),
(248, '91-1zwy', 2, '91-fc8c-01-2025', 91, 'Tati Mulyati', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-13 12:31:23', '2025-01-02 18:51:59', '2025-01-13 12:31:23', 0, NULL, 153, '2025-01-08 00:00:00'),
(249, '92-tgfk', 2, '92-qhsj-01-2025', 92, 'Siam Suroso', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-21 16:01:04', '2025-01-02 18:51:59', '2025-01-21 16:01:04', 0, '2025-01-18 00:00:00', 223, '2025-01-07 00:00:00'),
(250, '93-zzpa', 2, '93-7zgr-01-2025', 93, 'Rini Seminar', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-11 09:52:41', '2025-01-02 18:51:59', '2025-01-11 09:52:41', 0, NULL, 124, '2025-01-10 00:00:00'),
(251, '94-uu9p', 2, '94-p1lp-01-2025', 94, 'Risna Suhertini', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-05 07:59:59', '2025-01-02 18:51:59', '2025-01-05 07:59:59', 0, NULL, 34, '2025-01-02 00:00:00'),
(252, '95-qn3v', 2, '95-enz3-01-2025', 95, 'Dewi Sutriani', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-11 13:27:33', '2025-01-02 18:51:59', '2025-01-11 13:27:33', 0, NULL, 132, '2025-01-02 00:00:00'),
(253, '96-ulhx', 2, '96-pbnb-01-2025', 96, 'Mariati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 07:47:14', '2025-01-02 18:51:59', '2025-01-15 07:47:14', 0, NULL, 165, '2025-01-06 00:00:00'),
(254, '97-retd', 2, '97-q0lx-01-2025', 97, 'Devi Sevtiawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-17 15:12:08', '2025-01-02 18:51:59', '2025-01-17 15:12:08', 0, NULL, 212, '2025-01-05 00:00:00'),
(255, '98-a4uj', 2, '98-dald-01-2025', 98, 'Tatang Mulyana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-12 19:39:41', '2025-01-02 18:51:59', '2025-01-12 19:39:41', 0, NULL, 149, '2025-01-08 00:00:00'),
(256, '99-so0d', 2, '99-ekev-01-2025', 99, 'Tuti Helawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-13 17:06:50', '2025-01-02 18:51:59', '2025-01-13 17:06:50', 0, NULL, 154, '2025-01-15 00:00:00'),
(257, '100-tgmn', 2, '100-xb7i-01-2025', 100, 'Yayah Komariah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-05 12:18:40', '2025-01-02 18:51:59', '2025-01-05 12:18:40', 0, NULL, 39, '2025-01-17 00:00:00'),
(258, '101-k7k6', 2, '101-etrq-01-2025', 101, 'H Imon', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-14 04:14:36', '2025-01-02 18:51:59', '2025-01-14 04:14:36', 0, NULL, 159, '2025-01-08 00:00:00'),
(259, '102-5mtd', 2, '102-kmwj-01-2025', 102, 'Julianto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-16 17:12:05', '2025-01-02 18:51:59', '2025-01-16 17:12:05', 0, NULL, 201, '2025-01-12 00:00:00'),
(260, '103-rdvg', 2, '103-catd-01-2025', 103, 'Wahyu Maulana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 09:52:30', '2025-01-02 18:51:59', '2025-01-07 09:52:30', 0, NULL, 67, '2025-01-28 00:00:00'),
(261, '104-8pqc', 2, '104-yg0n-01-2025', 104, 'Rika Rahmawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 15:39:25', '2025-01-02 18:51:59', '2025-01-10 15:39:25', 0, NULL, 110, '2025-01-27 00:00:00'),
(262, '105-1xxi', 2, '105-a5ka-01-2025', 105, 'Neneng Suratni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 12:34:19', '2025-01-02 18:51:59', '2025-01-07 12:34:19', 0, NULL, 72, '2025-01-28 00:00:00'),
(263, '106-vq5s', 2, '106-kekb-01-2025', 106, 'Misbah Toko Bangunan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-03 13:08:37', '2025-01-02 18:51:59', '2025-01-03 13:08:37', 0, NULL, 16, '2025-01-04 00:00:00'),
(264, '107-c65r', 2, '107-tolf-01-2025', 107, 'Jajang Salahudin', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-16 18:47:22', '2025-01-02 18:52:00', '2025-01-16 18:47:22', 0, NULL, 205, '2025-01-06 00:00:00'),
(265, '108-qvvh', 2, '108-cp7a-01-2025', 108, 'Neng Siti', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-03 13:06:24', '2025-01-02 18:52:00', '2025-01-03 13:06:24', 0, NULL, 14, '2025-01-22 00:00:00'),
(266, '109-s7ve', 2, '109-slbg-01-2025', 109, 'Dedi Haerudi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-15 16:55:19', '2025-01-02 18:52:00', '2025-01-15 16:55:19', 0, NULL, 179, '2025-01-22 00:00:00'),
(267, '110-04gx', 2, '110-x5is-01-2025', 110, 'Amania Kusumah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 21:21:02', '2025-01-02 18:52:00', '2025-01-10 21:21:02', 0, NULL, 118, '2025-01-28 00:00:00'),
(268, '111-xcu5', 2, '111-fvx7-01-2025', 111, 'Nurul Aeni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-05 09:30:27', '2025-01-02 18:52:00', '2025-01-05 09:30:27', 0, NULL, 36, '2025-01-28 00:00:00'),
(269, '112-wspw', 2, '112-rihv-01-2025', 112, 'Pak Ojak', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-03 13:12:17', '2025-01-02 18:52:00', '2025-01-03 13:12:17', 0, NULL, 19, '2025-01-01 00:00:00'),
(270, '113-umct', 2, '113-e5xz-01-2025', 113, 'Redi Natisna', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-03 13:12:38', '2025-01-02 18:52:00', '2025-01-03 13:12:38', 0, NULL, 20, '2025-01-28 00:00:00'),
(271, '114-bj7q', 2, '114-qz6y-01-2025', 114, 'Agus Hermanto', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 17:49:44', '2025-01-02 18:52:00', '2025-01-10 17:49:44', 0, NULL, 115, '2025-01-08 00:00:00'),
(272, '115-2snn', 2, '115-knbr-01-2025', 115, 'Pak Agus', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 12:46:37', '2025-01-02 18:52:00', '2025-01-10 12:46:37', 0, NULL, 103, '2025-01-20 00:00:00'),
(273, '116-mr6h', 2, '116-2ik6-01-2025', 116, 'Wahid', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-01-02 18:52:00', '2025-01-02 18:52:00', 0, NULL, NULL, '2025-01-25 00:00:00'),
(274, '117-umuj', 2, '117-7uvz-01-2025', 117, 'Sularno', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-02 18:52:36', '2025-01-02 18:52:00', '2025-01-02 18:52:36', 0, NULL, 2, '2025-01-01 00:00:00'),
(275, '118-qpft', 2, '118-ymaq-01-2025', 118, 'Wida', 'ShaNet 1 (10 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-04 13:25:16', '2025-01-02 18:52:00', '2025-01-04 13:25:16', 0, NULL, 29, '2025-01-03 00:00:00'),
(276, '119-1vim', 2, '119-mbal-01-2025', 119, 'Iqbal Budi Prasetya', 'ShaNet 1 (10 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 12:39:22', '2025-01-02 18:52:00', '2025-01-10 12:39:22', 0, NULL, 102, '2025-01-04 00:00:00'),
(277, '120-eovc', 2, '120-d4pv-01-2025', 120, 'Usman Abdul Sobari', 'ShaNet 1 (10 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 15:40:35', '2025-01-02 18:52:00', '2025-01-10 15:40:35', 0, NULL, 113, '2025-01-04 00:00:00'),
(278, '121-1ahp', 2, '121-cmlt-01-2025', 121, 'Nuryani', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 09:52:53', '2025-01-02 18:52:00', '2025-01-07 09:52:53', 0, NULL, 68, '2025-01-06 00:00:00'),
(279, '122-yycw', 2, '122-i8o4-01-2025', 122, 'Rosidah', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-13 21:16:27', '2025-01-02 18:52:00', '2025-01-13 21:16:27', 0, NULL, 158, '2025-01-20 00:00:00'),
(280, '123-jff7', 2, '123-uynx-01-2025', 123, 'Siti Mariam', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-04 07:08:10', '2025-01-02 18:52:00', '2025-01-04 07:08:10', 0, NULL, 25, '2025-01-27 00:00:00'),
(281, '124-6e5u', 2, '124-kh8x-01-2025', 124, 'Iis Ismawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-04 07:06:41', '2025-01-02 18:52:00', '2025-01-04 07:06:41', 0, NULL, 23, '2025-01-27 00:00:00'),
(282, '125-nzzh', 2, '125-uj4i-01-2025', 125, 'Desi Nurdianti', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-04 07:07:23', '2025-01-02 18:52:00', '2025-01-04 07:07:23', 0, NULL, 24, '2025-01-27 00:00:00'),
(283, '126-eb3a', 2, '126-921l-01-2025', 126, 'Adis', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-05 18:31:50', '2025-01-02 18:52:00', '2025-01-05 18:31:50', 0, NULL, 41, '2025-01-28 00:00:00'),
(284, '127-tqaw', 2, '127-9ybj-01-2025', 127, 'Satim Silegar', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 10:59:24', '2025-01-02 18:52:01', '2025-01-10 10:59:24', 0, NULL, 96, '2025-01-28 00:00:00'),
(285, '128-2l3u', 2, '128-oedi-01-2025', 128, 'Imar Sulaeman', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-06 13:11:05', '2025-01-02 18:52:01', '2025-01-06 13:11:05', 0, NULL, 62, '2025-01-28 00:00:00'),
(286, '129-a6s7', 2, '129-eznn-01-2025', 129, 'Heni', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-06 10:38:50', '2025-01-02 18:52:01', '2025-01-06 10:38:50', 0, NULL, 47, '2025-01-28 00:00:00'),
(287, '130-beza', 2, '130-vu5x-01-2025', 130, 'Juju Juarsih', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 09:53:11', '2025-01-02 18:52:01', '2025-01-07 09:53:11', 0, NULL, 69, '2025-01-01 00:00:00'),
(288, '131-w7jd', 2, '131-diqm-01-2025', 131, 'Juhdi', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 16:10:20', '2025-01-02 18:52:01', '2025-01-07 16:10:20', 0, NULL, 74, '2025-01-01 00:00:00'),
(289, '132-ajao', 2, '132-9ey8-01-2025', 132, 'Cici', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-06 10:44:50', '2025-01-02 18:52:01', '2025-01-06 10:44:50', 0, NULL, 49, '2025-01-09 00:00:00'),
(290, '133-kys5', 2, '133-yqvi-01-2025', 133, 'Pepen', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-06 10:39:43', '2025-01-02 18:52:01', '2025-01-06 10:39:43', 0, NULL, 48, '2025-01-10 00:00:00'),
(291, '134-iwpn', 2, '134-eoom-01-2025', 134, 'Wayanto', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-06 15:29:00', '2025-01-02 18:52:01', '2025-01-06 15:29:00', 0, NULL, 64, '2025-01-10 00:00:00'),
(292, '135-a71o', 2, '135-pv7u-01-2025', 135, 'Heri Suandi', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-12 12:56:05', '2025-01-02 18:52:01', '2025-01-12 12:56:05', 0, NULL, 141, '2025-01-14 00:00:00'),
(293, '136-kx6h', 2, '136-8qo0-01-2025', 136, 'Miung', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 12:33:45', '2025-01-02 18:52:01', '2025-01-07 12:33:45', 0, NULL, 71, '2025-01-14 00:00:00'),
(294, '137-ttdo', 2, '137-6zeu-01-2025', 137, 'Dadan Hamdani', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 12:37:43', '2025-01-02 18:52:01', '2025-01-10 12:37:43', 0, NULL, 98, '2025-01-14 00:00:00'),
(295, '138-6wlu', 2, '138-fxbc-01-2025', 138, 'Okis', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-08 16:39:07', '2025-01-02 18:52:01', '2025-01-08 16:39:07', 0, NULL, 82, '2025-01-20 00:00:00'),
(296, '139-keei', 2, '139-1906-01-2025', 139, 'RT Oji', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-08 18:06:38', '2025-01-02 18:52:01', '2025-01-08 18:06:38', 0, NULL, 84, '2025-01-15 00:00:00'),
(297, '140-mq6e', 2, '140-3ioc-01-2025', 140, 'Suntari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-11 13:27:03', '2025-01-02 18:52:01', '2025-01-11 13:27:03', 0, NULL, 131, '2025-01-18 00:00:00'),
(298, '141-eumh', 2, '141-xttp-01-2025', 141, 'Engko', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 19:11:16', '2025-01-02 18:52:01', '2025-01-07 19:11:16', 0, NULL, 76, '2025-01-25 00:00:00'),
(299, '142-2noz', 2, '142-hkgh-01-2025', 142, 'Arya Cahyana Putra', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 10:59:49', '2025-01-02 18:52:01', '2025-01-10 10:59:49', 0, NULL, 97, '2025-01-01 00:00:00'),
(300, '143-rup1', 2, '143-jfft-01-2025', 143, 'Bajuri Enci', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-17 17:57:51', '2025-01-02 18:52:01', '2025-01-17 17:57:51', 0, NULL, 214, '2025-01-04 00:00:00'),
(301, '144-i2z2', 2, '144-u9wu-01-2025', 144, 'Salmah Saleha', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 15:38:22', '2025-01-02 18:52:01', '2025-01-10 15:38:22', 0, NULL, 106, '2025-01-08 00:00:00'),
(302, '145-xvib', 2, '145-qami-01-2025', 145, 'Darma Wirawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-04 08:34:39', '2025-01-02 18:52:01', '2025-01-04 08:34:39', 0, NULL, 27, '2025-01-13 00:00:00'),
(303, '146-w64i', 2, '146-fldr-01-2025', 146, 'Sulis (Rumah)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 12:38:33', '2025-01-02 18:52:01', '2025-01-10 12:38:33', 0, NULL, 100, '2025-01-20 00:00:00'),
(304, '147-dzfo', 2, '147-ujyo-01-2025', 147, 'Yuyun', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-16 17:52:07', '2025-01-02 18:52:02', '2025-01-16 17:52:07', 0, NULL, 202, '2025-01-15 00:00:00'),
(305, '148-nrm8', 2, '148-2h0k-01-2025', 148, 'Herman', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-09 18:44:04', '2025-01-02 18:52:02', '2025-01-09 18:44:04', 0, NULL, 89, '2025-01-07 00:00:00'),
(306, '149-vrr8', 2, '149-qsyo-01-2025', 149, 'Syarip Hidayat', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-05 10:58:25', '2025-01-02 18:52:02', '2025-01-05 10:58:25', 0, NULL, 38, '2025-01-12 00:00:00'),
(307, '150-dd4z', 2, '150-gij3-01-2025', 150, 'Dadi Kusnadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-18 07:38:09', '2025-01-02 18:52:02', '2025-01-18 07:38:09', 0, NULL, 215, '2025-01-17 00:00:00'),
(308, '151-do5m', 2, '151-34gp-01-2025', 151, 'Fera', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-04 07:20:59', '2025-01-02 18:52:02', '2025-01-04 07:20:59', 0, NULL, 26, '2025-01-28 00:00:00'),
(309, '152-gtal', 2, '152-jsly-01-2025', 152, 'Rifal Yudhi Pratama', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-03 13:09:56', '2025-01-02 18:52:02', '2025-01-03 13:09:56', 0, NULL, 18, '2025-01-26 00:00:00'),
(310, '153-lvtf', 2, '153-qy6f-01-2025', 153, 'Ani Sumarni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-07 17:44:19', '2025-01-02 18:52:02', '2025-01-07 17:44:19', 0, NULL, 75, '2025-01-28 00:00:00'),
(311, '154-u8km', 2, '154-omus-01-2025', 154, 'Hendri Gunawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-08 15:04:54', '2025-01-02 18:52:02', '2025-01-08 15:04:54', 0, NULL, 80, '2025-01-08 00:00:00'),
(312, '155-nepk', 2, '155-ymgj-01-2025', 155, 'Anggi', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-03 13:06:53', '2025-01-02 18:52:02', '2025-01-03 13:06:53', 0, NULL, 15, '2025-01-20 00:00:00'),
(313, '156-zfnv', 2, '156-jb1p-01-2025', 156, 'Sulis (Toko)', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 12:38:39', '2025-01-02 18:52:02', '2025-01-10 12:38:39', 0, NULL, 101, '2025-01-15 00:00:00'),
(314, '157-g5qn', 2, '157-aia9-01-2025', 157, 'Kuswandi', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Budi Kosasih', '2025-01-10 12:38:08', '2025-01-02 18:52:02', '2025-01-10 12:38:08', 0, NULL, 99, '2025-01-17 00:00:00'),
(315, '158-6ux8', 2, '158-scfb-01-2025', 158, 'Suci Ambar Sari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Admin ShaNet Fiber', '2025-01-15 17:50:06', '2025-01-02 18:52:02', '2025-01-15 17:50:06', 0, NULL, 182, '2025-01-28 00:00:00'),
(317, '160-vuhm', 2, '160-2oml-01-2025', 160, 'Sule', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-02 19:06:13', '2025-01-02 19:05:43', '2025-01-02 19:06:13', NULL, NULL, 3, NULL),
(318, '161-tnrx', 2, '161-9gqd-01-2025', 161, 'Bambang Sucipto', 'ShaNet 2 (15 Mbps)', 150000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2025-01-03 18:29:14', '2025-01-02 20:47:33', '2025-01-03 18:29:14', NULL, NULL, 22, NULL),
(319, '162-zlxi', 2, '162-mpto-01-2025', 162, 'Bunga Rinady Sandy', 'ShaNet 3 (30 Mbps)', 200000, 'Admin ShaNet Fiber', 'LS', 'Admin ShaNet Fiber', '2025-01-04 09:39:21', '2025-01-02 20:52:32', '2025-01-04 09:39:21', NULL, NULL, 28, NULL),
(320, '163-wde4', 2, '163-oztd-01-2025', 163, 'Budi Kosasih', 'Admin', 0, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-07 13:14:02', '2025-01-07 13:13:29', '2025-01-07 13:14:02', NULL, NULL, 73, NULL),
(321, '164-m9q5', 2, '164-q3br-01-2025', 164, 'Iin Indrawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 11:39:47', '2025-01-08 15:01:21', '2025-01-11 11:39:47', NULL, NULL, 128, NULL),
(322, '165-7c11', 2, '165-okl4-01-2025', 165, 'Dasih juarsih', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 16:42:06', '2025-01-08 15:03:19', '2025-01-11 16:42:06', NULL, NULL, 133, NULL),
(323, '166-nedt', 2, '166-l9df-01-2025', 166, 'POS RT.11/03 Krajan', 'Free WiFi', 0, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-08 15:32:48', '2025-01-08 15:32:12', '2025-01-08 15:32:48', NULL, NULL, 81, NULL),
(324, '167-jq1g', 2, '167-xaoz-01-2025', 167, 'Sa\'ad', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 09:27:47', '2025-01-08 15:35:55', '2025-01-11 09:27:47', NULL, NULL, 123, NULL),
(325, '168-vxxi', 2, '168-sdgq-01-2025', 168, 'Komarudin', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 10:26:21', '2025-01-08 18:13:28', '2025-01-11 10:26:21', NULL, NULL, 127, NULL),
(327, '170-uhla', 2, '170-xpz9-01-2025', 170, 'Cecep Sodikin', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 12:26:41', '2025-01-09 19:04:55', '2025-01-12 12:26:41', NULL, NULL, 140, NULL),
(328, '171-nvyc', 2, '171-xmzl-01-2025', 171, 'Maah Siti Komala', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 11:27:45', '2025-01-09 19:06:34', '2025-01-12 11:27:45', NULL, NULL, 139, NULL),
(329, '172-tzjc', 2, '172-09ij-01-2025', 172, 'Iwan Saepuloh', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 09:07:15', '2025-01-09 19:07:56', '2025-01-12 09:07:15', NULL, NULL, 136, NULL),
(330, '173-jwzw', 2, '173-cm2o-01-2025', 173, 'Saepudin', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 16:47:03', '2025-01-09 19:09:10', '2025-01-12 16:47:03', NULL, NULL, 145, NULL),
(331, '174-7ph6', 2, '174-uwxi-01-2025', 174, 'Sani Wartini', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 18:20:35', '2025-01-09 19:10:23', '2025-01-12 18:20:36', NULL, NULL, 148, NULL),
(332, '175-rhmp', 2, '175-slvr-01-2025', 175, 'Yati Haryati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 15:33:58', '2025-01-09 19:11:35', '2025-01-12 15:33:58', NULL, NULL, 143, NULL),
(333, '176-djnb', 2, '176-ddt8-01-2025', 176, 'Windi Gustiani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 16:16:09', '2025-01-09 19:12:31', '2025-01-12 16:16:09', NULL, NULL, 144, NULL),
(334, '177-zoct', 2, '177-6fxx-01-2025', 177, 'Virta Eka Yulistia', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 17:46:22', '2025-01-09 19:14:25', '2025-01-11 17:46:22', NULL, NULL, 134, NULL),
(335, '178-zhgt', 2, '178-n4qk-01-2025', 178, 'Budi Kosasih', 'Admin', 0, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-09 19:21:06', '2025-01-09 19:20:47', '2025-01-09 19:21:06', NULL, NULL, 91, NULL),
(336, '179-4zbg', 2, '179-qg6u-01-2025', 179, 'Alin Senia Trisiani', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-10 13:45:17', '2025-01-10 13:45:06', '2025-01-10 13:45:17', NULL, NULL, 104, NULL),
(337, '180-2rzq', 2, '180-qjmd-01-2025', 180, 'Ika Sartika', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-11 12:00:17', '2025-01-10 15:57:35', '2025-01-11 12:00:17', NULL, NULL, 130, NULL),
(338, '181-qyu1', 2, '181-wf7g-01-2025', 181, 'Nani Sumarni', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 18:03:39', '2025-01-11 19:20:39', '2025-01-12 18:03:39', NULL, NULL, 147, NULL),
(339, '182-kgyc', 2, '182-hmht-01-2025', 182, 'Rini Dewi Astuti', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 10:19:41', '2025-01-11 19:32:12', '2025-01-15 10:19:41', NULL, NULL, 170, NULL),
(340, '183-sscr', 2, '183-d1oh-01-2025', 183, 'Mohamad Ridwan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-12 12:57:06', '2025-01-12 12:56:49', '2025-01-12 12:57:06', NULL, NULL, 142, NULL),
(341, '184-eebg', 2, '184-dkbc-01-2025', 184, 'Muh Subhan', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-13 06:29:13', '2025-01-13 06:28:59', '2025-01-13 06:29:13', NULL, NULL, 151, NULL),
(342, '185-dyfx', 2, '185-lyke-01-2025', 185, 'Yayat Hidayat', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 10:19:47', '2025-01-13 09:18:42', '2025-01-15 10:19:47', NULL, NULL, 171, NULL),
(343, '186-z3yc', 2, '186-epns-01-2025', 186, 'Ida Susilawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-13 19:42:50', '2025-01-13 09:19:52', '2025-01-13 19:42:50', NULL, NULL, 157, NULL),
(344, '187-rtcj', 2, '187-kwly-01-2025', 187, 'Ratih Herawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 10:19:34', '2025-01-13 09:21:11', '2025-01-15 10:19:34', NULL, NULL, 169, NULL),
(345, '188-u1b8', 2, '188-wmqx-01-2025', 188, 'Heti Herawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-15 10:19:29', '2025-01-13 09:22:39', '2025-01-15 10:19:29', NULL, NULL, 168, NULL),
(346, '189-gxid', 2, '189-htdr-01-2025', 189, 'Serli Ardiawati', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-13 18:20:58', '2025-01-13 18:20:39', '2025-01-13 18:20:58', NULL, NULL, 156, NULL),
(347, '190-nmxx', 2, '190-uah1-01-2025', 190, 'Ahmad Zaenal Mustofa', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-17 11:39:05', '2025-01-17 11:38:46', '2025-01-17 11:39:05', NULL, NULL, 211, NULL),
(348, '191-eooi', 2, '191-otm9-01-2025', 191, 'Endang Rosidi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-19 20:31:56', '2025-01-19 16:11:09', '2025-01-19 20:31:56', NULL, NULL, 218, NULL),
(349, '192-vpgs', 2, '192-etwo-01-2025', 192, 'Ropik', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-19 17:05:47', '2025-01-19 17:04:08', '2025-01-19 17:05:47', NULL, NULL, 217, NULL),
(350, '193-dfng', 2, '193-xro0-01-2025', 193, 'Julian Ratu Malik Kusum', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-20 06:51:56', '2025-01-20 06:51:41', '2025-01-20 06:51:56', NULL, NULL, 219, NULL),
(351, '194-mu6i', 2, '194-3waz-01-2025', 194, 'Iwan Setiawan', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-21 10:17:50', '2025-01-21 10:17:38', '2025-01-21 10:17:50', NULL, NULL, 222, NULL),
(352, '195-7z80', 2, '195-3vdv-01-2025', 195, 'Sarkum', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-25 09:28:14', '2025-01-25 09:27:56', '2025-01-25 09:28:14', NULL, NULL, 224, NULL),
(353, '196-sih1', 2, '196-cso0-01-2025', 196, 'Hendi Suhendi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-28 10:41:23', '2025-01-28 10:30:45', '2025-01-28 10:41:23', NULL, NULL, 225, NULL),
(354, '197-eyby', 2, '197-jjr2-01-2025', 197, 'Krisnawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-28 10:41:33', '2025-01-28 10:31:59', '2025-01-28 10:41:33', NULL, NULL, 227, NULL),
(355, '198-snf5', 2, '198-oveh-01-2025', 198, 'Ryan Kurniawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-01-28 10:41:29', '2025-01-28 10:35:14', '2025-01-28 10:41:29', NULL, NULL, 226, NULL),
(356, '2-zboj', 6, '2-mh7f-02-2025', 2, 'Iis Sugiarti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:54', '2025-02-01 06:26:54', 0, NULL, NULL, '2025-02-12 00:00:00'),
(357, '3-uqfe', 6, '3-tucn-02-2025', 3, 'Asep Nasihin', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-02-01 07:08:44', '2025-02-01 06:26:54', '2025-02-01 07:08:44', 0, NULL, 236, '2025-02-15 00:00:00'),
(358, '4-10lv', 6, '4-cl72-02-2025', 4, 'Pak Surya', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-11 00:00:00'),
(359, '5-onu1', 6, '5-qlnn-02-2025', 5, 'Koesnadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-12 00:00:00'),
(360, '6-rg7p', 6, '6-zads-02-2025', 6, 'Cucu Suryani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-02 00:00:00'),
(361, '7-pjwu', 6, '7-slct-02-2025', 7, 'Sumarni', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-01 00:00:00'),
(362, '8-4sgx', 6, '8-kiks-02-2025', 8, 'Teh Darsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-15 00:00:00'),
(363, '9-mvwl', 6, '9-gsjr-02-2025', 9, 'Pak Eko', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-09 00:00:00'),
(364, '10-azw0', 6, '10-lsc7-02-2025', 10, 'Ustd. Ali Akbar', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-15 00:00:00'),
(365, '11-cefq', 6, '11-6tbb-02-2025', 11, 'Rita Susanti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:55', '2025-02-01 06:26:55', 0, NULL, NULL, '2025-02-01 00:00:00'),
(366, '12-4gkp', 6, '12-m1c8-02-2025', 12, 'Pak Suud', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-15 00:00:00'),
(367, '13-lgwg', 6, '13-vz7u-02-2025', 13, 'Mamah Anggi', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-07 00:00:00'),
(368, '14-62y4', 6, '14-flsi-02-2025', 14, 'Ai Solihah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-15 00:00:00'),
(369, '15-zenn', 6, '15-eeaz-02-2025', 15, 'Armiyanti', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-02-01 06:37:03', '2025-02-01 06:26:56', '2025-02-01 06:37:03', 0, NULL, 231, '2025-02-14 00:00:00'),
(370, '16-fyty', 6, '16-mxl0-02-2025', 16, 'Riska Azizah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-02 00:00:00'),
(371, '17-vbl0', 6, '17-2mbw-02-2025', 17, 'Iip', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-02 00:00:00'),
(372, '18-z44m', 6, '18-cb83-02-2025', 18, 'Ahmad Saepuloh', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-08 00:00:00'),
(373, '19-ipmi', 6, '19-ezav-02-2025', 19, 'Pak Lili', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-03 00:00:00'),
(374, '20-pul7', 6, '20-m1sx-02-2025', 20, 'Ibu Ayu', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:56', '2025-02-01 06:26:56', 0, NULL, NULL, '2025-02-15 00:00:00'),
(375, '21-w2o1', 6, '21-3yh6-02-2025', 21, 'Ratih Aisyah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-01 00:00:00');
INSERT INTO `billings` (`id`, `slug`, `billing_periode_id`, `billing_number`, `customer_id`, `customer_name`, `paket_name`, `paket_price`, `created_name`, `status`, `teller_name`, `payment_time`, `created_at`, `updated_at`, `tax`, `pay_later`, `receipt_id`, `due_date`) VALUES
(376, '22-twrt', 6, '22-en4d-02-2025', 22, 'AA Ramli (Rafka)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-04 00:00:00'),
(377, '23-8kdi', 6, '23-ee4s-02-2025', 23, 'Neni Nurhaeni', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-01 00:00:00'),
(378, '24-nhsd', 6, '24-gzqp-02-2025', 24, 'Etik Aprilia Ratna Sari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-15 00:00:00'),
(379, '25-zjh7', 6, '25-lkgf-02-2025', 25, 'Narsiah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-01 00:00:00'),
(380, '26-eqb8', 6, '26-uxbb-02-2025', 26, 'Novi Eka Purwaningsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-05 00:00:00'),
(381, '27-mzel', 6, '27-2ybm-02-2025', 27, 'Hasma Wati', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-10 00:00:00'),
(382, '28-lotm', 6, '28-kymx-02-2025', 28, 'Euis Ratnasari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:57', '2025-02-01 06:26:57', 0, NULL, NULL, '2025-02-15 00:00:00'),
(383, '29-w5bp', 6, '29-v8w2-02-2025', 29, 'Adi Amoy', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:58', '2025-02-01 06:26:58', 0, NULL, NULL, '2025-02-15 00:00:00'),
(384, '30-o5du', 6, '30-ykfj-02-2025', 30, 'A Zaky Alattas', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:59', '2025-02-01 06:26:59', 0, NULL, NULL, '2025-02-11 00:00:00'),
(385, '31-rvjl', 6, '31-dhiu-02-2025', 31, 'Teh Ai', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:59', '2025-02-01 06:26:59', 0, NULL, NULL, '2025-02-15 00:00:00'),
(386, '32-gmcr', 6, '32-0b46-02-2025', 32, 'Pak Slamet', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:59', '2025-02-01 06:26:59', 0, NULL, NULL, '2025-02-11 00:00:00'),
(387, '33-v5sa', 6, '33-erpe-02-2025', 33, 'Debbie Febriana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:59', '2025-02-01 06:26:59', 0, NULL, NULL, '2025-02-15 00:00:00'),
(388, '34-wate', 6, '34-pstn-02-2025', 34, 'Hafis', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:59', '2025-02-01 06:26:59', 0, NULL, NULL, '2025-02-15 00:00:00'),
(389, '35-musv', 6, '35-vwfw-02-2025', 35, 'Triyanti', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:26:59', '2025-02-01 06:26:59', 0, NULL, NULL, '2025-02-15 00:00:00'),
(390, '36-a6vd', 6, '36-5oyj-02-2025', 36, 'H. M Abu Rasyid. AS. SH', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-01 00:00:00'),
(391, '37-lots', 6, '37-mexq-02-2025', 37, 'Yuan Firmansyah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-15 00:00:00'),
(392, '38-2rzs', 6, '38-ssql-02-2025', 38, 'Dede Suherman', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-02-01 06:35:29', '2025-02-01 06:27:00', '2025-02-01 06:35:29', 0, NULL, 229, '2025-02-15 00:00:00'),
(393, '39-xi3q', 6, '39-afxn-02-2025', 39, 'Ida Siti', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-01 00:00:00'),
(394, '40-9zxr', 6, '40-8prv-02-2025', 40, 'Wandi Suhendi', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-12 00:00:00'),
(395, '41-olsd', 6, '41-j7rd-02-2025', 41, 'Muhammad Fahrizal', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-03 00:00:00'),
(396, '42-bgfy', 6, '42-w11f-02-2025', 42, 'Edi Suswanto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-10 00:00:00'),
(397, '43-5odo', 6, '43-xei3-02-2025', 43, 'Tri Nurhidayati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-15 00:00:00'),
(398, '44-qahv', 6, '44-4vjw-02-2025', 44, 'Pak Aam', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:00', '2025-02-01 06:27:00', 0, NULL, NULL, '2025-02-06 00:00:00'),
(399, '45-tbfj', 6, '45-r8ea-02-2025', 45, 'Rohani Rusmiati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:01', '2025-02-01 06:27:01', 0, NULL, NULL, '2025-02-12 00:00:00'),
(400, '46-jj8x', 6, '46-azul-02-2025', 46, 'Mulyana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:01', '2025-02-01 06:27:01', 0, NULL, NULL, '2025-02-01 00:00:00'),
(401, '47-pzwx', 6, '47-72tr-02-2025', 47, 'Sri Sulastri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:01', '2025-02-01 06:27:01', 0, NULL, NULL, '2025-02-10 00:00:00'),
(402, '48-d9hp', 6, '48-hw4j-02-2025', 48, 'Hendrizal T (Geprek Jogja)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:02', '2025-02-01 06:27:02', 0, NULL, NULL, '2025-02-15 00:00:00'),
(403, '49-c13h', 6, '49-wz8x-02-2025', 49, 'Ferry Kurniawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:02', '2025-02-01 06:27:02', 0, NULL, NULL, '2025-02-15 00:00:00'),
(404, '50-hhdv', 6, '50-b5wc-02-2025', 50, 'Nok Reni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:02', '2025-02-01 06:27:02', 0, NULL, NULL, '2025-02-11 00:00:00'),
(405, '51-1ikm', 6, '51-vigy-02-2025', 51, 'Ahmad Sugianto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:02', '2025-02-01 06:27:02', 0, NULL, NULL, '2025-02-15 00:00:00'),
(406, '52-gnzc', 6, '52-juva-02-2025', 52, 'Maharani Widyaningrum', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:02', '2025-02-01 06:27:02', 0, NULL, NULL, '2025-02-13 00:00:00'),
(407, '53-ck5p', 6, '53-2w84-02-2025', 53, 'Linda', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:02', '2025-02-01 06:27:02', 0, NULL, NULL, '2025-02-10 00:00:00'),
(408, '54-lz6o', 6, '54-jdgd-02-2025', 54, 'Agista Utari', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-15 00:00:00'),
(409, '55-kp6l', 6, '55-iuhx-02-2025', 55, 'Agustian Setiadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-07 00:00:00'),
(410, '56-mftp', 6, '56-6keq-02-2025', 56, 'Pak Iyan', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-10 00:00:00'),
(411, '57-b2yq', 6, '57-u3s8-02-2025', 57, 'Nur Fakriah Putri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-11 00:00:00'),
(412, '58-lvsw', 6, '58-sryi-02-2025', 58, 'Pak Jepri', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-12 00:00:00'),
(413, '59-moil', 6, '59-r9w2-02-2025', 59, 'Bu Ani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-15 00:00:00'),
(414, '60-lkc3', 6, '60-2vf7-02-2025', 60, 'Yeyet Abdulrachman', 'ShaNet 4 (50 Mbps)', 250000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-01 00:00:00'),
(415, '61-blug', 6, '61-cpvu-02-2025', 61, 'Pak Dendi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:03', '2025-02-01 06:27:03', 0, NULL, NULL, '2025-02-15 00:00:00'),
(416, '62-6diz', 6, '62-vxxj-02-2025', 62, 'Marya Ningsih', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:04', '2025-02-01 06:27:04', 0, NULL, NULL, '2025-02-01 00:00:00'),
(417, '63-p7ou', 6, '63-wrq0-02-2025', 63, 'Pak Kurdi (Rumah)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:04', '2025-02-01 06:27:04', 0, NULL, NULL, '2025-02-01 00:00:00'),
(418, '64-8gxl', 6, '64-g9lg-02-2025', 64, 'Kodrat (GOR ADHIGA)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:05', '2025-02-01 06:27:05', 0, NULL, NULL, '2025-02-05 00:00:00'),
(419, '65-2zsm', 6, '65-lnib-02-2025', 65, 'Lulu Andriani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:05', '2025-02-01 06:27:05', 0, NULL, NULL, '2025-02-08 00:00:00'),
(420, '66-sdqi', 6, '66-7zkj-02-2025', 66, 'Ma Aisah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:05', '2025-02-01 06:27:05', 0, NULL, NULL, '2025-02-08 00:00:00'),
(421, '67-vzfi', 6, '67-mcpu-02-2025', 67, 'Alya Iklima Hidayah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:05', '2025-02-01 06:27:05', 0, NULL, NULL, '2025-02-08 00:00:00'),
(422, '68-tlte', 6, '68-qlma-02-2025', 68, 'Pak Kurdi (Kontrakan)', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:05', '2025-02-01 06:27:05', 0, NULL, NULL, '2025-02-01 00:00:00'),
(423, '69-sz0a', 6, '69-squr-02-2025', 69, 'Teh Enen', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:05', '2025-02-01 06:27:05', 0, NULL, NULL, '2025-02-15 00:00:00'),
(424, '71-hetc', 6, '71-lxpq-02-2025', 71, 'Asep Topik Hidayat', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:06', '2025-02-01 06:27:06', 0, NULL, NULL, '2025-02-15 00:00:00'),
(425, '72-ninq', 6, '72-qo4g-02-2025', 72, 'Egy Laksma Suandi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:06', '2025-02-01 06:27:06', 0, NULL, NULL, '2025-02-15 00:00:00'),
(426, '73-jot7', 6, '73-iwp8-02-2025', 73, 'Nazri Nasier', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:06', '2025-02-01 06:27:06', 0, NULL, NULL, '2025-02-04 00:00:00'),
(427, '74-swep', 6, '74-clct-02-2025', 74, 'Moh. Gian Firtyanza', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:06', '2025-02-01 06:27:06', 0, NULL, NULL, '2025-02-09 00:00:00'),
(428, '75-wlfh', 6, '75-f5gr-02-2025', 75, 'Intan Ayu Sri Wahyuni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-15 00:00:00'),
(429, '76-k3bo', 6, '76-8tqo-02-2025', 76, 'Nurhayati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-15 00:00:00'),
(430, '77-awzs', 6, '77-u2ix-02-2025', 77, 'Irvan Mohammad Regent', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-01 00:00:00'),
(431, '78-jxnx', 6, '78-wk3e-02-2025', 78, 'Robi Anggara', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-01 00:00:00'),
(432, '80-u73b', 6, '80-dbfk-02-2025', 80, 'Iis Nurjanah', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-15 00:00:00'),
(433, '81-eayv', 6, '81-mzdv-02-2025', 81, 'Agus Rahmat (Azril)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-08 00:00:00'),
(434, '82-2pta', 6, '82-cp31-02-2025', 82, 'Neng Helawati', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-06 00:00:00'),
(435, '83-ag0l', 6, '83-8y0i-02-2025', 83, 'Rinji Maulani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:07', '2025-02-01 06:27:07', 0, NULL, NULL, '2025-02-01 00:00:00'),
(436, '84-xbcc', 6, '84-y0qv-02-2025', 84, 'Iman Nurjamansyah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-01 00:00:00'),
(437, '85-4awz', 6, '85-1u0b-02-2025', 85, 'Dani Ramdani', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-01 00:00:00'),
(438, '86-fh4r', 6, '86-cgwe-02-2025', 86, 'Mujinah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-01 00:00:00'),
(439, '87-7iv6', 6, '87-ojkp-02-2025', 87, 'Rodiah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-01 00:00:00'),
(440, '88-jj4s', 6, '88-84tb-02-2025', 88, 'Gilang Gumilang', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-01 00:00:00'),
(441, '89-pfss', 6, '89-lslx-02-2025', 89, 'Fadilah Nurhasanah', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-01 00:00:00'),
(442, '90-burd', 6, '90-e3jo-02-2025', 90, 'Mimin Rukmini', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-02-01 06:35:52', '2025-02-01 06:27:08', '2025-02-01 06:35:52', 0, NULL, 230, '2025-02-01 00:00:00'),
(443, '91-plfu', 6, '91-m9es-02-2025', 91, 'Tati Mulyati', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-08 00:00:00'),
(444, '92-71wk', 6, '92-e4nv-02-2025', 92, 'Siam Suroso', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:08', '2025-02-01 06:27:08', 0, NULL, NULL, '2025-02-07 00:00:00'),
(445, '93-kwdt', 6, '93-1f4m-02-2025', 93, 'Rini Seminar', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-10 00:00:00'),
(446, '94-wztj', 6, '94-fewc-02-2025', 94, 'Risna Suhertini', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-02 00:00:00'),
(447, '95-ehze', 6, '95-ogr9-02-2025', 95, 'Dewi Sutriani', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-02 00:00:00'),
(448, '96-dlqy', 6, '96-jgiy-02-2025', 96, 'Mariati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-06 00:00:00'),
(449, '97-lmtf', 6, '97-5rra-02-2025', 97, 'Devi Sevtiawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-05 00:00:00'),
(450, '98-ode1', 6, '98-vzdj-02-2025', 98, 'Tatang Mulyana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-08 00:00:00'),
(451, '99-nf75', 6, '99-2tsw-02-2025', 99, 'Tuti Helawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-15 00:00:00'),
(452, '100-5jrx', 6, '100-8dx8-02-2025', 100, 'Yayah Komariah', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:09', '2025-02-01 06:27:09', 0, NULL, NULL, '2025-02-15 00:00:00'),
(453, '101-zz7f', 6, '101-d82g-02-2025', 101, 'H Imon', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:10', '2025-02-01 06:27:10', 0, NULL, NULL, '2025-02-08 00:00:00'),
(454, '102-io64', 6, '102-6aka-02-2025', 102, 'Julianto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:10', '2025-02-01 06:27:10', 0, NULL, NULL, '2025-02-12 00:00:00'),
(455, '103-wkf0', 6, '103-eazy-02-2025', 103, 'Wahyu Maulana', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:10', '2025-02-01 06:27:10', 0, NULL, NULL, '2025-02-01 00:00:00'),
(456, '104-qbxd', 6, '104-hcq3-02-2025', 104, 'Rika Rahmawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:10', '2025-02-01 06:27:10', 0, NULL, NULL, '2025-02-01 00:00:00'),
(457, '105-udjk', 6, '105-zvah-02-2025', 105, 'Neneng Suratni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:10', '2025-02-01 06:27:10', 0, NULL, NULL, '2025-02-01 00:00:00'),
(458, '106-ebiu', 6, '106-nkth-02-2025', 106, 'Misbah Toko Bangunan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:10', '2025-02-01 06:27:10', 0, NULL, NULL, '2025-02-04 00:00:00'),
(459, '107-ywb9', 6, '107-u7fr-02-2025', 107, 'Jajang Salahudin', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:10', '2025-02-01 06:27:10', 0, NULL, NULL, '2025-02-06 00:00:00'),
(460, '108-0pfd', 6, '108-pla0-02-2025', 108, 'Neng Siti', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:11', '2025-02-01 06:27:11', 0, NULL, NULL, '2025-02-15 00:00:00'),
(461, '109-io20', 6, '109-lxbr-02-2025', 109, 'Dedi Haerudi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:11', '2025-02-01 06:27:11', 0, NULL, NULL, '2025-02-01 00:00:00'),
(462, '110-19ub', 6, '110-9sbs-02-2025', 110, 'Amania Kusumah', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-01 00:00:00'),
(463, '111-oeei', 6, '111-oaan-02-2025', 111, 'Nurul Aeni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-01 00:00:00'),
(464, '112-wxov', 6, '112-dx8e-02-2025', 112, 'Pak Ojak', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-01 00:00:00'),
(465, '113-nbif', 6, '113-zzr0-02-2025', 113, 'Redi Natisna', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-01 00:00:00'),
(466, '114-vg2p', 6, '114-79yt-02-2025', 114, 'Agus Hermanto', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-08 00:00:00'),
(467, '115-i6gw', 6, '115-ck0h-02-2025', 115, 'Pak Agus', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-15 00:00:00'),
(468, '116-stoe', 6, '116-toph-02-2025', 116, 'Wahid', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-15 00:00:00'),
(469, '117-qyhe', 6, '117-9qkx-02-2025', 117, 'Sularno', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-01 00:00:00'),
(470, '118-uskg', 6, '118-eetz-02-2025', 118, 'Wida', 'ShaNet 1 (10 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-03 00:00:00'),
(471, '119-q47d', 6, '119-u0lh-02-2025', 119, 'Iqbal Budi Prasetya', 'ShaNet 1 (10 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:12', '2025-02-01 06:27:12', 0, NULL, NULL, '2025-02-04 00:00:00'),
(472, '120-mw0p', 6, '120-ihot-02-2025', 120, 'Usman Abdul Sobari', 'ShaNet 1 (10 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:13', '2025-02-01 06:27:13', 0, NULL, NULL, '2025-02-04 00:00:00'),
(473, '121-0wlj', 6, '121-u9ju-02-2025', 121, 'Nuryani', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:13', '2025-02-01 06:27:13', 0, NULL, NULL, '2025-02-06 00:00:00'),
(474, '122-4sy5', 6, '122-zu3x-02-2025', 122, 'Rosidah', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:13', '2025-02-01 06:27:13', 0, NULL, NULL, '2025-02-15 00:00:00'),
(475, '123-q4lw', 6, '123-mgkq-02-2025', 123, 'Siti Mariam', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:13', '2025-02-01 06:27:13', 0, NULL, NULL, '2025-02-15 00:00:00'),
(476, '124-rcw0', 6, '124-kju8-02-2025', 124, 'Iis Ismawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-15 00:00:00'),
(477, '125-vx4q', 6, '125-v557-02-2025', 125, 'Desi Nurdianti', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-15 00:00:00'),
(478, '126-n5sa', 6, '126-inei-02-2025', 126, 'Adis', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-15 00:00:00'),
(479, '127-x60b', 6, '127-xft7-02-2025', 127, 'Satim Silegar', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-15 00:00:00'),
(480, '128-vtwb', 6, '128-pphq-02-2025', 128, 'Imar Sulaeman', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-15 00:00:00'),
(481, '129-3joo', 6, '129-smue-02-2025', 129, 'Heni', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-15 00:00:00'),
(482, '130-g2le', 6, '130-p9bk-02-2025', 130, 'Juju Juarsih', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-01 00:00:00'),
(483, '131-nep6', 6, '131-zjtx-02-2025', 131, 'Juhdi', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:14', '2025-02-01 06:27:14', 0, NULL, NULL, '2025-02-01 00:00:00'),
(484, '132-a050', 6, '132-9hyz-02-2025', 132, 'Cici', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-09 00:00:00'),
(485, '133-sh1k', 6, '133-55ra-02-2025', 133, 'Pepen', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-10 00:00:00'),
(486, '134-swf5', 6, '134-j5mf-02-2025', 134, 'Wayanto', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-10 00:00:00'),
(487, '135-ebqa', 6, '135-5x65-02-2025', 135, 'Heri Suandi', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-14 00:00:00'),
(488, '136-sco1', 6, '136-dsai-02-2025', 136, 'Miung', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-14 00:00:00'),
(489, '137-wnft', 6, '137-t1vw-02-2025', 137, 'Dadan Hamdani', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-14 00:00:00'),
(490, '138-ar2i', 6, '138-6ybe-02-2025', 138, 'Okis', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-15 00:00:00'),
(491, '139-dkje', 6, '139-abn0-02-2025', 139, 'RT Oji', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-15 00:00:00'),
(492, '140-0aok', 6, '140-izmc-02-2025', 140, 'Suntari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:15', '2025-02-01 06:27:15', 0, NULL, NULL, '2025-02-15 00:00:00'),
(493, '141-w6uv', 6, '141-argk-02-2025', 141, 'Engko', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-15 00:00:00'),
(494, '142-divn', 6, '142-gkzf-02-2025', 142, 'Arya Cahyana Putra', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-01 00:00:00'),
(495, '143-wd6d', 6, '143-ya1x-02-2025', 143, 'Bajuri Enci', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-04 00:00:00'),
(496, '144-lgpj', 6, '144-kyez-02-2025', 144, 'Salmah Saleha', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-08 00:00:00'),
(497, '145-xthr', 6, '145-jovt-02-2025', 145, 'Darma Wirawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-13 00:00:00'),
(498, '146-lhdv', 6, '146-9xnv-02-2025', 146, 'Sulis (Rumah)', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-15 00:00:00'),
(499, '147-edxf', 6, '147-efkz-02-2025', 147, 'Yuyun', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-15 00:00:00'),
(500, '148-6mll', 6, '148-ez94-02-2025', 148, 'Herman', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-07 00:00:00'),
(501, '149-gdqs', 6, '149-dhdg-02-2025', 149, 'Syarip Hidayat', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-12 00:00:00'),
(502, '150-68xw', 6, '150-rjjk-02-2025', 150, 'Dadi Kusnadi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:16', '2025-02-01 06:27:16', 0, NULL, NULL, '2025-02-15 00:00:00'),
(503, '151-db7v', 6, '151-9hac-02-2025', 151, 'Fera', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:17', '2025-02-01 06:27:17', 0, NULL, NULL, '2025-02-01 00:00:00'),
(504, '152-yrak', 6, '152-mcip-02-2025', 152, 'Rifal Yudhi Pratama', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:17', '2025-02-01 06:27:17', 0, NULL, NULL, '2025-02-15 00:00:00'),
(505, '153-lyve', 6, '153-bexw-02-2025', 153, 'Ani Sumarni', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:17', '2025-02-01 06:27:17', 0, NULL, NULL, '2025-02-01 00:00:00'),
(506, '154-ftjv', 6, '154-jbz3-02-2025', 154, 'Hendri Gunawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:17', '2025-02-01 06:27:17', 0, NULL, NULL, '2025-02-08 00:00:00'),
(507, '155-nfyd', 6, '155-shpp-02-2025', 155, 'Anggi', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:18', '2025-02-01 06:27:18', 0, NULL, NULL, '2025-02-15 00:00:00'),
(508, '156-pdan', 6, '156-melp-02-2025', 156, 'Sulis (Toko)', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:18', '2025-02-01 06:27:18', 0, NULL, NULL, '2025-02-15 00:00:00'),
(509, '157-de2v', 6, '157-vtqf-02-2025', 157, 'Kuswandi', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:18', '2025-02-01 06:27:18', 0, NULL, NULL, '2025-02-15 00:00:00'),
(510, '158-f95f', 6, '158-h3m4-02-2025', 158, 'Suci Ambar Sari', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:18', '2025-02-01 06:27:18', 0, NULL, NULL, '2025-02-15 00:00:00'),
(511, '160-ew4z', 6, '160-zd4o-02-2025', 160, 'Sule', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:18', '2025-02-01 06:27:18', 0, NULL, NULL, '2025-02-02 00:00:00'),
(512, '161-3bhm', 6, '161-2uix-02-2025', 161, 'Bambang Sucipto', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:18', '2025-02-01 06:27:18', 0, NULL, NULL, '2025-02-03 00:00:00'),
(513, '162-jnec', 6, '162-e2dn-02-2025', 162, 'Bunga Rinady Sandy', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:18', '2025-02-01 06:27:18', 0, NULL, NULL, '2025-02-04 00:00:00'),
(514, '163-pvzm', 6, '163-cwvf-02-2025', 163, 'Budi Kosasih', 'Admin', 0, 'Dian Ardiansyah', 'LS', NULL, NULL, '2025-02-01 06:27:19', '2025-02-01 06:27:19', 0, NULL, NULL, '2025-02-07 00:00:00'),
(515, '164-jjtt', 6, '164-ruu1-02-2025', 164, 'Iin Indrawati', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:19', '2025-02-01 06:27:19', 0, NULL, NULL, '2025-02-11 00:00:00'),
(516, '165-mu9e', 6, '165-cw60-02-2025', 165, 'Dasih Juarsih', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:19', '2025-02-01 06:27:19', 0, NULL, NULL, '2025-02-11 00:00:00'),
(517, '166-bzoo', 6, '166-bw9g-02-2025', 166, 'POS RT.11/03 Krajan', 'Free WiFi', 0, 'Dian Ardiansyah', 'LS', NULL, NULL, '2025-02-01 06:27:19', '2025-02-01 06:27:19', 0, NULL, NULL, '2025-02-08 00:00:00'),
(518, '167-e3dj', 6, '167-kzkm-02-2025', 167, 'Sa\'ad', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:19', '2025-02-01 06:27:19', 0, NULL, NULL, '2025-02-11 00:00:00'),
(519, '168-gums', 6, '168-tjqd-02-2025', 168, 'Komarudin', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:20', '2025-02-01 06:27:20', 0, NULL, NULL, '2025-02-11 00:00:00'),
(520, '170-elrq', 6, '170-ljxa-02-2025', 170, 'Cecep Sodikin', 'ShaNet 1 (10 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:22', '2025-02-01 06:27:22', 0, NULL, NULL, '2025-02-12 00:00:00'),
(521, '171-nr7r', 6, '171-kiai-02-2025', 171, 'Maah Siti Komala', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:22', '2025-02-01 06:27:22', 0, NULL, NULL, '2025-02-12 00:00:00'),
(522, '172-yrwj', 6, '172-6jgt-02-2025', 172, 'Iwan Saepuloh', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:22', '2025-02-01 06:27:22', 0, NULL, NULL, '2025-02-12 00:00:00'),
(523, '173-g1ed', 6, '173-cius-02-2025', 173, 'Saepudin', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:22', '2025-02-01 06:27:22', 0, NULL, NULL, '2025-02-12 00:00:00'),
(524, '174-xkdp', 6, '174-ylad-02-2025', 174, 'Sani Wartini', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:22', '2025-02-01 06:27:22', 0, NULL, NULL, '2025-02-12 00:00:00'),
(525, '175-fhkh', 6, '175-lqms-02-2025', 175, 'Yati Haryati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:22', '2025-02-01 06:27:22', 0, NULL, NULL, '2025-02-12 00:00:00'),
(526, '176-5zhk', 6, '176-u2sy-02-2025', 176, 'Windi Gustiani', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-12 00:00:00'),
(527, '177-3bqf', 6, '177-dfup-02-2025', 177, 'Virta Eka Yulistia', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-11 00:00:00'),
(528, '178-uy0b', 6, '178-3zuv-02-2025', 178, 'Budi Kosasih', 'Admin', 0, 'Dian Ardiansyah', 'LS', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-09 00:00:00'),
(529, '179-gxit', 6, '179-qjad-02-2025', 179, 'Alin Senia Trisiani', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-10 00:00:00'),
(530, '180-y5sa', 6, '180-0bqi-02-2025', 180, 'Ika Sartika', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-11 00:00:00'),
(531, '181-o6ji', 6, '181-l41y-02-2025', 181, 'Nani Sumarni', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-12 00:00:00'),
(532, '182-ijlt', 6, '182-resv-02-2025', 182, 'Deni', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-15 00:00:00'),
(533, '183-ebt3', 6, '183-dnds-02-2025', 183, 'Mohamad Ridwan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-12 00:00:00'),
(534, '184-z9tq', 6, '184-qehu-02-2025', 184, 'Muh Subhan', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'LS', 'Acep', '2025-02-01 06:55:50', '2025-02-01 06:27:23', '2025-02-01 06:55:50', 0, NULL, 235, '2025-02-15 00:00:00'),
(535, '185-tzpb', 6, '185-kpo6-02-2025', 185, 'Yayat Hidayat', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-15 00:00:00'),
(536, '186-oxlb', 6, '186-lw5m-02-2025', 186, 'Ida Susilawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:23', '2025-02-01 06:27:23', 0, NULL, NULL, '2025-02-13 00:00:00'),
(537, '187-6a3d', 6, '187-wc5x-02-2025', 187, 'Ratih Herawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:24', '2025-02-01 06:27:24', 0, NULL, NULL, '2025-02-15 00:00:00'),
(538, '188-7qsh', 6, '188-33ju-02-2025', 188, 'Heti Herawati', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:24', '2025-02-01 06:27:24', 0, NULL, NULL, '2025-02-15 00:00:00'),
(539, '189-rktg', 6, '189-esew-02-2025', 189, 'Serli Ardiawati', 'ShaNet 3 (30 Mbps)', 200000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:24', '2025-02-01 06:27:24', 0, NULL, NULL, '2025-02-13 00:00:00'),
(540, '190-xyzz', 6, '190-89yz-02-2025', 190, 'Ahmad Zaenal Mustofa', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:24', '2025-02-01 06:27:24', 0, NULL, NULL, '2025-02-15 00:00:00'),
(541, '191-uxq2', 6, '191-i1n9-02-2025', 191, 'Endang Rosidi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:24', '2025-02-01 06:27:24', 0, NULL, NULL, '2025-02-15 00:00:00'),
(542, '192-x2by', 6, '192-exzf-02-2025', 192, 'Ropik', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:25', '2025-02-01 06:27:25', 0, NULL, NULL, '2025-02-15 00:00:00'),
(543, '193-9wqa', 6, '193-zx7p-02-2025', 193, 'Julian Ratu Malik Kusum', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:25', '2025-02-01 06:27:25', 0, NULL, NULL, '2025-02-15 00:00:00'),
(544, '194-xccb', 6, '194-ctan-02-2025', 194, 'Iwan Setiawan', 'ShaNet 1 (10 Mbps)', 125000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:25', '2025-02-01 06:27:25', 0, NULL, NULL, '2025-02-01 00:00:00'),
(545, '195-xc5g', 6, '195-twzb-02-2025', 195, 'Sarkum', 'ShaNet 0 (7 Mbps)', 100000, 'Dian Ardiansyah', 'BL', NULL, NULL, '2025-02-01 06:27:25', '2025-02-01 06:27:25', 0, NULL, NULL, '2025-02-01 00:00:00'),
(546, '196-jl72', 6, '196-it3y-02-2025', 196, 'Hendi Suhendi', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-02-01 06:38:16', '2025-02-01 06:27:25', '2025-02-01 06:38:16', 0, NULL, 232, '2025-02-01 00:00:00'),
(547, '197-sixo', 6, '197-tyuc-02-2025', 197, 'Krisnawati', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-02-01 06:39:06', '2025-02-01 06:27:25', '2025-02-01 06:39:06', 0, NULL, 234, '2025-02-01 00:00:00'),
(548, '198-wfzi', 6, '198-408z-02-2025', 198, 'Ryan Kurniawan', 'ShaNet 2 (15 Mbps)', 150000, 'Dian Ardiansyah', 'LS', 'Dian Ardiansyah', '2025-02-01 06:38:45', '2025-02-01 06:27:25', '2025-02-01 06:38:45', 0, NULL, 233, '2025-02-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `billing_periodes`
--

CREATE TABLE `billing_periodes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing_periodes`
--

INSERT INTO `billing_periodes` (`id`, `name`, `created_at`, `updated_at`, `year`, `month`) VALUES
(2, 'January 2025', '2025-01-02 18:51:54', '2025-01-02 18:51:54', '2025', 'January'),
(6, 'February 2025', '2025-02-01 06:26:54', '2025-02-01 06:26:54', '2025', 'February');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paket_id` bigint UNSIGNED NOT NULL,
  `mikrotik_id` bigint UNSIGNED NOT NULL,
  `internet_service` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secret_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_ppp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ppp_type_id` bigint UNSIGNED DEFAULT NULL,
  `simpleque_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `simpleque_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_static` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mac_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interface` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arp_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_list_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `contact` text COLLATE utf8mb4_unicode_ci,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `activation` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `primary_server` bigint DEFAULT NULL,
  `discount` decimal(5,2) DEFAULT '0.00',
  `customer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_date` timestamp NULL DEFAULT NULL,
  `caller_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `internet_service`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `simpleque_id`, `simpleque_name`, `ip_static`, `mac_address`, `interface`, `arp_id`, `address_list_id`, `address`, `contact`, `disabled`, `activation`, `created_at`, `updated_at`, `deleted_at`, `primary_server`, `discount`, `customer_id`, `activation_date`, `caller_id`, `user_id`) VALUES
(2, 'Iis Sugiarti', '2021021201-at-iissugiarti-9c4c', '$2y$10$WmJwgIrELmq/.DCigJmsP.YnNw8KWNy5GUia05AHXiCfGpBRfaeKy', NULL, 1, 1, 'ppp', '*3', '2021021201@iissugiarti', '2021021201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6287779954399', 'false', 'true', '2024-12-17 10:07:12', '2025-01-06 12:27:14', NULL, NULL, '0.00', 'SNF01-002', '2021-02-12 08:00:00', NULL, 1),
(3, 'Asep Nasihin', '2021022001-at-asep-fjgp', '$2y$10$6ajAOwsVhUJbFj4MgHa77.prM3MioTv8oSoyn7/pw.nSQJmojbrIi', NULL, 1, 1, 'ppp', '*4', '2021022001@asep', '2021022001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6281321875971', 'false', 'true', '2024-12-17 10:07:12', '2025-01-06 12:23:45', NULL, NULL, '0.00', 'SNF01-003', '2021-02-15 08:00:00', NULL, 1),
(4, 'Pak Surya', '2021041101-at-surya-b00p', '$2y$10$v8UowO6/ztx3lK7HqH3Ofe.iRhK4unpK..G8puKmr.m7uP.sUQbpe', NULL, 3, 1, 'ppp', '*5', '2021041101@surya', '2021041101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '6281282400741', 'false', 'true', '2024-12-17 10:07:13', '2025-01-06 12:08:14', NULL, NULL, '0.00', 'SNF01-004', '2021-04-11 08:00:00', NULL, 1),
(5, 'Koesnadi', '2021071201-at-koesnadi-kq5f', '$2y$10$L2AJhL3ls4b4YBkb3UriD.oSIpkw4fRr19fCarg1EpDfV/oI8IDT2', NULL, 3, 1, 'ppp', '*6', '2021071201@koesnadi', '2021071201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '628974848114', 'false', 'true', '2024-12-17 10:07:13', '2025-01-06 12:29:11', NULL, NULL, '0.00', 'SNF01-005', '2021-07-12 08:00:00', NULL, 1),
(6, 'Cucu Suryani', '2021100201-at-cucu-kqtu', '$2y$10$371/iYudjOFCOe9Lp.CN7OginRgMhbnBjB5JhDMRFO4cYYwUWqeN.', 'innayahazizah00@gmail.com', 3, 1, 'ppp', '*7', '2021100201@cucu', '2021100201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 No. 37 Ciseureuh Purwakarta', '6282299409442', 'false', 'true', '2024-12-17 10:07:13', '2025-01-06 12:06:55', NULL, NULL, '0.00', 'SNF01-006', '2021-10-02 08:00:00', NULL, 1),
(7, 'Sumarni', '2022043001-at-sumarni-k6xu', '$2y$10$.qsLSR4jWZyQ5u0dSibusORxaKmmlHDKv.gTrXY.8CxK78IqWdS2a', 'sum402791@gmail.com', 1, 1, 'ppp', '*8', '2022043001@sumarni', '2022043001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '6289531041230', 'false', 'true', '2024-12-17 10:07:13', '2025-01-06 12:06:26', NULL, NULL, '0.00', 'SNF01-007', '2022-05-01 08:00:00', NULL, 1),
(8, 'Teh Darsih', '2022091801-at-darsih-2cj1', '$2y$10$Ak/llTgMyspb7u7/VXATnuWBNmRusjN/zuxl8M6ZKQzzKXRlt/seq', NULL, 1, 1, 'ppp', '*A', '2022091801@darsih', '2022091801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6285798509660', 'false', 'true', '2024-12-17 10:07:13', '2025-01-06 12:32:50', NULL, NULL, '0.00', 'SNF01-008', '2022-09-15 08:00:00', NULL, 1),
(9, 'Pak Eko', '2022100901-at-eko-za81', '$2y$10$SxbuM8jCmFb3snuyaF.TCOpGW4CP/Ze304h7itkKJlh6ZlXg6iAwe', NULL, 1, 1, 'ppp', '*B', '2022100901@eko', '2022100901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '62895360111123', 'false', 'true', '2024-12-17 10:07:14', '2025-01-06 12:04:09', NULL, NULL, '0.00', 'SNF01-009', '2022-10-09 08:00:00', NULL, 1),
(10, 'Ustd. Ali Akbar', '2022102001-at-ustdali-mxwh', '$2y$10$V1JHldc.8YEzTWSZA3rU7O2v1xF3w10TNNyWfgAULlFVQaItbmnQe', NULL, 1, 1, 'ppp', '*D', '2022102001@ustdali', '2022102001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6283836212877', 'false', 'true', '2024-12-17 10:07:14', '2025-01-06 12:19:09', NULL, NULL, '0.00', 'SNF01-010', '2022-10-15 08:00:00', NULL, 1),
(11, 'Rita Susanti', '100221029-at-rita-dp4x', '$2y$10$QfoHOrlZuCAeZXGXjW8YOOaZ.UVG34ZqgyrqiSHha/jzhjVOTmb1.', NULL, 1, 1, 'ppp', '*F', '100221029@rita', '100221029', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6285759233749', 'false', 'true', '2024-12-17 10:07:14', '2025-01-06 12:32:28', NULL, NULL, '0.00', 'SNF01-011', '2022-11-01 08:00:00', NULL, 1),
(12, 'Pak Suud', '2024011801-at-suud-bgqm', '$2y$10$Zvj2SJRgu85uB7oicKSRAODQjc0scl.80gqP9s/CJY1rs1MkT4AkK', NULL, 3, 1, 'ppp', '*10', '2024011801@suud', '2024011801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '62818209745', 'false', 'true', '2024-12-17 10:07:14', '2025-01-06 12:31:11', NULL, NULL, '0.00', 'SNF01-012', '2024-01-15 08:00:00', NULL, 1),
(13, 'Mamah Anggi', '2022110701-at-anggi-ijn1', '$2y$10$S7F4KVr6Vky1bhN2a2yaGOFKP/tkQPg1LMfsb20SrwXlj3qubrQ6K', NULL, 1, 1, 'ppp', '*12', '2022110701@mmhanggi', '2022110701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '628995430767', 'false', 'true', '2024-12-17 10:07:14', '2025-01-21 16:31:52', NULL, NULL, '0.00', 'SNF01-013', '2022-11-07 08:00:00', NULL, 1),
(14, 'Ai Solihah', '2022112001-at-adhiga-kfbj', '$2y$10$btrdD4J9fDCIcLnLC8nWHOA0cbOWNTU6tXHi65zsdNtpeFR1SuYoS', NULL, 3, 1, 'ppp', '*14', '2022112001@adhiga', '2022112001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6285220677026', 'false', 'true', '2024-12-17 10:07:15', '2025-01-06 12:21:53', NULL, NULL, '0.00', 'SNF01-014', '2022-11-15 08:00:00', NULL, 1),
(15, 'Armiyanti', '2021111401-at-armiyanti-pr2t', '$2y$10$WVY8LbhJChHmixt8f5Y3VOxPNTEgvGWBe.aO2/ZW68NGS1iNX4nqm', NULL, 4, 1, 'ppp', '*15', '2021111401@armiyanti', '2021111401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6281385195803', 'false', 'true', '2024-12-17 10:07:15', '2025-01-06 12:22:56', NULL, NULL, '0.00', 'SNF01-015', '2021-11-14 08:00:00', NULL, 1),
(16, 'Riska Azizah', '2023010201-at-rizka-c9ff', '$2y$10$zojX5f/aVBw2/rTcQjqYPedWQpf9.3v6MLdp5wDT53JcwfjxyjsvW', NULL, 1, 1, 'ppp', '*18', '2023010201@rizka', '2023010201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Griya Asri Blok K7 No. 7 RT 010/011 Ciseureuh Purwakarta', '6285647808733', 'false', 'true', '2024-12-17 10:07:15', '2025-01-02 15:48:05', NULL, NULL, '0.00', 'SNF01-016', '2023-01-02 08:00:00', NULL, 1),
(17, 'Iip', '2023040201-at-iip-0hfa', '$2y$10$cFNJNNSkI6LBZRbauaa8hurywawokoNwHSF7mmx36c460tIebxeSO', NULL, 3, 1, 'ppp', '*1B', '2023040201@iip', '2023040201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '628382843852', 'false', 'true', '2024-12-17 10:07:15', '2025-01-06 12:13:33', NULL, NULL, '0.00', 'SNF01-017', '2023-04-02 08:00:00', NULL, 1),
(18, 'Ahmad Saepuloh', '2023040801-at-ahmadraja-empb', '$2y$10$tfuEfkKJiJxigc2aC8NJouM68IKnWko0k6hXNm1AdQ7PRkaL.JCOW', 'ahmadsae13@gmail.com', 3, 1, 'ppp', '*1C', '2023040801@ahmadraja', '2023040801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6289691150846', 'false', 'true', '2024-12-17 10:07:15', '2025-01-06 12:19:45', NULL, NULL, '0.00', 'SNF01-018', '2023-04-08 08:00:00', NULL, 1),
(19, 'Pak Lili', '2023050301-at-lamjaya-aqa3', '$2y$10$tedTynnhgZKxucMqjoQaF.sG5do0a1MWxtzJ9gUcj6zGBH48ppPB6', NULL, 3, 1, 'ppp', '*1E', '2023050301@lamjaya', '2023050301', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6287825791053', 'false', 'true', '2024-12-17 10:07:15', '2025-01-06 12:03:06', NULL, NULL, '0.00', 'SNF01-019', '2023-05-03 08:00:00', NULL, 1),
(20, 'Ibu Ayu', '2023071701-at-ibuayu-zq1e', '$2y$10$Eb1XQmVeZVEry9x5rX0JG.HWj5dUXL7GHakkCOZY7wbEfJIKwtWqC', NULL, 3, 1, 'ppp', '*20', '2023071701@ibuayu', '2023071701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6287779845917', 'false', 'true', '2024-12-17 10:07:16', '2025-01-06 12:26:21', NULL, NULL, '0.00', 'SNF01-020', '2023-07-15 08:00:00', NULL, 1),
(21, 'Ratih Aisyah', '2023072901-at-ratih-kaz4', '$2y$10$U1NPgL04UVOFU39DQgYJNOZgrgER7VjQPXndxOzt8R.UQbiSYPk.y', NULL, 1, 1, 'ppp', '*22', '2023072901@ratih', '2023072901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', '62895405332171', 'false', 'true', '2024-12-17 10:07:16', '2025-01-02 16:36:05', NULL, NULL, '0.00', 'SNF01-021', '2023-08-01 08:00:00', NULL, 1),
(22, 'AA Ramli (Rafka)', '2023080401-at-rafka-zz4h', '$2y$10$lh2areDfbe9aeOzhUp6ODeynOTIPRNB77GTSjPoWR.4SIljwdPoPu', NULL, 3, 1, 'ppp', '*23', '2023080401@rafka', '2023080401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', '6285210226726', 'false', 'true', '2024-12-17 10:07:16', '2025-01-06 12:36:01', NULL, NULL, '0.00', 'SNF01-022', '2023-08-04 08:00:00', NULL, 1),
(23, 'Neni Nurhaeni', '2023110101-at-neni-bwij', '$2y$10$hNaO2zVHRCWexz7zdmBpP.bdZbA6h873HCnBX6xk7Gnt5ttnkijdu', 'neninurhaeni1990@gmail.com', 1, 1, 'ppp', '*2A', '2023110101@neni', '2023110101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '6289684514949', 'false', 'true', '2024-12-17 10:07:16', '2025-01-06 12:15:38', NULL, NULL, '0.00', 'SNF01-023', '2023-11-01 08:00:00', NULL, 1),
(24, 'Etik Aprilia Ratna Sari', '2023111501-at-aprilia-mqcd', '$2y$10$z4TNdnEVJdF5orNBHPx9QeHmBvrAAG6fp5H0Wq6vAHJUT3jtR.8HG', 'etikaprilia8@gmail.com', 3, 1, 'ppp', '*2C', '2023111501@aprilia', '2023111501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '6285727463177', 'false', 'true', '2024-12-17 10:07:16', '2025-01-06 12:12:25', NULL, NULL, '0.00', 'SNF01-024', '2023-11-15 08:00:00', NULL, 1),
(25, 'Narsiah', '2023010101-at-narsiah-g3nv', '$2y$10$zHKqKflP1Ljty.3eEV5kSehbTj1QCO2haa5UEP70Kqe1LWPhyuyg6', 'narsiiahnay@gmail.com', 1, 1, 'ppp', '*2D', '2023010101@narsiah', '2023010101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6289608997773', 'false', 'true', '2024-12-17 10:07:17', '2025-01-06 12:02:20', NULL, NULL, '0.00', 'SNF01-025', '2023-01-01 08:00:00', NULL, 1),
(26, 'Novi Eka Purwaningsih', '2023120501-at-novi-pt6b', '$2y$10$yOq3coWBtn1Ge3AUQ8JELOyh/NOXqcFOLZstEs/55H9sFUbxJK5Bm', NULL, 1, 1, 'ppp', '*2E', '2023120501@novi', '2023120501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '6283892406496', 'false', 'true', '2024-12-17 10:07:17', '2025-01-06 12:07:26', NULL, NULL, '0.00', 'SNF01-026', '2023-12-05 08:00:00', NULL, 1),
(27, 'Hasma Wati', '2023121001-at-hasma-g1cc', '$2y$10$Y708R1B2hMDZ8k0rcmDQQOzcngjM8imu6vSKtT0MXnQY51TqxqI/2', NULL, 1, 1, 'ppp', '*2F', '2023121001@hasma', '2023121001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6281808073633', 'false', 'true', '2024-12-17 10:07:17', '2025-01-06 12:10:10', NULL, NULL, '0.00', 'SNF01-027', '2023-12-10 08:00:00', NULL, 1),
(28, 'Euis Ratnasari', '2023121601-at-euis-nqco', '$2y$10$YP3K.FzsHJGfJ7GDDFLgmOv2BTDr/fMYujXwxC4BB5W8oVwZC.PhO', 'euisratnasari@gmail.com', 3, 1, 'ppp', '*30', '2023121601@euis', '2023121601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6289698432244', 'false', 'true', '2024-12-17 10:07:17', '2025-01-02 16:41:40', NULL, NULL, '0.00', 'SNF01-028', '2023-12-15 08:00:00', NULL, 1),
(29, 'Adi Amoy', '2023121701-at-amoy-mdqv', '$2y$10$gk/b/iCjZsWNcF2V/xHMQu2Krlqb9MYL5foIDp5FJ4Cd5X9eVpG86', NULL, 1, 1, 'ppp', '*31', '2023121701@amoy', '2023121701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6285715468128', 'false', 'true', '2024-12-17 10:07:17', '2025-01-02 16:37:12', NULL, NULL, '0.00', 'SNF01-029', '2023-12-15 08:00:00', NULL, 1),
(30, 'A Zaky Alattas', '2024021101-at-pakzaky-1nzr', '$2y$10$TJmuwSux0.bHT6oqtlZ.Vu.wpGMXFqYWLq.qq34Obgwbot/7KRn/e', NULL, 3, 1, 'ppp', '*3D', '2024021101@pakzaky', '2024021101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '628111972299', 'false', 'true', '2024-12-17 10:07:18', '2025-01-06 12:14:55', NULL, NULL, '0.00', 'SNF01-030', '2024-02-11 08:00:00', NULL, 1),
(31, 'Teh Ai', '2024021901-at-tehai-koyx', '$2y$10$5uPjSq/wPOU9Y6EWgDFL5.tBLjsnz1qu2Mri5hXiq10kNswjA19Zm', NULL, 1, 1, 'ppp', '*42', '2024021901@tehai', '2024021901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6283824439838', 'false', 'true', '2024-12-17 10:07:18', '2025-01-06 12:11:30', NULL, NULL, '0.00', 'SNF01-031', '2024-02-15 08:00:00', NULL, 1),
(32, 'Pak Slamet', '2024031101-at-slamet-8b1b', '$2y$10$RspfI.7wA7cI7nSjjk6MLOV7.TG8aP1uOb4E4dZlLpju9l7GUUs4e', NULL, 3, 1, 'ppp', '*4F', '2024031101@slamet', '2024031101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', '6289671475636', 'false', 'true', '2024-12-17 10:07:18', '2025-01-02 15:18:34', NULL, NULL, '0.00', 'SNF01-032', '2024-03-11 08:00:00', NULL, 1),
(33, 'Debbie Febriana', '2023122001-at-debbie-tqhk', '$2y$10$kaQ9/.w9O9XA2xSLKiQJD.5SIQXFVML4Y8occQkgE8xT37i0AHkQS', 'fdebi707@gmail.com', 3, 1, 'ppp', '*51', '2023122001@debbie', '2023122001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6283862780951', 'false', 'true', '2024-12-17 10:07:18', '2025-01-06 12:24:31', NULL, NULL, '0.00', 'SNF01-033', '2023-12-15 08:00:00', NULL, 1),
(34, 'Hafis', '2022101901-at-hafis-juwc', '$2y$10$aNLlxXsjy6MA38g4OuE4p.qPqZbOKxt4s4iga.AkN7gaOklLRvcJS', NULL, 1, 1, 'ppp', '*61', '2022101901@hafis', '2022101901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '628818253308', 'false', 'true', '2024-12-17 10:07:18', '2025-01-06 12:25:19', NULL, NULL, '0.00', 'SNF01-034', '2022-10-15 08:00:00', NULL, 1),
(35, 'Triyanti', '2024041701-at-yanti-4nrc', '$2y$10$U59I/HeEMP0CXYJ.GzRlu.Xw3WN7QKJKVdQvgrMob5HMsFTffrg2W', 'zakipwk2144@gmail.com', 4, 1, 'ppp', '*65', '2024041701@yanti', '2024041701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Gg. Beringin RT. 077/008 Nagrikaler Purwakarta', '6283124358737', 'false', 'true', '2024-12-17 10:07:19', '2025-01-01 20:14:43', NULL, NULL, '0.00', 'SNF01-035', '2024-04-15 08:00:00', NULL, 1),
(36, 'H. M Abu Rasyid. AS. SH', '2024110101-at-cahayaqolbu-tne8', '$2y$10$0DV3kn0iTpG87KzrWHDx9eqey19BrnJO.Vm3w5nMs5kBziFrdZ4ta', NULL, 4, 1, 'ppp', '*6D', '2024110101@cahayaqolbu', '2024110101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '6281316153848', 'false', 'true', '2024-12-17 10:07:19', '2025-01-06 12:13:52', NULL, NULL, '0.00', 'SNF01-036', '2024-11-01 08:00:00', NULL, 1),
(37, 'Yuan Firmansyah', '2024012501-at-yuan-hqsf', '$2y$10$D27QwoKp6ICVqvcbMtFWt.oskiJr8v7UUAGKdtdQk5w3YFRn5jJQa', NULL, 3, 1, 'ppp', '*6E', '2024012501@yuan', '2024012501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6285793309918', 'false', 'true', '2024-12-17 10:07:19', '2025-01-06 12:12:41', NULL, NULL, '0.00', 'SNF01-037', '2024-01-15 08:00:00', NULL, 1),
(38, 'Dede Suherman', '2021012001-at-dede-0prh', '$2y$10$ceXIfNK6EBHm/lTsokA9eOye8EGtkkGquuptcoMXd5DoQ7/vT/036', 'djhonexsekutipmuda41@gmail.com', 1, 1, 'ppp', '*70', '2021012001@dede', '2021012001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6281909404442', 'false', 'true', '2024-12-17 10:07:19', '2025-01-02 16:41:01', NULL, NULL, '0.00', 'SNF01-038', '2021-01-15 08:00:00', NULL, 1),
(39, 'Ida Siti', '2024050101-at-siti-urin', '$2y$10$P.qxjNhQQmE8avos8vG.m.DtOyY6tGOPdu1BJokZMdMz68fn7KcXC', NULL, 1, 1, 'ppp', '*71', '2024050101@siti', '2024050101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', '6282210830975', 'false', 'true', '2024-12-17 10:07:19', '2025-01-06 12:13:01', NULL, NULL, '0.00', 'SNF01-039', '2024-05-01 08:00:00', NULL, 1),
(40, 'Wandi Suhendi', '2023111201-at-wandi-7pks', '$2y$10$YBMcs4Lgiq0nU4z2htANg.25zGc8DykRD7wxLBjQvLJIhoKb3l7Ma', NULL, 1, 1, 'ppp', '*72', '2023111201@wandi', '2023111201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6287779838230', 'false', 'true', '2024-12-17 10:07:19', '2025-01-06 12:12:07', NULL, NULL, '0.00', 'SNF01-040', '2023-11-12 08:00:00', NULL, 1),
(41, 'Muhammad Fahrizal', '2024050301-at-fahrizal-gmqt', '$2y$10$q.WGuvddk2qjN50IGuqrNux9jOTdoYj2yfAg0S71d5wvJV.SCYWcq', 'muhammadfahrijal707@gmail.com', 3, 1, 'ppp', '*75', '2024050301@fahrizal', '2024050301', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '6283135700855', 'false', 'true', '2024-12-17 10:07:20', '2025-01-06 12:05:05', NULL, NULL, '0.00', 'SNF01-041', '2024-05-03 08:00:00', NULL, 1),
(42, 'Edi Suswanto', '2024051001-at-pakedi-l30d', '$2y$10$1LoYTSShAKvZRSy.1hWFHO5mxOBkzmxV3/Mw.iPeakbyuCRdPFDyG', NULL, 3, 1, 'ppp', '*76', '2024051003@pakedi', '2024051003', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '6281904094579', 'false', 'true', '2024-12-17 10:07:20', '2025-01-06 12:07:41', NULL, NULL, '0.00', 'SNF01-042', '2024-05-10 08:00:00', NULL, 1),
(43, 'Tri Nurhidayati', '2022121701-at-nurhidayati-eodb', '$2y$10$d5T6tdWT2dyp6lFKEd3bd.2Xcuv7Jmtl4BQxb.DzI/e5qXrVikaXm', 'trinur0501@gmail.com', 3, 1, 'ppp', '*78', '2022121701@nurhidayati', '2022121701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '6289698033060', 'false', 'true', '2024-12-17 10:07:20', '2025-01-06 12:09:15', NULL, NULL, '0.00', 'SNF01-043', '2022-12-15 08:00:00', NULL, 1),
(44, 'Pak Aam', '2022110601-at-aam-zvqq', '$2y$10$u7jIcYpVOubrN5MpoBlTAOn5OFePrUO7YqzJWckebzQgmJl7Mjz5O', NULL, 3, 1, 'ppp', '*79', '2022110601@aam', '2022110601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6287719301665', 'false', 'true', '2024-12-17 10:07:20', '2025-01-06 12:30:53', NULL, NULL, '0.00', 'SNF01-044', '2022-11-06 08:00:00', NULL, 1),
(45, 'Rohani Rusmiati', '2021031201-at-rohani-n16r', '$2y$10$FsRcdf3e61DX7jIy2os1YeNWiYxbU6E4bpM.XhtvzIEUnowdtFQqW', 'kebulisikefipurwakarta@gmail.com', 3, 1, 'ppp', '*7A', '2021031201@rohani', '2021031201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', '6287722273673', 'false', 'true', '2024-12-17 10:07:20', '2025-01-06 12:06:39', NULL, NULL, '0.00', 'SNF01-045', '2021-03-12 08:00:00', NULL, 1),
(46, 'Mulyana', '2023102901-at-mulya-arrv', '$2y$10$VHOpYNQzaEI5BwyOgiT56e6SC/PyhJLcJHjFCzp5kMS4DprmgWaMy', NULL, 3, 1, 'ppp', '*7C', '2023102901@mulya', '2023102901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6283817367818', 'false', 'true', '2024-12-17 10:07:21', '2025-01-06 12:29:55', NULL, NULL, '0.00', 'SNF01-046', '2023-11-01 10:07:12', NULL, 1),
(47, 'Sri Sulastri', '2024071001-at-sulastri-qdhs', '$2y$10$H9XNgWpTvUTaJaLJZPyYR.X7Cv2anwBWIU.QWooFWhGQWtOH1f3gm', NULL, 3, 1, 'ppp', '*89', '2024071001@sulastri', '2024071001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '6285624127735', 'false', 'true', '2024-12-17 10:07:21', '2025-01-06 12:04:27', NULL, NULL, '0.00', 'SNF01-047', '2024-07-10 10:07:12', NULL, 1),
(48, 'Hendrizal T (Geprek Jogja)', '2024062101-at-hendrizal-n1og', '$2y$10$bFkpXuLvghMnHcDxsxonPuybeJtK2XSs10BZhbav.iyExKG2SW2EC', NULL, 3, 1, 'ppp', '*8B', '2024062101@hendrizal', '2024062101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6287820284786', 'false', 'true', '2024-12-17 10:07:21', '2025-01-02 16:41:56', NULL, NULL, '0.00', 'SNF01-048', '2024-06-15 08:00:00', NULL, 1),
(49, 'Ferry Kurniawan', '2023072501-at-ustdfery-iwyq', '$2y$10$JZSqrRGrlfTFf7nIBB1FvuniAUfIsuW8qS2Jr17ThziXN6tIz9zRe', NULL, 3, 1, 'ppp', '*8C', '2023072501@ustdfery', '2023072501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '6287825303864', 'false', 'true', '2024-12-17 10:07:21', '2025-01-06 12:14:34', NULL, NULL, '0.00', 'SNF01-049', '2023-07-15 08:00:00', NULL, 1),
(50, 'Nok Reni', '2024071101-at-nokreni-3mw3', '$2y$10$PM0ropQwwcRqrfjOj0gho.D7D/uK1hzf6z3B5W5ezZgC7HEGVujOK', NULL, 3, 1, 'ppp', '*8D', '2024071101@nokreni', '2024071101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '6283175503164', 'false', 'true', '2024-12-17 10:07:21', '2025-01-06 12:07:57', NULL, NULL, '0.00', 'SNF01-050', '2024-07-11 10:07:12', NULL, 1),
(51, 'Ahmad Sugianto', '2024072501-at-ahmad-8vi2', '$2y$10$I3eU9JgvKRwNDNAw692b9e.ug8Em11RpgYO/Yzh7HIOhT3zfvi0bq', NULL, 3, 1, 'ppp', '*93', '2024072501@ahmad', '2024072501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '6287860734025', 'false', 'true', '2024-12-17 10:07:22', '2025-01-06 12:04:51', NULL, NULL, '0.00', 'SNF01-051', '2024-07-15 08:00:00', NULL, 1),
(52, 'Maharani Widyaningrum', '2023091301-at-maharani-oj3p', '$2y$10$4WCDO1BlG6U8MAPqJdU1QuWzONIjxvUpMa5eThQTnjiDbyjWj4Y.W', NULL, 3, 1, 'ppp', '*96', '2023091301@maharani', '2023091301', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '6281912512312', 'false', 'true', '2024-12-17 10:07:22', '2025-01-06 12:36:55', NULL, NULL, '0.00', 'SNF01-052', '2023-09-13 10:07:12', NULL, 1),
(53, 'Linda', '2024031001-at-linda-evnw', '$2y$10$khAJfI0bCxHi1aUdzQ88V.aeB6r5IfokYnPqfZdCkQMRsO8H97Jd6', NULL, 2, 1, 'ppp', '*97', '2024031001@linda', '2024031001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6283853508908', 'false', 'true', '2024-12-17 10:07:22', '2025-01-06 12:01:44', NULL, NULL, '0.00', 'SNF01-053', '2024-03-10 10:07:12', NULL, 1),
(54, 'Agista Utari', '2024022501-at-agis-lgds', '$2y$10$53cHQR4W.XDthV/LddaD2usqmnCIKFwUZYxaR7jl.WKKK4LEddglm', 'utariagista@gmail.com', 1, 1, 'ppp', '*98', '2024022501@agis', '2024022501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6283113359456', 'false', 'true', '2024-12-17 10:07:22', '2025-01-02 16:39:43', NULL, NULL, '0.00', 'SNF01-054', '2024-02-15 08:00:00', NULL, 1),
(55, 'Agustian Setiadi', '2024080701-at-agustian-zk9l', '$2y$10$wO2dfDNrRFqiu/54lXq9B.cwOOYLJG.4Z/1OdpJawpXyj0bdeE8ae', 'agustiansetiadi703@gmail.com', 3, 1, 'ppp', '*99', '2024080701@agustian', '2024080701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6285285092449', 'false', 'true', '2024-12-17 10:07:22', '2025-01-02 16:40:09', NULL, NULL, '0.00', 'SNF01-055', '2024-08-07 10:07:12', NULL, 1),
(56, 'Pak Iyan', '2023091001-at-iyan-lkzz', '$2y$10$gHqcnkfw6f2EXG.s5H.oZO3TgwO.oCf/99j2GXUwKFbChyV0iOPFG', NULL, 4, 1, 'ppp', '*9A', '2023091001@iyan', '2023091001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '6287879990597', 'false', 'true', '2024-12-17 10:07:22', '2025-01-06 12:14:11', NULL, NULL, '0.00', 'SNF01-056', '2023-09-10 10:07:12', NULL, 1),
(57, 'Nur Fakriah Putri', '2024081101-at-nurfakriah-xsfo', '$2y$10$MZPmc.cPv5QrLJARs023b.5P9PJ8fLT9C5vf8ye2dYhov6GBAeDG2', 'nurfakriahp@gmail.com', 3, 1, 'ppp', '*9B', '2024081101@nurfakriah', '2024081101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '62882000336411', 'false', 'true', '2024-12-17 10:07:23', '2025-01-06 12:37:42', NULL, NULL, '0.00', 'SNF01-057', '2024-08-11 10:07:12', NULL, 1),
(58, 'Pak Jepri', '2024061201-at-jepri-rxup', '$2y$10$cSZ.ErugBzK8LavYUNc6LeQDjPGaQgSF7kIMgLRGQZBypPQgi4NNG', NULL, 3, 1, 'ppp', '*9D', '2024061201@jepri', '2024061201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '6282125598764', 'false', 'true', '2024-12-17 10:07:23', '2025-01-06 12:19:28', NULL, NULL, '0.00', 'SNF01-058', '2024-06-12 10:07:12', NULL, 1),
(59, 'Bu Ani', '2024081701-at-ibuani-oihu', '$2y$10$wpnZ4wjf772yJwe6HGT27.cU7P80J8CP7HH.hCz8MMtJeGYODIlLi', NULL, 3, 1, 'ppp', '*9F', '2024081701@ibuani', '2024081701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6287794516139', 'false', 'true', '2024-12-17 10:07:23', '2025-01-02 16:40:46', NULL, NULL, '0.00', 'SNF01-059', '2024-08-15 08:00:00', NULL, 1),
(60, 'Yeyet Abdulrachman', '2023060101-at-abdulrahman-dtb2', '$2y$10$y5zXYQHXGQjbHnolJDcRGeXup.OZdU8KVPzrHwMVtVKscvg8WfMLO', NULL, 5, 1, 'ppp', '*A0', '2023060101@abdulrahman', '2023060101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', '6287879678412', 'false', 'true', '2024-12-17 10:07:23', '2025-01-02 16:35:44', NULL, NULL, '0.00', 'SNF01-060', '2023-06-01 10:07:12', NULL, 1),
(61, 'Pak Dendi', '2024082401-at-dendi-hjoi', '$2y$10$SN4XXod3xJMbE2HMoxM9ROvmteA4TF2m1rzuVRL5R4PQCc5eAc2I2', NULL, 3, 1, 'ppp', '*A2', '2024082401@dendi', '2024082401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', '6281385192239', 'false', 'true', '2024-12-17 10:07:23', '2025-01-06 12:01:13', NULL, NULL, '0.00', 'SNF01-061', '2024-08-15 08:00:00', NULL, 1),
(62, 'Marya Ningsih', '2024090101-at-ningsih-a8uf', '$2y$10$7j1Yf.W4BTnLeZo8sDToG.oSy1JMcMl7lfbEHjYduaVHwSEaXBe5S', 'maryaningsih@gmail.com', 1, 1, 'ppp', '*A4', '2024090103@ningsih', '2024090103', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', '6287840530823', 'false', 'true', '2024-12-17 10:07:24', '2025-01-06 12:06:09', NULL, NULL, '0.00', 'SNF01-062', '2024-09-01 10:07:12', NULL, 1),
(63, 'Pak Kurdi (Rumah)', '2024090101-at-pakkurdi-9lmx', '$2y$10$9.iU8vyDQK/bFyDVYUwf1OMXpY2zfNicankBA5BZyxdTj.CVMqk76', NULL, 3, 1, 'ppp', '*A5', '2024090101@pakkurdi', '2024090101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6282217713000', 'false', 'true', '2024-12-17 10:07:24', '2025-01-02 18:49:08', NULL, NULL, '0.00', 'SNF01-063', '2024-09-01 10:07:12', NULL, 1),
(64, 'Kodrat (GOR ADHIGA)', '2024030501-at-goradhigha-inrl', '$2y$10$sbNlIpZRxvaU93LEf.KUXeq94r9ChbfSefeSnW4sv9RqKL25vpSyq', NULL, 3, 1, 'ppp', '*A6', '2024030501@goradhigha', '2024030501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', NULL, 'false', 'true', '2024-12-17 10:07:24', '2025-01-01 16:19:21', NULL, NULL, '0.00', 'SNF01-064', '2024-03-05 10:07:12', NULL, 1),
(65, 'Lulu Andriani', '2024090801-at-lulu-o1pc', '$2y$10$oUbqaZuQiQj2ZkrFSPudF.H5BWv5VIi7QB80QSEHkwT0JdAKg80lq', NULL, 3, 1, 'ppp', '*A7', '2024090801@lulu', '2024090801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6283103801464', 'false', 'true', '2024-12-17 10:07:24', '2025-01-06 12:03:24', NULL, NULL, '0.00', 'SNF01-065', '2024-09-08 10:07:12', NULL, 1),
(66, 'Ma Aisah', '2024090802-at-aisah-fnmq', '$2y$10$2p2qG8LmTpM4VwKnMm7c3u2YFkFFGeufwTakrpL//jz0AbDy9a3y6', NULL, 3, 1, 'ppp', '*A8', '2024090802@aisah', '2024090802', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '62895806314100', 'false', 'true', '2024-12-17 10:07:24', '2025-01-02 20:56:45', NULL, NULL, '0.00', 'SNF01-066', '2024-09-08 10:07:12', NULL, 1),
(67, 'Alya Iklima Hidayah', '2024090803-at-alhikam-4kyp', '$2y$10$5y8X.vw9DLP25.CdkhR2LuZYx21euLiqWV0mKge0Zubf/jaiFPf1C', 'alyaiklima@gmail.com', 3, 1, 'ppp', '*A9', '2024090803@alhikam', '2024090803', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Panorama Indah B2-4 RT 013/013 Purwakarta', '6285926103222', 'false', 'true', '2024-12-17 10:07:25', '2025-01-01 20:14:21', NULL, NULL, '0.00', 'SNF01-067', '2024-09-08 10:07:12', NULL, 1),
(68, 'Pak Kurdi (Kontrakan)', '2024090102-at-pakkurdi-ybiu', '$2y$10$qIo2zNOLSzwIGl78JXdoPOz1kbVM3JvVnnMANNjq8/4eJjI8YZXFG', NULL, 1, 1, 'ppp', '*AC', '2024090102@pakkurdi', '2024090102', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6282117411456', 'false', 'true', '2024-12-17 10:07:25', '2025-01-02 18:50:22', NULL, NULL, '0.00', 'SNF01-068', '2024-09-01 10:07:12', NULL, 1),
(69, 'Teh Enen', '2024091701-at-enen-snmf', '$2y$10$C5WH8SxmbE4lIix4OOTYf.YMzG0177XtoqYBdL05F13BMtA4Gp6O2', NULL, 1, 1, 'ppp', '*AD', '2024091701@enen', '2024091701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6287879788949', 'false', 'true', '2024-12-17 10:07:25', '2025-01-02 16:44:20', NULL, NULL, '0.00', 'SNF01-069', '2024-09-15 08:00:00', NULL, 1),
(71, 'Asep Topik Hidayat', '2024092201-at-topik-gnqr', '$2y$10$N3Ywwuo166qum5LxbCbY..ELbXlpU2AxLbtft0knSMVqVJOb2/oa.', NULL, 1, 1, 'ppp', '*AF', '2024092201@topik', '2024092201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6281992515445', 'false', 'true', '2024-12-17 10:07:25', '2025-01-02 16:40:30', NULL, NULL, '0.00', 'SNF01-071', '2024-09-15 08:00:00', NULL, 1),
(72, 'Egy Laksma Suandi', '2024092401-at-egy-9vxl', '$2y$10$VKzS9oLI0mSWttjnJV6JguejrTIAdin/vz2oL/qBjkOkTRVI6ASsy', NULL, 3, 1, 'ppp', '*B0', '2024092401@egy', '2024092401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6289635533013', 'false', 'true', '2024-12-17 10:07:25', '2025-01-02 16:41:25', NULL, NULL, '0.00', 'SNF01-072', '2024-09-15 08:00:00', NULL, 1),
(73, 'Nazri Nasier', '2024100401-at-nazri-pzhm', '$2y$10$a8zC3l/z.Dt4VE5lWjrOlePxMv97o51HsLTBcDBSKu1S9YIGadMDa', 'nasiernazri@gmail.com', 3, 1, 'ppp', '*B5', '2024100401@nazri', '2024100401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6285210433997', 'false', 'true', '2024-12-17 10:07:26', '2025-01-06 12:30:22', NULL, NULL, '0.00', 'SNF01-073', '2024-10-04 10:07:12', NULL, 1),
(74, 'Moh. Gian Firtyanza', '2024100901-at-gian-5ksl', '$2y$10$AnyOlZXIpQJLi93ouMBhuesWoCqDMWwCw6PD1qPrjsGcRGhfrbdjC', NULL, 3, 1, 'ppp', '*B7', '2024100901@gian', '2024100901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Griya Asri Blok D2 No. 15 Ciseureuh Purwakarta', '6282122332067', 'false', 'true', '2024-12-17 10:07:26', '2025-01-02 16:42:47', NULL, NULL, '0.00', 'SNF01-074', '2024-10-09 10:07:12', NULL, 1),
(75, 'Intan Ayu Sri Wahyuni', '2024101701-at-intan-kir4', '$2y$10$gBQ9vpfuYpwNUfhCkn1dxeM16EKIaRDpu6egFMgcXNtHN6dcyVP3G', 'can.cc660@gmail.com', 3, 1, 'ppp', '*B8', '2024101701@intan', '2024101701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', '6289661700462', 'false', 'true', '2024-12-17 10:07:26', '2025-01-02 15:08:59', NULL, NULL, '0.00', 'SNF01-075', '2024-10-15 08:00:00', NULL, 1),
(76, 'Nurhayati', '2024102001-at-nurhayati-pthz', '$2y$10$1WMLlPbGCMuGvSyZdmCnueWjb7SJi7sIveSDOiVaTgezPm9UVp33m', NULL, 3, 1, 'ppp', '*B9', '2024102001@nurhayati', '2024102001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', '6289510273231', 'false', 'true', '2024-12-17 10:07:26', '2025-01-06 12:05:24', NULL, NULL, '0.00', 'SNF01-076', '2024-10-15 08:00:00', NULL, 1),
(77, 'Irvan Mohammad Regent', '2024102701-at-irvan-czhg', '$2y$10$II/Z64XYWSx0aM9y5LIlzez5T/eRy8HXehV/WxVm.2T2evB.MIUMC', 'irvanregent@gmail.com', 4, 1, 'ppp', '*BA', '2024102701@irvan', '2024102701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Griya Asri Blok K1 No. 10 Ciseureuh Purwakarta', '6289622707203', 'false', 'true', '2024-12-17 10:07:26', '2025-01-09 17:43:48', NULL, NULL, '0.00', 'SNF01-077', '2024-11-01 10:07:12', NULL, 1),
(78, 'Robi Anggara', '2024103001-at-robi-jsap', '$2y$10$DagCPvdPVs.o6bSFujWfDuCKOcvFctlllnc64wZMbGL74Ldrb63bO', 'robi702.ra@gmail.com', 3, 1, 'ppp', '*BD', '2024103001@robi', '2024103001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', '6287879621757', 'false', 'true', '2024-12-17 10:07:27', '2025-01-02 16:36:16', NULL, NULL, '0.00', 'SNF01-078', '2024-11-01 10:07:12', NULL, 1),
(80, 'Iis Nurjanah', '2024112601-at-iisnurjanah-ibzw', '$2y$10$/3X4.QVIL1asCe4mlScGVe9Rly3H0j3BycVD.bs7Zxt9f4u/H2YFe', 'utengiis@gmail.com', 2, 1, 'ppp', '*C0', '2024112601@iisnurjanah', '2024112601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6287718095891', 'false', 'true', '2024-12-17 10:07:27', '2025-01-02 16:39:23', NULL, NULL, '0.00', 'SNF01-080', '2024-11-15 08:00:00', NULL, 1),
(81, 'Agus Rahmat (Azril)', '2023020801-at-azril-y5nl', '$2y$10$EUpJvvPeujuXb1xnG42pd.jSj75AGZAXsrPgW5wm.cRW.zZ15w5dW', NULL, 3, 1, 'ppp', '*C5', '2023020801@azril', '2023020801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', '6285795716348', 'false', 'true', '2024-12-17 10:07:27', '2025-01-06 12:36:34', NULL, NULL, '0.00', 'SNF01-081', '2023-02-08 10:07:12', NULL, 1),
(82, 'Neng Helawati', '2024120601-at-helawati-2u4m', '$2y$10$.O8GVtiR9fLXEkeIy28lC.5Tl2sFYQ95m/xH4JZgqB0GzIL2MV9aS', 'nenghelawati59@gmail.com', 1, 1, 'ppp', '*C6', '2024120601@helawati', '2024120601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6285794547149', 'false', 'true', '2024-12-17 10:07:27', '2025-01-09 17:36:30', NULL, NULL, '0.00', 'SNF01-082', '2024-12-06 10:07:12', NULL, 1),
(83, 'Rinji Maulani', '2024113004-at-rinji-9inm', '$2y$10$ifOWiFBs4MxvDkkKDad8iuHfNweqJ.CoKCNikniVD60JHJdFQrMD6', 'rinjimaulani900@gmail.com', 3, 1, 'ppp', '*C7', '2024113004@rinji', '2024113004', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6281945958019', 'false', 'true', '2024-12-17 10:07:28', '2025-01-02 16:45:23', NULL, NULL, '0.00', 'SNF01-083', '2024-12-01 10:07:12', NULL, 1),
(84, 'Iman Nurjamansyah', '2024113002-at-iman-b0ea', '$2y$10$39acmSGGzK765MOyXmgMZuHPU4i0Ohf/QnY750gnjvXKX7jbnygxO', 'nurjaman@gmail.com', 1, 1, 'ppp', '*C8', '2024113002@iman', '2024113002', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '62895424273100', 'false', 'true', '2024-12-17 10:07:28', '2025-01-06 12:03:48', NULL, NULL, '0.00', 'SNF01-084', '2024-12-01 10:07:12', NULL, 1),
(85, 'Dani Ramdani', '2024113001-at-dani-krnl', '$2y$10$UTU8iQcKu0xURAZn7Qbx0uPb.XqOl0aDkMw9zN7IFM86ssxbux9uS', 'CHApliN10@gmail.com', 1, 1, 'ppp', '*C9', '2024113001@dani', '2024113001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6283816206823', 'false', 'true', '2024-12-17 10:07:28', '2025-01-06 12:09:34', NULL, NULL, '0.00', 'SNF01-085', '2024-12-01 10:07:12', NULL, 1),
(86, 'Mujinah', '2024113005-at-mujinah-zgjj', '$2y$10$pIhS2.9MX/hegG8t3siV3eaLowv.5qPwbxoPMTKgS4gXD436pX8aq', 'argazihan70@gmail.com', 1, 1, 'ppp', '*CA', '2024113005@mujinah', '2024113005', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6289659280003', 'false', 'true', '2024-12-17 10:07:28', '2025-01-02 16:46:53', NULL, NULL, '0.00', 'SNF01-086', '2024-12-01 10:07:12', NULL, 1),
(87, 'Rodiah', '2024120102-at-rodiah-qfwk', '$2y$10$PAkpcY3lLUWorAfXh6ka2OCNI4F4X7ZCHnn5Hx0o8SFOTeknvOvkm', 'raisanisan56@gmail.com', 1, 1, 'ppp', '*CB', '2024120102@rodiah', '2024120102', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6289503852046', 'false', 'true', '2024-12-17 10:07:28', '2025-01-02 16:44:53', NULL, NULL, '0.00', 'SNF01-087', '2024-12-01 10:07:12', NULL, 1),
(88, 'Gilang Gumilang', '2024113003-at-gilang-7y0a', '$2y$10$gOc3acqRSMYmnIwBFTkFNelywwmgee0DcrfeDGtTifBZdVhI55ZVO', 'gumilangg208@gmail.com', 2, 1, 'ppp', '*CC', '2024113003@gilang', '2024113003', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6289687854003', 'false', 'true', '2024-12-17 10:07:29', '2025-01-06 12:07:10', NULL, NULL, '0.00', 'SNF01-088', '2024-12-01 10:07:12', NULL, 1),
(89, 'Fadilah Nurhasanah', '2024120101-at-fadilah-acxu', '$2y$10$LwobVb.ZDBJzogdBSq4swucmdozpkdNfY8Wtk0BD3ENES419TSzzW', 'fadilahnurhasanah133@gmail.com', 2, 1, 'ppp', '*CD', '2024120101@fadilah', '2024120101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6285974706228', 'false', 'true', '2024-12-17 10:07:29', '2025-01-06 12:05:45', NULL, NULL, '0.00', 'SNF01-089', '2024-12-01 10:07:12', NULL, 1),
(90, 'Mimin Rukmini', '2024120103-at-mimin-wnn8', '$2y$10$UBTOXSPZMKSjaYF1aDnj8On2feffiIROLMiHzAOvU/vGvkwXOlyFe', 'adulema0@gmail.com', 1, 1, 'ppp', '*CE', '2024120103@mimin', '2024120103', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6287879752212', 'false', 'true', '2024-12-17 10:07:29', '2025-01-02 16:47:07', NULL, NULL, '0.00', 'SNF01-090', '2024-12-01 10:07:12', NULL, 1),
(91, 'Tati Mulyati', '2024120802-at-tati-rhef', '$2y$10$odJorqhsv9M/1087zjTd6.pApFcMgWXErawILz32L1E/TbEQOXSwu', 'martamarta3942@gmail.com', 1, 1, 'ppp', '*CF', '2024120802@tati', '2024120802', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6285967077930', 'false', 'true', '2024-12-17 10:07:29', '2025-01-13 12:31:00', NULL, NULL, '0.00', 'SNF01-091', '2024-12-08 10:07:12', NULL, 1),
(92, 'Siam Suroso', '2024120701-at-siam-byij', '$2y$10$MgUyh6zxWpcj7vSj/7iYQe8dowN6Nu1CfenXh56Ao7nTAgjRBInSO', NULL, 1, 1, 'ppp', '*D0', '2024120701@siam', '2024120701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6281386757581', 'false', 'true', '2024-12-17 10:07:29', '2025-01-06 12:10:28', NULL, NULL, '0.00', 'SNF01-092', '2024-12-07 10:07:12', NULL, 1),
(93, 'Rini Seminar', '2024121001-at-rini-zgwz', '$2y$10$JLvtav.keuJpS2g4eERD.uBF6SBua0qmSyOoxYtBcy4ks2aWOF4t6', 'hideungbodas69@gmail.com', 1, 1, 'ppp', '*D1', '2024121001@rini', '2024121001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6287822655767', 'false', 'true', '2024-12-17 10:07:30', '2025-01-06 12:31:43', NULL, NULL, '0.00', 'SNF01-093', '2024-12-10 10:07:12', NULL, 1),
(94, 'Risna Suhertini', '2024120202-at-risna-bije', '$2y$10$VSRkM.CBS/EmkckLw9zUsO6DSG7hycC2gu7YX3AEZw7yDJElPMkfS', 'summeryuri45@gmail.com', 1, 1, 'ppp', '*D2', '2024120202@risna', '2024120202', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6289639512085', 'false', 'true', '2024-12-17 10:07:30', '2025-01-06 12:08:59', NULL, NULL, '0.00', 'SNF01-094', '2024-12-02 10:07:12', NULL, 1),
(95, 'Dewi Sutriani', '2024120201-at-dewi-qqud', '$2y$10$3r7e1NMXfY4q4lfyZD55OO25KyzIAvTWD2ebO.YNwIoz8CQlfBSEi', 'destirahayu443@gmail.com', 1, 1, 'ppp', '*D3', '2024120201@dewi', '2024120201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6281287271594', 'false', 'true', '2024-12-17 10:07:30', '2025-01-06 12:08:34', NULL, NULL, '0.00', 'SNF01-095', '2024-12-02 10:07:12', NULL, 1),
(96, 'Mariati', '2024120602-at-mariati-j2qb', '$2y$10$5Sx9znzgiQybt6KSjDx3buZrXO7KW8ApHtNsQAgk7OxDroHN6vIVK', 'mariatimariati609@gmail.com', 3, 1, 'ppp', '*D4', '2024120602@mariati', '2024120602', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6283131898860', 'false', 'true', '2024-12-17 10:07:30', '2025-01-02 16:47:23', NULL, NULL, '0.00', 'SNF01-096', '2024-12-06 10:07:12', NULL, 1),
(97, 'Devi Sevtiawati', '2024120501-at-devi-fyah', '$2y$10$LNmvn2dG6OuECRnrXBmJpOTzCObTzW1/6ssrZ1StSW3/8CJy2M8qC', 'devii.setia18@gmail.com', 2, 1, 'ppp', '*D5', '2024120501@devi', '2024120501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6289527460013', 'false', 'true', '2024-12-17 10:07:30', '2025-01-06 12:09:49', NULL, NULL, '0.00', 'SNF01-097', '2024-12-05 10:07:12', NULL, 1),
(98, 'Tatang Mulyana', '2024120801-at-tatang-3wvd', '$2y$10$5oquTsGfQ2MXF6igYBYwc.Ssk2rywYBvoJtOFV5T/hiNuCUF7lXWS', NULL, 3, 1, 'ppp', '*D9', '2024120801@tatang', '2024120801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6287848773957', 'false', 'true', '2024-12-17 10:07:31', '2025-01-06 12:10:49', NULL, NULL, '0.00', 'SNF01-098', '2024-12-08 10:07:12', NULL, 1),
(99, 'Tuti Helawati', '2024121501-at-tuti-oinp', '$2y$10$41O1hHtjSWl0WW4Ey48EaO4iJHSroLmzyE.K9LefCXSnjwhoyDy.G', 'tutihelawati536@gmail.com', 3, 1, 'ppp', '*DB', '2024121501@tuti', '2024121501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', '6281398153066', 'false', 'true', '2024-12-17 10:07:31', '2025-01-06 12:11:47', NULL, NULL, '0.00', 'SNF01-099', '2024-12-15 08:00:00', NULL, 1),
(100, 'Yayah Komariah', '2024121701-at-yayah-v4f4', '$2y$10$7CAtjJQxuuQ.NmqkP4nbyOXJ/JZ/pngCKwE4nirnW.v4mcLeivEKu', 'tehyayah229@gmail.com', 1, 1, 'ppp', '*DC', '2024121701@yayah', '2024121701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6288808639199', 'false', 'true', '2024-12-17 10:07:31', '2025-01-06 12:33:24', NULL, NULL, '0.00', 'SNF01-100', '2024-12-15 08:00:00', NULL, 1),
(101, 'H Imon', '2024060801-at-imon-jmci', '$2y$10$SbWOOTB8corThN0FTDEigO9udtpRdIfAye.ZDDG5fQZXHhmCP3h9e', NULL, 3, 1, 'ppp', '*DE', '2024060801@imon', '2024060801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '6287779500883', 'false', 'true', '2024-12-26 18:42:34', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-101', '2024-06-08 10:07:12', NULL, 2),
(102, 'Julianto', '2024061202-at-julianto-vx73', '$2y$10$k.GxT83IwccF.sEm2ponZ.EQ6Mw6eqirZNRX8K8dYw.Nbb5FptmMq', NULL, 3, 1, 'ppp', '*DF', '2024061202@julianto', '2024061202', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', '6283867217015', 'false', 'true', '2024-12-26 18:42:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-102', '2024-06-12 10:07:12', NULL, 2),
(103, 'Wahyu Maulana', '2024063001-at-wahyu-qzlu', '$2y$10$c7lp.04f0vgDcB0MBWwVkOUidSJrjPPFZp9H1A5drQNnKuY63iIuK', NULL, 3, 1, 'ppp', '*E0', '2024063001@wahyu', '2024063001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '6285890005327', 'false', 'true', '2024-12-26 18:42:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-103', '2024-07-01 10:07:12', NULL, 2),
(104, 'Rika Rahmawati', '2024072701-at-rika-chpk', '$2y$10$C4jFx0hNoFKuMGfY5sczPuWDopYCVbTG/Ra9vNez6zDZXkLmOffuK', NULL, 3, 1, 'ppp', '*E1', '2024072701@rika', '2024072701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', '6281317000715', 'false', 'true', '2024-12-26 18:42:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-104', '2024-08-01 10:07:12', NULL, 2),
(105, 'Neneng Suratni', '2024082801-at-neneng-zlyi', '$2y$10$lTC5akctQf5SoeZd8Bb.s.Ytye3SZqr6Gc0a.7U6NbfAgiSMlAKKS', 'nenengsuratni.99@gmail.com', 3, 1, 'ppp', '*E2', '2024082801@neneng', '2024082801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', '6287778957694', 'false', 'true', '2024-12-26 18:42:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-105', '2024-09-01 10:07:12', NULL, 2),
(106, 'Misbah Toko Bangunan', '2024100402-at-misbah-usil', '$2y$10$zF6XQ5.TELOTgiqloculOes0q1GOEO58Jz0W4nQjYglg51nRTccA2', NULL, 3, 1, 'ppp', '*E3', '2024100402@misbah', '2024100402', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 008/002 Maracang Babakancikao', '6287778624000', 'false', 'true', '2024-12-26 18:42:35', '2025-01-08 15:06:28', NULL, NULL, '0.00', 'SNF01-106', '2024-10-04 10:07:12', NULL, 2),
(107, 'Jajang Salahudin', '2024100601-at-jajang-dsor', '$2y$10$w8SNoEfNZ.KItqQFXERWH.lAixARQSYOx8CJ10oQ65Y0ITkVWVjBm', NULL, 3, 1, 'ppp', '*E4', '2024100601@jajang', '2024100601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 003/001 Maracang Babakancikao', '62895333787739', 'false', 'true', '2024-12-26 18:42:36', '2025-01-08 15:08:50', NULL, NULL, '0.00', 'SNF01-107', '2024-10-06 10:07:12', NULL, 2),
(108, 'Neng Siti', '2024062201-at-nengsiti-owqj', '$2y$10$GFIRQUX.4BUmPmkF3PcvPu2Ky6aZb6DHtZ2c1hKntGOsoSdPYBIP6', NULL, 3, 1, 'ppp', '*E5', '2024062201@nengsiti', '2024062201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 007/002 Maracang Babakancikao', '6285798800407', 'false', 'true', '2024-12-26 18:42:36', '2025-01-08 15:07:59', NULL, NULL, '0.00', 'SNF01-108', '2024-06-15 10:07:12', NULL, 2),
(109, 'Dedi Haerudi', '2024062202-at-idoy-21bs', '$2y$10$8F3zcRZsDqZaEER5.EUnP.UOZP1nSA9LqJ.maWO7gHzysPS88sEiC', NULL, 3, 1, 'ppp', '*E6', '2024062202@idoy', '2024062202', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 007/002 Maracang Babakancikao', '6287785185102', 'false', 'true', '2024-12-26 18:42:36', '2025-01-08 15:08:31', NULL, NULL, '0.00', 'SNF01-109', '2024-07-01 10:07:12', NULL, 2),
(110, 'Amania Kusumah', '2024062901-at-amania-azy1', '$2y$10$i8s2KI6BS8hSCTaQTOYZj.86LMTOLURaBAMvjFspeJH6yOxlU1y4O', NULL, 3, 1, 'ppp', '*E7', '2024062901@amania', '2024062901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 008/002 Maracang Babakancikao', '628979309951', 'false', 'true', '2024-12-26 18:42:36', '2025-01-08 15:07:21', NULL, NULL, '0.00', 'SNF01-110', '2024-07-01 10:07:12', NULL, 2),
(111, 'Nurul Aeni', '2024062902-at-nurul-956p', '$2y$10$CKr3fNQnUbMc5vihQ5F0g.FezqpHvUMT0bU3vkfBlkB7lN5y5wVZe', NULL, 3, 1, 'ppp', '*E8', '2024062902@nurul', '2024062902', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 008/002 Maracang Babakancikao', '6289609996790', 'false', 'true', '2024-12-26 18:42:36', '2025-01-08 15:05:55', NULL, NULL, '0.00', 'SNF01-111', '2024-07-01 10:07:12', NULL, 2),
(112, 'Pak Ojak', '2024070101-at-ojak-vnug', '$2y$10$0pt4QSBVrE1Z8TeouFFEHOEb64LvElpg8j1cMwwNFxA//c8m86qL.', NULL, 3, 1, 'ppp', '*E9', '2024070101@ojak', '2024070101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 008/002 Maracang Babakancikao', '6287778703112', 'false', 'true', '2024-12-26 18:42:37', '2025-01-08 15:05:04', NULL, NULL, '0.00', 'SNF01-112', '2024-07-01 10:07:12', NULL, 2),
(113, 'Redi Natisna', '2024062903-at-redi-3qp3', '$2y$10$MM4q/TzrCI1qIlalNkdGfeOJTFNzOUZoD5IQprkzSHyMsg.otgfM6', NULL, 4, 1, 'ppp', '*EA', '2024062903@redi', '2024062903', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 010/003 Maracang Babakancikao', '6283128938001', 'false', 'true', '2024-12-26 18:42:37', '2025-01-08 15:04:13', NULL, NULL, '0.00', 'SNF01-113', '2024-07-01 10:07:12', NULL, 2),
(114, 'Agus Hermanto', '2024120802-at-agus-1gqr', '$2y$10$oCXQwjg.kZl7.5godvVC5Omjp54GTolL0/FGyr05mauQMOvMOzFYG', 'bagasbagus01175@gmail.com', 4, 1, 'ppp', '*EB', '2024120802@agus', '2024120802', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 010/003 Maracang Babakancikao', '6283894458546', 'false', 'true', '2024-12-26 18:42:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-114', '2024-12-08 10:07:12', NULL, 2),
(115, 'Pak Agus', '2024072001-at-pakagus-gfhr', '$2y$10$I6Hd2BGf2l1hXFQ3Rc1yP.Lj.72FcvmQ5dhOtWpNsW.sCZKqBVvS6', NULL, 3, 1, 'ppp', '*EC', '2024072001@pakagus', '2024072001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Babakan Jati RT. RW. 06 Maracang', '6285860251672', 'false', 'true', '2024-12-26 18:42:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-115', '2024-07-15 08:00:00', NULL, 2),
(116, 'Wahid', '2024082501-at-wahid-ygpf', '$2y$10$If0.64BgMTp5lXQen6t8zOxic/inYQz.i.yPfHyJEadDGmAJSs5oW', NULL, 3, 1, 'ppp', '*ED', '2024082501@wahid', '2024082501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Babakan Jati Desa Maracang', '6285794748005', 'false', 'true', '2024-12-26 18:42:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-116', '2024-08-15 08:00:00', NULL, 2),
(117, 'Sularno', 'sularno-kdbgrx', '$2y$10$LQ4YSrfU3MXaCY5dnDbB4Oj/KKKrVoyn1iwdoiwT9ylUYXesdqCY.', 'n.fitra.r@gmail.com', 3, 1, 'ppp', '*12A', '2025010101@sularno', '2025010101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Perum Griya Asri Blok K3 No. 3 RT 010/011 Ciseureuh Purwakarta', '6285212150070', 'false', 'true', '2025-01-01 20:18:39', '2025-01-01 20:20:19', NULL, 1, '0.00', 'SNF01-117', '2025-01-01 11:11:31', NULL, 1),
(118, 'Wida', '100230303-at-wida-ntqy', '$2y$10$K01THhdBwcK/nE.Dal4fsu1e0csJrYllqmskgJDdfybpNO2j8DZhq', NULL, 2, 1, 'ppp', '*FC', '2023030301@wida', '2023030301', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '628988560485', 'false', 'true', '2025-01-02 11:11:31', '2025-01-02 18:45:44', NULL, NULL, '20.00', 'SNF01-118', '2024-03-03 11:11:31', NULL, 2),
(119, 'Iqbal Budi Prasetya', '100230304-at-iqbal-0zzd', '$2y$10$6rg2QrMEUBKamK3ydRmvWO60HKWHBT5TZ70PIhTMIFSAwHqKKpaeO', NULL, 2, 1, 'ppp', '*FD', '2023030401@iqbal', '2023030401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6289632966676', 'false', 'true', '2025-01-02 11:11:31', '2025-01-02 18:45:44', NULL, NULL, '20.00', 'SNF01-119', '2024-03-04 11:11:31', NULL, 2),
(120, 'Usman Abdul Sobari', '100231204-at-usman-pq3u', '$2y$10$8KQMpmV7o5u9h/KxRAi4A.EdiHD2w/AAVUvWfVjRS8NNziNc75eX6', NULL, 2, 1, 'ppp', '*FE', '2023120401@usman', '2023120401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6283193222232', 'false', 'true', '2025-01-02 11:11:32', '2025-01-02 18:45:44', NULL, NULL, '20.00', 'SNF01-120', '2023-12-04 11:11:31', NULL, 2);
INSERT INTO `customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `internet_service`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `simpleque_id`, `simpleque_name`, `ip_static`, `mac_address`, `interface`, `arp_id`, `address_list_id`, `address`, `contact`, `disabled`, `activation`, `created_at`, `updated_at`, `deleted_at`, `primary_server`, `discount`, `customer_id`, `activation_date`, `caller_id`, `user_id`) VALUES
(121, 'Nuryani', '125230606-at-yance-n2qb', '$2y$10$WbOcZEEI5xwO4Kb5svBMYubrEK11uT4/3uxcIM0HImh.DCXcDYcTe', NULL, 2, 1, 'ppp', '*FF', '2023060601@yance', '2023060601', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6285213732627', 'false', 'true', '2025-01-02 11:11:32', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-121', '2023-06-06 11:11:31', NULL, 2),
(122, 'Rosidah', '125240420-at-rosidah-t1dk', '$2y$10$JPHe80onrQeRfWlF7D9D8uog9jv6dtr2cLzgSNKCPW2YQI2Ekvovi', NULL, 2, 1, 'ppp', '*100', '2024042002@rosidah', '2024042002', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6289652111141', 'false', 'true', '2025-01-02 11:11:32', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-122', '2024-04-15 08:00:00', NULL, 2),
(123, 'Siti Mariam', '125240427-at-amar-gwae', '$2y$10$oKlm03S96rR80O6d9ueLpuTjOaRCJocI.VEdh0XWw1/nxpFynCGSS', NULL, 2, 1, 'ppp', '*101', '2024042701@amar', '2024042701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6289509783282', 'false', 'true', '2025-01-02 11:11:32', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-123', '2024-04-15 08:00:00', NULL, 2),
(124, 'Iis Ismawati', '125240427-at-iis-pxtk', '$2y$10$fMZO/RhSoGHi4JZX.kfXhOk9KBG6Ih4CCJEohirYeTK3o3aTgjEyC', NULL, 2, 1, 'ppp', '*102', '2024042702@iis', '2024042702', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '62895374776212', 'false', 'true', '2025-01-02 11:11:32', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-124', '2024-04-15 08:00:00', NULL, 2),
(125, 'Desi Nurdianti', '125240427-at-salim-mwbz', '$2y$10$HYnvhxxbPOBe9/ODl7FoUeIXGXnlgUotU9NtYMiGt9txO2U.5FZTC', NULL, 2, 1, 'ppp', '*103', '2024042703@salim', '2024042703', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6283827502565', 'false', 'true', '2025-01-02 11:11:32', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-125', '2024-04-15 08:00:00', NULL, 2),
(126, 'Adis', '125240428-at-adis-spq7', '$2y$10$EiCyfdWO.TyvdzmVCpg4A.PvE04D3g9Hsr2RrERNNSN7.tXwVHS8K', NULL, 2, 1, 'ppp', '*104', '2024042801@adis', '2024042801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6287869634616', 'false', 'true', '2025-01-02 11:11:33', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-126', '2024-04-15 08:00:00', NULL, 2),
(127, 'Satim Silegar', '125240428-at-paksatim-2ldi', '$2y$10$DCInVoERqp73KkoSKHFGGONrPTqMHYkCGZByNAfNdsL/UuF3IaDs2', 'susisusilaw1410@gmail.com', 2, 1, 'ppp', '*105', '2024042802@paksatim', '2024042802', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6285943092738', 'false', 'true', '2025-01-02 11:11:33', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-127', '2024-04-15 08:00:00', NULL, 2),
(128, 'Imar Sulaeman', '125240428-at-sulaeman-3lbu', '$2y$10$icQ3tvGmx4jROqjsgZ.8mOLztLoPTQvVqm04ffks1kMj22jlLadQ2', 'imarsulaeman04@gmail.com', 2, 1, 'ppp', '*106', '2024042803@sulaeman', '2024042803', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6285210763586', 'false', 'true', '2025-01-02 11:11:33', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-128', '2024-04-15 08:00:00', NULL, 2),
(129, 'Heni', '125240430-at-heni-hijf', '$2y$10$K3kLEB.49XyA1b5c1dCklusWaxjTAOcZk1AXXxhmzVSi0g4H07vw2', NULL, 2, 1, 'ppp', '*107', '2024043001@heni', '2024043001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6287836276215', 'false', 'true', '2025-01-02 11:11:33', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-129', '2024-04-15 08:00:00', NULL, 2),
(130, 'Juju Juarsih', '125240501-at-juju-vz6i', '$2y$10$Oiv1kp0wFvUACf2reVZmoeqCPldcOrflimjgsK3h5Xvhwwj3FUonW', NULL, 2, 1, 'ppp', '*109', '2024050103@juju', '2024050103', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6289698054300', 'false', 'true', '2025-01-02 11:11:33', '2025-01-08 13:57:30', NULL, NULL, '0.00', 'SNF01-130', '2024-05-01 11:11:31', NULL, 2),
(131, 'Juhdi', '125240501-at-zudenk-8kxh', '$2y$10$R2Kckqa5kmAFYdwTh/6hOuF57cvYFf13lmBvy3v8zS/.mPHBwd1.S', 'juhdianjuhdi@gmail.com', 2, 1, 'ppp', '*10A', '2024050102@zudenk', '2024050102', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '628886243932', 'false', 'true', '2025-01-02 11:11:34', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-131', '2024-05-01 11:11:31', NULL, 2),
(132, 'Cici', '125240509-at-cici-wl2u', '$2y$10$hgCQqaTiEHPHtIl8uINO7OsnaTyn9GSWwAZWZGdOz3w/PjDIBBQlq', NULL, 3, 1, 'ppp', '*10C', '2024050901@cici', '2024050901', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6287828059654', 'false', 'true', '2025-01-02 11:11:34', '2025-01-06 10:43:08', NULL, NULL, '0.00', 'SNF01-132', '2024-05-09 11:11:31', NULL, 2),
(133, 'Pepen', '125240510-at-pepen-acfh', '$2y$10$UwuH3p1gnXa44fHHkHlUeeIWLbH87feztsXq54nlnHgV81Clfhzka', NULL, 2, 1, 'ppp', '*10D', '2024051001@pepen', '2024051001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6282123801066', 'false', 'true', '2025-01-02 11:11:34', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-133', '2024-05-10 11:11:31', NULL, 2),
(134, 'Wayanto', '125240510-at-wayanto-exut', '$2y$10$vifrJLsh..WHDLxwyfn8FOskZ1OLrYPgkfV3j6T6cosG7Al6/I4hK', NULL, 2, 1, 'ppp', '*10E', '2024051002@wayanto', '2024051002', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6289529839040', 'false', 'true', '2025-01-02 11:11:34', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-134', '2024-05-10 11:11:31', NULL, 2),
(135, 'Heri Suandi', '125240514-at-heri-k9d3', '$2y$10$WKX8aZa0R9po7kaSXtk7N.sgaCtG7nOn7U.zB5g.qQEKvg2EC4ER.', NULL, 2, 1, 'ppp', '*10F', '2024051401@heri', '2024051401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6287899566027', 'false', 'true', '2025-01-02 11:11:34', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-135', '2024-05-14 11:11:31', NULL, 2),
(136, 'Miung', '125240514-at-miung-glml', '$2y$10$gHd8kH4L9R094UNfla5dN.hhy2PHa1.CVCeROjxNkJDc/h15t/USy', NULL, 2, 1, 'ppp', '*110', '2024051402@miung', '2024051402', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6287794377878', 'false', 'true', '2025-01-02 11:11:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-136', '2024-05-14 11:11:31', NULL, 2),
(137, 'Dadan Hamdani', '125240514-at-rtahong-4qnl', '$2y$10$tdBs07m8YW8Wk963n0wlh.DR5TVe63JnaYrSITYEYtorthxohJOZO', NULL, 2, 1, 'ppp', '*111', '2024051403@rtahong', '2024051403', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6281399714844', 'false', 'true', '2025-01-02 11:11:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-137', '2024-05-14 11:11:31', NULL, 2),
(138, 'Okis', '150240420-at-okis-ht5c', '$2y$10$dfj.U7vo0VC7hnC0jGS1FuEvgy8TBYpie0ybFjBg.tQl9aAWVqKOG', NULL, 3, 1, 'ppp', '*113', '2024042001@okis', '2024042001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6282123324181', 'false', 'true', '2025-01-02 11:11:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-138', '2024-04-15 08:00:00', NULL, 2),
(139, 'RT Oji', '150240515-at-rtoji-wdff', '$2y$10$4fH2qOJ0Z3l3HoTPFozP9OrayUhmthHrDCkA4JtsX.9dPnaLnCgYG', NULL, 3, 1, 'ppp', '*114', '2024051501@rtoji', '2024051501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '62895811360899', 'false', 'true', '2025-01-02 11:11:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-139', '2024-05-15 08:00:00', NULL, 2),
(140, 'Suntari', '150240518-at-suntari-41sf', '$2y$10$q28vmlqJwgTrU4gnyyyR5ueBwVKGlGSSOHBoKAStEh6ei6SDI4lOu', NULL, 3, 1, 'ppp', '*115', '2024051801@suntari', '2024051801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6281909474994', 'false', 'true', '2025-01-02 11:11:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-140', '2024-05-15 08:00:00', NULL, 2),
(141, 'Engko', '150240525-at-engko-ozwu', '$2y$10$cve7Psrr/JL6QP21hnFAYe//d9do//uSQuCUJCm0s/FHk7hI8LZ.y', 'mmdrizky0905@gmail.com', 3, 1, 'ppp', '*116', '2024052501@engko', '2024052501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6281317532556', 'false', 'true', '2025-01-02 11:11:35', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-141', '2024-05-15 08:00:00', NULL, 2),
(142, 'Arya Cahyana Putra', '150240601-at-arya-tucj', '$2y$10$G5XwOurAtl3bijQSAZK7JegPq/Pw7YukhgHpa8IH0OC8fEG0fjlzW', NULL, 3, 1, 'ppp', '*117', '2024060101@arya', '2024060101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6281218931945', 'false', 'true', '2025-01-02 11:11:36', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-142', '2024-06-01 11:11:31', NULL, 2),
(143, 'Bajuri Enci', '150240704-at-bajuri-3xj1', '$2y$10$arxW.NmkAYj5BHOSj2.kg.qwlO..RaEW8./RBmZUt1qraUDAPqK5.', NULL, 3, 1, 'ppp', '*118', '2024070401@bajuri', '2024070401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6285603273195', 'false', 'true', '2025-01-02 11:11:36', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-143', '2024-07-04 11:11:31', NULL, 2),
(144, 'Salmah Saleha', '150240708-at-salmah-1hhc', '$2y$10$//V32lqGTmWO0jso3FFLjeiNb5k5UbyI.owu1dvDTLCmdHhu0nZ1u', NULL, 3, 1, 'ppp', '*119', '2024070801@salmah', '2024070801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6289515117637', 'false', 'true', '2025-01-02 11:11:36', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-144', '2024-07-08 11:11:31', NULL, 2),
(145, 'Darma Wirawan', '150240713-at-darma-uzpx', '$2y$10$11IfCN/TNG12QCBzAo8.aeVhxPW/FMffEyhyDXagqhY4CQvf2Rpz2', 'irawanindra1911@gmail.com', 3, 1, 'ppp', '*11A', '2024071301@darma', '2024071301', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6287837145242', 'false', 'true', '2025-01-02 11:11:36', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-145', '2024-07-13 11:11:31', NULL, 2),
(146, 'Sulis (Rumah)', '150240720-at-sulis-ike2', '$2y$10$QzWND1B3bUVVukHrApo35emjiXxCnDcHnQCSulRFhHYJ3IYda.bca', NULL, 3, 1, 'ppp', '*11B', '2024072002@sulis', '2024072002', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '62895348855444', 'false', 'true', '2025-01-02 11:11:36', '2025-01-07 15:57:11', NULL, NULL, '0.00', 'SNF01-146', '2024-07-15 08:00:00', NULL, 2),
(147, 'Yuyun', '150240815-at-yuyun-ramb', '$2y$10$d0Nbi8ilvWNJhq6vYxieQuoYwVoNwnw9FUILo/EZfrNkPhire72ye', NULL, 3, 1, 'ppp', '*11C', '2024081501@yuyun', '2024081501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '628882687989', 'false', 'true', '2025-01-02 11:11:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-147', '2024-08-15 08:00:00', NULL, 2),
(148, 'Herman', '150240907-at-eman-vzu4', '$2y$10$HKdPT4G2DQ2A.Th3q1oNaewtBrxbX8hqKwpX/QqsD3E9j2bgvRSNy', NULL, 3, 1, 'ppp', '*11D', '2024090701@eman', '2024090701', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6285216510760', 'false', 'true', '2025-01-02 11:11:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-148', '2024-09-07 11:11:31', NULL, 2),
(149, 'Syarip Hidayat', '150240912-at-syarip-8ira', '$2y$10$ZAXif/JWjLFEtU0qDlJ3lugbj1CqiG1VhMUm7H.Pyf1GLUII1V27e', NULL, 3, 1, 'ppp', '*11E', '2024091201@syarip', '2024091201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '62895348840602', 'false', 'true', '2025-01-02 11:11:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-149', '2024-09-12 11:11:31', NULL, 2),
(150, 'Dadi Kusnadi', '150241017-at-dadi-ddu3', '$2y$10$wjlwgFHDorSzDKdlZky7jOI11kOOcExOrVmQdT5UzOXXgjczdilEa', NULL, 3, 1, 'ppp', '*11F', '2024101702@dadi', '2024101702', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6287718592331', 'false', 'true', '2025-01-02 11:11:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-150', '2024-10-15 08:00:00', NULL, 2),
(151, 'Fera', '150241030-at-fera-es9h', '$2y$10$2A3OFATLiB24OE3.tB3J7uytohWCu0bVGE6qYAoKTB9q2QHwIfzTm', 'fera141120@gmail.com', 3, 1, 'ppp', '*120', '2024103002@fera', '2024103002', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '62895392820036', 'false', 'true', '2025-01-02 11:11:37', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-151', '2024-11-01 11:11:31', NULL, 2),
(152, 'Rifal Yudhi Pratama', '150241126-at-rifal-8kap', '$2y$10$gktmjliz1H79TRIYZfxssOnZWwb0IZAT4Jt4jl60oLoHExPBPGTRW', 'pratamarifal81@gmail.com', 3, 1, 'ppp', '*121', '2024112602@rifal', '2024112602', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '6287891528346', 'false', 'true', '2025-01-02 11:11:38', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-152', '2024-11-15 08:00:00', NULL, 2),
(153, 'Ani Sumarni', '150241130-at-anisumarni-wjne', '$2y$10$pGi9.ca/5AixTC9ei751fObU9mdofAjxutYoyXRSkqpH0YL.KC4Ei', 'ani653874@gmail.com', 3, 1, 'ppp', '*122', '2024113006@anisumarni', '2024113006', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6281289921468', 'false', 'true', '2025-01-02 11:11:38', '2025-01-08 15:11:50', NULL, NULL, '0.00', 'SNF01-153', '2024-12-01 11:11:31', NULL, 2),
(154, 'Hendri Gunawan', '150241208-at-hendri-dfun', '$2y$10$6SPEdT0S76o6DtGnIvjoiO7us.NGjdjo/ou0fkrxzeLmCmmKXxpMK', 'hg0753407@gmail.com', 3, 1, 'ppp', '*123', '2024120803@hendri', '2024120803', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6282241946794', 'false', 'true', '2025-01-02 11:11:38', '2025-01-08 15:12:21', NULL, NULL, '0.00', 'SNF01-154', '2024-12-08 11:11:31', NULL, 2),
(155, 'Anggi', '200240420-at-anggi-qkfv', '$2y$10$cZ5iIvDO6xDf681fWKXcOOB74xTY/PKtUEdz.ZK5j2fCulqnG5WZ2', NULL, 4, 1, 'ppp', '*124', '2024042003@anggi', '2024042003', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6281399008999', 'false', 'true', '2025-01-02 11:11:38', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-155', '2024-04-15 08:00:00', NULL, 2),
(156, 'Sulis (Toko)', '200240715-at-sulis-5urh', '$2y$10$cxZa59b1fqIerkFPGQPiq.5UiP6meeHgojM5znV/LDt/CWwlVncIa', NULL, 4, 1, 'ppp', '*125', '2024071501@sulis', '2024071501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6281945501926', 'false', 'true', '2025-01-02 11:11:38', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-156', '2024-07-15 08:00:00', NULL, 2),
(157, 'Kuswandi', '200241217-at-kuswandi-jvpc', '$2y$10$cesWDoeMWPt6E9bjVV4E2O2hvrRDBH5XHk4fLnifygUd4HmiOj8Hm', NULL, 4, 1, 'ppp', '*126', '2024121702@kuswandi', '2024121702', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6289526621826', 'false', 'true', '2025-01-02 11:11:39', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-157', '2024-12-15 08:00:00', NULL, 2),
(158, 'Suci Ambar Sari', '2024122801-at-suci-jrzh', '$2y$10$OrGysqna4xPRygyPzlOGvOzShm.wtLGNr0rAVV3duCXgi1jpRmcC2', 'sambarsari@gmail.com', 3, 1, 'ppp', '*127', '2024122801@suci', '2024122801', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', '6282115653662', 'false', 'true', '2025-01-02 11:11:39', '2025-01-02 18:45:44', NULL, NULL, '0.00', 'SNF01-158', '2024-12-15 08:00:00', NULL, 1),
(160, 'Sule', 'sule-7d3fk5', '$2y$10$v4leHG9Kas8LdavcUwsl..dwceBPNDeC8f6KwfPb6JHWda05gyDta', 'sule@shanetfiber.my.id', 2, 1, 'ppp', '*12C', '2024051404@sule', '2024051404', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 025/007 Desa Maracang', '6283148496911', 'false', 'true', '2025-01-02 19:05:43', '2025-01-02 19:11:25', NULL, 1, '0.00', 'SNF01-160', '2025-01-02 19:06:13', NULL, 2),
(161, 'Bambang Sucipto', 'bambang-5rwjpv', '$2y$10$93DI.kiye75XBT8y/nXCh..2SbxlBRkdqEp3cY4BTTtevssyiJHc.', 'elbenbeng94@gmail.com', 3, 1, 'ppp', '*12D', '2025010301@bambang', '2025010301', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', '62881023017678', 'false', 'true', '2025-01-02 20:47:33', '2025-01-03 18:30:19', NULL, 1, '0.00', 'SNF01-161', '2025-01-03 18:29:13', NULL, 1),
(162, 'Bunga Rinady Sandy', 'bunga-vqfhup', '$2y$10$vndMQ8AWgnNpo9lhvale8efXLwGE.XZHCgtEqq1LQOti/PtrS98si', 'brinady@gmail.com', 4, 1, 'ppp', '*12E', '2025010401@bunga', '2025010401', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6281313099996', 'false', 'true', '2025-01-02 20:52:32', '2025-01-08 15:11:03', NULL, 1, '0.00', 'SNF01-162', '2025-01-04 09:39:19', NULL, 2),
(163, 'Budi Kosasih', 'budi-bj6dei', '$2y$10$Qm7OMLm9JKTVIxW0kTPETOe5rbiiaV9VBstevAYs0AyXKJrEW/vk6', 'budiajakosasih@gmail.com', 24, 1, 'ppp', '*131', 'budi_aja@shanetfiber.my.id', 'budi_aja', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6285710840198', 'false', 'true', '2025-01-07 13:13:28', '2025-01-08 15:10:26', NULL, 1, '0.00', 'SNF01-163', '2025-01-07 13:14:02', NULL, 3),
(164, 'Iin Indrawati', 'iin-lycebj', '$2y$10$MA0KY4S1Pus/KG5fHbDdBeuiJfkUeczRoIXVJb87OFJUui.aq8hTa', 'ii3173869@gmail.com', 4, 1, 'ppp', '*138', '2025011103@iin', '2025011103', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6287728362852', 'false', 'true', '2025-01-08 15:01:21', '2025-01-11 11:40:27', NULL, 1, '0.00', 'SNF01-164', '2025-01-11 11:39:46', NULL, 2),
(165, 'Dasih Juarsih', 'dasih-mqpxly', '$2y$10$bypxm3j4EIv372H6kUGjP.eg/RoTsl.7pDvXr9FrTmXSmYrF/ADi6', 'rizwandadi04@gmail.com', 4, 1, 'ppp', '*13A', '2025011106@dasih', '2025011106', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6287838558662', 'false', 'true', '2025-01-08 15:03:19', '2025-01-11 16:46:06', NULL, 1, '0.00', 'SNF01-165', '2025-01-11 16:42:05', NULL, 2),
(166, 'POS RT.11/03 Krajan', 'pos-ykxtk4', '$2y$10$r7YvnwDNe94XruL/ok8vFu10TAZdQIM8ffHOvvTuLgZXwviLWfl5y', 'poskamRT11/03krajan@shanetfiber.my.id', 25, 1, 'ppp', '*132', 'poskamrt11rw03@krajan', 'poskamrt11rw03', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', NULL, 'false', 'true', '2025-01-08 15:32:12', '2025-01-11 12:20:02', NULL, 1, '0.00', 'SNF01-166', '2025-01-08 15:32:46', NULL, 3),
(167, 'Sa\'ad', 'saad-swtaz2', '$2y$10$40hGyXyn.cMLWLuzL1BRZ.Z7wxCSRpl2Cn1hcAz6/AZGpTdcKFyhy', 'abisubur80@gmail.com', 3, 1, 'ppp', '*136', '2025011101@saad', '2025011101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6285213355245', 'false', 'true', '2025-01-08 15:35:55', '2025-01-11 09:28:44', NULL, 1, '0.00', 'SNF01-167', '2025-01-11 09:27:46', NULL, 2),
(168, 'Komarudin', 'komarudin-8uxips', '$2y$10$xbKzituzCOTwLbL6VC1M5.MHS2Tdo0fcvPGIAlUY0l5uLHHvj6oqy', 'komarrohmah@gmail.com', 2, 1, 'ppp', '*137', '2025011102@komarudin', '2025011102', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6281573221580', 'false', 'true', '2025-01-08 18:13:28', '2025-01-11 10:27:30', NULL, 1, '0.00', 'SNF01-168', '2025-01-11 10:26:20', NULL, 2),
(170, 'Cecep Sodikin', 'cecep-tky5ei', '$2y$10$Eh14fozgVGE1OQAaDPop3u6hhwGwnJMTbJivRQiBIHKjqyhBjZMWC', 'zanok1933@gmail.com', 2, 1, 'ppp', '*13E', '2025011203@cecep', '2025011203', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6283837140348', 'false', 'true', '2025-01-09 19:04:55', '2025-01-13 06:32:59', NULL, 1, '20.00', 'SNF01-170', '2025-01-12 12:26:41', NULL, 2),
(171, 'Maah Siti Komala', 'maah-1fzly0', '$2y$10$Oxt3g8C2/Zes4ydSoirqHehCABM4JYnMvjYa.TNFGn9R9pjKIpU5i', 'maahsiti@shanetfiber.my.id', 2, 1, 'ppp', '*13D', '2025011202@maah', '2025011202', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6285643247670', 'false', 'true', '2025-01-09 19:06:34', '2025-01-12 11:28:25', NULL, 1, '0.00', 'SNF01-171', '2025-01-12 11:27:45', NULL, 2),
(172, 'Iwan Saepuloh', 'iwan-hobhi4', '$2y$10$Zfl3PHYE9OCK.HEkeW61e.SL//yd1RleyrIv78Hn.fuz.ABeQ4K9.', 'iwansaepulloh@gmail.com', 3, 1, 'ppp', '*13C', '2025011201@iwan', '2025011201', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '628993496794', 'false', 'true', '2025-01-09 19:07:56', '2025-01-12 09:08:08', NULL, 1, '0.00', 'SNF01-172', '2025-01-12 09:07:14', NULL, 2),
(173, 'Saepudin', 'saepudin-nkgiv8', '$2y$10$nmuz5EIJdCgK.UnmVoWghuBouAyuGtX6kj257gIdQujK2/BX5GcMi', 'hanhanhanda26@gmail.com', 2, 1, 'ppp', '*143', '2025011206@saepudin', '2025011206', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6287885540760', 'false', 'true', '2025-01-09 19:09:10', '2025-01-12 16:48:14', NULL, 1, '0.00', 'SNF01-173', '2025-01-12 16:47:03', NULL, 2),
(174, 'Sani Wartini', 'sani-j7np5j', '$2y$10$u8X9NBTBSRBTzilw3xhshukFzenKlR6CCvwoooM/wPuhKadR/0CIC', 'saniwartini23@gmail.com', 2, 1, 'ppp', '*145', '2025011208@sani', '2025011208', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6283130389613', 'false', 'true', '2025-01-09 19:10:23', '2025-01-12 18:21:25', NULL, 1, '0.00', 'SNF01-174', '2025-01-12 18:20:34', NULL, 2),
(175, 'Yati Haryati', 'yati-9jfnrk', '$2y$10$pI8PVLcJ2KeCzs1PTglLM.TbAVaSDFPtPm5rCTDdmO3t2lk14oo0y', 'yatih4767@gmail.com', 2, 1, 'ppp', '*141', '2025011204@yati', '2025011204', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6283816943314', 'false', 'true', '2025-01-09 19:11:35', '2025-01-12 15:34:50', NULL, 1, '0.00', 'SNF01-175', '2025-01-12 15:33:58', NULL, 2),
(176, 'Windi Gustiani', 'windi-bkw1nj', '$2y$10$i1mmu7XjQgG/l2/PIWa3xeDPW7eaOuw5KtuETEG0Ojzdrws.gIpNy', 'windigustiani0@gmail.com', 3, 1, 'ppp', '*142', '2025011205@windi', '2025011205', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6282298006955', 'false', 'true', '2025-01-09 19:12:31', '2025-01-12 16:16:51', NULL, 1, '0.00', 'SNF01-176', '2025-01-12 16:16:09', NULL, 2),
(177, 'Virta Eka Yulistia', 'virta-whwukb', '$2y$10$ocYApQMoQrJKdScqCD27BufSVvTvGpO8ahh/VfUaW9GS5uePuhAF.', 'virta.eka.ve@gmail.com', 4, 1, 'ppp', '*13B', '2025011107@virta', '2025011107', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6285710511588', 'false', 'true', '2025-01-09 19:14:25', '2025-01-11 17:47:22', NULL, 1, '0.00', 'SNF01-177', '2025-01-11 17:46:20', NULL, 2),
(178, 'Budi Kosasih', 'budi-2czcff', '$2y$10$nwxYq5XeJ0mPVU8/FJo.hu5PpAhvBFxogpcz7AoymbEk6BfdyVJAe', 'budi_kos@shanetfiber.my.id', 24, 1, 'ppp', '*134', 'budi_kos@shanetfiber.my.id', 'budi_kos', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', NULL, 'false', 'true', '2025-01-09 19:20:47', '2025-01-09 19:21:41', NULL, 1, '0.00', 'SNF01-178', '2025-01-09 19:21:05', NULL, 3),
(179, 'Alin Senia Trisiani', 'alin-zjcoq7', '$2y$10$hfNbH6o1Apfv72Z5VrllxubKgXcHeQ5fwSWQ/g4h0Cf7CePSDUScy', 'nurisamslik@gmail.com', 4, 1, 'ppp', '*135', '2025011001@alin', '2025011001', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Jl. Sukamaju RT 09/07 Ciseureuh Purwakarta', '6281909487546', 'false', 'true', '2025-01-10 13:45:05', '2025-01-10 13:46:11', NULL, 1, '0.00', 'SNF01-179', '2025-01-10 13:45:16', NULL, 1),
(180, 'Ika Sartika', 'ika-bksedw', '$2y$10$du03Nmu2Y0Pejb5NqrYsSOgE90w6iN5LL.PG6GL59fcpvViKxfAxK', 'anoybungsu@gmail.com', 3, 1, 'ppp', '*139', '2025011104@ika', '2025011104', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6297779844486', 'false', 'true', '2025-01-10 15:57:35', '2025-01-11 12:00:58', NULL, 1, '0.00', 'SNF01-180', '2025-01-11 12:00:16', NULL, 2),
(181, 'Nani Sumarni', 'nani-xmcy11', '$2y$10$6Mj.CFgULs3vPZcelQBMqu5oH2DqpyO.nJljrZqbvSEtErA34tUE2', 'ns3729306@gmail.com', 2, 1, 'ppp', '*144', '2025011207@nani', '2025011207', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6281384736565', 'false', 'true', '2025-01-11 19:20:39', '2025-01-12 18:04:41', NULL, 1, '0.00', 'SNF01-181', '2025-01-12 18:03:39', NULL, 2),
(182, 'Deni', 'rini-bb4ors', '$2y$10$n9zxIk3i9.INJOKl8x1AGO4J7ZqydjDsmUl5yFBOllXZNsfLivg5G', 'deni@shanetfiber.my.id', 2, 1, 'ppp', '*14C', '2025011504@deni', '2025011504', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6281011228286', 'false', 'true', '2025-01-11 19:32:12', '2025-01-16 20:47:56', NULL, 1, '0.00', 'SNF01-182', '2025-01-15 08:00:00', NULL, 2),
(183, 'Mohamad Ridwan', 'mohamad-efaed2', '$2y$10$jjzle52YdJbvjjHBiXGzoekAy822u2ec8QUjYIvF9dLK2xZE0OAXq', 'iwandewa66@gmail.com', 3, 1, 'ppp', '*140', '2025011105@ridwan', '2025011105', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6285311901966', 'false', 'true', '2025-01-12 12:56:49', '2025-01-12 12:58:53', NULL, 1, '0.00', 'SNF01-183', '2025-01-12 12:57:06', NULL, 2),
(184, 'Muh Subhan', 'muh-7uieak', '$2y$10$xz7goMNlVpe5DIUyC.ILzuEmpkX.wy1eMMjTq68FeDeYWim/2316C', 'subhan@shanetfiber.my.id', 1, 1, 'ppp', '*147', '2021012101@subhan', '2021012101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', '6281384729066', 'false', 'true', '2025-01-13 06:28:59', '2025-01-13 06:29:55', NULL, 1, '0.00', 'SNF01-184', '2021-01-15 08:00:00', NULL, 1),
(185, 'Yayat Hidayat', 'yayat-abqxnb', '$2y$10$euZgqCjtC/c8eSwCmve8jumfh9yeaw6vycG.HPUg4BhBthJFW1bba', 'sarifhidayat010402@gmail.com', 2, 1, 'ppp', '*14D', '2025011501@yayat', '2025011501', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6285643247630', 'false', 'true', '2025-01-13 09:18:42', '2025-01-15 10:22:12', NULL, 1, '0.00', 'SNF01-185', '2025-01-15 08:00:00', NULL, 2),
(186, 'Ida Susilawati', 'ida-2lbiea', '$2y$10$NZufA3.gy32T6hM330dwfu7AVCGTt1/HftevihEvhhRoiHiSFSnsK', 'amiida417@gmail.com', 3, 1, 'ppp', '*149', '2025011302@ida', '2025011302', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6283101457933', 'false', 'true', '2025-01-13 09:19:52', '2025-01-13 19:43:37', NULL, 1, '0.00', 'SNF01-186', '2025-01-13 19:42:50', NULL, 2),
(187, 'Ratih Herawati', 'ratih-8hmwcj', '$2y$10$gdwjdUxDvJqwYiaOzUe.DeZEwCdl43EpVP8wXC0ozWK08bdHGWeSq', 'herawatiratih09@gmail.com', 2, 1, 'ppp', '*14B', '2025011503@herawati', '2025011503', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6285794280375', 'false', 'true', '2025-01-13 09:21:11', '2025-01-20 07:00:39', NULL, 1, '0.00', 'SNF01-187', '2025-01-15 08:00:00', NULL, 2),
(188, 'Heti Herawati', 'heti-3pbgkm', '$2y$10$o.UIgEp.3OYpvqbVGEko6ebeiJ5Tx.6JBcbu5P808JflOZyilseNW', 'hetiherawati1987@gmail.com', 2, 1, 'ppp', '*14A', '2025011502@heti', '2025011502', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6287734107570', 'false', 'true', '2025-01-13 09:22:39', '2025-01-15 10:21:40', NULL, 1, '0.00', 'SNF01-188', '2025-01-15 08:00:00', NULL, 2),
(189, 'Serli Ardiawati', 'serli-tsq4dd', '$2y$10$lZiAxeKj87YlrNp22cW0kO.USwX5KCNYIu3PfdBCCItrnjNNXj4Om', 'serliardiawati2424@gmail.com', 4, 1, 'ppp', '*148', '2025011301@serli', '2025011301', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Maracang Babakancikao', '6281398917897', 'false', 'true', '2025-01-13 18:20:39', '2025-01-13 18:21:55', NULL, 1, '0.00', 'SNF01-189', '2025-01-13 18:20:57', NULL, 2),
(190, 'Ahmad Zaenal Mustofa', 'ahmad-kxef5z', '$2y$10$PyTNULUgBX2JIehUitUhDOtL2PRl10YrnhFQs.p4pYTtzMHHJWiEu', 'ahmadzainalmustofa24@gmail.com', 3, 1, 'ppp', '*14E', '2025011505@zaenal', '2025011505', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '62818988156', 'false', 'true', '2025-01-17 11:38:45', '2025-01-17 11:39:49', NULL, 1, '0.00', 'SNF01-190', '2025-01-15 11:39:05', NULL, 2),
(191, 'Endang Rosidi', 'endang-adzgzh', '$2y$10$G20KE0wxW8V02/0cmlPk1.QQAknO6dbB1jGowTWSfsA/zcG.xFs52', 'romlahkomariah023@gmail.com', 3, 1, 'ppp', '*151', '2025011507@endang', '2025011507', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Maracang Babakancikao', '6281909373419', 'false', 'true', '2025-01-19 16:11:09', '2025-01-19 20:33:14', NULL, 1, '0.00', 'SNF01-191', '2025-01-15 20:31:46', NULL, 2),
(192, 'Ropik', 'ropik-w5f8vt', '$2y$10$MKgos9f5KFzrpPxeqRk.GOJmHFglK9CS7O54GbvaY0wIWMPUsfq/2', 'rifkirey223@gmail.com', 3, 1, 'ppp', '*150', '2025011506@ropik', '2025011506', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6287879844497', 'false', 'true', '2025-01-19 17:04:08', '2025-01-19 17:08:11', NULL, 1, '0.00', 'SNF01-192', '2025-01-15 17:05:45', NULL, 2),
(193, 'Julian Ratu Malik Kusum', 'julian-owzzfq', '$2y$10$sMAO3onGmFrzjPG8TqjV2ORLucu4v0hCvQaCJIBs/23vVYZKHKZ8m', 'ratu@shanetfiber.my.id', 2, 1, 'ppp', '*152', '2025011508@ratu', '2025011508', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 024/007 Desa Maracang', '62859109735399', 'false', 'true', '2025-01-20 06:51:41', '2025-01-20 06:53:38', NULL, 1, '0.00', 'SNF01-193', '2025-01-15 06:51:56', NULL, 2),
(194, 'Iwan Setiawan', 'iwan-bixuiv', '$2y$10$pLfTc/vt2GlI6VR2r4F6uuvDHipRz77pKZUsCDm.D0en4Snlx6NFu', 'iwansetiawan@gmail.com', 2, 1, 'ppp', '*153', '2025020101@setiawan', '2025020101', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Rawabolang RT. 023/007 Desa Maracang', '6281287804240', 'false', 'true', '2025-01-21 10:17:38', '2025-01-21 10:19:27', NULL, 1, '0.00', 'SNF01-194', '2025-02-01 10:17:50', NULL, 2),
(195, 'Sarkum', 'sarkum-bhod9u', '$2y$10$e8rAi34CbsADFjfgI7ZMC.lV2/nmEPiyw.ymsRTZoiipQ0gn1mkzC', 'sarkum@shanetfiber.my.id', 1, 1, 'ppp', '*154', '2025020102@sarkum', '2025020102', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', '6283174427122', 'false', 'true', '2025-01-25 09:27:56', '2025-01-25 09:29:27', NULL, 1, '0.00', 'SNF01-195', '2025-02-01 09:28:14', NULL, 1),
(196, 'Hendi Suhendi', 'hendi-yo88ni', '$2y$10$mrXB/BTeVfXpT3svhx6J7ej5Zy/lfM1lKnIQYz61QL6031BmEUIS.', 'insyiramahren19@gmail.com', 3, 1, 'ppp', '*155', '2025020103@hendi', '2025020103', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Warnasari RT. 002/002 Ciseureuh Purwakarta', '6287778650580', 'false', 'true', '2025-01-28 10:30:45', '2025-01-28 10:44:44', NULL, 1, '0.00', 'SNF01-196', '2025-02-01 10:00:00', NULL, 1),
(197, 'Krisnawati', 'krisnawati-iypzzl', '$2y$10$mpAaiiSj4OdgDifV6S8rnupaVoeMYdKvpZfCJ8HF6tGIImAVxz.X2', 'ramadhanagutian@gmail.com', 3, 1, 'ppp', '*157', '2025020104@krisnawati', '2025020104', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Warnasari RT. 002/002 Ciseureuh Purwakarta', '6281995922670', 'false', 'true', '2025-01-28 10:31:59', '2025-01-28 10:45:59', NULL, 1, '0.00', 'SNF01-197', '2025-02-01 12:00:00', NULL, 1),
(198, 'Ryan Kurniawan', 'ryan-0yb2gt', '$2y$10$y6M6qck9bMN81D1H6fT3Uen8ajjoLKRZNZAv//QPeK/89zMYRHAPW', 'ryank1192@gmail.com', 3, 1, 'ppp', '*156', '2025020105@ryan', '2025020105', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kp. Krajan RT. 007/002 Maracang Babakancikao', '6285217195598', 'false', 'true', '2025-01-28 10:35:14', '2025-01-28 10:48:53', NULL, 1, '0.00', 'SNF01-198', '2025-02-01 13:00:00', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer_onts`
--

CREATE TABLE `customer_onts` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `merk_ont` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vlan_id` int DEFAULT NULL,
  `type_ont` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `port` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '80',
  `mac_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_onts`
--

INSERT INTO `customer_onts` (`id`, `customer_id`, `merk_ont`, `vlan_id`, `type_ont`, `username`, `password`, `port`, `mac_address`, `description`, `created_at`, `updated_at`) VALUES
(2, 2, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:12', '2024-12-17 10:07:12'),
(3, 3, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:12', '2024-12-17 10:07:12'),
(4, 4, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:13', '2024-12-17 10:07:13'),
(5, 5, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:13', '2024-12-17 10:07:13'),
(6, 6, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:13', '2024-12-17 10:07:13'),
(7, 7, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:13', '2024-12-17 10:07:13'),
(8, 8, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:13', '2024-12-17 10:07:13'),
(9, 9, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:14', '2024-12-17 10:07:14'),
(10, 10, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:14', '2024-12-17 10:07:14'),
(11, 11, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:14', '2024-12-17 10:07:14'),
(12, 12, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:14', '2024-12-17 10:07:14'),
(13, 13, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:14', '2024-12-17 10:07:14'),
(14, 14, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:15', '2024-12-17 10:07:15'),
(15, 15, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:15', '2024-12-17 10:07:15'),
(16, 16, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:15', '2024-12-17 10:07:15'),
(17, 17, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:15', '2024-12-17 10:07:15'),
(18, 18, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:15', '2024-12-17 10:07:15'),
(19, 19, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:15', '2024-12-17 10:07:15'),
(20, 20, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:16', '2024-12-17 10:07:16'),
(21, 21, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:16', '2024-12-17 10:07:16'),
(22, 22, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:16', '2024-12-17 10:07:16'),
(23, 23, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:16', '2024-12-17 10:07:16'),
(24, 24, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:16', '2024-12-17 10:07:16'),
(25, 25, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:17', '2024-12-17 10:07:17'),
(26, 26, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:17', '2024-12-17 10:07:17'),
(27, 27, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:17', '2024-12-17 10:07:17'),
(28, 28, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:17', '2024-12-17 10:07:17'),
(29, 29, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:17', '2024-12-17 10:07:17'),
(30, 30, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:18', '2024-12-17 10:07:18'),
(31, 31, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:18', '2024-12-17 10:07:18'),
(32, 32, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:18', '2024-12-17 10:07:18'),
(33, 33, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:18', '2024-12-17 10:07:18'),
(34, 34, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:18', '2024-12-17 10:07:18'),
(35, 35, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:19', '2024-12-17 10:07:19'),
(36, 36, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:19', '2024-12-17 10:07:19'),
(37, 37, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:19', '2024-12-17 10:07:19'),
(38, 38, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:19', '2024-12-17 10:07:19'),
(39, 39, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:19', '2024-12-17 10:07:19'),
(40, 40, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:19', '2024-12-17 10:07:19'),
(41, 41, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:20', '2024-12-17 10:07:20'),
(42, 42, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:20', '2024-12-17 10:07:20'),
(43, 43, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:20', '2024-12-17 10:07:20'),
(44, 44, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:20', '2024-12-17 10:07:20'),
(45, 45, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:20', '2024-12-17 10:07:20'),
(46, 46, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:21', '2024-12-17 10:07:21'),
(47, 47, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:21', '2024-12-17 10:07:21'),
(48, 48, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:21', '2024-12-17 10:07:21'),
(49, 49, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:21', '2024-12-17 10:07:21'),
(50, 50, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:21', '2024-12-17 10:07:21'),
(51, 51, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:22', '2024-12-17 10:07:22'),
(52, 52, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:22', '2024-12-17 10:07:22'),
(53, 53, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:22', '2024-12-17 10:07:22'),
(54, 54, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:22', '2024-12-17 10:07:22'),
(55, 55, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:22', '2024-12-17 10:07:22'),
(56, 56, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:22', '2024-12-17 10:07:22'),
(57, 57, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:23', '2024-12-17 10:07:23'),
(58, 58, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:23', '2024-12-17 10:07:23'),
(59, 59, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:23', '2024-12-17 10:07:23'),
(60, 60, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:23', '2024-12-17 10:07:23'),
(61, 61, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:23', '2024-12-17 10:07:23'),
(62, 62, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:24', '2024-12-17 10:07:24'),
(63, 63, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:24', '2024-12-17 10:07:24'),
(64, 64, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:24', '2024-12-17 10:07:24'),
(65, 65, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:24', '2024-12-17 10:07:24'),
(66, 66, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:24', '2024-12-17 10:07:24'),
(67, 67, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:25', '2024-12-17 10:07:25'),
(68, 68, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:25', '2024-12-17 10:07:25'),
(69, 69, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:25', '2024-12-17 10:07:25'),
(71, 71, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:25', '2024-12-17 10:07:25'),
(72, 72, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:26', '2024-12-17 10:07:26'),
(73, 73, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:26', '2024-12-17 10:07:26'),
(74, 74, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:26', '2024-12-17 10:07:26'),
(75, 75, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:26', '2024-12-17 10:07:26'),
(76, 76, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:26', '2024-12-17 10:07:26'),
(77, 77, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:26', '2024-12-17 10:07:26'),
(78, 78, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:27', '2024-12-17 10:07:27'),
(80, 80, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:27', '2024-12-17 10:07:27'),
(81, 81, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:27', '2024-12-17 10:07:27'),
(82, 82, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:27', '2024-12-17 10:07:27'),
(83, 83, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:28', '2024-12-17 10:07:28'),
(84, 84, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:28', '2024-12-17 10:07:28'),
(85, 85, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:28', '2024-12-17 10:07:28'),
(86, 86, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:28', '2024-12-17 10:07:28'),
(87, 87, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:28', '2024-12-17 10:07:28'),
(88, 88, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:29', '2024-12-17 10:07:29'),
(89, 89, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:29', '2024-12-17 10:07:29'),
(90, 90, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:29', '2024-12-17 10:07:29'),
(91, 91, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:29', '2024-12-17 10:07:29'),
(92, 92, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:29', '2024-12-17 10:07:29'),
(93, 93, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:30', '2024-12-17 10:07:30'),
(94, 94, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:30', '2024-12-17 10:07:30'),
(95, 95, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:30', '2024-12-17 10:07:30'),
(96, 96, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:30', '2024-12-17 10:07:30'),
(97, 97, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:30', '2024-12-17 10:07:30'),
(98, 98, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:31', '2024-12-17 10:07:31'),
(99, 99, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:31', '2024-12-17 10:07:31'),
(100, 100, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-17 10:07:31', '2024-12-17 10:07:31'),
(101, 101, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:34', '2024-12-26 18:42:34'),
(102, 102, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:35', '2024-12-26 18:42:35'),
(103, 103, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:35', '2024-12-26 18:42:35'),
(104, 104, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:35', '2024-12-26 18:42:35'),
(105, 105, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:35', '2024-12-26 18:42:35'),
(106, 106, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:35', '2024-12-26 18:42:35'),
(107, 107, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:36', '2024-12-26 18:42:36'),
(108, 108, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:36', '2024-12-26 18:42:36'),
(109, 109, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:36', '2024-12-26 18:42:36'),
(110, 110, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:36', '2024-12-26 18:42:36'),
(111, 111, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:36', '2024-12-26 18:42:36'),
(112, 112, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:37', '2024-12-26 18:42:37'),
(113, 113, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:37', '2024-12-26 18:42:37'),
(114, 114, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:37', '2024-12-26 18:42:37'),
(115, 115, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:37', '2024-12-26 18:42:37'),
(116, 116, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2024-12-26 18:42:37', '2024-12-26 18:42:37'),
(117, 117, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-01 20:18:39', '2025-01-01 20:18:39'),
(118, 118, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:31', '2025-01-02 11:11:31'),
(119, 119, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:31', '2025-01-02 11:11:31'),
(120, 120, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:32', '2025-01-02 11:11:32'),
(121, 121, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:32', '2025-01-02 11:11:32'),
(122, 122, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:32', '2025-01-02 11:11:32'),
(123, 123, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:32', '2025-01-02 11:11:32'),
(124, 124, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:32', '2025-01-02 11:11:32'),
(125, 125, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:32', '2025-01-02 11:11:32'),
(126, 126, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:33', '2025-01-02 11:11:33'),
(127, 127, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:33', '2025-01-02 11:11:33'),
(128, 128, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:33', '2025-01-02 11:11:33'),
(129, 129, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:33', '2025-01-02 11:11:33'),
(130, 130, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:33', '2025-01-02 11:11:33'),
(131, 131, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:34', '2025-01-02 11:11:34'),
(132, 132, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:34', '2025-01-02 11:11:34'),
(133, 133, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:34', '2025-01-02 11:11:34'),
(134, 134, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:34', '2025-01-02 11:11:34'),
(135, 135, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:34', '2025-01-02 11:11:34'),
(136, 136, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:35', '2025-01-02 11:11:35'),
(137, 137, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:35', '2025-01-02 11:11:35'),
(138, 138, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:35', '2025-01-02 11:11:35'),
(139, 139, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:35', '2025-01-02 11:11:35'),
(140, 140, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:35', '2025-01-02 11:11:35'),
(141, 141, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:35', '2025-01-02 11:11:35'),
(142, 142, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:36', '2025-01-02 11:11:36'),
(143, 143, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:36', '2025-01-02 11:11:36'),
(144, 144, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:36', '2025-01-02 11:11:36'),
(145, 145, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:36', '2025-01-02 11:11:36'),
(146, 146, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:36', '2025-01-02 11:11:36'),
(147, 147, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:37', '2025-01-02 11:11:37'),
(148, 148, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:37', '2025-01-02 11:11:37'),
(149, 149, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:37', '2025-01-02 11:11:37'),
(150, 150, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:37', '2025-01-02 11:11:37'),
(151, 151, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:37', '2025-01-02 11:11:37'),
(152, 152, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:38', '2025-01-02 11:11:38'),
(153, 153, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:38', '2025-01-02 11:11:38'),
(154, 154, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:38', '2025-01-02 11:11:38'),
(155, 155, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:38', '2025-01-02 11:11:38'),
(156, 156, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:38', '2025-01-02 11:11:38'),
(157, 157, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:39', '2025-01-02 11:11:39'),
(158, 158, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 11:11:39', '2025-01-02 11:11:39'),
(160, 160, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 19:05:43', '2025-01-02 19:05:43'),
(161, 161, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 20:47:33', '2025-01-02 20:47:33'),
(162, 162, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-02 20:52:32', '2025-01-02 20:52:32'),
(163, 163, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-07 13:13:28', '2025-01-07 13:13:28'),
(164, 164, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-08 15:01:21', '2025-01-08 15:01:21'),
(165, 165, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-08 15:03:19', '2025-01-08 15:03:19'),
(166, 166, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-08 15:32:12', '2025-01-08 15:32:12'),
(167, 167, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-08 15:35:55', '2025-01-08 15:35:55'),
(168, 168, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-08 18:13:28', '2025-01-08 18:13:28'),
(170, 170, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:04:55', '2025-01-09 19:04:55'),
(171, 171, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:06:34', '2025-01-09 19:06:34'),
(172, 172, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:07:56', '2025-01-09 19:07:56'),
(173, 173, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:09:10', '2025-01-09 19:09:10'),
(174, 174, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:10:23', '2025-01-09 19:10:23'),
(175, 175, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:11:35', '2025-01-09 19:11:35'),
(176, 176, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:12:31', '2025-01-09 19:12:31'),
(177, 177, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:14:25', '2025-01-09 19:14:25'),
(178, 178, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-09 19:20:47', '2025-01-09 19:20:47'),
(179, 179, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-10 13:45:06', '2025-01-10 13:45:06'),
(180, 180, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-10 15:57:35', '2025-01-10 15:57:35'),
(181, 181, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-11 19:20:39', '2025-01-11 19:20:39'),
(182, 182, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-11 19:32:12', '2025-01-11 19:32:12'),
(183, 183, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-12 12:56:49', '2025-01-12 12:56:49'),
(184, 184, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-13 06:28:59', '2025-01-13 06:28:59'),
(185, 185, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-13 09:18:42', '2025-01-13 09:18:42'),
(186, 186, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-13 09:19:52', '2025-01-13 09:19:52'),
(187, 187, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-13 09:21:11', '2025-01-13 09:21:11'),
(188, 188, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-13 09:22:39', '2025-01-13 09:22:39'),
(189, 189, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-13 18:20:39', '2025-01-13 18:20:39'),
(190, 190, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-17 11:38:46', '2025-01-17 11:38:46'),
(191, 191, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-19 16:11:09', '2025-01-19 16:11:09'),
(192, 192, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-19 17:04:08', '2025-01-19 17:04:08'),
(193, 193, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-20 06:51:41', '2025-01-20 06:51:41'),
(194, 194, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-21 10:17:38', '2025-01-21 10:17:38'),
(195, 195, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-25 09:27:56', '2025-01-25 09:27:56'),
(196, 196, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-28 10:30:45', '2025-01-28 10:30:45'),
(197, 197, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-28 10:31:59', '2025-01-28 10:31:59'),
(198, 198, NULL, NULL, NULL, NULL, NULL, '80', NULL, NULL, '2025-01-28 10:35:14', '2025-01-28 10:35:14');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_customers`
--

CREATE TABLE `deleted_customers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paket_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mikrotik_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secret_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_ppp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ppp_type_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `contact` text COLLATE utf8mb4_unicode_ci,
  `activation_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deleted_customers`
--

INSERT INTO `deleted_customers` (`id`, `name`, `slug`, `password`, `email`, `paket_id`, `mikrotik_id`, `secret_id`, `username`, `password_ppp`, `ppp_type_id`, `address`, `contact`, `activation_date`, `created_at`, `updated_at`) VALUES
(1, 'Mohamad Ridwan', 'mohamad-rrtlma', '$2y$10$fl5kgeCiUNqPCggGglwfUO0cVyYfcV6zuK4e.wxMTxyO02yiJxgQa', 'iwandewa66@gmail.com', '3', '1', NULL, '2025011105@ridwan', '2025011105', '5', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', '6285311901966', NULL, '2025-01-12 12:54:42', '2025-01-12 12:54:42');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merk_routers`
--

CREATE TABLE `merk_routers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `merk_routers`
--

INSERT INTO `merk_routers` (`id`, `name`, `disabled`, `created_at`, `updated_at`) VALUES
(1, 'Huawei', 'false', '2025-01-02 16:10:18', '2025-01-02 16:10:18'),
(2, 'TP-Link', 'false', '2025-01-02 16:11:50', '2025-01-02 16:11:50'),
(3, 'Totolink', 'false', '2025-01-02 16:12:24', '2025-01-02 16:12:24'),
(4, 'ZTE', 'false', '2025-01-02 16:13:09', '2025-01-02 16:13:09'),
(5, 'HSGQ', 'false', '2025-01-02 16:13:22', '2025-01-02 16:13:22'),
(6, 'Tenda', 'false', '2025-01-02 16:13:32', '2025-01-02 16:13:32');

-- --------------------------------------------------------

--
-- Table structure for table `midtrans`
--

CREATE TABLE `midtrans` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `midtrans_merchant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_client_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_server_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_sandbox_client_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_sandbox_server_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_is_production` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `midtrans`
--

INSERT INTO `midtrans` (`id`, `slug`, `name`, `midtrans_merchant_id`, `midtrans_client_key`, `midtrans_server_key`, `midtrans_sandbox_client_key`, `midtrans_sandbox_server_key`, `midtrans_is_production`, `disabled`, `created_at`, `updated_at`) VALUES
(1, 'midtrans', 'Midtrans', NULL, NULL, NULL, NULL, NULL, 'false', 'true', '2024-12-15 20:28:23', '2024-12-15 20:28:23');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
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
(37, '2024_12_28_103343_add_user_id_to_customers_table', 2),
(38, '2024_12_30_162724_add_vlan_id_to_customer_onts', 2),
(39, '2024_11_16_113613_create_whatsapp_messages_table', 3),
(40, '2024_11_16_113666_create_whatsapp_messages_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `mikrotiks`
--

CREATE TABLE `mikrotiks` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` bigint NOT NULL,
  `merk_router` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_router` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ros_version_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mikrotiks`
--

INSERT INTO `mikrotiks` (`id`, `name`, `slug`, `host`, `username`, `password`, `port`, `merk_router`, `type_router`, `disabled`, `created_at`, `updated_at`, `ros_version_id`) VALUES
(1, 'ShaNet Fiber', 'shanet-fiber-kofx', '103.176.96.151', 'shanum.net', 'shanum250117', 8803, NULL, NULL, 'false', '2024-12-16 12:49:11', '2025-01-02 16:33:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 2),
(1, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `number` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_status` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1=menunggu pembayaran, 2=sudah dibayar, 3=kadaluarsa, 4=batal',
  `snap_token` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `billing_id` bigint UNSIGNED NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_time` timestamp NULL DEFAULT NULL,
  `pdf_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pakets`
--

CREATE TABLE `pakets` (
  `id` bigint UNSIGNED NOT NULL,
  `mikrotik_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` int NOT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pakets`
--

INSERT INTO `pakets` (`id`, `mikrotik_id`, `name`, `profile`, `slug`, `description`, `price`, `disabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'ShaNet 0 (7 Mbps)', 'ShaNet_0_7Mbps', 'shanet-0-7mbps-zbav', 'Speed up to 7 Mbps', 100000, 'false', '2024-12-17 09:27:04', '2025-01-01 14:51:57', NULL),
(2, 1, 'ShaNet 1 (10 Mbps)', 'ShaNet_1_10Mbps', 'shanet-1-10mbps-sslv', 'Speed Up to 10 Mbps', 125000, 'false', '2024-12-17 09:27:04', '2025-01-01 14:51:57', NULL),
(3, 1, 'ShaNet 2 (15 Mbps)', 'ShaNet_2_15Mbps', 'shanet-2-15mbps-kxvk', 'Speed Up to 15 Mbps', 150000, 'false', '2024-12-17 09:26:40', '2025-01-01 14:51:57', NULL),
(4, 1, 'ShaNet 3 (30 Mbps)', 'ShaNet_3_30Mbps', 'shanet-3-30mbps-jsep', 'Speed Up to 30 Mbps', 200000, 'false', '2024-12-17 09:26:40', '2025-01-01 14:51:57', NULL),
(5, 1, 'ShaNet 4 (50 Mbps)', 'ShaNet_4_50Mbps', 'shanet-4-50mbps-pavs', 'Speed Up to 50 Mbps', 250000, 'false', '2024-12-17 09:26:40', '2025-01-01 14:51:57', NULL),
(7, 1, 'Expire', 'Expire', 'expire-kcb4', 'Customer Expire', 0, 'false', '2024-12-17 09:27:04', '2025-01-07 13:05:58', NULL),
(8, 1, 'L2TP Profile', 'L2TP Profile', 'l2tp-profile-tunh', 'L2TP Profile', 0, 'false', '2024-12-17 09:26:40', '2025-01-01 14:51:57', NULL),
(24, 1, 'Admin', 'Admin', 'admin-hgti', 'Admin', 0, 'false', '2025-01-07 13:03:04', '2025-01-07 13:03:04', NULL),
(25, 1, 'Free WiFi', 'Free_Wifi', 'free-wifi-xxpy', 'Free for public facilities', 0, 'false', '2025-01-06 07:20:45', '2025-01-07 13:08:30', NULL),
(26, 1, 'ShaNet D1 50Mbps', 'ShaNet_D_50Mbps', 'shanet-d-50mbps-yjod', 'Dedicated 50Mbps CIR 1:1', 1800000, 'false', '2025-01-24 21:38:47', '2025-01-24 21:49:32', NULL),
(27, 1, 'ShaNet D2 100Mbps', 'ShaNet_D_100Mbps', 'shanet-d-100mbps-kywx', 'Dedicated 100Mbps CIR 1:1', 3000000, 'false', '2025-01-24 21:38:47', '2025-01-24 21:49:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `slug`, `disabled`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Midtrans', 'midtrans', 'false', 'Midtrans Payment Gateway', '2024-12-15 20:28:23', '2024-12-15 20:28:23');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `title`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', NULL, 0, '2024-12-15 20:28:25', '2024-12-15 20:28:25'),
(2, 'is-admin', 'web', 'Is Admin', 1, '2024-12-15 20:28:25', '2024-12-15 20:28:25'),
(3, 'can-update', 'web', 'Can Update', 1, '2024-12-15 20:28:25', '2024-12-15 20:28:25'),
(4, 'Server', 'web', NULL, 0, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(5, 'Role', 'web', NULL, 0, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(6, 'Packet', 'web', NULL, 0, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(7, 'PPP', 'web', NULL, 0, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(8, 'User', 'web', NULL, 0, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(9, 'Customer', 'web', NULL, 0, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(10, 'Billing', 'web', NULL, 0, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(11, 'server-list', 'web', 'Server List', 4, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(12, 'server-create', 'web', 'Add Server', 4, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(13, 'server-edit', 'web', 'Edit Server', 4, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(14, 'server-delete', 'web', 'Delete Server', 4, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(15, 'role-list', 'web', 'Role List', 5, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(16, 'role-create', 'web', 'Create Role', 5, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(17, 'role-edit', 'web', 'Edit Role', 5, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(18, 'role-delete', 'web', 'Delete Role', 5, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(19, 'packet-list', 'web', 'Packet List', 6, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(20, 'packet-create', 'web', 'Add Packet', 6, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(21, 'packet-edit', 'web', 'Edit Packet', 6, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(22, 'packet-delete', 'web', 'Delete Packet', 6, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(23, 'secret-list', 'web', 'PPP Secret List', 7, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(24, 'secret-create', 'web', 'Add PPP Secret', 7, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(25, 'secret-edit', 'web', 'Edit PPP Secret', 7, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(26, 'secret-delete', 'web', 'Delete PPP Secret', 7, '2024-12-15 20:28:31', '2024-12-15 20:28:31'),
(27, 'secret-disable', 'web', 'Disable/Enable PPP Secret', 7, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(28, 'user-list', 'web', 'User List', 8, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(29, 'user-create', 'web', 'Add User', 8, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(30, 'user-edit', 'web', 'Edit User', 8, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(31, 'user-delete', 'web', 'Delete User', 8, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(32, 'customer-list', 'web', 'Customer List', 9, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(33, 'customer-create', 'web', 'Add Customer', 9, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(34, 'customer-edit', 'web', 'Edit Customer', 9, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(35, 'customer-delete', 'web', 'Delete Customer', 9, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(36, 'billing-list', 'web', 'Billing List', 10, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(37, 'billing-create', 'web', 'Create Billing', 10, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(38, 'billing-edit', 'web', 'Edit Billing', 10, '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(39, 'billing-delete', 'web', 'Delete Billing', 10, '2024-12-15 20:28:32', '2024-12-15 20:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
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
  `id` bigint UNSIGNED NOT NULL,
  `mikrotik_id` bigint UNSIGNED NOT NULL,
  `profile_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `local_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remote_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dns_server` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `use_ipv6` enum('no','yes','required','default') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes',
  `use_mpls` enum('no','yes','required','default') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `use_encryption` enum('no','yes','required','default') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `use_compression` enum('no','yes','default') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `max_upload` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_download` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_upload` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_download` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `burst_upload` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `burst_download` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `burst_threshold_upload` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `burst_threshold_download` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `burst_time_upload` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `burst_time_download` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` enum('1','2','3','4','5','6','7','8') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '8',
  `rasio_cir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_timeout` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idle_timeout` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `only_one` enum('no','yes','default') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `insert_queue_before` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_queue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queue_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `script_on_up` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `script_on_down` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppp_types`
--

CREATE TABLE `ppp_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ppp_types`
--

INSERT INTO `ppp_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'any', '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(2, 'async', '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(3, 'l2tp', '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(4, 'ovpn', '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(5, 'pppoe', '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(6, 'pptp', '2024-12-15 20:28:32', '2024-12-15 20:28:32'),
(7, 'sstp', '2024-12-15 20:28:32', '2024-12-15 20:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `billing_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paket_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paket_price` int NOT NULL,
  `tax` int DEFAULT NULL,
  `created_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `teller_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_time` timestamp NULL DEFAULT NULL,
  `payment_methode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_account_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `slug`, `periode`, `number`, `billing_number`, `customer_id`, `customer_name`, `customer_address`, `paket_name`, `paket_price`, `tax`, `created_name`, `teller_name`, `payment_time`, `payment_methode`, `bank_name`, `account_name`, `account_number`, `deposit_bank_name`, `deposit_account_name`, `deposit_account_number`, `reference_id`, `created_at`, `updated_at`) VALUES
(1, 'inv-117-lep8', 'January 2025', '00001-KWT-January-2025', '117-u5qp-01-2025', 117, 'Sularno', 'Perum Griya Asri Blok K3 No. 3 RT 010/011 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-01 20:19:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-01 20:19:04', '2025-01-01 20:19:04'),
(2, '117-umuj', 'January 2025', 'KWT-00001-January-2025', '117-7uvz-01-2025', 117, 'Sularno', 'Perum Griya Asri Blok K3 No. 3 RT 010/011 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-02 18:52:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-02 18:52:36', '2025-01-02 18:52:36'),
(3, 'inv-160-fjdp', 'January 2025', '00002-KWT-January-2025', '160-2oml-01-2025', 160, 'Sule', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-02 19:06:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-02 19:06:13', '2025-01-02 19:06:13'),
(4, '6-1ny7', 'January 2025', 'KWT-00001-January-2025', '6-ucgt-01-2025', 6, 'Cucu Suryani', 'Kp. Sukamaju RT 009/007 No. 37 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-02 19:20:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-02 19:20:09', '2025-01-02 19:20:09'),
(6, '17-kn4y', 'January 2025', 'KWT-00003-January-2025', '17-oirq-01-2025', 17, 'Iip', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-02 19:26:09', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'Mandiri', 'Iip', NULL, NULL, '2025-01-02 19:26:09', '2025-01-02 19:26:09'),
(7, '4-jxrx', 'January 2025', 'KWT-00004-January-2025', '4-1ztx-01-2025', 4, 'Pak Surya', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-02 19:34:24', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-02 19:34:24', '2025-01-02 19:34:24'),
(8, '3-gieb', 'January 2025', 'KWT-00005-January-2025', '3-5htw-01-2025', 3, 'Asep Nasihin', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-02 19:35:30', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-02 19:35:30', '2025-01-02 19:35:30'),
(9, '86-qiy2', 'January 2025', 'KWT-00006-January-2025', '86-qwam-01-2025', 86, 'Mujinah', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-03 04:42:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 04:42:58', '2025-01-03 04:42:58'),
(10, '55-7uoa', 'January 2025', 'KWT-00007-January-2025', '55-l7pk-01-2025', 55, 'Agustian Setiadi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-03 09:04:34', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', 'Bank BCA', 'Nayunda Gustira', NULL, NULL, '2025-01-03 09:04:34', '2025-01-03 09:04:34'),
(11, '64-zp3c', 'January 2025', 'KWT-00008-January-2025', '64-p4d0-01-2025', 64, 'Kodrat (GOR ADHIGA)', 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-03 09:25:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 09:25:58', '2025-01-03 09:25:58'),
(12, '19-rtrt', 'January 2025', 'KWT-00009-January-2025', '19-y1c7-01-2025', 19, 'Pak Lili', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-03 11:23:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 11:23:34', '2025-01-03 11:23:34'),
(13, '152-gtal', 'January 2025', 'KWT-00010-January-2025', '152-jsly-01-2025', 152, 'Rifal Yudhi Pratama', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-03 13:05:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:05:23', '2025-01-03 13:05:23'),
(14, '108-qvvh', 'January 2025', 'KWT-00011-January-2025', '108-cp7a-01-2025', 108, 'Neng Siti', 'Kp. Krajan RT. 007/002 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-03 13:06:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:06:24', '2025-01-03 13:06:24'),
(15, '155-nepk', 'January 2025', 'KWT-00012-January-2025', '155-ymgj-01-2025', 155, 'Anggi', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-03 13:06:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:06:53', '2025-01-03 13:06:53'),
(16, '106-vq5s', 'January 2025', 'KWT-00013-January-2025', '106-kekb-01-2025', 106, 'Misbah Toko Bangunan', 'Kp. Krajan RT. 008/002 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-03 13:08:37', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:08:37', '2025-01-03 13:08:37'),
(17, '89-gpvk', 'January 2025', 'KWT-00014-January-2025', '89-dbxl-01-2025', 89, 'Fadilah Nurhasanah', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-03 13:09:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:09:23', '2025-01-03 13:09:23'),
(18, '152-gtal', 'January 2025', 'KWT-00015-January-2025', '152-jsly-01-2025', 152, 'Rifal Yudhi Pratama', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-03 13:09:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:09:56', '2025-01-03 13:09:56'),
(19, '112-wspw', 'January 2025', 'KWT-00016-January-2025', '112-rihv-01-2025', 112, 'Pak Ojak', 'Kp. Krajan RT. 008/002 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-03 13:12:17', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:12:17', '2025-01-03 13:12:17'),
(20, '113-umct', 'January 2025', 'KWT-00017-January-2025', '113-e5xz-01-2025', 113, 'Redi Natisna', 'Kp. Krajan RT. 010/003 Desa Maracang', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-03 13:12:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 13:12:38', '2025-01-03 13:12:38'),
(21, '77-dxvt', 'January 2025', 'KWT-00018-January-2025', '77-e8pg-01-2025', 77, 'Irvan Mohammad Regent', 'Perum Griya Asri Blok K1 No. 10 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-03 17:55:47', 'transfer', 'Bank Central Asia', 'PT. Global Sarana Elektronik', '2317111157', NULL, NULL, NULL, NULL, '2025-01-03 17:55:47', '2025-01-03 17:55:47'),
(22, 'inv-161-hhfr', 'January 2025', '00019-KWT-January-2025', '161-9gqd-01-2025', 161, 'Bambang Sucipto', 'Kp. Sukamaju RT. 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-03 18:29:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-03 18:29:14', '2025-01-03 18:29:14'),
(23, '124-6e5u', 'January 2025', 'KWT-00001-January-2025', '124-kh8x-01-2025', 124, 'Iis Ismawati', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-04 07:06:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 07:06:41', '2025-01-04 07:06:41'),
(24, '125-nzzh', 'January 2025', 'KWT-00002-January-2025', '125-uj4i-01-2025', 125, 'Desi Nurdianti', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-04 07:07:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 07:07:23', '2025-01-04 07:07:23'),
(25, '123-jff7', 'January 2025', 'KWT-00003-January-2025', '123-uynx-01-2025', 123, 'Siti Mariam', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-04 07:08:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 07:08:10', '2025-01-04 07:08:10'),
(26, '151-do5m', 'January 2025', 'KWT-00004-January-2025', '151-34gp-01-2025', 151, 'Fera', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-04 07:20:59', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 07:20:59', '2025-01-04 07:20:59'),
(27, '145-xvib', 'January 2025', 'KWT-00005-January-2025', '145-qami-01-2025', 145, 'Darma Wirawan', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-04 08:34:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 08:34:39', '2025-01-04 08:34:39'),
(28, 'inv-162-eoag', 'January 2025', '00006-KWT-January-2025', '162-mpto-01-2025', 162, 'Bunga Rinady Sandy', 'Kp. Rawabolang RT. 023/007 Maracang Babakancikao', 'ShaNet 3 (30 Mbps)', 200000, NULL, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-04 09:39:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 09:39:20', '2025-01-04 09:39:20'),
(29, '118-qpft', 'January 2025', 'KWT-00001-January-2025', '118-ymaq-01-2025', 118, 'Wida', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-04 13:25:16', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 13:25:16', '2025-01-04 13:25:16'),
(30, '49-bd1m', 'January 2025', 'KWT-00002-January-2025', '49-6zag-01-2025', 49, 'Ferry Kurniawan', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-04 15:24:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-04 15:24:56', '2025-01-04 15:24:56'),
(31, '38-xwlb', 'January 2025', 'KWT-00003-January-2025', '38-znoj-01-2025', 38, 'Dede Suherman', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-05 06:41:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 06:41:07', '2025-01-05 06:41:07'),
(32, '76-5swr', 'January 2025', 'KWT-00004-January-2025', '76-tvqt-01-2025', 76, 'Nurhayati', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-05 06:51:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 06:51:41', '2025-01-05 06:51:41'),
(33, '26-h6x5', 'January 2025', 'KWT-00005-January-2025', '26-fyhr-01-2025', 26, 'Novi Eka Purwaningsih', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-05 06:51:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 06:51:52', '2025-01-05 06:51:52'),
(34, '94-uu9p', 'January 2025', 'KWT-00006-January-2025', '94-p1lp-01-2025', 94, 'Risna Suhertini', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-05 07:59:59', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-05 07:59:59', '2025-01-05 07:59:59'),
(35, '22-cgb7', 'January 2025', 'KWT-00007-January-2025', '22-bp34-01-2025', 22, 'AA Ramli (Rafka)', 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-05 09:05:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 09:05:08', '2025-01-05 09:05:08'),
(36, '111-xcu5', 'January 2025', 'KWT-00008-January-2025', '111-fvx7-01-2025', 111, 'Nurul Aeni', 'Kp. Krajan RT. 008/002 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-05 09:30:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 09:30:27', '2025-01-05 09:30:27'),
(37, '8-ryw5', 'January 2025', 'KWT-00009-January-2025', '8-fdxf-01-2025', 8, 'Teh Darsih', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-05 10:40:48', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 10:40:48', '2025-01-05 10:40:48'),
(38, '149-vrr8', 'January 2025', 'KWT-00010-January-2025', '149-qsyo-01-2025', 149, 'Syarip Hidayat', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-05 10:58:25', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 10:58:25', '2025-01-05 10:58:25'),
(39, '100-tgmn', 'January 2025', 'KWT-00011-January-2025', '100-xb7i-01-2025', 100, 'Yayah Komariah', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-05 12:18:40', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 12:18:40', '2025-01-05 12:18:40'),
(40, '21-pmsq', 'January 2025', 'KWT-00012-January-2025', '21-yrcz-01-2025', 21, 'Ratih Aisyah', 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-05 12:19:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 12:19:27', '2025-01-05 12:19:27'),
(41, '126-eb3a', 'January 2025', 'KWT-00013-January-2025', '126-921l-01-2025', 126, 'Adis', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-05 18:31:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-05 18:31:50', '2025-01-05 18:31:50'),
(42, '59-wzop', 'January 2025', 'KWT-00014-January-2025', '59-aex6-01-2025', 59, 'Bu Ani', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-06 06:33:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 06:33:44', '2025-01-06 06:33:44'),
(43, '28-lllv', 'January 2025', 'KWT-00015-January-2025', '28-o6nl-01-2025', 28, 'Euis Ratnasari', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-06 07:12:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 07:12:19', '2025-01-06 07:12:19'),
(44, '81-pqnn', 'January 2025', 'KWT-00016-January-2025', '81-tzkf-01-2025', 81, 'Agus Rahmat (Azril)', 'Kp. Sukamaju RT 009/007 Gg. Adipati Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-06 08:02:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 08:02:14', '2025-01-06 08:02:14'),
(45, '5-bcj8', 'January 2025', 'KWT-00017-January-2025', '5-2nmj-01-2025', 5, 'Koesnadi', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-06 08:03:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 08:03:54', '2025-01-06 08:03:54'),
(46, '34-rlwz', 'January 2025', 'KWT-00018-January-2025', '34-lnu3-01-2025', 34, 'Hafis', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-06 08:04:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 08:04:53', '2025-01-06 08:04:53'),
(47, '129-a6s7', 'January 2025', 'KWT-00019-January-2025', '129-eznn-01-2025', 129, 'Heni', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 10:38:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 10:38:50', '2025-01-06 10:38:50'),
(48, '133-kys5', 'January 2025', 'KWT-00020-January-2025', '133-yqvi-01-2025', 133, 'Pepen', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 10:39:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 10:39:43', '2025-01-06 10:39:43'),
(49, '132-ajao', 'January 2025', 'KWT-00021-January-2025', '132-9ey8-01-2025', 132, 'Cici', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 10:44:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 10:44:50', '2025-01-06 10:44:50'),
(50, '128-2l3u', 'January 2025', 'KWT-00022-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:10:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:10:55', '2025-01-06 13:10:55'),
(51, '128-2l3u', 'January 2025', 'KWT-00023-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:10:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:10:55', '2025-01-06 13:10:55'),
(52, '128-2l3u', 'January 2025', 'KWT-00023-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:10:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:10:57', '2025-01-06 13:10:57'),
(53, '128-2l3u', 'January 2025', 'KWT-00024-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:10:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:10:58', '2025-01-06 13:10:58'),
(54, '128-2l3u', 'January 2025', 'KWT-00025-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:00', '2025-01-06 13:11:00'),
(55, '128-2l3u', 'January 2025', 'KWT-00026-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:01', '2025-01-06 13:11:01'),
(56, '128-2l3u', 'January 2025', 'KWT-00027-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:02', '2025-01-06 13:11:02'),
(57, '128-2l3u', 'January 2025', 'KWT-00028-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:02', '2025-01-06 13:11:02'),
(58, '128-2l3u', 'January 2025', 'KWT-00029-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:02', '2025-01-06 13:11:02'),
(59, '128-2l3u', 'January 2025', 'KWT-00030-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:03', '2025-01-06 13:11:03'),
(60, '128-2l3u', 'January 2025', 'KWT-00030-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:03', '2025-01-06 13:11:03'),
(61, '128-2l3u', 'January 2025', 'KWT-00030-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:03', '2025-01-06 13:11:03'),
(62, '128-2l3u', 'January 2025', 'KWT-00031-January-2025', '128-oedi-01-2025', 128, 'Imar Sulaeman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 13:11:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 13:11:05', '2025-01-06 13:11:05'),
(63, '75-ubdm', 'January 2025', 'KWT-00032-January-2025', '75-6ou1-01-2025', 75, 'Intan Ayu Sri Wahyuni', 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-06 15:16:32', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-06 15:16:32', '2025-01-06 15:16:32'),
(64, '134-iwpn', 'January 2025', 'KWT-00033-January-2025', '134-eoom-01-2025', 134, 'Wayanto', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-06 15:29:00', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-06 15:29:00', '2025-01-06 15:29:00'),
(65, '60-ujr0', 'January 2025', 'KWT-00034-January-2025', '60-b5dl-01-2025', 60, 'Yeyet Abdulrachman', 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', 'ShaNet 4 (50 Mbps)', 250000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-07 04:14:11', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-07 04:14:11', '2025-01-07 04:14:11'),
(66, '74-mwf6', 'January 2025', 'KWT-00035-January-2025', '74-i3uf-01-2025', 74, 'Moh. Gian Firtyanza', 'Perum Griya Asri Blok D2 No. 15 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-07 04:48:26', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-07 04:48:26', '2025-01-07 04:48:26'),
(67, '103-rdvg', 'January 2025', 'KWT-00036-January-2025', '103-catd-01-2025', 103, 'Wahyu Maulana', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 09:52:30', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 09:52:30', '2025-01-07 09:52:30'),
(68, '121-1ahp', 'January 2025', 'KWT-00037-January-2025', '121-cmlt-01-2025', 121, 'Nuryani', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 09:52:53', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 09:52:53', '2025-01-07 09:52:53'),
(69, '130-beza', 'January 2025', 'KWT-00038-January-2025', '130-vu5x-01-2025', 130, 'Juju Juarsih', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 09:53:11', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 09:53:11', '2025-01-07 09:53:11'),
(70, '1-puhq', 'January 2025', 'KWT-00039-January-2025', '1-ddl2-01-2025', 1, 'Muh Subhan', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-07 10:04:01', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-07 10:04:01', '2025-01-07 10:04:01'),
(71, '136-kx6h', 'January 2025', 'KWT-00040-January-2025', '136-8qo0-01-2025', 136, 'Miung', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 12:33:45', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 12:33:45', '2025-01-07 12:33:45'),
(72, '105-1xxi', 'January 2025', 'KWT-00041-January-2025', '105-a5ka-01-2025', 105, 'Neneng Suratni', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 12:34:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 12:34:19', '2025-01-07 12:34:19'),
(73, 'inv-163-wisy', 'January 2025', '00042-KWT-January-2025', '163-oztd-01-2025', 163, 'Budi Kosasih', 'Kp. Rawabolang RT. 023/007 Maracang Babakancikao', 'Admin', 0, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-07 13:14:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 13:14:02', '2025-01-07 13:14:02'),
(74, '131-w7jd', 'January 2025', 'KWT-00001-January-2025', '131-diqm-01-2025', 131, 'Juhdi', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 16:10:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 16:10:20', '2025-01-07 16:10:20'),
(75, '153-lvtf', 'January 2025', 'KWT-00002-January-2025', '153-qy6f-01-2025', 153, 'Ani Sumarni', 'Kp. Rawabolang RT. 025/007 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 17:44:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 17:44:19', '2025-01-07 17:44:19'),
(76, '141-eumh', 'January 2025', 'KWT-00003-January-2025', '141-xttp-01-2025', 141, 'Engko', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-07 19:11:16', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-07 19:11:16', '2025-01-07 19:11:16'),
(77, '71-w0xh', 'January 2025', 'KWT-00004-January-2025', '71-uux6-01-2025', 71, 'Asep Topik Hidayat', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-08 08:18:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-08 08:18:20', '2025-01-08 08:18:20'),
(78, '27-flsp', 'January 2025', 'KWT-00005-January-2025', '27-3idt-01-2025', 27, 'Hasma Wati', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-08 13:51:46', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-08 13:51:46', '2025-01-08 13:51:46'),
(79, '13-ckd4', 'January 2025', 'KWT-00006-January-2025', '13-oneq-01-2025', 13, 'Mamah Anggi', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-08 14:55:55', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-08 14:55:55', '2025-01-08 14:55:55'),
(80, '154-u8km', 'January 2025', 'KWT-00007-January-2025', '154-omus-01-2025', 154, 'Hendri Gunawan', 'Kp. Rawabolang RT. 025/007 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-08 15:04:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-08 15:04:54', '2025-01-08 15:04:54'),
(81, 'inv-166-7fin', 'January 2025', '00008-KWT-January-2025', '166-l9df-01-2025', 166, 'POS RT.11/03 Krajan', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'Free WiFi', 0, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-08 15:32:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-08 15:32:47', '2025-01-08 15:32:47'),
(82, '138-6wlu', 'January 2025', 'KWT-00001-January-2025', '138-fxbc-01-2025', 138, 'Okis', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-08 16:39:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-08 16:39:07', '2025-01-08 16:39:07'),
(83, '43-gj5a', 'January 2025', 'KWT-00002-January-2025', '43-a0b4-01-2025', 43, 'Tri Nurhidayati', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-08 17:37:53', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-08 17:37:53', '2025-01-08 17:37:53'),
(84, '139-keei', 'January 2025', 'KWT-00003-January-2025', '139-1906-01-2025', 139, 'RT Oji', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-08 18:06:38', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-08 18:06:38', '2025-01-08 18:06:38'),
(85, '54-wulk', 'January 2025', 'KWT-00004-January-2025', '54-vhlg-01-2025', 54, 'Agista Utari', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-08 19:06:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-08 19:06:09', '2025-01-08 19:06:09'),
(86, '80-axhk', 'January 2025', 'KWT-00005-January-2025', '80-tfc1-01-2025', 80, 'Iis Nurjanah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-09 12:42:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 12:42:44', '2025-01-09 12:42:44'),
(87, '18-pkvt', 'January 2025', 'KWT-00006-January-2025', '18-7c9u-01-2025', 18, 'Ahmad Saepuloh', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-09 12:47:51', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 12:47:51', '2025-01-09 12:47:51'),
(88, '87-iygh', 'January 2025', 'KWT-00007-January-2025', '87-3vpg-01-2025', 87, 'Rodiah', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-09 13:17:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 13:17:02', '2025-01-09 13:17:02'),
(89, '148-nrm8', 'January 2025', 'KWT-00008-January-2025', '148-2h0k-01-2025', 148, 'Herman', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-09 18:44:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 18:44:04', '2025-01-09 18:44:04'),
(90, '72-ypjn', 'January 2025', 'KWT-00009-January-2025', '72-d9qp-01-2025', 72, 'Egy Laksma Suandi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-09 18:53:31', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 18:53:31', '2025-01-09 18:53:31'),
(91, 'inv-178-ygxf', 'January 2025', '00010-KWT-January-2025', '178-n4qk-01-2025', 178, 'Budi Kosasih', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'Admin', 0, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-09 19:21:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 19:21:06', '2025-01-09 19:21:06'),
(92, '82-zinx', 'January 2025', 'KWT-00001-January-2025', '82-jmra-01-2025', 82, 'Neng Helawati', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-09 19:25:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-09 19:25:13', '2025-01-09 19:25:13'),
(93, '66-k2uz', 'January 2025', 'KWT-00002-January-2025', '66-s5zs-01-2025', 66, 'Ma Aisah', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-10 08:41:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 08:41:26', '2025-01-10 08:41:26'),
(94, '85-vl0e', 'January 2025', 'KWT-00003-January-2025', '85-pc9p-01-2025', 85, 'Dani Ramdani', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-10 08:42:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 08:42:04', '2025-01-10 08:42:04'),
(95, '65-lpdp', 'January 2025', 'KWT-00004-January-2025', '65-l5qd-01-2025', 65, 'Lulu Andriani', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-10 08:42:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 08:42:23', '2025-01-10 08:42:23'),
(96, '127-tqaw', 'January 2025', 'KWT-00005-January-2025', '127-9ybj-01-2025', 127, 'Satim Silegar', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 10:59:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 10:59:24', '2025-01-10 10:59:24'),
(97, '142-2noz', 'January 2025', 'KWT-00006-January-2025', '142-hkgh-01-2025', 142, 'Arya Cahyana Putra', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 10:59:49', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 10:59:49', '2025-01-10 10:59:49'),
(98, '137-ttdo', 'January 2025', 'KWT-00007-January-2025', '137-6zeu-01-2025', 137, 'Dadan Hamdani', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 12:37:43', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 12:37:43', '2025-01-10 12:37:43'),
(99, '157-g5qn', 'January 2025', 'KWT-00008-January-2025', '157-aia9-01-2025', 157, 'Kuswandi', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 12:38:08', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 12:38:08', '2025-01-10 12:38:08'),
(100, '146-w64i', 'January 2025', 'KWT-00009-January-2025', '146-fldr-01-2025', 146, 'Sulis (Rumah)', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 12:38:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 12:38:33', '2025-01-10 12:38:33'),
(101, '156-zfnv', 'January 2025', 'KWT-00010-January-2025', '156-jb1p-01-2025', 156, 'Sulis (Toko)', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 12:38:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 12:38:39', '2025-01-10 12:38:39'),
(102, '119-1vim', 'January 2025', 'KWT-00011-January-2025', '119-mbal-01-2025', 119, 'Iqbal Budi Prasetya', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 12:39:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 12:39:22', '2025-01-10 12:39:22'),
(103, '115-2snn', 'January 2025', 'KWT-00012-January-2025', '115-knbr-01-2025', 115, 'Pak Agus', 'Kp. Babakan Jati RT. RW. 06 Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 12:46:37', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 12:46:37', '2025-01-10 12:46:37'),
(104, 'inv-179-vady', 'January 2025', '00013-KWT-January-2025', '179-qg6u-01-2025', 179, 'Alin Senia Trisiani', 'Jl. Sukamaju RT 09/07 Ciseureuh Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-10 13:45:17', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 13:45:17', '2025-01-10 13:45:17'),
(105, '47-yphw', 'January 2025', 'KWT-00001-January-2025', '47-8jez-01-2025', 47, 'Sri Sulastri', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-10 15:37:09', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-10 15:37:09', '2025-01-10 15:37:09'),
(106, '144-i2z2', 'January 2025', 'KWT-00002-January-2025', '144-u9wu-01-2025', 144, 'Salmah Saleha', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:38:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:38:22', '2025-01-10 15:38:22'),
(107, '104-8pqc', 'January 2025', 'KWT-00003-January-2025', '104-yg0n-01-2025', 104, 'Rika Rahmawati', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:39:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:39:24', '2025-01-10 15:39:24'),
(108, '104-8pqc', 'January 2025', 'KWT-00004-January-2025', '104-yg0n-01-2025', 104, 'Rika Rahmawati', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:39:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:39:24', '2025-01-10 15:39:24'),
(109, '104-8pqc', 'January 2025', 'KWT-00004-January-2025', '104-yg0n-01-2025', 104, 'Rika Rahmawati', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:39:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:39:24', '2025-01-10 15:39:24'),
(110, '104-8pqc', 'January 2025', 'KWT-00004-January-2025', '104-yg0n-01-2025', 104, 'Rika Rahmawati', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:39:24', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:39:24', '2025-01-10 15:39:24'),
(111, '120-eovc', 'January 2025', 'KWT-00004-January-2025', '120-d4pv-01-2025', 120, 'Usman Abdul Sobari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:40:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:40:29', '2025-01-10 15:40:29'),
(112, '120-eovc', 'January 2025', 'KWT-00005-January-2025', '120-d4pv-01-2025', 120, 'Usman Abdul Sobari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:40:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:40:33', '2025-01-10 15:40:33'),
(113, '120-eovc', 'January 2025', 'KWT-00006-January-2025', '120-d4pv-01-2025', 120, 'Usman Abdul Sobari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 100000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 15:40:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 15:40:35', '2025-01-10 15:40:35'),
(114, '114-bj7q', 'January 2025', 'KWT-00007-January-2025', '114-qz6y-01-2025', 114, 'Agus Hermanto', 'Kp. Krajan RT. 010/003 Maracang Babakancikao', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 17:49:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 17:49:41', '2025-01-10 17:49:41'),
(115, '114-bj7q', 'January 2025', 'KWT-00008-January-2025', '114-qz6y-01-2025', 114, 'Agus Hermanto', 'Kp. Krajan RT. 010/003 Maracang Babakancikao', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 17:49:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 17:49:44', '2025-01-10 17:49:44'),
(116, '58-2jgq', 'January 2025', 'KWT-00009-January-2025', '58-yiqn-01-2025', 58, 'Pak Jepri', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-10 19:36:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 19:36:21', '2025-01-10 19:36:21'),
(117, '58-2jgq', 'January 2025', 'KWT-00010-January-2025', '58-yiqn-01-2025', 58, 'Pak Jepri', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-10 19:36:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 19:36:23', '2025-01-10 19:36:23'),
(118, '110-04gx', 'January 2025', 'KWT-00011-January-2025', '110-x5is-01-2025', 110, 'Amania Kusumah', 'Kp. Krajan RT. 008/002 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-10 21:21:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-10 21:21:02', '2025-01-10 21:21:02'),
(119, '67-6w7x', 'January 2025', 'KWT-00012-January-2025', '67-surq-01-2025', 67, 'Alya Iklima Hidayah', 'Perum Panorama Indah B2-4 RT 013/013 Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 05:37:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 05:37:21', '2025-01-11 05:37:21'),
(120, '69-rjmt', 'January 2025', 'KWT-00013-January-2025', '69-aka8-01-2025', 69, 'Teh Enen', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-11 08:00:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 08:00:20', '2025-01-11 08:00:20'),
(121, '56-kxvu', 'January 2025', 'KWT-00014-January-2025', '56-eo4a-01-2025', 56, 'Pak Iyan', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 08:41:56', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-11 08:41:56', '2025-01-11 08:41:56'),
(122, '33-etls', 'January 2025', 'KWT-00015-January-2025', '33-bulg-01-2025', 33, 'Debbie Febriana', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 08:42:17', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 08:42:17', '2025-01-11 08:42:17'),
(123, 'inv-167-w5ww', 'January 2025', '00016-KWT-January-2025', '167-xaoz-01-2025', 167, 'Sa\'ad', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 09:27:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 09:27:47', '2025-01-11 09:27:47'),
(124, '93-zzpa', 'January 2025', 'KWT-00001-January-2025', '93-7zgr-01-2025', 93, 'Rini Seminar', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-11 09:52:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 09:52:41', '2025-01-11 09:52:41'),
(125, '39-vng6', 'January 2025', 'KWT-00002-January-2025', '39-1oqu-01-2025', 39, 'Ida Siti', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-11 10:22:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 10:22:34', '2025-01-11 10:22:34'),
(126, '39-vng6', 'January 2025', 'KWT-00003-January-2025', '39-1oqu-01-2025', 39, 'Ida Siti', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-11 10:22:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 10:22:36', '2025-01-11 10:22:36'),
(127, 'inv-168-8sb6', 'January 2025', '00004-KWT-January-2025', '168-sdgq-01-2025', 168, 'Komarudin', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 10:26:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 10:26:21', '2025-01-11 10:26:21'),
(128, 'inv-164-shqc', 'January 2025', '00001-KWT-January-2025', '164-q3br-01-2025', 164, 'Iin Indrawati', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 3 (30 Mbps)', 200000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 11:39:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 11:39:47', '2025-01-11 11:39:47'),
(129, '7-dr9a', 'January 2025', 'KWT-00001-January-2025', '7-dgrb-01-2025', 7, 'Sumarni', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-11 11:53:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 11:53:20', '2025-01-11 11:53:20'),
(130, 'inv-180-dyhh', 'January 2025', '00002-KWT-January-2025', '180-qjmd-01-2025', 180, 'Ika Sartika', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 12:00:17', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 12:00:17', '2025-01-11 12:00:17'),
(131, '140-mq6e', 'January 2025', 'KWT-00001-January-2025', '140-3ioc-01-2025', 140, 'Suntari', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-11 13:27:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 13:27:03', '2025-01-11 13:27:03'),
(132, '95-qn3v', 'January 2025', 'KWT-00002-January-2025', '95-enz3-01-2025', 95, 'Dewi Sutriani', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-11 13:27:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 13:27:33', '2025-01-11 13:27:33'),
(133, 'inv-165-czyc', 'January 2025', '00003-KWT-January-2025', '165-okl4-01-2025', 165, 'Dasih Juarsih', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 3 (30 Mbps)', 200000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 16:42:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 16:42:06', '2025-01-11 16:42:06'),
(134, 'inv-177-yogk', 'January 2025', '00001-KWT-January-2025', '177-6fxx-01-2025', 177, 'Virta Eka Yulistia', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 3 (30 Mbps)', 200000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-11 17:46:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-11 17:46:21', '2025-01-11 17:46:21'),
(135, '84-ozx6', 'January 2025', 'KWT-00001-January-2025', '84-1pol-01-2025', 84, 'Iman Nurjamansyah', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-12 08:27:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 08:27:21', '2025-01-12 08:27:21'),
(136, 'inv-172-ejzz', 'January 2025', '00002-KWT-January-2025', '172-09ij-01-2025', 172, 'Iwan Saepuloh', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 09:07:15', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 09:07:15', '2025-01-12 09:07:15'),
(137, '88-axcf', 'January 2025', 'KWT-00001-January-2025', '88-aqoq-01-2025', 88, 'Gilang Gumilang', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-12 09:28:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 09:28:06', '2025-01-12 09:28:06'),
(138, '2-9je3', 'January 2025', 'KWT-00002-January-2025', '2-ygfb-01-2025', 2, 'Iis Sugiarti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 11:26:13', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-12 11:26:13', '2025-01-12 11:26:13'),
(139, 'inv-171-uvch', 'January 2025', '00003-KWT-January-2025', '171-xmzl-01-2025', 171, 'Maah Siti Komala', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 11:27:45', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 11:27:45', '2025-01-12 11:27:45'),
(140, 'inv-170-d6cw', 'January 2025', '00001-KWT-January-2025', '170-xpz9-01-2025', 170, 'Cecep Sodikin', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 12:26:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 12:26:41', '2025-01-12 12:26:41'),
(141, '135-a71o', 'January 2025', 'KWT-00001-January-2025', '135-pv7u-01-2025', 135, 'Heri Suandi', 'Kp. Rawabolang RT. 025/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-12 12:56:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 12:56:05', '2025-01-12 12:56:05'),
(142, 'inv-183-ti84', 'January 2025', '00002-KWT-January-2025', '183-d1oh-01-2025', 183, 'Mohamad Ridwan', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 12:57:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 12:57:06', '2025-01-12 12:57:06'),
(143, 'inv-175-6g2d', 'January 2025', '00001-KWT-January-2025', '175-slvr-01-2025', 175, 'Yati Haryati', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 15:33:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 15:33:58', '2025-01-12 15:33:58'),
(144, 'inv-176-9mqq', 'January 2025', '00001-KWT-January-2025', '176-ddt8-01-2025', 176, 'Windi Gustiani', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 16:16:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 16:16:09', '2025-01-12 16:16:09'),
(145, 'inv-173-11hk', 'January 2025', '00001-KWT-January-2025', '173-cm2o-01-2025', 173, 'Saepudin', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 16:47:03', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 16:47:03', '2025-01-12 16:47:03'),
(146, '44-4ep7', 'January 2025', 'KWT-00001-January-2025', '44-1iv7-01-2025', 44, 'Pak Aam', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 16:50:55', 'transfer', 'Bank Central Asia', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-12 16:50:55', '2025-01-12 16:50:55');
INSERT INTO `receipts` (`id`, `slug`, `periode`, `number`, `billing_number`, `customer_id`, `customer_name`, `customer_address`, `paket_name`, `paket_price`, `tax`, `created_name`, `teller_name`, `payment_time`, `payment_methode`, `bank_name`, `account_name`, `account_number`, `deposit_bank_name`, `deposit_account_name`, `deposit_account_number`, `reference_id`, `created_at`, `updated_at`) VALUES
(147, 'inv-181-472y', 'January 2025', '00002-KWT-January-2025', '181-wf7g-01-2025', 181, 'Nani Sumarni', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 18:03:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 18:03:39', '2025-01-12 18:03:39'),
(148, 'inv-174-d0my', 'January 2025', '00001-KWT-January-2025', '174-uwxi-01-2025', 174, 'Sani Wartini', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-12 18:20:35', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 18:20:35', '2025-01-12 18:20:35'),
(149, '98-a4uj', 'January 2025', 'KWT-00001-January-2025', '98-dald-01-2025', 98, 'Tatang Mulyana', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-12 19:39:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 19:39:41', '2025-01-12 19:39:41'),
(150, '25-klxl', 'January 2025', 'KWT-00002-January-2025', '25-nbwd-01-2025', 25, 'Narsiah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-12 20:08:26', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-12 20:08:26', '2025-01-12 20:08:26'),
(151, 'inv-184-pbmi', 'January 2025', '00003-KWT-January-2025', '184-dkbc-01-2025', 184, 'Muh Subhan', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-13 06:29:13', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-13 06:29:13', '2025-01-13 06:29:13'),
(153, '91-1zwy', 'January 2025', 'KWT-00001-January-2025', '91-fc8c-01-2025', 91, 'Tati Mulyati', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-13 12:31:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-13 12:31:23', '2025-01-13 12:31:23'),
(154, '99-so0d', 'January 2025', 'KWT-00002-January-2025', '99-ekev-01-2025', 99, 'Tuti Helawati', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-13 17:06:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-13 17:06:50', '2025-01-13 17:06:50'),
(155, '41-knkv', 'January 2025', 'KWT-00003-January-2025', '41-q406-01-2025', 41, 'Muhammad Fahrizal', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-13 17:26:16', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-13 17:26:16', '2025-01-13 17:26:16'),
(156, 'inv-189-ykvl', 'January 2025', '00004-KWT-January-2025', '189-htdr-01-2025', 189, 'Serli Ardiawati', 'Kp. Rawabolang RT. 024/007 Maracang Babakancikao', 'ShaNet 3 (30 Mbps)', 200000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-13 18:20:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-13 18:20:58', '2025-01-13 18:20:58'),
(157, 'inv-186-omis', 'January 2025', '00001-KWT-January-2025', '186-epns-01-2025', 186, 'Ida Susilawati', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-13 19:42:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-13 19:42:50', '2025-01-13 19:42:50'),
(158, '122-yycw', 'January 2025', 'KWT-00001-January-2025', '122-i8o4-01-2025', 122, 'Rosidah', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-13 21:16:27', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-13 21:16:27', '2025-01-13 21:16:27'),
(159, '101-k7k6', 'January 2025', 'KWT-00002-January-2025', '101-etrq-01-2025', 101, 'H Imon', 'Kp. Cikadu RT. 011/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-14 04:14:36', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-14 04:14:36', '2025-01-14 04:14:36'),
(160, '15-pjca', 'January 2025', 'KWT-00003-January-2025', '15-lxnz-01-2025', 15, 'Armiyanti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-14 07:19:31', 'transfer', 'Bank Mandiri', 'Dian Ardiansyah', '1730012695905', NULL, NULL, NULL, NULL, '2025-01-14 07:19:31', '2025-01-14 07:19:31'),
(161, '83-xyig', 'January 2025', 'KWT-00004-January-2025', '83-d58b-01-2025', 83, 'Rinji Maulani', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-14 11:15:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-14 11:15:20', '2025-01-14 11:15:20'),
(162, '42-abxj', 'January 2025', 'KWT-00005-January-2025', '42-gddu-01-2025', 42, 'Edi Suswanto', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-14 14:03:30', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-14 14:03:30', '2025-01-14 14:03:30'),
(163, '35-r0bo', 'January 2025', 'KWT-00006-January-2025', '35-waeu-01-2025', 35, 'Triyanti', 'Gg. Beringin RT. 077/008 Nagrikaler Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-14 21:18:02', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-14 21:18:02', '2025-01-14 21:18:02'),
(164, '30-9zk8', 'January 2025', 'KWT-00007-January-2025', '30-ooxb-01-2025', 30, 'A Zaky Alattas', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 07:13:40', 'transfer', 'Mandiri', 'Dian Ardiansyah', '1730012695905', NULL, NULL, NULL, NULL, '2025-01-15 07:13:40', '2025-01-15 07:13:40'),
(165, '96-ulhx', 'January 2025', 'KWT-00008-January-2025', '96-pbnb-01-2025', 96, 'Mariati', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 07:47:14', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-15 07:47:14', '2025-01-15 07:47:14'),
(166, '32-rojn', 'January 2025', 'KWT-00009-January-2025', '32-y6vf-01-2025', 32, 'Pak Slamet', 'Jl. Sukamaju RT. 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 08:40:46', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 08:40:46', '2025-01-15 08:40:46'),
(167, '16-vvbb', 'January 2025', 'KWT-00010-January-2025', '16-mzxq-01-2025', 16, 'Riska Azizah', 'Perum Griya Asri Blok K7 No. 7 RT 010/011 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 09:47:44', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 09:47:44', '2025-01-15 09:47:44'),
(168, 'inv-188-c3f3', 'January 2025', '00011-KWT-January-2025', '188-wmqx-01-2025', 188, 'Heti Herawati', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 10:19:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 10:19:29', '2025-01-15 10:19:29'),
(169, 'inv-187-lnsq', 'January 2025', '00001-KWT-January-2025', '187-kwly-01-2025', 187, 'Ratih Herawati', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 10:19:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 10:19:34', '2025-01-15 10:19:34'),
(170, 'inv-182-rwdr', 'January 2025', '00001-KWT-January-2025', '182-hmht-01-2025', 182, 'Rini Dewi Astuti', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 10:19:41', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 10:19:41', '2025-01-15 10:19:41'),
(171, 'inv-185-yv4u', 'January 2025', '00001-KWT-January-2025', '185-lyke-01-2025', 185, 'Yayat Hidayat', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 10:19:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 10:19:47', '2025-01-15 10:19:47'),
(172, '10-gcyy', 'January 2025', 'KWT-00001-January-2025', '10-uunt-01-2025', 10, 'Ustd. Ali Akbar', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 10:57:49', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 10:57:49', '2025-01-15 10:57:49'),
(173, '14-tjdw', 'January 2025', 'KWT-00002-January-2025', '14-2ruf-01-2025', 14, 'Ai Solihah', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 12:25:25', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-15 12:25:25', '2025-01-15 12:25:25'),
(174, '90-j6g4', 'January 2025', 'KWT-00003-January-2025', '90-5sbo-01-2025', 90, 'Mimin Rukmini', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 12:45:40', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 12:45:40', '2025-01-15 12:45:40'),
(175, '57-55vn', 'January 2025', 'KWT-00004-January-2025', '57-awcb-01-2025', 57, 'Nur Fakriah Putri', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 14:00:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 14:00:14', '2025-01-15 14:00:14'),
(176, '24-yefh', 'January 2025', 'KWT-00005-January-2025', '24-lr28-01-2025', 24, 'Etik Aprilia Ratna Sari', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-15 15:38:53', 'transfer', 'Mandiri', 'Dian Ardiansyah', '1730012695905', NULL, NULL, NULL, NULL, '2025-01-15 15:38:53', '2025-01-15 15:38:53'),
(177, '23-yszq', 'January 2025', 'KWT-00006-January-2025', '23-srdi-01-2025', 23, 'Neni Nurhaeni', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 15:55:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 15:55:19', '2025-01-15 15:55:19'),
(178, '62-dpel', 'January 2025', 'KWT-00007-January-2025', '62-a9cn-01-2025', 62, 'Marya Ningsih', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 16:15:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 16:15:06', '2025-01-15 16:15:06'),
(179, '109-s7ve', 'January 2025', 'KWT-00008-January-2025', '109-slbg-01-2025', 109, 'Dedi Haerudi', 'Kp. Krajan RT. 007/002 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-15 16:55:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 16:55:19', '2025-01-15 16:55:19'),
(180, '63-farx', 'January 2025', 'KWT-00009-January-2025', '63-c1rq-01-2025', 63, 'Pak Kurdi (Rumah)', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 17:44:30', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 17:44:30', '2025-01-15 17:44:30'),
(181, '68-e6nv', 'January 2025', 'KWT-00010-January-2025', '68-kcb7-01-2025', 68, 'Pak Kurdi (Kontrakan)', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 17:44:39', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 17:44:39', '2025-01-15 17:44:39'),
(182, '158-6ux8', 'January 2025', 'KWT-00011-January-2025', '158-scfb-01-2025', 158, 'Suci Ambar Sari', 'Kp. Sukamaju RT 009/007 Batara Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 17:50:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 17:50:06', '2025-01-15 17:50:06'),
(183, '46-i2dl', 'January 2025', 'KWT-00012-January-2025', '46-arcl-01-2025', 46, 'Mulyana', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-15 18:06:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-15 18:06:34', '2025-01-15 18:06:34'),
(184, '40-trzd', 'January 2025', 'KWT-00013-January-2025', '40-qzkp-01-2025', 40, 'Wandi Suhendi', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-16 05:41:36', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-16 05:41:36', '2025-01-16 05:41:36'),
(185, '48-cgiw', 'January 2025', 'KWT-00014-January-2025', '48-jnsc-01-2025', 48, 'Hendrizal T (Geprek Jogja)', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-16 06:07:34', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 06:07:34', '2025-01-16 06:07:34'),
(186, '61-p5or', 'January 2025', 'KWT-00015-January-2025', '61-eufb-01-2025', 61, 'Pak Dendi', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-16 06:08:56', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-16 06:08:56', '2025-01-16 06:08:56'),
(187, '50-4wi8', 'January 2025', 'KWT-00016-January-2025', '50-xbfl-01-2025', 50, 'Nok Reni', 'Kp. Sukamaju RT 009/007 Gg. Sadewa Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-16 06:41:20', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 06:41:20', '2025-01-16 06:41:20'),
(188, '9-1vjx', 'January 2025', 'KWT-00017-January-2025', '9-2aua-01-2025', 9, 'Pak Eko', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-16 07:15:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 07:15:02', '2025-01-16 07:15:02'),
(189, '52-le8u', 'January 2025', 'KWT-00018-January-2025', '52-vnpn-01-2025', 52, 'Maharani Widyaningrum', 'Kp. Sukamaju RT 009/007 Gg. Nakula Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-16 07:39:43', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-16 07:39:43', '2025-01-16 07:39:43'),
(190, '78-mh4h', 'January 2025', 'KWT-00019-January-2025', '78-6gv5-01-2025', 78, 'Robi Anggara', 'Jl. Veteran RT. 003/002 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-16 07:58:48', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-16 07:58:48', '2025-01-16 07:58:48'),
(191, '37-btrx', 'January 2025', 'KWT-00020-January-2025', '37-qitk-01-2025', 37, 'Yuan Firmansyah', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-16 16:30:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 16:30:23', '2025-01-16 16:30:23'),
(192, '31-tgzz', 'January 2025', 'KWT-00021-January-2025', '31-jupy-01-2025', 31, 'Teh Ai', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-16 16:31:10', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 16:31:10', '2025-01-16 16:31:10'),
(193, '29-hdzg', 'January 2025', 'KWT-00022-January-2025', '29-uw32-01-2025', 29, 'Adi Amoy', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-16 16:31:57', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 16:31:57', '2025-01-16 16:31:57'),
(194, '102-5mtd', 'January 2025', 'KWT-00023-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:11:54', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:11:54', '2025-01-16 17:11:54'),
(195, '102-5mtd', 'January 2025', 'KWT-00024-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:11:58', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:11:58', '2025-01-16 17:11:58'),
(196, '102-5mtd', 'January 2025', 'KWT-00025-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:12:01', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:12:01', '2025-01-16 17:12:01'),
(197, '102-5mtd', 'January 2025', 'KWT-00026-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:12:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:12:02', '2025-01-16 17:12:02'),
(198, '102-5mtd', 'January 2025', 'KWT-00027-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:12:02', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:12:02', '2025-01-16 17:12:02'),
(199, '102-5mtd', 'January 2025', 'KWT-00027-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:12:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:12:04', '2025-01-16 17:12:04'),
(200, '102-5mtd', 'January 2025', 'KWT-00028-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:12:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:12:04', '2025-01-16 17:12:04'),
(201, '102-5mtd', 'January 2025', 'KWT-00029-January-2025', '102-kmwj-01-2025', 102, 'Julianto', 'Kp. Cikadu RT. 010/004 Kadumekar Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:12:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:12:05', '2025-01-16 17:12:05'),
(202, '147-dzfo', 'January 2025', 'KWT-00030-January-2025', '147-ujyo-01-2025', 147, 'Yuyun', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 17:52:07', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 17:52:07', '2025-01-16 17:52:07'),
(203, '53-f6qr', 'January 2025', 'KWT-00031-January-2025', '53-ad66-01-2025', 53, 'Linda', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-16 18:14:51', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 18:14:51', '2025-01-16 18:14:51'),
(204, '107-c65r', 'January 2025', 'KWT-00032-January-2025', '107-tolf-01-2025', 107, 'Jajang Salahudin', 'Kp. Krajan RT. 003/001 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 18:47:19', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 18:47:19', '2025-01-16 18:47:19'),
(205, '107-c65r', 'January 2025', 'KWT-00033-January-2025', '107-tolf-01-2025', 107, 'Jajang Salahudin', 'Kp. Krajan RT. 003/001 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-16 18:47:22', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-16 18:47:22', '2025-01-16 18:47:22'),
(206, '73-frc1', 'January 2025', 'KWT-00034-January-2025', '73-eax1-01-2025', 73, 'Nazri Nasier', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-17 04:13:48', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-17 04:13:48', '2025-01-17 04:13:48'),
(207, '45-y1kn', 'January 2025', 'KWT-00035-January-2025', '45-1k0q-01-2025', 45, 'Rohani Rusmiati', 'Kp. Sukamaju RT 009/007 Green Sapir Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-17 07:42:07', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-17 07:42:07', '2025-01-17 07:42:07'),
(208, '11-elpm', 'January 2025', 'KWT-00036-January-2025', '11-ptyl-01-2025', 11, 'Rita Susanti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-17 08:54:18', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-17 08:54:18', '2025-01-17 08:54:18'),
(210, '12-0iza', 'January 2025', 'KWT-00037-January-2025', '12-schs-01-2025', 12, 'Pak Suud', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-17 09:57:18', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-17 09:57:18', '2025-01-17 09:57:18'),
(211, 'inv-190-yupd', 'January 2025', '00038-KWT-January-2025', '190-uah1-01-2025', 190, 'Ahmad Zaenal Mustofa', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-17 11:39:05', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-17 11:39:05', '2025-01-17 11:39:05'),
(212, '97-retd', 'January 2025', 'KWT-00001-January-2025', '97-q0lx-01-2025', 97, 'Devi Sevtiawati', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 1 (10 Mbps)', 125000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-17 15:12:08', 'transfer', 'DANA', 'Budi Kosasih', '085710840198', NULL, NULL, NULL, NULL, '2025-01-17 15:12:08', '2025-01-17 15:12:08'),
(214, '143-rup1', 'January 2025', 'KWT-00003-January-2025', '143-jfft-01-2025', 143, 'Bajuri Enci', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-17 17:57:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-17 17:57:50', '2025-01-17 17:57:50'),
(215, '150-dd4z', 'January 2025', 'KWT-00004-January-2025', '150-gij3-01-2025', 150, 'Dadi Kusnadi', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Budi Kosasih', 'Budi Kosasih', '2025-01-18 07:38:09', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-18 07:38:09', '2025-01-18 07:38:09'),
(216, '51-gavq', 'January 2025', 'KWT-00005-January-2025', '51-qtg9-01-2025', 51, 'Ahmad Sugianto', 'Kp. Sukamaju RT 009/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-19 10:18:40', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-01-19 10:18:40', '2025-01-19 10:18:40'),
(217, 'inv-192-kisk', 'January 2025', '00006-KWT-January-2025', '192-etwo-01-2025', 192, 'Ropik', 'Kp. Krajan RT. 011/003 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-19 17:05:47', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-19 17:05:47', '2025-01-19 17:05:47'),
(218, 'inv-191-t63j', 'January 2025', '00001-KWT-January-2025', '191-otm9-01-2025', 191, 'Endang Rosidi', 'Kp. Rawabolang RT. 024/007 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-19 20:31:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-19 20:31:56', '2025-01-19 20:31:56'),
(219, 'inv-193-u2si', 'January 2025', '00001-KWT-January-2025', '193-xro0-01-2025', 193, 'Julian Ratu Malik Kusum', 'Kp. Rawabolang RT. 024/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-20 06:51:56', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-20 06:51:56', '2025-01-20 06:51:56'),
(220, '20-qeci', 'January 2025', 'KWT-00001-January-2025', '20-z1zq-01-2025', 20, 'Ibu Ayu', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-20 09:55:12', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-20 09:55:12', '2025-01-20 09:55:12'),
(222, 'inv-194-ec3l', 'January 2025', '00002-KWT-January-2025', '194-3waz-01-2025', 194, 'Iwan Setiawan', 'Kp. Rawabolang RT. 023/007 Desa Maracang', 'ShaNet 1 (10 Mbps)', 125000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-21 10:17:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-21 10:17:50', '2025-01-21 10:17:50'),
(223, '92-tgfk', 'January 2025', 'KWT-00001-January-2025', '92-qhsj-01-2025', 92, 'Siam Suroso', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Admin ShaNet Fiber', 'Admin ShaNet Fiber', '2025-01-21 16:01:04', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-21 16:01:04', '2025-01-21 16:01:04'),
(224, 'inv-195-zgxj', 'January 2025', '00002-KWT-January-2025', '195-3vdv-01-2025', 195, 'Sarkum', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-25 09:28:14', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-25 09:28:14', '2025-01-25 09:28:14'),
(225, 'inv-196-ds8e', 'January 2025', '00001-KWT-January-2025', '196-cso0-01-2025', 196, 'Hendi Suhendi', 'Kp. Warnasari RT. 002/002 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-28 10:41:23', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-28 10:41:23', '2025-01-28 10:41:23'),
(226, 'inv-198-1xwd', 'January 2025', '00001-KWT-January-2025', '198-oveh-01-2025', 198, 'Ryan Kurniawan', 'Kp. Krajan RT. 007/002 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-28 10:41:28', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-28 10:41:28', '2025-01-28 10:41:28'),
(227, 'inv-197-bmfk', 'January 2025', '00001-KWT-January-2025', '197-jjr2-01-2025', 197, 'Krisnawati', 'Kp. Warnasari RT. 002/002 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, NULL, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-28 10:41:33', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-28 10:41:33', '2025-01-28 10:41:33'),
(228, '36-4m1e', 'January 2025', 'KWT-00001-January-2025', '36-43qs-01-2025', 36, 'H. M Abu Rasyid. AS. SH', 'Kp. Sukamaju RT 005/007 Gg. Paraji Ciseureuh Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-01-30 06:51:21', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-30 06:51:21', '2025-01-30 06:51:21'),
(229, '38-2rzs', 'February 2025', 'KWT-00002-February-2025', '38-ssql-02-2025', 38, 'Dede Suherman', 'Kp. Sukamaju RT 005/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-02-01 06:35:29', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-01 06:35:29', '2025-02-01 06:35:29'),
(230, '90-burd', 'February 2025', 'KWT-00003-February-2025', '90-e3jo-02-2025', 90, 'Mimin Rukmini', 'Kp. Sukamaju RT 010/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-02-01 06:35:52', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-01 06:35:52', '2025-02-01 06:35:52'),
(231, '15-zenn', 'February 2025', 'KWT-00004-February-2025', '15-eeaz-02-2025', 15, 'Armiyanti', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 3 (30 Mbps)', 200000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-02-01 06:37:03', 'transfer', 'Mandiri', 'Dian Ardiansyah', '1730012695905', NULL, NULL, NULL, NULL, '2025-02-01 06:37:03', '2025-02-01 06:37:03'),
(232, '196-jl72', 'February 2025', 'KWT-00005-February-2025', '196-it3y-02-2025', 196, 'Hendi Suhendi', 'Kp. Warnasari RT. 002/002 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-02-01 06:38:16', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-01 06:38:16', '2025-02-01 06:38:16'),
(233, '198-wfzi', 'February 2025', 'KWT-00006-February-2025', '198-408z-02-2025', 198, 'Ryan Kurniawan', 'Kp. Krajan RT. 007/002 Maracang Babakancikao', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-02-01 06:38:45', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-01 06:38:45', '2025-02-01 06:38:45'),
(234, '197-sixo', 'February 2025', 'KWT-00007-February-2025', '197-tyuc-02-2025', 197, 'Krisnawati', 'Kp. Warnasari RT. 002/002 Ciseureuh Purwakarta', 'ShaNet 2 (15 Mbps)', 150000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-02-01 06:39:06', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-01 06:39:06', '2025-02-01 06:39:06'),
(235, '184-z9tq', 'February 2025', 'KWT-00008-February-2025', '184-qehu-02-2025', 184, 'Muh Subhan', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Acep', 'Acep', '2025-02-01 06:55:50', 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-02-01 06:55:50', '2025-02-01 06:55:50'),
(236, '3-uqfe', 'February 2025', 'KWT-00009-February-2025', '3-tucn-02-2025', 3, 'Asep Nasihin', 'Kp. Sukamaju RT 009/007 Ciseureuh Purwakarta', 'ShaNet 0 (7 Mbps)', 100000, 0, 'Dian Ardiansyah', 'Dian Ardiansyah', '2025-02-01 07:08:44', 'transfer', 'BCA', 'Dian Ardiansyah', '2990407315', NULL, NULL, NULL, NULL, '2025-02-01 07:08:44', '2025-02-01 07:08:44');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'web', '2024-12-15 20:28:45', '2024-12-15 20:28:45'),
(2, 'Operator', 'web', '2024-12-17 10:04:42', '2024-12-17 10:04:42'),
(3, 'Kasir', 'web', '2024-12-17 10:05:30', '2024-12-17 10:05:30');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
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
(39, 1),
(19, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(32, 3),
(36, 3),
(38, 3),
(39, 3);

-- --------------------------------------------------------

--
-- Table structure for table `type_routers`
--

CREATE TABLE `type_routers` (
  `id` bigint UNSIGNED NOT NULL,
  `merk_router_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `server_type` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `disabled`, `created_at`, `updated_at`, `picture`) VALUES
(1, 'Dian Ardiansyah', 'randika.nurfaizi@bni.co.id', NULL, '$2y$10$7mCFb5u9QwJA5RYNhgpfSO/IFKYeD.PnjGSvhcrrWphfJbIxPeaMG', NULL, 'false', '2024-12-15 20:28:45', '2024-12-23 11:16:28', NULL),
(2, 'Budi Kosasih', 'randika.nurfaizi470@gmail.com', NULL, '$2y$10$7mCFb5u9QwJA5RYNhgpfSO/IFKYeD.PnjGSvhcrrWphfJbIxPeaMG', 'n37zCGf14lG516kBdYRG3HrUhqmp4wNVtTI2nwLp15vVWg0gtgDODuOgPzSX', 'false', '2024-12-23 11:15:32', '2025-01-03 04:17:29', NULL),
(3, 'Admin ShaNet Fiber', 'admin@shanetfiber.my.id', NULL, '$2y$10$7mCFb5u9QwJA5RYNhgpfSO/IFKYeD.PnjGSvhcrrWphfJbIxPeaMG', NULL, 'false', '2024-12-31 10:36:40', '2024-12-31 10:36:40', NULL),
(4, 'Acep', 'dikarandika.rn@gmail.com', NULL, '$2y$10$7mCFb5u9QwJA5RYNhgpfSO/IFKYeD.PnjGSvhcrrWphfJbIxPeaMG', NULL, 'false', '2025-01-29 17:16:24', '2025-01-29 17:16:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `websystems`
--

CREATE TABLE `websystems` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpanel_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_gateway` enum('enable','disable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'disable',
  `enable_tax` tinyint(1) NOT NULL DEFAULT '0',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `long_customer_code` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `websystems`
--

INSERT INTO `websystems` (`id`, `slug`, `title`, `version`, `cpanel_url`, `created_at`, `updated_at`, `payment_gateway`, `enable_tax`, `tax_rate`, `email`, `url`, `address`, `city`, `postal_code`, `country_code`, `phone`, `customer_code`, `long_customer_code`) VALUES
(1, 'websystem', 'ShaNet Fiber Management', '1.0.9b3', NULL, '2024-09-29 08:50:11', '2024-11-15 04:08:11', 'disable', 0, '11.00', 'd_ardi@ymail.com', NULL, 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh', 'Kabupaten Purwakarta', '41118', 'IDN', '085795909904', 'SNF', 3);

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_gateways`
--

CREATE TABLE `whatsapp_gateways` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remaining_day` int NOT NULL DEFAULT '1',
  `schedule_time` int NOT NULL DEFAULT '10',
  `whatsapp_number_id` bigint UNSIGNED DEFAULT NULL,
  `country_code` int DEFAULT '62',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_callback` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_gateways`
--

INSERT INTO `whatsapp_gateways` (`id`, `slug`, `name`, `remaining_day`, `schedule_time`, `whatsapp_number_id`, `country_code`, `description`, `url_callback`, `api_key`, `cert_file`, `disabled`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'gateway', 'WA Gateway', 1, 9, 2, 62, 'Official Contact Shanum Network Fiber', 'http://103.129.148.212:3000', 'yE1Q82tGjnB3xZveu50yqQ==', NULL, 0, '2024-12-31 10:00:22', '2025-01-15 21:51:07', NULL);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auto_isolirs`
--
ALTER TABLE `auto_isolirs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `billings`
--
ALTER TABLE `billings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=549;

--
-- AUTO_INCREMENT for table `billing_periodes`
--
ALTER TABLE `billing_periodes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `customer_onts`
--
ALTER TABLE `customer_onts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `deleted_customers`
--
ALTER TABLE `deleted_customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `merk_routers`
--
ALTER TABLE `merk_routers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `midtrans`
--
ALTER TABLE `midtrans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `mikrotiks`
--
ALTER TABLE `mikrotiks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pakets`
--
ALTER TABLE `pakets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppp_profiles`
--
ALTER TABLE `ppp_profiles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppp_types`
--
ALTER TABLE `ppp_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `type_routers`
--
ALTER TABLE `type_routers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `websystems`
--
ALTER TABLE `websystems`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `whatsapp_gateways`
--
ALTER TABLE `whatsapp_gateways`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
