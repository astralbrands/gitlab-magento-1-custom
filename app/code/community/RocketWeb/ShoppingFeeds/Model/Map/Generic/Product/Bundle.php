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
class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Bundle
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
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

        if ($product->getWeightType() == Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes_Extend::DYNAMIC
            || $this->getAdapter()->getFeed()->getConfig('bundle_combined_weight'))
        {
            $weight = '';
            $assocProducts = $this->getAdapter()->getAssociated();
            if (is_array($assocProducts)) {
                $weight = 0;
                $bundleType = $this->getAdapter()->getProduct()->getTypeInstance(true);
                $optionsCollection = $bundleType->getOptionsCollection($product);
                foreach ($optionsCollection as $option) {
                    if ($selections = $option->getSelections()) {
                        foreach ($selections as $selection) {
                            $minQty = $selection->getSelectionQty();
                            if ($minQty && array_key_exists($selection->getId(), $assocProducts)) {
                                $weight += $minQty * $assocProducts[$selection->getId()]->getWeight();
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $weight_attribute = $this->getAdapter()->getGenerator()->getAttribute($map['attribute']);
            if ($weight_attribute === false) {
                Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
            }
            $weight = $this->getAdapter()->getAttributeValue($product, $weight_attribute);
        }

        if ($weight != "") {
            if (strpos($weight, $unit) === false) {
                $weight = number_format((float)$weight, 2). ' '. $unit;
            }
        }

        return $this->getAdapter()->cleanField($weight, $params);
    }

    /**
     * Returns true for bundle items, and false for the others.
     *
     * @param array $params
     * @return string
     */
    public function mapDirectiveIsBundle($params = array())
    {
        return 'TRUE';
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
}