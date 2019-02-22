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
class RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Price extends Varien_Object
{
    const PRICE_START_AT = 0;
    const PRICE_SUM_DEFAULT_QTY = 1;

    public function toOptionArray()
    {
        $vals = array(
            self::PRICE_START_AT => Mage::helper('rocketshoppingfeeds')->__('Minimal price'),
            self::PRICE_SUM_DEFAULT_QTY => Mage::helper('rocketshoppingfeeds')->__('Sum of associated products prices'),
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
            self::PRICE_START_AT => Mage::helper('rocketshoppingfeeds')->__('Minimal price'),
            self::PRICE_SUM_DEFAULT_QTY => Mage::helper('rocketshoppingfeeds')->__('Sum of associated products prices'),
        );
        $options = array();
        foreach ($vals as $k => $v) {
            $options[$k] = $v;
        }

        return $options;
    }

}