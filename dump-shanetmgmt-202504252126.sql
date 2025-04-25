-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: 103.176.96.151    Database: shanetmgmt
-- ------------------------------------------------------
-- Server version	8.0.41-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ppp_profiles`
--

DROP TABLE IF EXISTS `ppp_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ppp_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `local_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remote_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bridge_learning` enum('no','yes','required','default') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `change_tcp_mss` enum('no','yes','required','default') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `use_upnp` enum('no','yes','required','default') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insert_queue_before` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_queue` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `queue_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `script_on_up` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `script_on_down` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` enum('false','true') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ppp_profiles`
--

LOCK TABLES `ppp_profiles` WRITE;
/*!40000 ALTER TABLE `ppp_profiles` DISABLE KEYS */;
INSERT INTO `ppp_profiles` VALUES (4,NULL,'ShaNet_0_7Mbps',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','4M','7M','4000k','7000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','4M/7M','none','none','none',NULL,'','',0,'Upto 7Mbps (Rp.100,000)','false','2025-04-25 20:52:15','2025-04-25 20:52:15'),(5,NULL,'ShaNet_1_10Mbps',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','6M','10M','6000k','10000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','6M/10M','none','none','none',NULL,'','',0,'Upto 10Mbps (Rp.125,000)','false','2025-04-25 20:53:46','2025-04-25 20:53:46'),(6,NULL,'ShaNet_2_15Mbps',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','8M','15M','8000k','15000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','8M/15M','none','none','none',NULL,'','',0,'Upto 15Mbps (Rp.150,000)','false','2025-04-25 20:54:54','2025-04-25 20:54:54'),(7,NULL,'ShaNet_3_30Mbps',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','15M','30M','15000k','30000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','15M/30M','none','none','none',NULL,NULL,NULL,0,'Upto 30Mbps (Rp.200,000)','false','2025-04-25 20:57:57','2025-04-25 20:57:57'),(8,NULL,'ShaNet_4_50Mbps',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','25M','50M','25000k','50000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','25M/50M','none','none','none',NULL,NULL,NULL,0,'Upto 50Mbps (Rp.300,000)','false','2025-04-25 20:59:57','2025-04-25 20:59:57'),(9,NULL,'ShaNet_D_50Mbps',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','55M','55M','55000k','55000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','55M/55M','none','none','none',NULL,NULL,NULL,0,'Dedicated 50Mbps (Rp.1.800,000)','false','2025-04-25 21:02:01','2025-04-25 21:02:01'),(10,NULL,'ShaNet_D_100Mbps',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','105M','105M','105000k','105000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','105M/105M','none','none','none',NULL,NULL,NULL,0,'Dedicated 100Mbps (Rp.3.500,000)','false','2025-04-25 21:03:05','2025-04-25 21:03:05'),(11,NULL,'Admin',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','50M','50M','50000k','50000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','50M/50M','none','none','none',NULL,NULL,NULL,0,'Free Vasility','false','2025-04-25 21:03:52','2025-04-25 21:03:52'),(12,NULL,'Expire',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','0.256M','0.256M','256k','256k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'no','default','default','0.256M/0.256M','none','none','none',NULL,NULL,NULL,0,'Client Isolir','false','2025-04-25 21:04:44','2025-04-25 21:04:44'),(13,NULL,'Free_Wifi',NULL,NULL,NULL,'8.8.8.8, 8.8.4.4','yes','default','yes','default','3M','5M','3000k','5000k',NULL,NULL,NULL,NULL,NULL,NULL,'8','1',NULL,NULL,'yes','default','default','3M/5M','none','none','none',NULL,NULL,NULL,0,'Free Vasility','false','2025-04-25 21:05:41','2025-04-25 21:05:41');
/*!40000 ALTER TABLE `ppp_profiles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-25 21:26:40
