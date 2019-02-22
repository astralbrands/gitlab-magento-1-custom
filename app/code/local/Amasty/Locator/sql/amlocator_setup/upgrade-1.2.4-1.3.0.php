<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */

$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amlocator/table_location_product')}` (
	`location_id` INT(11) NOT NULL,
	`product_id` INT(5) NOT NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
");

$this->run("
CREATE TABLE `{$this->getTable('amlocator/table_location_category')}` (
	`location_id` INT(11) NOT NULL,
	`category_id` INT(5) NOT NULL
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
");

$this->endSetup();