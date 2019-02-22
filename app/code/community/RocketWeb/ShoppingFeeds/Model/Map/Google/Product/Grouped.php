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
class RocketWeb_ShoppingFeeds_Model_Map_Google_Product_Grouped
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveIdentifier($params = array())
    {
        $val = '';
        $product = $this->getAdapter()->getProduct();
        $attr_code = $params['map']['param'];

        if (!empty($attr_code) && $product->hasData($attr_code)) {
            $attribute = $this->getAdapter()->getGenerator()->getAttribute($attr_code);
            $val = $this->getAdapter()->cleanField($this->getAdapter()->getAttributeValue($product, $attribute), $params);
        }

        if (empty($val)) {
            $adapter = Mage::helper('rocketshoppingfeeds')->sortAssocsByPrice($this->getAdapter()->getAssocAdapters(), 'min');
            if (!is_null($adapter)) {
                $val = $adapter->mapColumn($params['map']['column']);
            }
        }

        return $val;
    }
}