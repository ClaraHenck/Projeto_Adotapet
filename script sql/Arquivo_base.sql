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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adotantes`
--

LOCK TABLES `adotantes` WRITE;
/*!40000 ALTER TABLE `adotantes` DISABLE KEYS */;
INSERT INTO `adotantes` VALUES (1,'clarahenc21@gmail.com','$2y$10$5f4fNtdcAdet5CdxnHtrqep4wJ5BpGGIZc2MbOo8U9.5P028yyTBK','ana clara','19 981866290','2026-06-23 12:18:04'),(2,'admin@email.com','$2y$10$kTF24o51P1aeCjiyv3NEGuFesQYWjRddfU.frLRQYmfziM6VdWwKS','Clara','19 981866290','2026-08-12 17:35:15'),(3,'teste@email.com','$2y$10$kKn0eVjMbikWNDfVp0vFzedmKXqp8sjhjmYoE2b0HwvHmbSSQ../C','Teste','11999999999','2026-09-08 12:08:09');
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
  `foto_url` text NOT NULL,
  `descricao` text NOT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ong_id` (`ong_id`),
  CONSTRAINT `animais_ibfk_1` FOREIGN KEY (`ong_id`) REFERENCES `ongs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12345684 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animais`
--

LOCK TABLES `animais` WRITE;
/*!40000 ALTER TABLE `animais` DISABLE KEYS */;
INSERT INTO `animais` VALUES (12345678,2,'belinha','Poddle','15','grande','completa','https://images.unsplash.com/photo-1543466835-00a7907e9de1?q=80&w=500','descricao','2026-07-29 17:30:00'),(12345681,2,'Thor','Cão - Labrador','9','grande','completa','https://unsplash.com','Dócil, companheiro e excelente para famílias com crianças.','2026-07-29 19:10:00'),(12345682,1,'Fiat Argo','Cachorro - labrador','8 anos','Médio','Vacinado e Vermifugado','data:image/jpeg;base64,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','Sociavel','2026-08-12 17:51:29'),(12345683,1,'Lamborghini','Gato - siames','14 anos','Pequeno','Apenas Vacinado','data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBwgHBgkIBwgKCgkLDRYPDQwMDRsUFRAWIB0iIiAdHx8kKDQsJCYxJx8fLT0tMTU3Ojo6Iys/RD84QzQ5OjcBCgoKDQwNGg8PGjclHyU3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3N//AABEIAJQA8QMBIgACEQEDEQH/xAAbAAACAwEBAQAAAAAAAAAAAAAEBQIDBgABB//EAEAQAAIBAwMBBgUCAwUFCQAAAAECAwAEEQUSITEGEyJBUWEUMnGBkSNCFVKhVJKx0eEWM8Hw8QckJUNTYmNylP/EABkBAAMBAQEAAAAAAAAAAAAAAAECAwAEBf/EACURAAICAgICAwACAwAAAAAAAAABAhEDIRIxQVEEEyIyYRRCcf/aAAwDAQACEQMRAD8AC7dQraamkoA8YOa1XYhM6ZHlsgjNKO3dmGmWdxkKSKu7DXhNqYh0RiBXk3cUVf8AE3EaANniq7qVUU1FXYZND3Lbh4qon+dE2zNaxesZSiggUukUuhJY9Ohp7dWiTtuAqC6dtAyOPWuafOyfkzMURhbeRjPNO7O9TbtbBFSvtMMifptzVMNmLe3Jk61uLrZhvE0Uy8AVCeCGNCWxQensWY8YA6UXeDKeI9KnklxVmWzO3UYeYlBxQssch4UU5miDJlOtDxqQfGKhF/arD0KkhkGN2auK7Y+c05Fj3qbgCSaEn02fHTinWJ8tmcgBEEgwakluI+TjFSijkD7SKKlhYxdKrNUqQqkQjCKM5r0SeLwnNBgSFipJphaxRxAFzk+dcn0u7KJnkwlulEe3AqtLCO3O44z7URcalDAPDyaEzLeneh2rVox0Zsrv71IYvApP0FCaJdLNM28YPuKZrYHYe85rrWziikyVCn2p4tJUZMaCCGVcEj6YqXwcMaEriqd0YQ+Ln1oQ3DAkb8ilWmYlfYEJx','asdfghjklç','2026-08-12 18:12:07');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidaturas`
--

LOCK TABLES `candidaturas` WRITE;
/*!40000 ALTER TABLE `candidaturas` DISABLE KEYS */;
INSERT INTO `candidaturas` VALUES (3,1,0,12345678,1,'Pendente','80%','2026-08-12 17:33:27'),(6,2,NULL,12345678,3,'Pendente','60%','2026-08-12 17:48:05'),(7,2,NULL,12345681,3,'Pendente','60%','2026-08-12 17:48:10'),(8,1,NULL,12345682,1,'Pendente','80%','2026-08-12 17:59:18');
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
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nome_instituicao` varchar(255) NOT NULL,
  `cnpj` varchar(18) DEFAULT NULL,
  `telefone` varchar(20) NOT NULL,
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ongs`
--

LOCK TABLES `ongs` WRITE;
/*!40000 ALTER TABLE `ongs` DISABLE KEYS */;
INSERT INTO `ongs` VALUES (1,'ana.hencklein41@gmail.com','$2y$10$7VcFI/7G9Ethf8voIWXaSOy1qfztVWEWi.IlwpaNve4yCndO.Xjoi','Clara',NULL,'19 981866290','2026-06-23 19:46:22',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'henckleinclara@gmail.com','$2y$10$C2Bu6YlNPT.xeQW65ZTgr.sTnV8HilDGFWCEGo6K/eAIv4KBgiAjG','blusas',NULL,'19 981866290','2026-06-23 19:46:59',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'contato@protecao.org.br','123456','ONG Proteção Animal SP',NULL,'(11) 98888-1111','2026-08-26 18:03:13','Av. Paulista','1000','Bela Vista','São Paulo','SP',NULL,-23.56141400,-46.65588100),(4,'patinhas@abrigofelizm.org','123456','Abrigo Patinhas Felizes',NULL,'(11) 97777-2222','2026-08-26 18:03:13','Rua Augusta','500','Consolação','São Paulo','SP',NULL,-23.55322100,-46.64811200),(5,'contato@amigofiel.org','123456','Instituto Amigo Fiel',NULL,'(11) 96666-3333','2026-08-26 18:03:13','Av. Rebouças','1200','Pinheiros','São Paulo','SP',NULL,-23.56583000,-46.68083000);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionarios`
--

LOCK TABLES `questionarios` WRITE;
/*!40000 ALTER TABLE `questionarios` DISABLE KEYS */;
INSERT INTO `questionarios` VALUES (1,1,'sitio',0,16,'experiente',1,1,'sedentario','2026-08-05 18:25:21'),(3,2,'casa_sem_quintal',1,10,'experiente',0,0,'ativo','2026-08-12 17:43:38');
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

-- Dump completed on 2026-09-08  9:14:06
