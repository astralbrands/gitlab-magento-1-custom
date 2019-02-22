<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */



$this->startSetup();


$this->run("ALTER TABLE `{$this->getTable('amlocator/table_location')}` ADD COLUMN `phone` VARCHAR(50) NULL AFTER `description`,
	ADD COLUMN `email` VARCHAR(80) NULL AFTER `phone`,
	ADD COLUMN `website` VARCHAR(100) NULL AFTER `email`;  ");




$this->endSetup();