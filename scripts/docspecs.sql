-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: deedbox
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `doc_spec_maingroup`
--

DROP TABLE IF EXISTS `doc_spec_maingroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc_spec_maingroup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `name` varchar(45) NOT NULL,
  `code` varchar(45) NOT NULL,
  `sort` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_spec_maingroup`
--

LOCK TABLES `doc_spec_maingroup` WRITE;
/*!40000 ALTER TABLE `doc_spec_maingroup` DISABLE KEYS */;
INSERT INTO `doc_spec_maingroup` VALUES (1,'2012-07-25 10:00:00','2012-07-25 10:00:00','Anfrage/Kalkulation/Angebot','ACA',5),(3,'2012-07-25 10:00:00','2012-07-25 10:00:00','Genehmigungsdokumente','ACB',7),(5,'2012-07-25 10:00:00','2012-07-25 10:00:00','Vertragliche Dokumente','ACC',9),(9,'2012-07-25 10:00:00','2012-07-25 10:00:00','Bestell- und Lieferdokumente','ACD',4),(12,'2012-07-25 10:00:00','2012-07-25 10:00:00','Rechnungsdokumente','ACE',3),(13,'2012-07-25 10:00:00','2012-07-25 10:00:00','Versicherungsdokumente','ACF',2),(16,'2012-07-25 10:00:00','2012-07-25 10:00:00','Gewährleistungsdokumente','ACG',6),(17,'2012-07-25 10:00:00','2012-07-25 10:00:00','Gutachten','ACH',8),(18,'2012-07-25 10:00:00','2012-07-25 10:00:00','Bankdokumente','ACI',1),(19,'2012-07-25 10:00:00','2012-07-25 10:00:00','Sonstige','ACZ',10);
/*!40000 ALTER TABLE `doc_spec_maingroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doc_spec_recog_feats`
--

DROP TABLE IF EXISTS `doc_spec_recog_feats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc_spec_recog_feats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fk_doc_spec_recog_feat` int(10) unsigned NOT NULL,
  `fk_doc_spec` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_spec_recog_feats`
--

LOCK TABLES `doc_spec_recog_feats` WRITE;
/*!40000 ALTER TABLE `doc_spec_recog_feats` DISABLE KEYS */;
INSERT INTO `doc_spec_recog_feats` VALUES (2,2,12),(5,5,12),(6,6,12),(7,7,12),(8,8,12),(9,9,16),(10,10,16),(11,11,16),(12,3,16),(16,14,13),(17,15,13),(18,16,13),(19,17,13),(20,18,1),(21,19,1),(22,20,1),(23,21,1),(24,22,1),(25,23,1),(26,24,1),(27,25,1),(28,26,5),(29,27,5),(30,28,5),(31,29,5),(32,30,5),(33,31,5),(34,32,5),(35,30,5),(36,33,5),(37,34,5),(38,35,9),(39,36,9),(40,37,18),(41,38,18),(42,39,18),(43,40,18),(44,41,18),(45,42,9),(46,43,18);
/*!40000 ALTER TABLE `doc_spec_recog_feats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doc_spec_recog_feat`
--

DROP TABLE IF EXISTS `doc_spec_recog_feat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doc_spec_recog_feat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '	',
  `name` varchar(45) NOT NULL,
  `query` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doc_spec_recog_feat`
--

LOCK TABLES `doc_spec_recog_feat` WRITE;
/*!40000 ALTER TABLE `doc_spec_recog_feat` DISABLE KEYS */;
INSERT INTO `doc_spec_recog_feat` VALUES (2,'Rechnungsnummer','/rechnungsnummer/ui'),(3,'Artikel','/artikel/ui'),(5,'Rechnungsbetrag','/rechnungsbetrag/ui'),(6,'Einzahlungsschein','/Einzahlung Giro/u'),(7,'Empfangsschein','/Empfangsschein/u'),(8,'Zahlungsbedingungen','/zahlungsbedingungen/ui'),(9,'Garantieschein','/garantieschein/ui'),(10,'Garantiedauer','/garantie[ -]*dauer/ui'),(11,'Monat','/[0-9]?[0-9].*monat[e]?/ui'),(14,'Police Nr','/(police)([ -])?(nr|nummer)?([\\.:])?/ui'),(15,'Prämie','/prämie/ui'),(16,'Versicherungsnehmer','/versicherungs[ -]*nehmer/ui'),(17,'Stempelsteuer','/stempelsteuer/ui'),(18,'Antrag','/antrag/ui'),(19,'Anfrage','/anfrage/ui'),(20,'Gesuch','/gesuch/ui'),(21,'Erkundigung','/erkundigung/ui'),(22,'Offerte','/offerte/ui'),(23,'Angebot','/angebot/ui'),(24,'Gültigkeit','/gültig bis|gültigkeit/ui'),(25,'Unterbreiten','/unterbreiten/ui'),(26,'Vertrag','/vertrag|verträge/ui'),(27,'Käufer','/käufer/ui'),(28,'Verkäufer','/verkäufer/ui'),(29,'Vertragsparteien','/partei[e]*[n*]/ui'),(30,'Gerichtsstand','/gerichtsstand/ui'),(31,'Haftung','/haftung/ui'),(32,'Verzug','/verzug/ui'),(33,'AGB','/AGB/'),(34,'Allgemeine Geschäftsbedingungen','/allgemeine geschäftsbedingungen/ui'),(35,'Bestellung','/bestellung[e]*[n]*/ui'),(36,'Lieferschein','/lieferschein/ui'),(37,'Kontoauszug','/kontoauszug/ui'),(38,'Saldo','/saldo/ui'),(39,'IBAN','/iban[ :]*[a-zA-Z]{2}[0-9]{2} ?[a-zA-Z0-9]{4} ?[0-9]{4} ?[0-9]{4} ?[0-9]{4} ?[0-9]/ui'),(40,'HABEN','/(haben|gutschrift)/ui'),(41,'Valuta','/valuta/ui'),(42,'Bestell Nummer','/bestell[-]?nummer[:;]? *[0-9a-z\\.-]{3}/ui'),(43,'SOLL','/(soll|belastung)/ui');
/*!40000 ALTER TABLE `doc_spec_recog_feat` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-09-10 10:51:15
