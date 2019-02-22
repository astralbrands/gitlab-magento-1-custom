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

/** @var $installer RocketWeb_ShoppingFeeds_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;
$installer->startSetup();

// Migrate old widget values (<v1.6.4)
$oldconf = $installer->getConnection()->fetchAll("SELECT * from `{$this->getTable('core_config_data')}` WHERE path = 'rocketweb_googlebasefeedgenerator/columns/product_type_by_category'");

$feeds = $installer->getConnection()->fetchAll("
SELECT f.store_id, fc.* FROM `{$this->getTable('rocketshoppingfeeds/feed_config')}` AS fc
INNER JOIN `{$this->getTable('rocketshoppingfeeds/feed')}` AS f ON (fc.feed_id = f.id)
WHERE path = 'categories_provider_taxonomy_by_category'
");

if (count($feeds)) {
    foreach ($feeds as $feed) {
        $value = json_decode($feed['value']);

        // Find product types from old config
        $types = array();
        foreach ($oldconf as $conf) {
            if ($conf['scope_id'] == $feed['store_id']) {
                $types = json_decode($conf['value']);
            }
        }
        if (empty($types)) {
            foreach ($oldconf as $conf) {
                if ($conf['scope_id'] == 0) {
                    $types = json_decode($conf['value']);
                }
            }
        }

        // Convert to new array key names
        $newValue = array();
        if (!empty($value)) {
            foreach ($value as $k => $v) {
                if (is_object($v) && property_exists($v, 'category')) {
                    $newValue[$k] = new stdClass();
                    $newValue[$k]->id = $v->category;
                    $newValue[$k]->tx = property_exists($v, 'value') ? $v->value : '';
                    $newValue[$k]->ty = '';
                    $newValue[$k]->d = property_exists($v, 'disabled') ? $v->disabled : 0;
                    $newValue[$k]->p = property_exists($v, 'order') ? abs($v->order) : 0;

                    // Fill in old product_types
                    foreach ($types as $type) {
                        if ($type->category == $v->category) {
                            $newValue[$k]->ty = $type->value;
                        }
                    }
                }
            }
        }

        // Update config values
        $model = Mage::getModel('rocketshoppingfeeds/config')->load($feed['id']);
        $model->setData('value', json_encode($newValue));
        $model->save();
    }
}

$installer->endSetup();