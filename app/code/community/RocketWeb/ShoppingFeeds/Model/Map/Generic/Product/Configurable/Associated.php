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
class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Configurable_Associated
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * Note: Magento takes the sale price from parent if it's a configurable.
     *
     * @param  array $params
     * @return string
     */
    public function mapDirectiveSalePriceEffectiveDate($params = array())
    {
        return $this->getAdapter()->getParentMap()->getCellValue($params);
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

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getAdapter()->getParentMap()->getProduct();


        // equivalent to default/template/catalog/product/msrp_price.phtml
        if ($this->getAdapter()->getHelper()->hasMsrp($product)){
            $qtyIncrements = $this->getAdapter()->getHelper()->getQuantityIcrements($product);
            $price = $this->getAdapter()->convertPrice($product->getMsrp() * $qtyIncrements);
        }

        return ($price > 0) ? sprintf("%.2F", $price). ' '. $this->getAdapter()->getData('store_currency_code') : '';
    }
}
