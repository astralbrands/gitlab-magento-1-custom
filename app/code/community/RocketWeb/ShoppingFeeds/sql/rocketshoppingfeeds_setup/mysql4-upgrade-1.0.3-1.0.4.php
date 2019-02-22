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

if (!$installer->getConnection()->tableColumnExists($this->getTable('rocketshoppingfeeds/feed'), 'use_for_microdata')) {
    $installer->run("ALTER TABLE {$installer->getTable('rocketshoppingfeeds/feed')} ADD COLUMN `use_for_microdata` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Use feed settings for microdata';");
}

$installer->endSetup();