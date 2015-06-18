SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `deedbox` ;
CREATE SCHEMA IF NOT EXISTS `deedbox` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `deedbox` ;

-- -----------------------------------------------------
-- Table `deedbox`.`document`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `deedbox`.`document` ;

CREATE  TABLE IF NOT EXISTS `deedbox`.`document` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `created_at` DATETIME NOT NULL ,
  `modified_at` DATETIME NOT NULL ,
  `last_found` DATETIME NULL ,
  `original_filename` VARCHAR(256) NOT NULL ,
  `name` VARCHAR(45) NULL ,
  `path` VARCHAR(255) NOT NULL COMMENT '				' ,
  `file_name` VARCHAR(255) NOT NULL ,
  `tag` VARCHAR(255) NULL ,
  `content` LONGTEXT NULL COMMENT '		' ,
  `thumbnail` BLOB NULL ,
  `preview` LONGBLOB NULL ,
  `fk_doc_spec` INT UNSIGNED NULL ,
  `spec_accuracy` TINYINT UNSIGNED NULL ,
  `fk_doc_group` INT UNSIGNED NULL ,
  `group_accuracy` TINYINT UNSIGNED NULL ,
  `document_date` DATETIME NULL ,
  `document_IBAN` VARCHAR(45) NULL ,
  `document_amount` DECIMAL(10,2) NULL ,
  `document_stats` longtext,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `deedbox`.`doc_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `deedbox`.`doc_group` ;

CREATE  TABLE IF NOT EXISTS `deedbox`.`doc_group` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `created_at` DATETIME NOT NULL ,
  `modified_at` DATETIME NOT NULL ,
  `recognition_feature` LONGBLOB NOT NULL COMMENT '	' ,
  `sift_index` LONGTEXT NOT NULL ,
  `name` VARCHAR(45) NOT NULL COMMENT '			' ,
  `short_name` VARCHAR(20) NOT NULL ,
  `website` VARCHAR(100) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
PACK_KEYS = Default;


-- -----------------------------------------------------
-- Table `deedbox`.`doc_spec_maingroup`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `deedbox`.`doc_spec_maingroup` ;

CREATE  TABLE IF NOT EXISTS `deedbox`.`doc_spec_maingroup` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `created_at` DATETIME NOT NULL ,
  `modified_at` DATETIME NOT NULL ,
  `name` VARCHAR(45) NOT NULL ,
  `code` VARCHAR(45) NOT NULL ,
  `sort` INT UNSIGNED NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `deedbox`.`doc_spec_recog_feat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `deedbox`.`doc_spec_recog_feat` ;

CREATE  TABLE IF NOT EXISTS `deedbox`.`doc_spec_recog_feat` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '	' ,
  `name` VARCHAR(45) NOT NULL ,
  `query` TEXT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `deedbox`.`doc_spec_recog_feats`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `deedbox`.`doc_spec_recog_feats` ;

CREATE  TABLE IF NOT EXISTS `deedbox`.`doc_spec_recog_feats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `fk_doc_spec_recog_feat` INT UNSIGNED NOT NULL ,
  `fk_doc_spec` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `deedbox`.`doc_spec_maingroup`
-- -----------------------------------------------------
START TRANSACTION;
USE `deedbox`;
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (1, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Anfrage', 'ACA1', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (2, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Offerte', 'ACA2', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (3, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Genehmigungsantrag', 'ACB1', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (4, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Genehmigung', 'ACB2', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (5, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Lieferbedingung', 'ACC1', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (6, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Vertrag', 'ACC2', 8);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (7, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Allgemeine Geschäftsbedingungen', 'ACC3', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (9, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Bestellung', 'ACD1', 7);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (10, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Auftrag', 'ACD2', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (11, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Lieferschein', 'ACD3', 6);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (12, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Rechnung', 'ACE1', 1);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (13, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Versicherungspolice', 'ACF1', 5);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (14, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Schadensmeldung', 'ACF2', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (16, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Garantieschein', 'ACG1', 4);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (17, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Gutachten', 'ACH1', NULL);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (18, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Konotauszug', 'ACI1', 3);
INSERT INTO `deedbox`.`doc_spec_maingroup` (`id`, `created_at`, `modified_at`, `name`, `code`, `sort`) VALUES (19, '2012-07-25 10:00:00', '2012-07-25 10:00:00', 'Sonstige', 'ACZ1', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `deedbox`.`doc_spec_recog_feat`
-- -----------------------------------------------------
START TRANSACTION;
USE `deedbox`;
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (1, 'Rechnung', '/[Rr]echnung|RECHNUNG/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (2, 'Rechnungsnummer', '/[Rr]echnungsnummer/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (3, 'Artikel', '/[Aa]rtikel/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (4, 'Artikelnummer', '/[Aa]rtikelnummer/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (5, 'Rechnungsbetrag', '/[Rr]echnungsbetrag/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (6, 'Einzahlungsschein', '/Einzahlung Giro/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (7, 'Empfangsschein', '/Empfangsschein/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (8, 'Zahlungsbedingungen', '/[Zz]ahlungsbedingungen/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (9, 'Garantieschein', '/[Gg]arantieschein|GARANTIESCHEIN/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (10, 'Garantiedauer', '/[Gg]arantiedauer|GARANTIEDAUER|[Gg]arantie[ -][Dd]auer/');
INSERT INTO `deedbox`.`doc_spec_recog_feat` (`id`, `name`, `query`) VALUES (11, 'Monat', '/[Mm]onat* |MONAT* /');

COMMIT;

-- -----------------------------------------------------
-- Data for table `deedbox`.`doc_spec_recog_feats`
-- -----------------------------------------------------
START TRANSACTION;
USE `deedbox`;
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (1, 1, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (2, 2, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (3, 3, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (4, 4, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (5, 5, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (6, 6, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (7, 7, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (8, 8, 12);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (9, 9, 16);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (10, 10, 16);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (11, 11, 16);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (12, 3, 16);
INSERT INTO `deedbox`.`doc_spec_recog_feats` (`id`, `fk_doc_spec_recog_feat`, `fk_doc_spec`) VALUES (13, 4, 17);

COMMIT;
