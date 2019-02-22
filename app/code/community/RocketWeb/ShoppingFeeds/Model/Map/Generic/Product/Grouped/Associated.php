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
class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Grouped_Associated
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * @param array $params
     * @return mixed|string
     */
    public function mapColumnLink($params = array())
    {
        $args = array('map' => $params['map']);
        $product = $this->getAdapter()->getProduct();
        $add_unique = $this->getAdapter()->getFeed()->getConfig('grouped_associated_products_link_add_unique');

        switch ($this->getAdapter()->getFeed()->getConfig('grouped_associated_products_link')) {
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Associated_Link::FROM_PARENT:
                $value = $this->getAdapter()->getParentMap()->mapColumn('link');
                if ($add_unique) {
                    $value = $this->getAdapter()->addUrlUniqueParams($value, $product);
                }
                break;
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Associated_Link::FROM_ASSOCIATED_PARENT:
                if ($product->isVisibleInSiteVisibility()) {
                    $value = $this->getAdapter()->getCellValue($args);
                } else {
                    $value = $this->getAdapter()->getParentMap()->mapColumn('link');
                    if ($add_unique) {
                        $value = $this->getAdapter()->addUrlUniqueParams($value, $product);
                    }
                }
                break;

            default:
                $value = $this->getAdapter()->getParentMap()->mapColumn('link');
                if ($add_unique) {
                    $value = $this->getAdapter()->addUrlUniqueParams($value, $product);
                }
        }

        return $value;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveGoogleCategoryByCategory($params = array())
    {
        // try to get value from parent first
        $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->getCellValue($params) : '';

        if (empty($value)) {
            $value = Mage::getSingleton('rocketshoppingfeeds/map_generic_product_abstract')->mapDirectiveGoogleCategoryByCategory($params);
        }
        return $value;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveProductTypeByCategory($params = array())
    {
        // try to get value from parent first
        $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->getCellValue($params) : '';

        if (empty($value)) {
            $value = Mage::getSingleton('rocketshoppingfeeds/map_generic_product_abstract')->mapDirectiveProductTypeByCategory($params);
        }
        return $value;
    }

    /**
     * @param $params
     * @param $attributes_codes
     * @return string
     */
    public function mapDirectiveVariantAttributes($params = array())
    {
        // try to get value from parent first
        $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->getCellValue($params) : '';

        if (empty($value)) {
            $value = Mage::getSingleton('rocketshoppingfeeds/map_generic_product_abstract')->mapDirectiveVariantAttributes($params);
        }

        return $value;
    }
}