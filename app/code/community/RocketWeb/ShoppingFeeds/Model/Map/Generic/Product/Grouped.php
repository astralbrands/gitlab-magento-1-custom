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
class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Grouped
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveSalePriceEffectiveDate($params = array())
    {
        if (!$this->getAdapter()->hasSpecialPrice()) {
            return '';
        }

        $cell = false;

        $displayMode = $this->getAdapter()->getFeed()->getConfig('grouped_price_display_mode');
        if ($this->getAdapter()->hasDefaultQty()
            && $displayMode == RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Price::PRICE_SUM_DEFAULT_QTY
        ) {
            //get min interval from all associated products
            $start = $end = null;
            foreach ($this->getAdapter()->getAssocAdapters() as $assocAdapter) {
                if ($assocAdapter->getProduct()->getQty() > 0) {
                    $dates = $assocAdapter->getSalePriceEffectiveDates();
                    if (!empty($dates)) {
                        if (empty($start) || $start < $dates['start']) {
                            $start = $dates['start'];
                        }
                        if (empty($end) || $end > $dates['end']) {
                            $end = $dates['end'];
                        }
                    }
                }
            }
            $cell = $this->getAdapter()->formatDateInterval(array('start' => $start, 'end' => $end));
        } else {
            $minAssocAdapter = $this->getAdapter()->getMinPriceAssocAdapter();
            if ($minAssocAdapter) {
                $cell = $minAssocAdapter->getCellValue($params);
            }
        }

        return $cell ? $cell : '';
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
            foreach ($this->getAdapter()->getAssociated() as $assocProduct) {
                $qty += $assocProduct->getData('quantity');
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
        $grouped_status = Mage::getSingleton('rocketshoppingfeeds/map_generic_product_abstract')->mapDirectiveAvailability($params);

        if ($this->getAdapter()->hasAssociatedStockStatus()
            && $grouped_status == Mage::helper('rocketshoppingfeeds/map')->getInStockStatus()
            && $this->getAdapter()->getAssociatedStockStatus() == Mage::helper('rocketshoppingfeeds/map')->getInStockStatus()) {
            return Mage::helper('rocketshoppingfeeds/map')->getInStockStatus();
        }

        return $this->getAdapter()->cleanField($grouped_status, $params);
    }

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

        // Grouped products don't have weight. Sum it's children weights
        $weight = 0;
        foreach ($this->getAdapter()->getAssocAdapters() as $childAdapter) {
            $weight += str_replace(' '.$unit, '', $childAdapter->mapColumn($params['map']['column']));
        }

        $weight = number_format((float)$weight, 2). ' '. $unit;
        $weight = $this->getAdapter()->cleanField($weight);

        return $weight;
    }
}
