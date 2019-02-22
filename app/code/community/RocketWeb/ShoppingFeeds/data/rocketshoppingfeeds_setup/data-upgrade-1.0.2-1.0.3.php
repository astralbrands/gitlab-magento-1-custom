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

/**
 * Change start_at from all schedule tables to UTC. They were added using the store's timezone
 */

date_default_timezone_set('UTC');
$utcTz = new DateTimeZone('UTC');
$configTz = Mage::getStoreConfig('general/locale/timezone', 0);

$schedules = Mage::getResourceModel('rocketshoppingfeeds/feed_schedule_collection');
foreach($schedules as $s) {
    $schedule = Mage::getModel('rocketshoppingfeeds/feed_schedule')->load($s->getId());
    $storeTz = new DateTimeZone($configTz);
    $old = new DateTime(date('Y-m-d '.$schedule->getStartAt().':i:s'), $storeTz);
    $old->setTimezone($utcTz);
    $schedule
        ->setStartAt($old->format('H'))
        ->save();

}

$installer->endSetup();