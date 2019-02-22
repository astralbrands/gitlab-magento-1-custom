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
class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Configurable
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * Get value from lowest priced associated item when missing
     *
     * @param array $params
     * @return string
     */
    public function mapDirectiveShippingWeight($params = array())
    {
        $map = $params['map'];
        $map['attribute'] = 'weight';
        $unit = $map['param'];

        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();

        // Get attribute value
        $weightAttribute = $this->getAdapter()->getGenerator()->getAttribute($map['attribute']);
        if ($weightAttribute === false) {
            Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
        }

        $weight = $this->getAdapter()->getAttributeValue($product, $weightAttribute);

        // Configurable doesn't have weight of it's own. Get the weight of the most expensive associate
        if (empty($weight)) {
            $adapter = Mage::helper('rocketshoppingfeeds')->sortAssocsByPrice($this->getAdapter()->getAssocAdapters(), 'min');
            if (!is_null($adapter)) {
                $weight = $adapter->mapColumn($params['map']['column']);
            }
        } else {
            if (strpos($weight, $unit) === false) {
                $weight = number_format((float)$weight, 2). ' '. $unit;
                $weight = $this->getAdapter()->cleanField($weight);
            }
        }

        return $weight;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveQuantity($params = array())
    {
        $cell = $this->getAdapter()->getInventoryCount();

        // If Qty not set at parent item, summarize it from associated items
        if ($params['map']['param'] == RocketWeb_ShoppingFeeds_Model_Source_Directive_Product_Quantity::ITEM_SUM_DEFAULT_QTY) {
            $qty = 0;
            foreach ($this->getAdapter()->getAssociated() as $assoc) {
                $qty += $assoc->getData('quantity');
            }
            $cell = $qty ? $qty : $cell;
        }

        $cell = sprintf('%d', $cell);
        $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveAvailability($params = array())
    {
        // Set the computed configurable stock status
        if ($this->getAdapter()->hasAssociatedStockStatus() && $this->getAdapter()->getAssociatedStockStatus() == Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus()) {
            return $this->getAdapter()->cleanField($this->getAdapter()->getAssociatedStockStatus(), $params);
        }

        return Mage::getSingleton('rocketshoppingfeeds/map_generic_product_abstract')->mapDirectiveAvailability($params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectivePrice($params = array())
    {
        $prices = $this->getAdapter()->getPrices();
        $includingTax = array_key_exists('param', $params['map']) ? (boolean)$params['map']['param'] : true;
        $price = $includingTax ? $prices['p_incl_tax'] : $prices['p_excl_tax'];

        /**
         * Special case when Configurable product price = 0
         * Usually this means there is extension using SCP price
         */
        if (!$price || $price == 0) {
            $price = $this->getAdapter()->getMinAssociatedPrice($includingTax, false);
        }

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getAdapter()->getProduct();

        // equivalent to default/template/catalog/product/msrp_price.phtml
        if ($this->getAdapter()->getHelper()->hasMsrp($product)) {
            $qtyIncrements = $this->getAdapter()->getHelper()->getQuantityIcrements($product);
            $price = $this->getAdapter()->convertPrice($product->getMsrp() * $qtyIncrements);
        }

        return ($price > 0) ? sprintf("%.2F", $price) . ' ' . $this->getAdapter()->getData('store_currency_code') : '';
    }

    /**
     * @param  array $params
     * @return string
     */
    public function mapDirectiveSalePrice($params = array())
    {
        if (!$this->getAdapter()->hasSpecialPrice()) {
            return '';
        }

        $prices = $this->getAdapter()->getPrices();
        $includingTax = array_key_exists('param', $params['map']) ? (boolean)$params['map']['param'] : true;
        $price = $includingTax ? $prices['sp_incl_tax'] : $prices['sp_excl_tax'];

        if (!$price || $price == 0) {
            $price = $this->getAdapter()->getMinAssociatedPrice($includingTax, true);
        }

        return ($price > 0) ? sprintf("%.2F", $price) . ' ' . $this->getAdapter()->getData('store_currency_code') : '';
    }
}
