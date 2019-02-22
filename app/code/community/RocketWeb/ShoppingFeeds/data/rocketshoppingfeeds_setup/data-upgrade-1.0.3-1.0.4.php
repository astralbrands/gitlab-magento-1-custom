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

$stores = Mage::getModel('core/store')->getCollection();
foreach ($stores as $store) {
    if (Mage::getStoreConfig(RocketWeb_ShoppingFeeds_Block_Product_View_Microdata::XML_PATH_ENABLED, $store) == 1) {
        $feedCol = Mage::getModel('rocketshoppingfeeds/feed')->getCollection()
            ->addStoreFilter($store->getId())
            ->addFieldToSelect(new Zend_Db_Expr("IF(type='google_shopping', 1, 10) as feed_type"))
            ->addFieldToSelect('id')
            ->addFieldToFilter('status', array('neq' => RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_DISABLED));
        $feedCol->getSelect()->order(array('feed_type ASC', 'id DESC')) ;
        $feed = $feedCol->getFirstItem();

        if ($feed->getId()) {
            $installer->getConnection('core_write')->update(
                $installer->getTable('rocketshoppingfeeds/feed'),
                array('use_for_microdata' => 1),
                array('id = ?' => $feed->getId())
            );
        }
    }
}

$installer->endSetup();