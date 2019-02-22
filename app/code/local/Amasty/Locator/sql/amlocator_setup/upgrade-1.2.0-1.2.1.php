<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */



$this->startSetup();


$this->run("ALTER TABLE `{$this->getTable('amlocator/table_location')}` CHANGE COLUMN `zip` `zip` CHAR(20) NOT NULL  ");



$this->endSetup();