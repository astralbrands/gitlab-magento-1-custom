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
class RocketWeb_ShoppingFeeds_Model_Source_Producttypes extends Varien_Object
{

    public function toOptionArray()
    {
        $types = Mage::getModel('catalog/product_type')->getOptionArray();
        foreach ($types as $k => $v) {
            $options[] = array('value' => $k, 'label' => $v);
        }

        return $options;
    }

    public function toArray(array $arrAttributes = array())
    {
        $types = Mage::getModel('catalog/product_type')->getOptionArray();
        $options = array();
        foreach ($types as $k => $v) {
            $options[$k] = $v;
        }

        return $options;
    }

}