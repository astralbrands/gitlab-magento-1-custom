<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

$installer = $this;

$installer->startSetup();

$installer->run("DROP TABLE IF EXISTS `{$installer->getTable('storelocator/storelocator')}`");
$installer->run("

CREATE TABLE IF NOT EXISTS  `{$installer->getTable('storelocator/storelocator')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `store_id` int(11) DEFAULT NULL,
  `products` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `city` varchar(30) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `street2` varchar(100) DEFAULT NULL,
  `state` varchar(30) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `country` varchar(30) DEFAULT NULL,
  `lat` decimal(11,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `update_geo` char(1) DEFAULT 0,
  `phone` varchar(30) DEFAULT NULL, 
  `website` varchar(100) DEFAULT NULL,
  `status` char(1) DEFAULT NULL,  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store locations Table';

"
);


$installer->endSetup(); 