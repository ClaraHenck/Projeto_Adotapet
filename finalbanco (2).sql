CREATE DATABASE  IF NOT EXISTS `adotapet_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `adotapet_db`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: adotapet_db
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `adotantes`
--

DROP TABLE IF EXISTS `adotantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adotantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adotantes`
--

LOCK TABLES `adotantes` WRITE;
/*!40000 ALTER TABLE `adotantes` DISABLE KEYS */;
INSERT INTO `adotantes` VALUES (1,'adotante@email.com','$2y$10$n3Qsd5FGEuJDeVq9ohOlQOJ6oPU18A7BZmNZm3Y2mtJSqKF0yczfa','Adotante teste','99012345678','2026-10-07 14:12:34');
/*!40000 ALTER TABLE `adotantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `animais`
--

DROP TABLE IF EXISTS `animais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `animais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ong_id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `especie_raca` varchar(150) NOT NULL,
  `idade_estimada` varchar(50) NOT NULL,
  `porte` varchar(50) NOT NULL,
  `carteira_vacinacao` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `descricao` text NOT NULL,
  `temperamento` varchar(50) NOT NULL,
  `sociabilidade` varchar(100) NOT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'Disponível',
  PRIMARY KEY (`id`),
  KEY `ong_id` (`ong_id`),
  CONSTRAINT `animais_ibfk_1` FOREIGN KEY (`ong_id`) REFERENCES `ongs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animais`
--

LOCK TABLES `animais` WRITE;
/*!40000 ALTER TABLE `animais` DISABLE KEYS */;
INSERT INTO `animais` VALUES (2,1,'Panda','Cachorro - Vira-Lata','Não informada','Médio','Completa','uploads/pet_1792003828_6acfcef4940f2.png','Carinhoso','Dócil','Sociável com todos','2026-10-14 18:50:28','Indisponível'),(3,1,'Fumaça','Cachorro - Vira-Lata','1 anos','Grande','Apenas Vacinado','uploads/pet_1792003965_6acfcf7d5c384.png','Muito dócil, alegre e brincalhão\r\nAparência: Pelagem preta com detalhes em marrom e uma linda mancha branca no peito\r\nExpressão: Muito carismático e sempre com a língua para fora de tanta felicidade','Brincalhão','Sociável com todos','2026-10-14 18:52:45','Disponível'),(4,1,'Calu','Cachorro - Vira-Lata','1 anos','Médio','Apenas Vacinado','uploads/pet_1793101683_6ae08f73a378e.jpg','Dominante','Dominante','Apenas com animais','2026-10-27 11:48:11','Indisponível'),(5,1,'Kiara','Cachorro - Vira-Lata','1 anos','Grande','Vacinado e Vermifugado','uploads/pet_1793102161_6ae09151be3fb.jpg','Não tão dócil com crianças','Agitado','Apenas com animais','2026-10-27 11:56:01','Disponível');
/*!40000 ALTER TABLE `animais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidaturas`
--

DROP TABLE IF EXISTS `candidaturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidaturas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `adotante_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `animal_id` int NOT NULL,
  `questionario_id` int NOT NULL,
  `status_candidatura` varchar(50) DEFAULT 'Pendente',
  `compatibilidade` varchar(10) DEFAULT '70%',
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `adotante_id` (`adotante_id`),
  KEY `animal_id` (`animal_id`),
  KEY `questionario_id` (`questionario_id`),
  CONSTRAINT `candidaturas_ibfk_1` FOREIGN KEY (`adotante_id`) REFERENCES `adotantes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidaturas_ibfk_2` FOREIGN KEY (`animal_id`) REFERENCES `animais` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidaturas_ibfk_3` FOREIGN KEY (`questionario_id`) REFERENCES `questionarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidaturas`
--

LOCK TABLES `candidaturas` WRITE;
/*!40000 ALTER TABLE `candidaturas` DISABLE KEYS */;
INSERT INTO `candidaturas` VALUES (4,1,NULL,4,1,'Aprovado','71%','2026-11-04 11:25:08');
/*!40000 ALTER TABLE `candidaturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ongs`
--

DROP TABLE IF EXISTS `ongs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ongs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `foto` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome_instituicao` varchar(255) NOT NULL,
  `descricao` text,
  `cnpj` varchar(18) DEFAULT NULL,
  `telefone` varchar(20) NOT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `horario_atendimento` varchar(255) DEFAULT NULL,
  `raio_atuacao` varchar(100) DEFAULT NULL,
  `chave_pix` varchar(255) DEFAULT NULL,
  `capacidade_abrigados` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ongs`
--

LOCK TABLES `ongs` WRITE;
/*!40000 ALTER TABLE `ongs` DISABLE KEYS */;
INSERT INTO `ongs` VALUES (1,'ong_foto_1_1791982339.png','ong@gmail.com','$2y$10$Lyoyd0cadt3MsgzMF6HTOu6R0Cqf9LJKrnpub2E10ACbgEXYT.dR2','Ajuda animais',NULL,NULL,'99012345678',NULL,'2026-10-07 14:36:57',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `ongs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questionarios`
--

DROP TABLE IF EXISTS `questionarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `adotante_id` int NOT NULL,
  `onde_mora` varchar(50) NOT NULL,
  `tem_area_externa` tinyint(1) NOT NULL,
  `horas_fora_casa` int NOT NULL,
  `experiencia` varchar(50) NOT NULL,
  `tem_criancas` tinyint(1) NOT NULL,
  `tem_outros_animais` tinyint(1) NOT NULL,
  `nivel_atividade_fisica` varchar(30) NOT NULL,
  `data_resposta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `adotante_id` (`adotante_id`),
  CONSTRAINT `questionarios_ibfk_1` FOREIGN KEY (`adotante_id`) REFERENCES `adotantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionarios`
--

LOCK TABLES `questionarios` WRITE;
/*!40000 ALTER TABLE `questionarios` DISABLE KEYS */;
INSERT INTO `questionarios` VALUES (1,1,'casa_sem_quintal',1,8,'experiente',0,0,'moderado','2026-11-04 11:24:49');
/*!40000 ALTER TABLE `questionarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-04 10:42:44
