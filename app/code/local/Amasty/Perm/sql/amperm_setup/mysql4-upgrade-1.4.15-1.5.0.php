<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

/**
 * @var Mage_Core_Model_Resource_Setup $this
 */
$installer = $this;
$installer->startSetup();

$this->run("
CREATE TABLE {$this->getTable('amperm/queue')} (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `emails` VARCHAR(255) NOT NULL default '',
  `order_ids` VARCHAR(255) NOT NULL default '',
  `processed` TINYINT(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
