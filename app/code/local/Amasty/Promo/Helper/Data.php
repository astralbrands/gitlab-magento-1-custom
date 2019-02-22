<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Helper_Data extends Mage_Core_Helper_Abstract
{
    const RULE_TYPE_ALL_SKU = 0;
    const RULE_TYPE_ONE_SKU = 1;

    const CHECKOUT_MODULE_NAME = 'checkout';
    const AMASTY_CHECKOUT_MODULE_NAME = 'amscheckoutfront';

    protected $_productsCache = null;
    protected $_rules = array();
    protected $actionInRules;

    /**
     * @param $product
     * @param $qtyRequested
     * @return mixed
     */
    public function checkAvailableQty($product, $qtyRequested)
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = $this->_getOrderCreateModel();

        $stockItem = Mage::getModel('cataloginventory/stock_item')
            ->assignProduct($product);

        if (!$stockItem->getManageStock() || !$stockItem->getIsInStock()) {
            return $qtyRequested;
        }

        $qtyAdded = 0;

        /** @var Mage_Eav_Model_Entity_Collection_Abstract $item */
        foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
            if ($item->getProductId() == $product->getId()) {
                $qtyAdded += $item->getQty();
            }
        }

        $qty = $stockItem->getStockQty() - $qtyAdded;

        return min($qty, $qtyRequested);
    }

    /**
     * @return bool
     */
    public function applyToAdminOrders()
    {
        return Mage::app()->getStore()->isAdmin()
            && Mage::getStoreConfig('ampromo/general/apply_to_admin_orders');
    }

    protected function _getOrderCreateModel()
    {
        $ret = false;
        if ($this->applyToAdminOrders()) {
            $ret = Mage::getSingleton('adminhtml/sales_order_create');
        } else {
            $ret = Mage::getModel('checkout/cart');;
        }
        return $ret;
    }

    public function addProduct($product, $super = false, $options = false, $bundleOptions = false, $ruleId = false, $amgiftcardValues = array(), $qty = 1, $downloadableLinks = array(), $requestInfo = array())
    {
        /**
         * @var Mage_Checkout_Model_Cart $cart
         */
        $cart = $this->_getOrderCreateModel();

        $qty = $this->checkAvailableQty($product, $qty);

        if (($qty <= 0) && ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) {
            $this->showMessage(
                $this->__(
                    "We apologize, but your free gift is not available at the moment",
                    $product->getName()
                ), false, true
            );

            return;
        }

        $requestInfo['qty'] = $qty;

        if (isset($requestInfo['options'])) {
            $requestInfo['options'] = array();
        }

        if ($super) {
            $requestInfo['super_attribute'] = $super;
        }

        if ($options) {
            $requestInfo['options'] = $options;
        } else {
            $requestInfo['ampromo_rule_id'] = $ruleId;
        }

        if ($bundleOptions) {
            $requestInfo['bundle_option'] = $bundleOptions;
        }

        /* To compatibility amgiftcard module */
        if ($amgiftcardValues) {
            $requestInfo = array_merge($amgiftcardValues, $requestInfo);
        }

        if (isset($downloadableLinks) && count($downloadableLinks) > 0
            && $product->getTypeId() == 'downloadable'
        ) {
            $requestInfo['links'] = $downloadableLinks;
        }

        $requestInfo['options']['ampromo_rule_id'] = $ruleId;

        try {
            $cart->addProduct(+$product->getId(), $requestInfo);

            $cart->getQuote()->setTotalsCollectedFlag(false);

            $cart->getQuote()->getShippingAddress()->unsetData('cached_items_nonnominal');

            $cart->getQuote()->collectTotals();

            $cart->getQuote()->save();

            Mage::getSingleton('ampromo/registry')->restore($product->getData('sku'));

//            if (!Mage::app()->getRequest()->isXmlHttpRequest()) {
            $this->showMessage(
                $this->__(
                    "Free gift <b>%s</b> was added to your shopping cart",
                    $product->getName()
                ), false, true
            );
//            }
        } catch (Exception $e) {
            $this->showMessage($e->getMessage(), true, true);
        }
    }

    public function getRule($ruleId)
    {
        if (!isset($this->_rules[$ruleId])) {
            $this->_rules[$ruleId] = Mage::getModel('salesrule/rule');
            $this->_rules[$ruleId]->load($ruleId);
        }

        return $this->_rules[$ruleId];
    }

    /**
     * Get ready items for add in a cart
     * @param bool|false $flushCache
     * @return array|null
     */
    public function getNewItems($flushCache = false)
    {
        if ($this->_productsCache === null || $flushCache) {
            $items = Mage::getSingleton('ampromo/registry')->getLimits();

            $groups = $items['_groups'];
            unset($items['_groups']);

            if (!$items && !$groups) {
                return array();
            }

            $cart = Mage::getModel('checkout/cart')->getQuote();
            foreach ($cart->getAllItems() as $item) {
                $productName = $item->getProduct()->getName();
                $productPrice = $item->getProduct()->getPrice();
            }

            $allowedSku = array_keys($items);

            $sku2rules = array();

            foreach ($items as $sku => $item) {
                $sku2rules[$item['sku']] = $item['rule_id'];
            }

            foreach ($groups as $ruleId => $rule) {
                $allowedSku = array_merge($allowedSku, $rule['sku']);

                foreach ($allowedSku as &$sku)
                    $sku = (string)$sku;

                if (is_array($rule['sku'])) {
                    foreach ($rule['sku'] as $sku) {
                        $sku2rules[$sku] = $rule['rule_id'];
                    }
                }
            }

            $addAttributes = array();
            if ($this->isModuleEnabled('Amasty_GiftCard')) {
                $addAttributes = Mage::helper('amgiftcard')->getAmGiftCardOptionsInCart();
            }

            // convert SKUs to string
            $allowedSku = array_map('strval', $allowedSku);

            $products = Mage::getResourceModel('catalog/product_collection')
                ->addFieldToFilter('sku', array('in' => $allowedSku))
                ->addAttributeToSelect(
                    array_merge(
                        array(
                            'name', 'small_image', 'status', 'visibility', 'price',
                            'links_purchased_separately', 'links_exist'
                        ),
                        $addAttributes
                    )
                );

            $strWithPromoSku = "'" . implode("', '", $allowedSku). "'";
            $products->getSelect()->order(new Zend_Db_Expr('FIELD(e.sku, ' . $strWithPromoSku . ')'));

            foreach ($products as $key => $product) {
                $productSku = $product->getSku();
                $ruleId = isset($sku2rules[$productSku]) ? $sku2rules[$productSku] : null;
                $rule = $this->getRule($ruleId);

                if (!in_array($product->getTypeId(), array('simple', 'configurable', 'virtual', 'bundle', 'amgiftcard', 'downloadable', 'giftcard', 'amstcred'))) {
                    $this->showMessage($this->__("We apologize, but products of type <b>%s</b> are not supported", $product->getTypeId()));
                    $products->removeItemByKey($key);
                }

                if (($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) &&
                    (!$product->isSalable()
                        || !$this->checkAvailableQty($product, 1))
                ) {
                    $this->showMessage($this->__("We apologize, but your free gift is not available at the moment"));
                    $products->removeItemByKey($key);
                } else if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    $products->removeItemByKey($key);
                }

                foreach ($product->getProductOptionsCollection() as $option) {
                    $option->setProduct($product);
                    $product->addOption($option);
                }

                if ($rule && $rule->getAmpromoShowOrigPrice()) {
                    $product->setAmpromoShowOrigPrice($rule->getAmpromoShowOrigPrice());
                    $price = $product->getPrice();
                    if ($product->getTypeId() == 'giftcard') {
                        $_amount = Mage::helper("ampromo")->getGiftcardAmounts($product);
                        $price = array_shift($_amount);
                    }

                    $product->setSpecialPrice($this->getDiscountPrice($rule, $price, $product));
                    $product->setFinalPrice($product->getSpecialPrice());
                }

                $product->setData('ampromo_rule', $rule);
            }

            $this->_productsCache = $products;
        }

        return $this->_productsCache;
    }

    /**
     * @param $product
     * @return array
     */
    public function getGiftcardAmounts($product)
    {
        $result = array();
        foreach ($product->getGiftcardAmounts() as $amount) {
            $result[] = Mage::app()->getStore()->roundPrice($amount['website_value']);
        }
        sort($result);

        return $result;
    }

    function getDiscountPrice($rule, $price, $product = null)
    {
        $ampromoDiscountValue = $rule->getAmpromoDiscountValue();
        $discountValue = trim($ampromoDiscountValue);
        $minPrice = $rule->getAmpromoMinPrice();

        if (!empty($discountValue)) {
            if (!$product->getIsProceedDiscount()) {
                $delta = 0;
                preg_match('/[0-9]+(\.[0-9][0-9]?)?/', $discountValue, $matches);
                $operator = $discountValue[0];

                if ('%' == $discountValue[strlen($discountValue) - 1] && $matches[0]) {
                    $delta = $price * $matches[0] / 100;
                    $operator = "-";
                } else {
                    $delta = $matches[0];
                }

                switch ($operator) {
                    case '+':
                        $price = $price + $delta;
                        break;
                    case '-':
                        $price = $price - $delta;
                        break;
                    case '*':
                        $price = $price * $delta;
                        break;
                    case '/':
                        $price = $price / $delta;
                        break;
                    default:
                        $price = $delta;
                        break;
                }

                if ($price < 0) {
                    $price = 0;
                }
                if ($product->getProductType() == 'bundle') {
                    $product->setIsProceedDiscount(true);
                }
            }
        } else {
            $price = 0;
        }

        if (!empty($minPrice) && $price < $minPrice) {
            $price = $minPrice;
        }

        return $price;
    }

    public function showMessage($message, $isError = true, $showEachTime = false)
    {
        if (!Mage::getStoreConfigFlag('ampromo/messages/errors') && $isError
        ) {
            return;
        }

        if (!Mage::getStoreConfigFlag('ampromo/messages/success')
            && !$isError
        ) {
            return;
        }

        // show on cart page only
        $all = Mage::getSingleton('core/session')->getMessages(false)->toString();
        if (false !== strpos($all, $message)) {
            return;
        }

        if ($isError && Mage::app()->getRequest()->getParam('debug') !== null) {
            Mage::getSingleton('core/session')->addError($message);
        } else {
            $arr = Mage::getSingleton('core/session')->getAmpromoMessages();

            if (!is_array($arr)) {
                $arr = array();
            }

            if (!in_array($message, $arr) || $showEachTime) {

                if (!Mage::app()->getLayout()->getAllBlocks()
                    && Mage::app()->getRequest()->getModuleName() !== self::CHECKOUT_MODULE_NAME
                ) {
                    Mage::getSingleton('core/session')->addSuccess($message);
                } else {
                    if (Mage::app()->getRequest()->getModuleName() == self::AMASTY_CHECKOUT_MODULE_NAME) {
                        Mage::getSingleton('core/session')->addSuccess($message);
                    } else {
                        Mage::getSingleton('checkout/session')->addSuccess($message);
                    }
                }

                $arr[] = $message;
                Mage::getSingleton('core/session')->setAmpromoMessages($arr);
            }
        }
    }

    public function processPattern($pattern)
    {
        $result = preg_replace_callback(
            '#{url\s+(?P<url>[\w/]+?)}#',
            array($this, 'replaceUrl'),
            $pattern
        );

        return $result;
    }

    public function replaceUrl($matches)
    {
        return $this->_getUrl($matches['url']);
    }

    public function updateNotificationCookie($value = null)
    {
        if ($value === null) {
            $newItems = $this->getNewItems();
            if (!is_array($newItems)) {
                $newItems = $newItems->getData();
            }
            $value = empty($newItems) ? 0 : 1;
        }

        Mage::getModel('core/cookie')->set(
            'am_promo_notification',
            $value,
            null, null, null, null, false
        );
    }

    public function getUrlParams()
    {
        if (Mage::app()->getRequest()->isXmlHttpRequest()) {
            $returnUrl = Mage::app()->getRequest()->getServer('HTTP_REFERER');
        } else {
            $returnUrl = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
        }

        $params = array(
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core')->urlEncode($returnUrl)
        );

        $params['_secure'] = Mage::app()->getStore()->isCurrentlySecure();

        return $params;
    }

    /**
     * @return array
     */
    public function getIsNotAllowedAssignedAttributes()
    {
        return array('stock_item_qty', 'weight');
    }

    /**
     * @param $address
     * @param $rule
     * @return array|bool
     */
    public function getTriggeredItems($address, $rule)
    {
        $arrX = array();
        $allItems = $this->getAllItems($address);

        if (!$allItems) {
            return false;
        }

        foreach ($allItems as $item) {

            if ($item->getIsAmpromoGift() && Mage::getStoreConfig('ampromo/limitations/skip_promo_item_add')) {
                continue;
            }

            if (!$item->getId()) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            if (!$this->_validate($rule->getConditions(), $item, $address, true)) {
                continue;
            }

            $arrX[$item->getId()] = $item;
        }

        return $arrX;
    }

    /**
     * @param $cond
     * @param Varien_Object $object
     * @param $address
     * @param bool $triggered
     * @return bool
     */
    protected function _validate($cond, Varien_Object $object, $address, $triggered = false) {

        if (!$cond->getConditions()) {
            return true;
        }

        if (!$cond->getAggregator()) {
            return true;
        }

        $all    = $cond->getAggregator() === 'all';
        $true   = (bool)$cond->getValue();

        foreach ($cond->getConditions() as $cond) {

            /**
             * check if Validator Found, than validate by whole cart
             */
            $validated = $cond->validate(
                (
                in_array('Found', explode('_', get_class($cond)))? $address : $object
                ),
                $triggered
            );

            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }
        return $all ? true : false;
    }

    /**
     * @param $address
     * @return mixed
     */
    public function getAllItems($address)
    {
        $items = $address->getQuote()->getAllItems();

        if (!$items) {
            $items = $address->getAllNonNominalItems();
            if (!$items) { // CE 1.3 version
                $items = $address->getAllVisibleItems();
            }
            if (!$items) { // cart has virtual products
                $cart = Mage::getSingleton('checkout/cart');
                $items = $cart->getItems();
            }
        }

        return $items;
    }
}
