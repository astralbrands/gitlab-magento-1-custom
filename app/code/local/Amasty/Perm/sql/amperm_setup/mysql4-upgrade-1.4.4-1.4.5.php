<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('amperm/order')};

CREATE TABLE {$this->getTable('amperm/order')} (
  `perm_order_id` int(10) unsigned NOT NULL auto_increment,
  `uid` mediumint(9) unsigned NOT NULL default '0',
  `oid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`perm_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('amperm/order')} (`uid`, `oid`)
  SELECT `uid`, `entity_id`
    FROM {$this->getTable('sales/order_grid')}
    WHERE `uid` > 0;

ALTER TABLE `{$this->getTable('sales/order_grid')}` DROP COLUMN `uid`;
");

$this->endSetup();
