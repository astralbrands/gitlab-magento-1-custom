<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE `{$this->getTable('amlocator/block')}` (
	`block_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`start_ip_num` INT(10) UNSIGNED NOT NULL,
	`end_ip_num` INT(10) UNSIGNED NOT NULL,
	`geoip_loc_id` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`block_id`),
	INDEX `start_ip_num` (`start_ip_num`),
	INDEX `end_ip_num` (`end_ip_num`)
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;
");


$installer->run("
CREATE TABLE `{$this->getTable('amlocator/ip_location')}` (
	`location_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`geoip_loc_id` INT(10) UNSIGNED NOT NULL,
	`country` CHAR(2) NULL DEFAULT NULL,
	`region` CHAR(2) NULL DEFAULT NULL,
	`city` VARCHAR(255) NULL DEFAULT NULL,
	`postal_code` CHAR(5) NULL DEFAULT NULL,
	`latitude` FLOAT NULL DEFAULT NULL,
	`longitude` FLOAT NULL DEFAULT NULL,
	`dma_code` INT(11) NULL DEFAULT NULL,
	`area_code` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`location_id`),
	INDEX `geoip_loc_id` (`geoip_loc_id`)
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;
");

$installer->run("
CREATE TABLE `{$this->getTable('amlocator/table_location')}` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`country` VARCHAR(100) NOT NULL,
	`city` VARCHAR(255) NOT NULL,
	`zip` CHAR(5) NOT NULL,
	`address` VARCHAR(255) NOT NULL,
	`status` TINYINT(4) NOT NULL,
	`lat` DECIMAL(10,8) NOT NULL,
	`lng` DECIMAL(11,8) NOT NULL,
	`photo` VARCHAR(255) NOT NULL,
	`marker` VARCHAR(255) NOT NULL,
	`position` SMALLINT(6) NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
");

$installer->run("
CREATE TABLE `{$this->getTable('amlocator/table_location_store')}` (
	`location_id` INT(11) NOT NULL,
	`store_id` INT(5) NOT NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
");

$installer->endSetup(); 