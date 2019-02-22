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

$installer->run('TRUNCATE TABLE `' . $installer->getTable('rocketshoppingfeeds/shipping') . '`;');

if (!$installer->getConnection()->tableColumnExists($this->getTable('rocketshoppingfeeds/shipping'), 'feed_id')) {
    // TODO should have change the store_id into feed_id
    $installer->run("ALTER TABLE {$installer->getTable('rocketshoppingfeeds/shipping')} ADD COLUMN `feed_id` int(10) unsigned NOT NULL COMMENT 'Feed Id' after `product_id`;");
}

if (!$installer->getConnection()->tableColumnExists($this->getTable('rocketshoppingfeeds/shipping'), 'currency_code')) {
    $installer->run("ALTER TABLE {$installer->getTable('rocketshoppingfeeds/shipping')} ADD COLUMN `currency_code` varchar(3) NOT NULL COMMENT 'Currency code for cached shipping rate' after `value`;");
}

$installer->endSetup();