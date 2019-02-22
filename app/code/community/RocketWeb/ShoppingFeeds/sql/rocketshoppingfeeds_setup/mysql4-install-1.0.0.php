<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 * @copyright  Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

/**
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 * @author     RocketWeb
 */

/**
 * @var $installer RocketWeb_ShoppingFeeds_Model_Resource_Eav_Mysql4_Setup
 */
$installer = $this;
$installer->startSetup();

if ($installer->upgradeGoogleShoppingFeed() === false) {
    $installer->run("
DROP TABLE IF EXISTS {$installer->getTable('rocketshoppingfeeds/feed')};
CREATE TABLE {$installer->getTable('rocketshoppingfeeds/feed')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` smallint(5) unsigned NOT NULL,
  `name` text NOT NULL,
  `type` varchar(100) NOT NULL DEFAULT 'generic',
  `status` smallint(5) unsigned NOT NULL DEFAULT '1',
  `messages` varchar(1500) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `use_for_microdata` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Use feed settings for microdata',
  PRIMARY KEY (`id`),
  KEY `FK_STORE_ID` (`store_id`),
  KEY `IDX_TYPE` (`type`),
  KEY `IDX_UPDATED_AT` (`updated_at`),
  CONSTRAINT `FK_FEED_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$installer->getTable('rocketshoppingfeeds/feed_config')};
CREATE TABLE {$installer->getTable('rocketshoppingfeeds/feed_config')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL,
  `path` varchar(255) NOT NULL DEFAULT 'general',
  `value` mediumtext,
  PRIMARY KEY (`id`),
  KEY `FK_FEED_ID` (`feed_id`),
  KEY `IDX_PATH` (`path`),
  CONSTRAINT `FK_FEED_CONFIG_FEED` FOREIGN KEY (`feed_id`) REFERENCES {$installer->getTable('rocketshoppingfeeds/feed')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$installer->getTable('rocketshoppingfeeds/feed_schedule')};
CREATE TABLE {$installer->getTable('rocketshoppingfeeds/feed_schedule')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL,
  `start_at` smallint(6) DEFAULT NULL,
  `processed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `batch_mode` smallint(6) NOT NULL,
  `batch_limit` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_RW_GFEED_FEED_SCHEDULE_FEED_ID` (`feed_id`),
  CONSTRAINT `FK_RW_GFEED_FEED_SCHEDULE_FEED_ID_RW_GFEED_FEED_ID` FOREIGN KEY (`feed_id`) REFERENCES {$installer->getTable('rocketshoppingfeeds/feed')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$installer->getTable('rocketshoppingfeeds/queue')};
CREATE TABLE {$installer->getTable('rocketshoppingfeeds/queue')} (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) unsigned DEFAULT NULL,
  `feed_id` int(11) unsigned NOT NULL,
  `message` varchar(1500) NOT NULL,
  `is_read` int(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_FEED_ID` (`feed_id`),
  CONSTRAINT `FK_FEED_QUEUE_FEED` FOREIGN KEY (`feed_id`) REFERENCES {$installer->getTable('rocketshoppingfeeds/feed')} (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$installer->getTable('rocketshoppingfeeds/feed_ftp')};
CREATE TABLE {$installer->getTable('rocketshoppingfeeds/feed_ftp')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Ftp Id',
  `feed_id` int(10) unsigned NOT NULL COMMENT 'Feed Id',
  `username` varchar(255) NOT NULL COMMENT 'FTP Username',
  `password` varchar(255) NOT NULL COMMENT 'FTP Password',
  `host` varchar(255) NOT NULL COMMENT 'FTP Host',
  `port` int(10) unsigned NOT NULL COMMENT 'FTP Port',
  `path` varchar(255) NOT NULL COMMENT 'FTP Path',
  `mode` varchar(255) DEFAULT NULL COMMENT 'Mode - FTP or SFTP',
  PRIMARY KEY (`id`),
  KEY `IDX_RW_GFEED_FEED_FTP_FEED_ID` (`feed_id`),
  CONSTRAINT `FK_RSF_FEED_FTP_FEED_ID_RSF_FEED_ID` FOREIGN KEY (`feed_id`) REFERENCES {$installer->getTable('rocketshoppingfeeds/feed')} (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$installer->getTable('rocketshoppingfeeds/process')};
CREATE TABLE {$installer->getTable('rocketshoppingfeeds/process')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `parent_item_id` int(10) unsigned DEFAULT NULL,
  `status` smallint(5) unsigned NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_ITEM_ID_STORE_ID` (`item_id`,`feed_id`),
  KEY `IDX_PARENT_ITEM_ID` (`parent_item_id`),
  KEY `IDX_STATUS` (`status`),
  KEY `IDX_UPDATED_AT` (`updated_at`),
  KEY `FK_RW_GFEED_PROCESS_FEED_ID_RW_GFEED_FEED_ID` (`feed_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$installer->getTable('rocketshoppingfeeds/shipping')};
CREATE TABLE {$installer->getTable('rocketshoppingfeeds/shipping')} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `feed_id` int(10) unsigned NOT NULL COMMENT 'Feed Id',
  `store_id` smallint(5) unsigned NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `value` text NOT NULL,
  `currency_code` varchar(3) NOT NULL COMMENT 'Currency code for cached shipping rate',
  PRIMARY KEY (`id`),
  KEY `IDX_PRODUCT_ID_STORE_ID` (`product_id`,`store_id`),
  KEY `IDX_UPDATED_AT` (`updated_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

    // Add product attribute
    $installer->addAttribute(
        'catalog_product', 'rw_google_base_skip_submi', array(
            'type' => 'int',
            'input' => 'select',
            'backend' => 'catalog/product_attribute_backend_boolean',
            'source' => 'eav/entity_attribute_source_boolean',
            'label' => 'Skip from Being Submitted',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'visible' => true,
            'required' => 0,
            'user_defined' => false,
            'visible_on_front' => false,
            'used_for_price_rules' => false,
            'position' => 10,
            'default' => 0,
            'group' => 'Google Shopping Feed'
        )
    );

    // Install manufacturer attribute if missing
    $id = $installer->getConnection()->fetchOne("SELECT `attribute_id` FROM `{$this->getTable('eav_attribute')}` WHERE `attribute_code` = 'manufacturer'");
    if (!$id) {
        $installer->addAttribute(
            'catalog_product', 'manufacturer', array(
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'Manufacturer',
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                'note' => 'Manufacturer name of the product. By default mapped in google shopping feed to brand column',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'visible_on_front' => false,
                'used_for_price_rules' => false,
                'position' => 80,
                'group' => 'Google Shopping Feed',
                'default' => '',
                'class' => '',
                'source' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'is_configurable' => false
            )
        );
    }
}

$installer->endSetup();