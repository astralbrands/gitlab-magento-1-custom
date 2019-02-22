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
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_ShoppingFeeds_Model_Source_Category_Mode extends Varien_Object
{
    const PRIORITY_ONLY = 0;
    const PRIORITY_AFTER_LEVEL = 1;

    public function toOptionArray()
    {
        $vals = array(
            self::PRIORITY_ONLY => Mage::helper('rocketshoppingfeeds')->__('Priority through all category depths'),
            self::PRIORITY_AFTER_LEVEL => Mage::helper('rocketshoppingfeeds')->__('Priority on categories of the same depth'),
        );

        $options = array();
        foreach ($vals as $k => $v) {
            $options[] = array('value' => $k, 'label' => $v);
        }
        return $options;
    }

    public function toArray(array $arrAttributes = array())
    {
        $vals = array(
            self::PRIORITY_ONLY => Mage::helper('rocketshoppingfeeds')->__('Priority throuh all category depths'),
            self::PRIORITY_AFTER_LEVEL => Mage::helper('rocketshoppingfeeds')->__('Priority on categories of the same deepth'),
        );

        $options = array();
        foreach ($vals as $k => $v) {
            $options[$k] = $v;
        }

        return $options;
    }
}