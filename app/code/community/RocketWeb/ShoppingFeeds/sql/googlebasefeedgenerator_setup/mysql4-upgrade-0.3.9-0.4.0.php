<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
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

$installer->run(
    "

DROP TABLE IF EXISTS {$this->getTable('rocketshoppingfeeds/process')};

CREATE TABLE `{$this->getTable('rocketshoppingfeeds/process')}` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`store_id` smallint(5) unsigned NOT NULL,
	`item_id` int unsigned NOT NULL,
	`parent_item_id` int unsigned NULL,
	`status`  smallint(5) unsigned NOT NULL,
	`updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	PRIMARY KEY  (`id`),
	KEY `IDX_ITEM_ID_STORE_ID` (`item_id`, `store_id`),
	KEY `IDX_PARENT_ITEM_ID` (`parent_item_id`),
	KEY `IDX_STATUS` (`status`),
	KEY `IDX_UPDATED_AT` (`updated_at`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
"
);

$installer->endSetup();
