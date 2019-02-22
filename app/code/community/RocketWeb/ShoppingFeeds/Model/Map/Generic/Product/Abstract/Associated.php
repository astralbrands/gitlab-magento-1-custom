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
class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Abstract_Associated
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * @param array $params
     * @return mixed|string
     */
    public function mapDirectiveUrl($params = array())
    {
        $args = array('map' => $params['map']);
        $product = $this->getAdapter()->getProduct();
        $parent = Mage::getSingleton('rocketshoppingfeeds/map_generic_product_abstract');

        if (!$this->getAdapter()->hasParentMap()) {
            return $parent->mapDirectiveUrl($params);
        }

        switch ($this->getAdapter()->getFeed()->getConfig('configurable_associated_products_link')) {
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Associated_Link::FROM_PARENT:
                $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->mapColumn($args['map']['column']) : '';
                break;
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Associated_Link::FROM_ASSOCIATED_PARENT:
                if ($product->isVisibleInSiteVisibility()) {
                    return $parent->mapDirectiveUrl($params);
                } else {
                    $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->mapColumn($args['map']['column']) : $parent->mapDirectiveUrl($params);
                }
                break;

            default:
                $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->mapColumn($args['map']['column']) : '';
        }

        // Add unique URLs to associated of bundle and configurable if the config is set.
        if ($this->getAdapter()->getFeed()->getConfig('configurable_associated_products_link_add_unique')) {
            $value = $this->getAdapter()->addUrlUniqueParams($value, $this->getAdapter()->getProduct());
        }

        return $value;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnAvailability($params = array())
    {
        $args = array('map' => $params['map']);

        if ($this->getAdapter()->hasParentMap()) {
            $value = $this->getAdapter()->getParentMap()->mapColumn('availability');
            // Gets out of stock if parent is out of stock, this applies for bundle as well
            if ($this->getAdapter()->getFeed()->getConfig('configurable_inherit_parent_out_of_stock') && strcasecmp(Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus(), $value) == 0) {
                return $value;
            }
        }

        return $this->getAdapter()->getCellValue($args);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapColumnBrand($params = array())
    {
        $args = array('map' => $params['map']);
        $value = $this->getAdapter()->getCellValue($args);

        if (empty($value)) {
            $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->mapColumn('brand') : '';
        }
        $this->getAdapter()->findAndReplace($value, $params['map']['column']);

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
        $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->getCellValue($params): '';
        if (empty($value)) {
            $value = Mage::getSingleton('rocketshoppingfeeds/map_generic_product_abstract')->mapDirectiveVariantAttributes($params);
        }
        return $value;
    }
}
