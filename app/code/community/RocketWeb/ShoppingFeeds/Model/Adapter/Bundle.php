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
 * @method Mage_Catalog_Model_Product getProduct() Current product or null
 * @method RocketWeb_ShoppingFeeds_Model_Feed getFeed() Current feed
 * @method array getCacheMapValues()
 * @method array getColumnsMap()
 * @method RocketWeb_ShoppingFeeds_Model_Map_Option getOptionProcessor()
 * @method array getAssocColumnsInherit()
 */
class RocketWeb_ShoppingFeeds_Model_Adapter_Bundle
    extends RocketWeb_ShoppingFeeds_Model_Adapter_Abstract
{
    protected $_assocBundleOption = array();
    protected $_assoc_ids = array();

    /**
     * Support for configurable items product option not yet implemented
     * @return bool
     */
    protected function _isAllowProductOptions()
    {
        return false;
    }

    /**
     * Get the number of child products in the bundle
     * see _beforeMap()
     * @return int
     */
    public function getChildrenCount()
    {
        $options = $this->getProduct()->getTypeInstance(true)->getOptionsIds($this->getProduct());
        return (count($options));
    }

    /**
     * Iterate through associated products and set mapping objects
     *
     * @return $this
     */
    public function _beforeMap()
    {
        if ($this->isSkip()) {
            return $this;
        }

        $bundleType = $this->getProduct()->getTypeInstance(true);

        $optionIds = $bundleType->getOptionsIds($this->getProduct());
        if ($optionIds) {
            $assocCollection = $bundleType->getSelectionsCollection($optionIds, $this->getProduct());
        }

        $statusOptions = array();
        $associated = array();

        foreach ($assocCollection as $option) {

            $assocId = $option->product_id;

            $assoc = Mage::getModel('catalog/product');
            $assoc->setStoreId($this->getFeed()->getStoreId());
            $assoc->getResource()->load($assoc, $assocId);

            if ($this->getGenerator()->getData('verbose')) {
                echo $this->getGenerator()->formatMemory(memory_get_usage(true)) . " - Bundle associated SKU " . $assoc->getSku() . ", ID " . $assoc->getId() . "\n";
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
            if ($this->getFeed()->isAllowedConfig('filters', 'add_out_of_stock') && $this->getFeed()->getConfig('filters_add_out_of_stock')) {
                $canBeProcessed = true;
            } elseif ($stock == Mage::helper('rocketshoppingfeeds/map')->getInStockStatus()) {
                $canBeProcessed = true;
            } else {
                // Set skip messages
                $this->log(sprintf("product id %d sku %s, configurable item, skipped - out of stock", $assocId, $assoc->getSku()));
            }

            if ($canBeProcessed) {
                $message = $this->getGenerator()->checkPriceRangeSkip($assoc, ', bundle item');
                if ($message !== false) {
                    $this->log($message);
                } else {
                    $associated[] = $assoc;
                    $this->_assoc_ids[] = $assocId;
                }
            }

            // Build array of stocks by option
            if (!array_key_exists($option->getOptionId(), $statusOptions)) {
                $statusOptions[$option->getOptionId()] = array();
            }
            array_push($statusOptions[$option->getOptionId()], $stock);
        }

        $this->setAssociated($associated);

        if ($this->getFeed()->isAllowedConfig('general', 'use_default_stock') && $this->getFeed()->getConfig('general_use_default_stock')) {
            // Force bundle stock status if one of the option is out of stock
            $status = Mage::helper('rocketshoppingfeeds/map')->getInStockStatus();
            foreach ($statusOptions as $statusOption) {
                if (count(array_unique($statusOption)) === 1
                    && end($statusOption) === Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus())
                {
                    $status = Mage::helper('rocketshoppingfeeds/map')->getOutOfStockStatus();
                }
            }
            $this->setAssociatedStockStatus($status);
        }

        return parent::_beforeMap();
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

        $store = $this->getStore();
        /** @var Mage_Weee_Helper_Data $weeeHelper */
        $weeeHelper = Mage::helper('weee');
        $helper = $this->getHelper();
        /** @var Mage_Tax_Helper_Data $taxHelper */
        $taxHelper = $this->getHelper('rocketshoppingfeeds/tax');
        $algorithm = $taxHelper->getConfig()->getAlgorithm($store);
        $isVersion1702OrLess = version_compare(Mage::getVersion(), '1.7.0.2', '<=');

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getProduct();


        if ($this->getHelper()->isModuleEnabled('Aitoc_Aitcbp')) {
            $product = $product->load($product->getid());
        }

        // Compute Weee tax
        $weeExcludingTax = $weeeHelper->getAmountForDisplay($product);
        $weeIncludingTax = $weeExcludingTax;
        if ($weeeHelper->isTaxable()) {
            $weeIncludingTax = $weeeHelper->getAmountInclTaxes($weeeHelper->getProductWeeeAttributesForRenderer($product, null, null, null, true));
        }

        $qtyIncrements = $helper->getQuantityIcrements($product);
        $prices = array();

        $price = $this->calcMinimalPrice();
        $rulesPrice = $this->getPriceByCatalogRules($price);
        $price = min($price, $rulesPrice);
        $prices['p_excl_tax'] = $this->convertPrice($price);

        $price = $this->calcMinimalPrice(true);
        $rulesPrice = $this->getPriceByCatalogRules($price);
        $price = min($price, $rulesPrice);
        $prices['p_incl_tax'] = $this->convertPrice($price);

        $specialPrice = $this->convertPrice($this->getSpecialPrice());
        $prices['sp_excl_tax'] = $specialPrice;

        $specialPrice = $this->convertPrice($this->getSpecialPrice(true));
        $prices['sp_incl_tax'] = $specialPrice;

        /**
         * Problems with Tax, it returns the same price (p_incl_tax = p_excl_tax)
         */

        /*if ($algorithm !== Mage_Tax_Model_Calculation::CALC_UNIT_BASE && $qtyIncrements > 1.0) {
            // We need to multiply base before calculating tax for whole ((itemPrice * qty) + vat = total)
            $prices['p_excl_tax'] *= $qtyIncrements;
            $prices['p_incl_tax'] = $taxHelper->getPrice($product, $prices['p_excl_tax'], true);

            $prices['sp_excl_tax'] *= $qtyIncrements;
            $prices['sp_incl_tax'] = $taxHelper->getPrice($product, $prices['sp_excl_tax'], true);
        } else */
        if ($qtyIncrements > 1.0) {
            // We just need to multiply incl_tax/excl_tax prices
            foreach ($prices as $code => $price) {
                $prices[$code] = $price * $qtyIncrements;
            }
        }

        $this->setData('price_array', $prices);
        return $this->getData('price_array');
    }

    /**
     * When computing the special price, we send the $price parameter from associated items
     * @return mixed
     */
    public function getPriceByCatalogRules($price = null)
    {
        if (is_null($price)) {
            $price = $this->getProduct()->getPrice();
        }
        // todo: if bundle has dynamic price, calculate to the product having this minimal price
        return Mage_Catalog_Model_Product_Type_Price::calculatePrice(
            $price,
            false, false, false, false,
            $this->getStore()->getWebsiteId(),
            Mage_Customer_Model_Group::NOT_LOGGED_IN_ID,
            $this->getProduct()->getId()
        );
    }

    /**
     * @param $product
     * @return float|mixed
     */
    public function calcMinimalPrice($includingTax = false)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getProduct();

        //remove special price from calculation - this is the only way to get the price before discount
        $specialPrice = $product->getSpecialPrice();
        if (!empty($specialPrice)) {
            $product->setSpecialPrice('0');

            //force re-calculation
            $product->setData('min_price', '');
            $product->setData('max_price', '');
            $product->setFinalPrice(null);
        }

        if (version_compare('1.6.0.0', Mage::getVersion(), '>=')
            && version_compare('1.10.1.1', Mage::getVersion(), '!=')
        ) {
            $_prices = $product->getPriceModel()->getTotalPrices($product, 'min', $includingTax);
        } else {
            $_prices = $product->getPriceModel()->getPricesDependingOnTax($product, 'min', $includingTax);
        }
        if (is_array($_prices)) {
            $price = min($_prices);
        } else {
            $price = $_prices;
        }

        //put special price back
        $product->setSpecialPrice($specialPrice);

        return $price;
    }

    /**
     * @param bool|true $process_rules
     * @param null $product
     * @return bool
     */
    public function hasSpecialPrice($process_rules = true, $product = null)
    {
        $price = $this->getPriceByCatalogRules($this->calcMinimalPrice());
        if ($price <= $this->getSpecialPrice()) {
            return false;
        }
        return parent::hasSpecialPrice($process_rules, $product);
    }

    /**
     * @param null $product
     * @return float|int
     */
    public function getSpecialPrice($includingTax = false)
    {
        $price = $this->calcMinimalPrice($includingTax);

        $specialPricePercent = $this->getProduct()->getSpecialPrice();
        if ($specialPricePercent <= 0 || $specialPricePercent > 100) {
            return $price;
        } else {
            $specialPrice = ($specialPricePercent) * $price / 100;
            return $specialPrice;
        }
    }

    /**
     * @return array
     */
    public function map()
    {
        $rows = array();
        $parentRow = null;
        $this->_beforeMap();

        if ($this->getTools()->isAllowBundleMode()) {
            if (!$this->isSkip()) {

                // simulate parent::map() without clearing associated_maps from memory, as associated more could be on.
                $row = parent::_map();
                reset($row); $parentRow = current($row);

                // remove parent and skipping flag so that the associated items could still be processed.
                if ($this->isSkip()) {
                    $parentRow = null;
                }
            }
        }

        if ($this->getTools()->isAllowBundleAssociatedMode() && $this->hasAssocAdapters()) {
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
     * Returns a list of associated IDs with their corresponded option ID & Value on bundle page.
     *
     * @return array
     */
    private function _getAssocBundleOption()
    {
        if (empty($this->_assocBundleOption)) {

            $selectCollection = $this->getProduct()->getTypeInstance(true)->getSelectionsCollection(
                $this->getProduct()->getTypeInstance(true)->getOptionsIds($this->getProduct()), $this->getProduct()
            );
            foreach ($selectCollection as $item) {
                if (!array_key_exists($item->getEntityId(), $this->_assocBundleOption)) {
                    $this->_assocBundleOption[$item->getEntityId()] = array();
                }
                $this->_assocBundleOption[$item->getEntityId()][$item->getOptionId()] = $item->getSelectionId();
            }
        }
        return $this->_assocBundleOption;
    }

    /**
     * Returns list of option & value for a specific assoc.
     * TODO: if an associated is used in many options, this needs to return only the one option value which makes up current assoc product
     * see RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Associated::mapDirectiveUrl()
     *
     * @param null $assocId
     */
    public function getOptionCodes($assocId = null)
    {
        $assocBundleOption = $this->_getAssocBundleOption();

        if (is_null($assocId)) {
            return $assocBundleOption;
        } elseif (array_key_exists($assocId, $assocBundleOption)) {
            return $assocBundleOption[$assocId];
        }

        return array();
    }
}
