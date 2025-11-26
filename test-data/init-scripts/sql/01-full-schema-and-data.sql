-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: topseven
-- ------------------------------------------------------
-- Server version	8.0.44

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
-- Table structure for table `calendar`
--

DROP TABLE IF EXISTS `calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar` (
  `season` tinyint NOT NULL,
  `day` tinyint NOT NULL,
  `date` date NOT NULL,
  UNIQUE KEY `id` (`season`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar`
--

LOCK TABLES `calendar` WRITE;
/*!40000 ALTER TABLE `calendar` DISABLE KEYS */;
INSERT INTO `calendar` VALUES (1,1,'2025-09-01'),(1,2,'2025-09-08'),(1,3,'2025-09-15'),(1,4,'2025-09-22'),(1,5,'2025-09-29'),(1,6,'2025-10-06'),(1,7,'2025-10-13'),(1,8,'2025-10-20'),(1,9,'2025-10-27'),(1,10,'2025-11-03'),(1,11,'2025-11-10'),(1,12,'2025-11-17'),(1,13,'2025-11-24'),(1,14,'2025-12-01'),(1,15,'2025-12-08'),(1,16,'2025-12-15'),(1,17,'2025-12-22'),(1,18,'2025-12-29'),(1,19,'2026-01-05'),(1,20,'2026-01-12'),(1,21,'2026-01-19'),(1,22,'2026-01-26'),(1,23,'2026-02-02'),(1,24,'2026-02-09'),(1,25,'2026-02-16'),(1,26,'2026-02-23');
/*!40000 ALTER TABLE `calendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team` int NOT NULL,
  `created_by` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('match_amical','visionnage','reunion','autre') DEFAULT 'autre',
  `proposed_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text,
  `status` enum('proposed','confirmed','cancelled') DEFAULT 'proposed',
  `min_players` int DEFAULT '3',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_team` (`team`),
  KEY `idx_date` (`proposed_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event`
--

LOCK TABLES `event` WRITE;
/*!40000 ALTER TABLE `event` DISABLE KEYS */;
INSERT INTO `event` VALUES (1,1,2,'Match amical contre les Tigres','match_amical','2025-11-25 15:00:00','Stade Municipal','Match amical pour prÃ©parer le prochain match officiel','proposed',4,'2025-11-20 07:34:20','2025-11-20 07:47:38'),(2,1,2,'Visionnage match France-NZ','visionnage','2025-11-22 20:00:00','Bar des Sports','On se retrouve pour regarder le match ensemble','confirmed',3,'2025-11-20 07:34:20','2025-11-20 07:47:38'),(3,1,2,'RÃ©union tactique','reunion','2025-11-28 18:30:00','Salle de rÃ©union','Discussion sur les stratÃ©gies pour la fin de saison','proposed',5,'2025-11-20 07:34:20','2025-11-20 07:47:38'),(4,1,2,'EntraÃ®nement collectif','autre','2025-12-05 19:00:00','Terrain synthÃ©tique','EntraÃ®nement en Ã©quipe avant le match important','proposed',6,'2025-11-20 07:34:20','2025-11-20 07:47:38');
/*!40000 ALTER TABLE `event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_availability`
--

DROP TABLE IF EXISTS `event_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_availability` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `player_id` int NOT NULL,
  `status` enum('available','maybe','unavailable') NOT NULL,
  `comment` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_event_player` (`event_id`,`player_id`),
  KEY `idx_event` (`event_id`),
  KEY `idx_player` (`player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_availability`
--

LOCK TABLES `event_availability` WRITE;
/*!40000 ALTER TABLE `event_availability` DISABLE KEYS */;
INSERT INTO `event_availability` VALUES (1,1,2,'available','Je serai lÃ  !','2025-11-20 07:34:21'),(2,1,3,'available','OK pour moi','2025-11-20 07:34:21'),(3,1,4,'maybe','Pas sÃ»r, je confirme demain','2025-11-20 07:34:21'),(4,2,2,'available',NULL,'2025-11-20 07:34:21'),(5,2,3,'available','Super idÃ©e !','2025-11-20 07:34:21'),(6,2,4,'available',NULL,'2025-11-20 07:34:21'),(7,2,5,'unavailable','DÃ©solÃ©, pas dispo ce soir','2025-11-20 07:34:21'),(8,3,2,'available',NULL,'2025-11-20 07:34:21'),(9,3,3,'maybe','Ã‡a dÃ©pend de mon boulot','2025-11-20 07:34:21');
/*!40000 ALTER TABLE `event_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum`
--

DROP TABLE IF EXISTS `forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum` (
  `idx1` int NOT NULL AUTO_INCREMENT,
  `idx2` int NOT NULL,
  `team` smallint NOT NULL,
  `player` mediumint NOT NULL,
  `season` tinyint NOT NULL DEFAULT '0',
  `day` tinyint NOT NULL,
  `date` datetime NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`idx1`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum`
--

LOCK TABLES `forum` WRITE;
/*!40000 ALTER TABLE `forum` DISABLE KEYS */;
INSERT INTO `forum` VALUES (1,0,1,2,1,5,'2025-11-15 23:07:00','test\r\n'),(3,0,1,2,1,4,'2025-11-16 20:31:00','test 2');
/*!40000 ALTER TABLE `forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match`
--

DROP TABLE IF EXISTS `match`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `match` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season` tinyint DEFAULT '0',
  `day` tinyint NOT NULL,
  `team1` tinyint NOT NULL COMMENT 'Equipe locale',
  `team2` tinyint NOT NULL COMMENT 'Equipe visiteur',
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `team1` (`team1`),
  KEY `team2` (`team2`),
  KEY `season_day` (`season`,`day`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match`
--

LOCK TABLES `match` WRITE;
/*!40000 ALTER TABLE `match` DISABLE KEYS */;
INSERT INTO `match` VALUES (1,1,1,1,2,'2025-09-01','15:00:00'),(2,1,1,3,4,'2025-09-01','15:00:00'),(3,1,1,5,6,'2025-09-01','15:00:00'),(4,1,1,7,8,'2025-09-01','15:00:00'),(5,1,1,9,10,'2025-09-01','15:00:00'),(6,1,1,11,12,'2025-09-01','15:00:00'),(7,1,1,13,14,'2025-09-01','15:00:00'),(8,1,2,1,2,'2025-09-08','15:00:00'),(9,1,2,3,4,'2025-09-08','15:00:00'),(10,1,2,5,6,'2025-09-08','15:00:00'),(11,1,2,7,8,'2025-09-08','15:00:00'),(12,1,2,9,10,'2025-09-08','15:00:00'),(13,1,2,11,12,'2025-09-08','15:00:00'),(14,1,2,13,14,'2025-09-08','15:00:00'),(15,1,3,1,2,'2025-09-15','15:00:00'),(16,1,3,3,4,'2025-09-15','15:00:00'),(17,1,3,5,6,'2025-09-15','15:00:00'),(18,1,3,7,8,'2025-09-15','15:00:00'),(19,1,3,9,10,'2025-09-15','15:00:00'),(20,1,3,11,12,'2025-09-15','15:00:00'),(21,1,3,13,14,'2025-09-15','15:00:00'),(22,1,4,1,2,'2025-09-22','15:00:00'),(23,1,4,3,4,'2025-09-22','15:00:00'),(24,1,4,5,6,'2025-09-22','15:00:00'),(25,1,4,7,8,'2025-09-22','15:00:00'),(26,1,4,9,10,'2025-09-22','15:00:00'),(27,1,4,11,12,'2025-09-22','15:00:00'),(28,1,4,13,14,'2025-09-22','15:00:00'),(29,1,5,1,2,'2025-09-29','15:00:00'),(30,1,5,3,4,'2025-09-29','15:00:00'),(31,1,5,5,6,'2025-09-29','15:00:00'),(32,1,5,7,8,'2025-09-29','15:00:00'),(33,1,5,9,10,'2025-09-29','15:00:00'),(34,1,5,11,12,'2025-09-29','15:00:00'),(35,1,5,13,14,'2025-09-29','15:00:00');
/*!40000 ALTER TABLE `match` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password`
--

DROP TABLE IF EXISTS `password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `player` mediumint NOT NULL,
  `status` tinyint NOT NULL,
  `keyword` varchar(32) NOT NULL,
  `time` int NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password`
--

LOCK TABLES `password` WRITE;
/*!40000 ALTER TABLE `password` DISABLE KEYS */;
/*!40000 ALTER TABLE `password` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player`
--

DROP TABLE IF EXISTS `player`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `player` (
  `player_idx` mediumint NOT NULL AUTO_INCREMENT,
  `season` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `pseudo` varchar(40) NOT NULL,
  `captain` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Capitaine',
  `rank` tinyint NOT NULL,
  `rankFinal` tinyint NOT NULL,
  `point` smallint NOT NULL,
  `J` tinyint NOT NULL COMMENT 'Joué',
  `G` tinyint NOT NULL COMMENT 'Gagné',
  `N` tinyint NOT NULL COMMENT 'Nul',
  `P` tinyint NOT NULL COMMENT 'Perdu',
  `team` smallint NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(32) NOT NULL,
  `password_new` varchar(255) DEFAULT NULL COMMENT 'Argon2ID password hash (replaces MD5)',
  `date_reg` datetime NOT NULL,
  `pm` int NOT NULL COMMENT 'Points Marqués',
  `pe` int NOT NULL COMMENT 'Points Encaissés',
  `ve` tinyint NOT NULL COMMENT 'Victoires Extérieures',
  `evo` tinyint NOT NULL COMMENT 'Evolution',
  `fun` int NOT NULL COMMENT 'Points fun',
  `bd` tinyint NOT NULL,
  `bo` tinyint NOT NULL,
  `pc` tinyint NOT NULL COMMENT 'Points coiffeur',
  `eq` tinyint NOT NULL COMMENT 'Equipes différentes',
  `d14` tinyint DEFAULT NULL COMMENT 'Journée pour 14 équipes différentes',
  PRIMARY KEY (`player_idx`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player`
--

LOCK TABLES `player` WRITE;
/*!40000 ALTER TABLE `player` DISABLE KEYS */;
INSERT INTO `player` VALUES (1,1,1,'Team Alpha','testuser1',0,3,0,134,35,27,0,8,1,'test1@topseven.fr','cc03e747a6afbbcbf8be7668acfebee5','$argon2id$v=19$m=65536,t=4,p=1$Z2dWRno5TW5VTzdaU28zbw$DCgH1K9TTVkKftqA7LfGlrG0LKZgC5pNU97iLks94SU','2025-11-09 20:36:58',977,812,0,0,0,0,0,0,14,NULL),(2,1,1,'Team Beta','testuser2',0,2,0,135,35,27,0,8,1,'test2@topseven.fr','','$argon2id$v=19$m=65536,t=4,p=1$akpnYUVoYzBZcDJFcUtlWg$mxDGpCHZFrcKsrouNQN4XyPcvvh/22oe9HKAU9qWk5Q','2025-11-09 20:36:58',993,772,0,0,0,0,0,0,14,NULL),(3,1,1,'Team Gamma','testuser3',0,1,0,147,35,29,0,6,1,'test3@topseven.fr','','$argon2id$v=19$m=65536,t=4,p=1$ai5KYmNJOEhTQ1pEb3hzbQ$XDwToDS720RDbUaDERaV6QUbw8j0jJXz/m6GVcB60P0','2025-11-09 20:36:59',1041,775,0,0,0,0,0,0,14,NULL),(4,1,1,'Team Delta','testuser4',0,4,0,123,35,25,0,10,1,'test4@topseven.fr','','$argon2id$v=19$m=65536,t=4,p=1$VjhpVmRwaGZjbktHMnprNQ$7CoGmLBMuMSDh8RMH9SaVYJNUmNJiNHD4xDIUjA4M/8','2025-11-09 20:36:59',933,817,0,0,0,0,0,0,14,NULL),(5,1,1,'Team Epsilon','testuser5',0,5,0,118,35,23,0,12,1,'test5@topseven.fr','','$argon2id$v=19$m=65536,t=4,p=1$UVcxSzBvMWYwRm1KU2Q1SA$lJ76nh5foCY3hDNCKlPZkJErqKIqgtHFRz9MBrU0b+Y','2025-11-09 20:36:59',973,868,0,0,0,0,0,0,12,NULL),(6,1,1,'Team Zeta','testuser6',0,7,0,108,35,20,0,15,1,'test6@topseven.fr','','$argon2id$v=19$m=65536,t=4,p=1$VkYwb1ZZVHVMeVF0Q3NneQ$4PnvaLb9xf9O/uNaS+6mzOCsaqEzuHqnSs6owsJyU+0','2025-11-09 20:36:59',929,865,0,0,0,0,0,0,14,NULL),(7,1,1,'Team Eta','testuser7',0,6,0,111,35,21,0,14,1,'test7@topseven.fr','','$argon2id$v=19$m=65536,t=4,p=1$Rkt3UUNvQmhUbmxta2ZRUA$LQPIOjUFpaN8O1jCff7eYJx4Rp/TtE+d1YxmFBv13Ek','2025-11-09 20:36:59',940,883,0,0,0,0,0,0,14,NULL);
/*!40000 ALTER TABLE `player` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prono`
--

DROP TABLE IF EXISTS `prono`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prono` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season` tinyint NOT NULL DEFAULT '0',
  `day` tinyint NOT NULL,
  `player` smallint NOT NULL,
  `match` mediumint NOT NULL,
  `team` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `day` (`day`),
  KEY `player` (`player`)
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prono`
--

LOCK TABLES `prono` WRITE;
/*!40000 ALTER TABLE `prono` DISABLE KEYS */;
INSERT INTO `prono` VALUES (1,1,1,1,7,13),(2,1,1,1,5,9),(3,1,1,1,6,11),(4,1,1,1,1,2),(5,1,1,1,2,3),(6,1,1,1,5,10),(7,1,1,1,2,4),(8,1,1,2,6,11),(9,1,1,2,5,9),(10,1,1,2,7,13),(11,1,1,2,4,8),(12,1,1,2,1,2),(13,1,1,2,3,5),(14,1,1,2,4,7),(15,1,1,3,6,11),(16,1,1,3,4,8),(17,1,1,3,1,2),(18,1,1,3,5,9),(19,1,1,3,3,5),(20,1,1,3,7,14),(21,1,1,3,2,3),(22,1,1,4,7,13),(23,1,1,4,5,9),(24,1,1,4,3,5),(25,1,1,4,5,10),(26,1,1,4,4,8),(27,1,1,4,1,1),(28,1,1,4,3,6),(29,1,1,5,7,13),(30,1,1,5,5,9),(31,1,1,5,1,2),(32,1,1,5,3,6),(33,1,1,5,3,5),(34,1,1,5,6,11),(35,1,1,5,2,4),(36,1,1,6,5,9),(37,1,1,6,4,8),(38,1,1,6,6,11),(39,1,1,6,2,3),(40,1,1,6,3,5),(41,1,1,6,3,6),(42,1,1,6,5,10),(43,1,1,7,6,11),(44,1,1,7,2,3),(45,1,1,7,4,7),(46,1,1,7,2,4),(47,1,1,7,7,13),(48,1,1,7,1,1),(49,1,1,7,3,5),(50,1,2,1,9,4),(51,1,2,1,13,12),(52,1,2,1,12,10),(53,1,2,1,11,7),(54,1,2,1,10,5),(55,1,2,1,13,11),(56,1,2,1,14,13),(57,1,2,2,11,7),(58,1,2,2,14,13),(59,1,2,2,13,12),(60,1,2,2,12,10),(61,1,2,2,9,4),(62,1,2,2,9,3),(63,1,2,2,11,8),(64,1,2,3,14,13),(65,1,2,3,11,7),(66,1,2,3,8,1),(67,1,2,3,13,12),(68,1,2,3,10,5),(69,1,2,3,12,9),(70,1,2,3,13,11),(71,1,2,4,12,10),(72,1,2,4,10,5),(73,1,2,4,14,13),(74,1,2,4,11,7),(75,1,2,4,8,1),(76,1,2,4,9,4),(77,1,2,4,8,2),(78,1,2,5,10,5),(79,1,2,5,11,7),(80,1,2,5,14,13),(81,1,2,5,13,11),(82,1,2,5,9,4),(83,1,2,5,9,3),(84,1,2,5,8,1),(85,1,2,6,13,12),(86,1,2,6,9,4),(87,1,2,6,10,5),(88,1,2,6,8,2),(89,1,2,6,12,10),(90,1,2,6,8,1),(91,1,2,6,14,13),(92,1,2,7,13,12),(93,1,2,7,10,5),(94,1,2,7,13,11),(95,1,2,7,12,9),(96,1,2,7,14,13),(97,1,2,7,8,2),(98,1,2,7,12,10),(99,1,3,1,15,1),(100,1,3,1,21,13),(101,1,3,1,20,11),(102,1,3,1,18,8),(103,1,3,1,19,10),(104,1,3,1,21,14),(105,1,3,1,20,12),(106,1,3,2,16,4),(107,1,3,2,20,11),(108,1,3,2,17,5),(109,1,3,2,18,8),(110,1,3,2,21,13),(111,1,3,2,20,12),(112,1,3,2,17,6),(113,1,3,3,19,10),(114,1,3,3,16,4),(115,1,3,3,20,11),(116,1,3,3,17,5),(117,1,3,3,15,1),(118,1,3,3,18,8),(119,1,3,3,16,3),(120,1,3,4,18,8),(121,1,3,4,19,10),(122,1,3,4,15,1),(123,1,3,4,17,5),(124,1,3,4,21,14),(125,1,3,4,20,11),(126,1,3,4,16,3),(127,1,3,5,15,1),(128,1,3,5,21,13),(129,1,3,5,20,11),(130,1,3,5,17,6),(131,1,3,5,18,7),(132,1,3,5,18,8),(133,1,3,5,15,2),(134,1,3,6,18,8),(135,1,3,6,16,4),(136,1,3,6,20,12),(137,1,3,6,16,3),(138,1,3,6,21,14),(139,1,3,6,15,2),(140,1,3,6,19,9),(141,1,3,7,20,11),(142,1,3,7,19,10),(143,1,3,7,21,14),(144,1,3,7,19,9),(145,1,3,7,18,8),(146,1,3,7,16,4),(147,1,3,7,20,12),(148,1,4,1,27,12),(149,1,4,1,22,1),(150,1,4,1,28,13),(151,1,4,1,24,6),(152,1,4,1,26,9),(153,1,4,1,27,11),(154,1,4,1,28,14),(155,1,4,2,24,6),(156,1,4,2,28,13),(157,1,4,2,23,3),(158,1,4,2,27,12),(159,1,4,2,22,1),(160,1,4,2,25,8),(161,1,4,2,23,4),(162,1,4,3,23,3),(163,1,4,3,25,8),(164,1,4,3,27,12),(165,1,4,3,22,1),(166,1,4,3,28,13),(167,1,4,3,24,6),(168,1,4,3,26,9),(169,1,4,4,22,1),(170,1,4,4,23,3),(171,1,4,4,27,12),(172,1,4,4,26,9),(173,1,4,4,24,6),(174,1,4,4,23,4),(175,1,4,4,28,13),(176,1,4,5,24,6),(177,1,4,5,28,13),(178,1,4,5,23,3),(179,1,4,5,23,4),(180,1,4,5,22,2),(181,1,4,5,25,8),(182,1,4,5,27,12),(183,1,4,6,23,3),(184,1,4,6,27,12),(185,1,4,6,25,8),(186,1,4,6,22,2),(187,1,4,6,27,11),(188,1,4,6,24,5),(189,1,4,6,23,4),(190,1,4,7,24,6),(191,1,4,7,26,9),(192,1,4,7,23,3),(193,1,4,7,28,13),(194,1,4,7,27,12),(195,1,4,7,27,11),(196,1,4,7,25,8),(197,1,5,1,31,5),(198,1,5,1,30,4),(199,1,5,1,29,1),(200,1,5,1,34,11),(201,1,5,1,32,7),(202,1,5,1,33,9),(203,1,5,1,35,13),(204,1,5,2,34,11),(205,1,5,2,30,4),(206,1,5,2,35,14),(207,1,5,2,33,9),(208,1,5,2,31,5),(209,1,5,2,34,12),(210,1,5,2,33,10),(211,1,5,3,32,7),(212,1,5,3,33,9),(213,1,5,3,30,4),(214,1,5,3,29,1),(215,1,5,3,35,14),(216,1,5,3,33,10),(217,1,5,3,29,2),(218,1,5,4,35,14),(219,1,5,4,32,7),(220,1,5,4,33,9),(221,1,5,4,32,8),(222,1,5,4,29,1),(223,1,5,4,31,6),(224,1,5,4,34,12),(225,1,5,5,33,9),(226,1,5,5,34,11),(227,1,5,5,31,5),(228,1,5,5,31,6),(229,1,5,5,34,12),(230,1,5,5,29,1),(231,1,5,5,29,2),(232,1,5,6,32,7),(233,1,5,6,33,9),(234,1,5,6,30,3),(235,1,5,6,34,11),(236,1,5,6,31,6),(237,1,5,6,35,14),(238,1,5,6,33,10),(239,1,5,7,31,5),(240,1,5,7,33,9),(241,1,5,7,32,8),(242,1,5,7,34,12),(243,1,5,7,30,3),(244,1,5,7,32,7),(245,1,5,7,31,6);
/*!40000 ALTER TABLE `prono` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `score`
--

DROP TABLE IF EXISTS `score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `score` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season` tinyint NOT NULL DEFAULT '0',
  `day` tinyint NOT NULL COMMENT 'Journée championnat',
  `team` tinyint NOT NULL COMMENT 'Equipe championnat',
  `rank` tinyint NOT NULL,
  `pm` smallint NOT NULL COMMENT 'Points marqués',
  `pe` smallint NOT NULL COMMENT 'Points encaissés',
  `pc` tinyint NOT NULL COMMENT 'Points championnat',
  `bd` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Bonus défensif',
  `bo` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Bonus offensif',
  `em` tinyint NOT NULL DEFAULT '0' COMMENT 'Essais Marqués',
  `ee` tinyint NOT NULL DEFAULT '0' COMMENT 'Essais Encaissés',
  `ve` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Victoire extérieure',
  `J` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Joué',
  `V` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Victoire',
  `N` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Nul',
  `D` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Défaite',
  PRIMARY KEY (`id`),
  KEY `team_i` (`team`),
  KEY `day_i` (`day`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `score`
--

LOCK TABLES `score` WRITE;
/*!40000 ALTER TABLE `score` DISABLE KEYS */;
INSERT INTO `score` VALUES (1,1,1,1,14,18,39,0,0,0,2,5,0,1,0,0,1),(2,1,1,2,1,39,18,5,0,1,5,2,0,1,1,0,0),(3,1,1,3,5,36,32,5,0,1,5,4,0,1,1,0,0),(4,1,1,4,9,32,36,2,1,1,4,5,0,1,0,0,1),(5,1,1,5,3,30,12,5,0,1,4,1,0,1,1,0,0),(6,1,1,6,12,12,30,0,0,0,1,4,0,1,0,0,1),(7,1,1,7,10,30,34,2,1,1,4,4,0,1,0,0,1),(8,1,1,8,6,34,30,5,0,1,4,4,0,1,1,0,0),(9,1,1,9,2,32,13,5,0,1,4,1,0,1,1,0,0),(10,1,1,10,13,13,32,0,0,0,1,4,0,1,0,0,1),(11,1,1,11,4,29,17,5,0,1,4,2,0,1,1,0,0),(12,1,1,12,11,17,29,0,0,0,2,4,0,1,0,0,1),(13,1,1,13,7,34,31,5,0,1,4,4,0,1,1,0,0),(14,1,1,14,8,31,34,2,1,1,4,4,0,1,0,0,1),(15,1,2,1,2,30,21,5,0,1,4,3,0,1,1,0,0),(16,1,2,2,12,21,30,0,0,0,3,4,0,1,0,0,1),(17,1,2,3,14,16,30,0,0,0,2,4,0,1,0,0,1),(18,1,2,4,1,30,16,5,0,1,4,2,0,1,1,0,0),(19,1,2,5,5,25,14,4,0,0,3,2,0,1,1,0,0),(20,1,2,6,13,14,25,0,0,0,2,3,0,1,0,0,1),(21,1,2,7,6,27,19,4,0,0,3,2,0,1,1,0,0),(22,1,2,8,11,19,27,0,0,0,2,3,0,1,0,0,1),(23,1,2,9,9,20,22,1,1,0,2,3,0,1,0,0,1),(24,1,2,10,7,22,20,4,0,0,3,2,0,1,1,0,0),(25,1,2,11,10,31,40,1,0,1,4,5,0,1,0,0,1),(26,1,2,12,3,40,31,5,0,1,5,4,0,1,1,0,0),(27,1,2,13,4,35,32,5,0,1,5,4,0,1,1,0,0),(28,1,2,14,8,32,35,2,1,1,4,5,0,1,0,0,1),(29,1,3,1,5,36,31,5,0,1,5,4,0,1,1,0,0),(30,1,3,2,8,31,36,2,1,1,4,5,0,1,0,0,1),(31,1,3,3,9,29,35,2,1,1,4,5,0,1,0,0,1),(32,1,3,4,4,35,29,5,0,1,5,4,0,1,1,0,0),(33,1,3,5,3,31,19,5,0,1,4,2,0,1,1,0,0),(34,1,3,6,12,19,31,0,0,0,2,4,0,1,0,0,1),(35,1,3,7,13,22,36,0,0,0,3,5,0,1,0,0,1),(36,1,3,8,2,36,22,5,0,1,5,3,0,1,1,0,0),(37,1,3,9,10,27,28,1,1,0,3,4,0,1,0,0,1),(38,1,3,10,6,28,27,5,0,1,4,3,0,1,1,0,0),(39,1,3,11,7,15,14,4,0,0,2,2,0,1,1,0,0),(40,1,3,12,11,14,15,1,1,0,2,2,0,1,0,0,1),(41,1,3,13,1,37,17,5,0,1,5,2,0,1,1,0,0),(42,1,3,14,14,17,37,0,0,0,2,5,0,1,0,0,1),(43,1,4,1,1,35,13,5,0,1,5,1,0,1,1,0,0),(44,1,4,2,14,13,35,0,0,0,1,5,0,1,0,0,1),(45,1,4,3,3,38,20,5,0,1,5,2,0,1,1,0,0),(46,1,4,4,12,20,38,0,0,0,2,5,0,1,0,0,1),(47,1,4,5,8,27,32,1,1,0,3,4,0,1,0,0,1),(48,1,4,6,5,32,27,5,0,1,4,3,0,1,1,0,0),(49,1,4,7,11,24,32,0,0,0,3,4,0,1,0,0,1),(50,1,4,8,4,32,24,5,0,1,4,3,0,1,1,0,0),(51,1,4,9,7,19,13,4,0,0,2,1,0,1,1,0,0),(52,1,4,10,10,13,19,1,1,0,1,2,0,1,0,0,1),(53,1,4,11,9,24,29,1,1,0,3,4,0,1,0,0,1),(54,1,4,12,6,29,24,5,0,1,4,3,0,1,1,0,0),(55,1,4,13,2,34,14,5,0,1,4,2,0,1,1,0,0),(56,1,4,14,13,14,34,0,0,0,2,4,0,1,0,0,1),(57,1,5,1,3,34,20,5,0,1,4,2,0,1,1,0,0),(58,1,5,2,12,20,34,0,0,0,2,4,0,1,0,0,1),(59,1,5,3,10,22,30,0,0,0,3,4,0,1,0,0,1),(60,1,5,4,5,30,22,5,0,1,4,3,0,1,1,0,0),(61,1,5,5,6,32,28,5,0,1,4,4,0,1,1,0,0),(62,1,5,6,8,28,32,2,1,1,4,4,0,1,0,0,1),(63,1,5,7,2,28,13,5,0,1,4,1,0,1,1,0,0),(64,1,5,8,13,13,28,0,0,0,1,4,0,1,0,0,1),(65,1,5,9,7,17,10,4,0,0,2,1,0,1,1,0,0),(66,1,5,10,9,10,17,1,1,0,1,2,0,1,0,0,1),(67,1,5,11,4,29,17,5,0,1,4,2,0,1,1,0,0),(68,1,5,12,11,17,29,0,0,0,2,4,0,1,0,0,1),(69,1,5,13,14,12,34,0,0,0,1,4,0,1,0,0,1),(70,1,5,14,1,34,12,5,0,1,4,1,0,1,1,0,0);
/*!40000 ALTER TABLE `score` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `season`
--

DROP TABLE IF EXISTS `season`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `season` (
  `Id` tinyint NOT NULL,
  `title` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `start` date NOT NULL,
  `start_register` date NOT NULL,
  `stop_register` date NOT NULL,
  `close_forum` date NOT NULL,
  UNIQUE KEY `Id` (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `season`
--

LOCK TABLES `season` WRITE;
/*!40000 ALTER TABLE `season` DISABLE KEYS */;
INSERT INTO `season` VALUES (1,'Season 2025-2026 (Test)','2025-09-01','2025-08-01','2025-12-31','2026-06-30');
/*!40000 ALTER TABLE `season` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team`
--

DROP TABLE IF EXISTS `team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team` (
  `team_short` text NOT NULL,
  `team_long` varchar(50) NOT NULL,
  `team_idx` tinyint NOT NULL,
  `season` tinyint NOT NULL,
  `previous_season` tinyint NOT NULL,
  UNIQUE KEY `Idx` (`team_idx`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team`
--

LOCK TABLES `team` WRITE;
/*!40000 ALTER TABLE `team` DISABLE KEYS */;
INSERT INTO `team` VALUES ('Toulouse','Stade Toulousain',1,1,0),('La Rochelle','Stade Rochelais',2,1,0),('Bordeaux','Union Bordeaux-Bègles',3,1,0),('Clermont','ASM Clermont Auvergne',4,1,0),('Racing','Racing 92',5,1,0),('Toulon','RC Toulon',6,1,0),('Castres','Castres Olympique',7,1,0),('Montpellier','Montpellier HR',8,1,0),('Lyon','LOU Rugby',9,1,0),('Stade Français','Stade Français Paris',10,1,0),('Pau','Section Paloise',11,1,0),('Bayonne','Aviron Bayonnais',12,1,0),('Perpignan','USA Perpignan',13,1,0),('Vannes','RC Vannes',14,1,0);
/*!40000 ALTER TABLE `team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_player`
--

DROP TABLE IF EXISTS `team_player`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_player` (
  `team_idx` smallint NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `season` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  UNIQUE KEY `team_idx` (`team_idx`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_player`
--

LOCK TABLES `team_player` WRITE;
/*!40000 ALTER TABLE `team_player` DISABLE KEYS */;
INSERT INTO `team_player` VALUES (1,'Test Team',1,1);
/*!40000 ALTER TABLE `team_player` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-26 21:10:14
