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
 * @method array getAssociated()
 * @method string getAssociatedStockStatus()
 */
class RocketWeb_ShoppingFeeds_Model_Adapter_Configurable
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
        $associatedDontSkip = array();
        $stockStatusFlag = false;
        $stockStatus = false;

        $message = $this->getGenerator()->checkPriceRangeSkip($this->getProduct());

        if ($message !== false) {
            $this->setSkip($message);
        }

        foreach ($this->getAssocIds() as $assocId) {

            $isSkip = false;
            $assoc = Mage::getModel('catalog/product');
            $assoc->setStoreId($this->getFeed()->getStoreId());
            $assoc->getResource()->load($assoc, $assocId);
            $assoc->setData('quantity', 0);

            if ($this->getGenerator()->getData('verbose')) {
                echo $this->getGenerator()->formatMemory(memory_get_usage(true)) . " - Configurable associated SKU " . $assoc->getSku() . ", ID " . $assoc->getId() . "\n";
            }

            $stock = Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus();

            if ($this->getFeed()->isAllowedConfig('general', 'use_default_stock') && !$this->getFeed()->getConfig('general_use_default_stock')) {
                $stock_attribute = $this->getGenerator()->getAttribute($this->getFeed()->getConfig('general_stock_attribute_code'));
                if ($stock_attribute === false) {
                    Mage::throwException(sprintf('Invalid attribute for Availability column. Please make sure proper attribute is set under the setting "Alternate Stock/Availability Attribute.". Provided attribute code \'%s\' could not be found.', $this->getFeed()->getConfig('general_stock_attribute_code')));
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

            // Skip assoc considering the appropriate stock status
            if (!$this->getFeed()->getConfig('configurable_add_out_of_stock')
                && $stock != Mage::helper('rocketshoppingfeeds/map')->getInStockStatus())
            {
                $isSkip = true;
                $this->log(sprintf("product id %d sku %s, configurable item, skipped - out of stock", $assocId, $assoc->getSku()));
            }

            $message = $this->getGenerator()->checkPriceRangeSkip($assoc, ', configurable item');
            if ($message !== false) {
                $isSkip = true;
                $this->log($message);
            }

            $associatedDontSkip[] = $assoc;

            if (!$isSkip) {
                $associated[$assocId] = $assoc;
            }

            // Set stock status of the current item and check if the status has changed
            if ($stockStatus != false && $stock != $stockStatus) {
                $stockStatusFlag = true;
            }
            $stockStatus = $stock;
        }

        $this->setVariants($associatedDontSkip);

        // Set configurable stock status if all assocs have the same stock status, only for default stocks
        if ($this->getFeed()->getConfig('general_use_default_stock') && $stockStatus && !$stockStatusFlag) {
            $this->setAssociatedStockStatus($stockStatus);
            if ($stockStatus == Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus() && !$this->getFeed()->getConfig('filters_add_out_of_stock')) {
                $this->setSkip(sprintf("product id %d sku %s, configurable, skipped - out of stock.", $this->getProduct()->getId(), $this->getProduct()->getSku()));
            }
        }
        $this->setAssociated($associated);

        return parent::_beforeMap();
    }

    public function map()
    {
        $rows = array();
        $parentRow = null;
        $this->_beforeMap();

        if ($this->getTools()->isAllowConfigurableMode()) {
            if (!$this->isSkip()) {

                // simulate parent::map() without clearing associated_maps from memory, as associated more could be on.
                $row = parent::_map();
                reset($row); $parentRow = current($row);
                $this->_checkEmptyColumns($parentRow);

                // remove parent and skipping flag so that the associated items could still be processed.
                if ($this->isSkip()) {
                    $parentRow = null;
                }
            }
        }

        if ($this->getTools()->isAllowConfigurableAssociatedMode() && !$this->hasSkipAssocs() && $this->hasAssocAdapters()) {

            foreach ($this->getAssocAdapters() as $assocAdapter) {
                $row = $assocAdapter->map();
                reset($row); $row = current($row);
                if (!$assocAdapter->isSkip()) {
                    $rows[] = $row;
                }
            }
        }

        // Fill in parent columns specified in $inherit_columns with values list from associated items
        if (!is_null($parentRow)) {
            $this->mergeVariantValuesToParent($parentRow, $rows);
            array_unshift($rows, $parentRow);
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
        $associated = $this->getAssociated();
        if (is_array($associated)) {
            foreach ($associated as $assoc) {
                if ($assoc->getEntityid()) {
                    $this->getTools()->clearNestedObject($assoc);
                }
            }
        }

        $this->_cache_map_values = array();
        return $rows;
    }

    /**
     * Array with associated products ids in current store.
     *
     * @return array
     */
    public function getAssocIds()
    {
        if (is_null($this->getAssociatedIds())) {
            $this->setAssociatedIds($this->loadAssocIds($this->getProduct(), $this->getFeed()->getStoreId()));
        }
        return $this->getAssociatedIds();
    }

    /**
     * Get price from associated products
     * This gets used when store is using SCP extensions
     * to set up the price only for associated simple products
     * and the Configurable product (special_)price = 0
     *
     * @param bool|true $tax
     * @param bool|false $sale
     * @return int|string
     */
    public function getMinAssociatedPrice($tax = true, $sale = false)
    {
        $assocAdapters = $this->getAssocAdapters();
        if (!empty($assocAdapters)) {
            $minPrice = PHP_INT_MAX;

            foreach ($assocAdapters as $adapter) {
                $adapterPrices = $adapter->getPrices();
                $assocPrice =
                    $tax ? ($sale ? $adapterPrices['sp_incl_tax'] : $adapterPrices['p_incl_tax'])
                         : ($sale ? $adapterPrices['sp_excl_tax'] : $adapterPrices['p_excl_tax']);
                if ($minPrice > $assocPrice && $assocPrice != 0) {
                    $minPrice = $assocPrice;
                }
            }
            if ($minPrice != PHP_INT_MAX) {
                return $minPrice;
            }
        }
        return '';
    }

}
