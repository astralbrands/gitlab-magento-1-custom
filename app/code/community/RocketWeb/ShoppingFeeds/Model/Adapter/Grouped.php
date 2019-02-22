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

/**
 * @method array getAssociated() Get child products of this group
 */
class RocketWeb_ShoppingFeeds_Model_Adapter_Grouped
    extends RocketWeb_ShoppingFeeds_Model_Adapter_Abstract
{
    /**
     * Support for configurable items product option not yet implemented
     * @return bool
     */
    protected function _isAllowProductOptions()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getChildrenCount() {
        return (count($this->getAssocIds()));
    }

    /**
     * Iterate through associated products and set mapping objects
     *
     * @return $this
     */
    public function _beforeMap()
    {
        $associated = $this->getAssociated();
        if (!empty($associated) || $this->isSkip()) {
            return $this;
        }

        $associated = array();
        $assoc_ids = $this->getAssocIds();

        $stockStatus = Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus();

        $skipped = 0;

        foreach ($this->getAssocIds() as $assocId) {

            $assoc = Mage::getModel('catalog/product');
            $assoc->setStoreId($this->getFeed()->getStoreId());
            $assoc->getResource()->load($assoc, $assocId);
            $assoc->setData('quantity', 0);

            if ($this->getGenerator()->getData('verbose')) {
                echo $this->getGenerator()->formatMemory(memory_get_usage(true)) . " - Grouped associated SKU " . $assoc->getSku() . ", ID " . $assoc->getEntityId() . "\n";
            }

            $stock = Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus();

            if ($this->getFeed()->isAllowedConfig('general', 'use_default_stock') && !$this->getFeed()->getConfig('general_use_default_stock')) {
                $stock_attribute = $this->getGenerator()->getAttribute($this->getFeed()->getConfig('general_stock_attribute_code'));
                if ($stock_attribute === false) {
                    Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $this->getFeed()->getConfig('general_stock_attribute_code')));
                }

                $stock = trim(strtolower($this->getAttributeValue($assoc, $stock_attribute)));
                if (array_search($stock, Mage::helper('rocketshoppingfeeds/map')->getAllowedStockStatuses()) === false) {
                    $stock = Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus();
                }
            } else {
                $stockItem = Mage::getModel('cataloginventory/stock_item');
                $stockItem->setStoreId($this->getFeed()->getStoreId());
                $stockItem->getResource()->loadByProductId($stockItem, $assoc->getId());
                $stockItem->setOrigData();

                if ($stockItem->getId() && $stockItem->getIsInStock()) {
                    $assoc->setData('quantity', $stockItem->getQty());
                    $stock = Mage::helper('rocketshoppingfeeds/map')->getInStockStatus();
                }

                // Clear stockItem memory
                unset($stockItem->_data);
                $this->getTools()->clearNestedObject($stockItem);
            }
            $canBeProcessed = false;
            // Append assoc considering the appropriate stock status
            if ($this->getFeed()->isAllowedConfig('grouped', 'add_out_of_stock') && $this->getFeed()->getConfig('grouped_add_out_of_stock')
                || $stock == Mage::helper('rocketshoppingfeeds/map')->getInStockStatus())
            {
                $canBeProcessed = true;
            } else {
                // Set skip messages
                if ($this->getFeed()->getConfig('log_skip')) {
                    $this->log(sprintf("product id %d sku %s, grouped associated, skipped - out of stock", $assocId, $assoc->getSku()));
                }
            }

            if ($canBeProcessed) {
                $message = $this->getGenerator()->checkPriceRangeSkip($assoc, ', group item');
                if ($message !== false) {
                    $skipped++;
                    $this->log($message);
                    $key = array_search($assocId, $assoc_ids);
                    unset($assoc_ids[$key]);
                } else {
                    $associated[$assocId] = $assoc;
                }
                
            }
            // Set stock status of the current item and check if the status has changed
            if ($stock == Mage::helper('rocketshoppingfeeds/map')->getInStockStatus()) {
                $stockStatus = $stock;
            }
        }

        $this->setData('assoc_ids', $assoc_ids);
        $this->setAssociated($associated);

        // Set grouped stock status if all assocs have the same stock status, only for default stocks
        if ($this->getFeed()->getConfig('general_use_default_stock')) {
            $this->setAssociatedStockStatus($stockStatus);
            if ($stockStatus == Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus() && !$this->getFeed()->getConfig('filters_add_out_of_stock')) {
                $this->setSkip(sprintf("product id %d sku %s, grouped, skipped - out of stock.", $this->getProduct()->getId(), $this->getProduct()->getSku()));
            } else if ($skipped == count($this->getAssocIds())){
                $this->setSkip(sprintf("product id %d sku %s, grouped, skipped - out of price range", 
                    $this->getProduct()->getId(), $this->getProduct()->getSku()));                
            }
        }

        return parent::_beforeMap();
    }

    /**
     * Builds the associated maps from assocs array
     *
     * @return $this
     */
    protected function _setAssocAdapters()
    {
        $associated = array();
        $assocAdaptersArr = array();
        foreach ($this->getAssociated() as $assoc) {
            $assocAdapter = Mage::helper('rocketshoppingfeeds/factory')
                ->getChildAdapterModel($assoc, $this, $this->getFeed());
            $assocId = $assoc->getEntityId();
            if (! in_array($assocId, $this->getAssocIds()) ) {
                continue;
            }
            if ($assocAdapter->checkSkipSubmission('grouped')) {
                continue;
            }
            if (!$this->hasSkipDuplicateCheck() && $assocAdapter->isDuplicate()) {
                continue;
            }
            $assocAdaptersArr[$assocId] = $assocAdapter;
            $associated[$assocId] = $assoc;
        }

        $this->setAssocAdapters($assocAdaptersArr);
        $this->setAssociated($associated);

        if (count($assocAdaptersArr) <= 0) {
            $this->setSkip(sprintf("product id %d product sku %s, skipped - All associated products of the grouped product are disabled or out of stock.", $this->getProduct()->getId(), $this->getProduct()->getSku()));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function map()
    {
        $rows = array();
        $this->_beforeMap();

        if ($this->getTools()->isAllowGroupedMode()) {
            if (!$this->isSkip()) {

                // simulate parent::map() without clearing associated_maps from memory, as associated more could be on.
                $row = parent::_map();
                reset($row); $row = current($row);
                $this->_checkEmptyColumns($row);

                if (!$this->isSkip()) {
                    $rows[] = $row;
                }
            }
        }

        if ($this->getTools()->isAllowGroupedAssociatedMode()) {
            foreach ($this->getAssocAdapters() as $assocAdapter) {

                $row = $assocAdapter->map();
                reset($row); $row = current($row);

                if (!$assocAdapter->isSkip()) {
                    $rows[] = $row;
                }
            }
        }

        // if any of the associated not skipped, force add them to the feed
        if (count($rows)) {
            $this->unSkip();
        }

        return $this->_afterMap($rows);
    }

    /**
     * @param $rows
     * @return array
     */
    public function _afterMap($rows)
    {
        // Free some memory
        foreach ($this->getAssociated() as $assoc) {
            $this->getTools()->clearNestedObject($assoc);
        }
        return parent::_afterMap($rows);
    }

    /**
     * Computes prices for given or current product.
     * It returns an array of 4 prices: price and special_price, both including and excluding tax
     *
     * @return mixed
     */
    public function getPrices()
    {
        if ($this->hasData('price_array')) {
            return $this->getData('price_array');
        }

        $prices = array('p_excl_tax' => 0, 'p_incl_tax' => 0, 'sp_excl_tax' => 0, 'sp_incl_tax' => 0);

        if (!$this->hasAssocAdapters()) {
            return $prices;
        }

        switch ($this->getFeed()->getConfig('grouped_price_display_mode')) {

            case RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Price::PRICE_SUM_DEFAULT_QTY :
                foreach ($this->getAssocAdapters() as $assocAdapter) {
                    $qty = $assocAdapter->getProduct()->getQty();
                    $qty = $qty > 0 ? $qty : 1;
                    $p_asoc = $assocAdapter->getPrices();
                    $prices['p_excl_tax']  += $p_asoc['p_excl_tax'] * $qty;
                    $prices['p_incl_tax']  += $p_asoc['p_incl_tax'] * $qty;
                    $prices['sp_excl_tax'] += $p_asoc['sp_excl_tax'] * $qty;
                    $prices['sp_incl_tax'] += $p_asoc['sp_incl_tax'] * $qty;
                }
                break;
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Price::PRICE_START_AT:
                $minAssocAdapter = $this->getMinPriceAssocAdapter();
                if ($minAssocAdapter) {
                    $prices = $minAssocAdapter->getPrices();
                }
                break;
        }

        $this->setData('price_array', $prices);
        return $this->getData('price_array');
    }

    /**
     * @return mixed
     */
    public function getMinPriceAssocAdapter()
    {
        if ($this->hasData('min_price_assoc_adapter')) {
            return $this->getData('min_price_assoc_adapter');
        }

        $minPrice = PHP_INT_MAX;

        if ($this->hasAssocAdapters()) {
            foreach ($this->getAssocAdapters() as $assocAdapter) {
                $prices = $assocAdapter->getPrices();
                $price = $assocAdapter->hasSpecialPrice() ? $prices['sp_excl_tax'] : $prices['p_excl_tax'];
                if ($price > 0 && $minPrice > $price) {
                    $minPrice = $price;
                    $this->setData('min_price_assoc_adapter', $assocAdapter);
                }
            }
        }

        return $this->getData('min_price_assoc_adapter');
    }

    /**
     * @return bool
     */
    public function hasDefaultQty()
    {
        $has = false;
        if ($this->hasAssocAdapters()) {
            foreach ($this->getAssocAdapters() as $assocAdapter) {
                if ($assocAdapter->getProduct()->getQty() > 0) {
                    $has = true;
                    break;
                }
            }
        }
        return $has;
    }

    /**
     * Array with associated products ids in current store.
     *
     * @return array
     */
    public function getAssocIds()
    {
        if (!is_array($this->getData('assoc_ids'))) {
            $this->setData('assoc_ids', $this->loadAssocIds($this->getProduct(), $this->getFeed()->getStoreId()));
        }

        return $this->getData('assoc_ids');
    }

    /**
     * @param bool|true $rules
     * @param null $product
     * @return bool
     */
    public function hasSpecialPrice($rules = true, $product = null)
    {
        $has = false;
        $display_mode = $this->getFeed()->getConfig('grouped_price_display_mode');

        if ($this->hasDefaultQty() && $display_mode == RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Price::PRICE_SUM_DEFAULT_QTY) {
            foreach ($this->getAssocAdapters() as $assocAdapter) {
                $has = $assocAdapter->hasSpecialPrice($rules, $product);
                if ($has && $assocAdapter->getProduct()->getQty() > 0) {
                    break;
                }
            }
        } else { // RocketWeb_ShoppingFeeds_Model_Source_Product_Grouped_Price::PRICE_START_AT
            $minAssocAdapter = $this->getMinPriceAssocAdapter();
            if ($minAssocAdapter) {
                $has = $minAssocAdapter->hasSpecialPrice($rules, $product);
            }
        }

        return $has;
    }
}
