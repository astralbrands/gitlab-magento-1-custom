<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


abstract class Amasty_Promo_Model_Rule_Action_ActionAbstract
{
    /**
     * @var string
     */
    protected $_actionName = '';

    /**
     * Product IDs which have special price
     *
     * @var array
     */
    protected $_itemsWithDiscount;

    protected $_bundleProductsInCart = array();
    protected $_isHandled;

    /**
     * Label of Action
     *
     * @return string
     */
    abstract public function getLabel();

    /**
     * Action Code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_actionName;
    }

    /**
     * @param Mage_SalesRule_Model_Rule      $rule
     * @param Mage_Sales_Model_Quote_Item    $item
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_Sales_Model_Quote         $quote
     * @param float                          $qty
     * @param Varien_Object                  $discounts   [discount_amount, base_discount_amount]
     * @param $eventItem
     *
     * @return bool
     */
    public function process($rule, $item, $address, $quote, $qty, $discounts, $eventItem)
    {
        if (isset($this->_isHandled[$rule->getId()])) { //Issue #69 when more the 1 product
            return false;
        }

        $this->_isHandled[$rule->getId()] = true;

        $promoSku = $rule->getPromoSku();
        if (!$promoSku) {
            return false;
        }

        $qty = $this->getFreeItemsQty($rule, $quote, $address);
        if (!$qty) {
            //@todo  - add new field for label table
            // and show message like "Add 2 more products to get free items"
            return false;
        }

        if ($rule->getAmpromoType() == Amasty_Promo_Helper_Data::RULE_TYPE_ONE_SKU) {
            Mage::getSingleton('ampromo/registry')->addPromoItem(
                array_map('trim', preg_split('/\s*,\s*/', $promoSku, -1, PREG_SPLIT_NO_EMPTY)),
                $qty,
                $rule,
                $eventItem
            );
            return true;
        }
        $promoSku = explode(',', $promoSku);
        foreach ($promoSku as $sku) {
            $sku = trim($sku);
            if (!$sku) {
                continue;
            }

            $currentQty = $this->findQtyForItemInQuote($quote, $sku);
            $qty = ($currentQty < 0) ? $qty : min($qty, $currentQty);
            Mage::getSingleton('ampromo/registry')->addPromoItem($sku, $qty, $rule, $eventItem);
        }

        return true;
    }

    protected function validate($quote)
    {
        // Ignore gift products when validating promo rule conditions
        if (Mage::getStoreConfig('ampromo/limitations/skip_promo_item_add')) {
            $giftCount = count($this->getGifts($quote));
            if ($giftCount > 0) {
                return false;
            }

            foreach ($quote->getAllItems() as $item) {
                if (($item->getIsAmpromoGift() && !stristr($item->getSku(), $sku))) {
                    return false;
                }
            }
        }
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param string                 $sku
     *
     * @return int
     */
    public function findQtyForItemInQuote($quote, $sku = '')
    {
        $currentQty = -1;
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            if ($item->getSku() == $sku) {
                $currentQty = $item->getQty();
                break;
            }
        }

        return $currentQty;
    }

    /**
     * @param Mage_SalesRule_Model_Rule $rule
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return int
     */
    public function getFreeItemsQty($rule, $quote, $address)
    {
        $qty = 0;
        $amount = max(1, $rule->getDiscountAmount());
        $step = max(1, $rule->getDiscountStep());
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item
                || $item->getIsPromo()
                || $this->_skip($item, $address)
                || !$rule->getActions()->validate($item)
                || $item->getParentItemId()
                || $item->getProduct()->getParentProductId()
            ) {
                continue;
            }

            $qty = $qty + $item->getQty();
        }

        $qty = floor($qty / $step) * $amount;
        $max = $rule->getDiscountQty();
        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }

    /**
     * determines if we should skip the items with special price or other (in future) conditions
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return bool
     */
    protected function _skip($item, $address)
    {
        $skipSpecialPrice = Mage::getStoreConfig('ampromo/limitations/skip_special_price');
        $skipChildSpecialPrice = Mage::getStoreConfig('ampromo/limitations/skip_special_price_configurable');

        if (!$skipSpecialPrice && !$skipChildSpecialPrice) {
            return false;
        }

        $product = $item->getProduct();
        if ($skipSpecialPrice
            && ($item->getIsAmpromoGift()
                || ($product->getSpecialPrice() && $product->getPrice() != $product->getSpecialPrice()))
        ) {
            return true;
        }

        if ($item->getProductType() == 'bundle') {
            return false;
        }

        $this->collectItemsWithDiscount($item, $address);

        if ($skipChildSpecialPrice && $item->getProductType() == "configurable") {
            foreach ($item->getChildren() as $child) {
                if (in_array($child->getProductId(), $this->_itemsWithDiscount)) {
                    return true;
                }
            }
        }

        if (!in_array($item->getProductId(), $this->_itemsWithDiscount)) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return array|bool|null
     */
    protected function collectItemsWithDiscount($item, $address)
    {
        if ($this->_itemsWithDiscount === null) {
            $this->_itemsWithDiscount = $productIds = array();

            foreach ($this->_getAllItems($address) as $addressItem) {
                $productIds[] = $addressItem->getProductId();
            }

            if (!$productIds) {
                return false;
            }

            $customerGroupId    = $item->getProduct()->getCustomerGroupId();
            $storeId            = $item->getProduct()->getStoreId();
            $websiteId          = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
            $productsCollection = Mage::getModel('catalog/product')->getCollection()
                ->addPriceData($customerGroupId, $websiteId)
                ->addAttributeToFilter('entity_id', array('in' => $productIds))
                ->addAttributeToFilter('price', array('gt' => new Zend_Db_Expr('final_price')));

            foreach ($productsCollection as $product) {
                $this->_itemsWithDiscount[] = $product->getId();
            }
        }

        return $this->_itemsWithDiscount;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return array|Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getAllItems($address)
    {
        $items = $address->getAllNonNominalItems();
        if (!$items) { // CE 1.3 version
            $items = $address->getAllVisibleItems();
        }
        if (!$items) { // cart has virtual products
            $cart = Mage::getSingleton('checkout/cart');
            $items = $cart->getItems();
        }
        return $items;
    }

    /**
     * @param $item
     * @return mixed
     */
    protected function _getParentItem($item)
    {
        if ($item->getParentItemId()
            && $item->getParentItem()->getProductType() == 'bundle'
            && !in_array($item->getParentItem()->getSku(), $this->_bundleProductsInCart)
        ) {
            $item = $item->getParentItem();

            $this->_bundleProductsInCart[] = $item->getSku();
        }

        return $item;
    }

    /**
     * @param $product
     * @return bool
     */
    protected function isConfigurableProcessed($product)
    {
        $productParent = $product->getParentItem();

        if ($productParent && $productParent->getProductType() == 'configurable') {
            return true;
        }

        return false;
    }
}