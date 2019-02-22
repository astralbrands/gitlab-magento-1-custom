<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */
$this->startSetup();

$this->run("

ALTER TABLE `{$this->getTable('amogrid/order_item_product')}`
ENGINE = INNODB;    

");

$this->endSetup(); 