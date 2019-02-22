<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Model_Observer
{
    const EQUALS = 0;
    const CONTAINS = 1;

    const CONFIG = 0;
    const AUTO = 1;
    const NOTAUTO = 2;


    protected $_isHandled = array();
    protected $_toAdd = array();

    protected $_itemsWithDiscount = array();
    protected $_calcHelper;

    protected $_rules = array();

    protected $_onCollectTotalAfterBusy = false;
    protected $_bundleProductsInCart = array();

    protected $_selfExecuted = false;

    /*
    * array with all actions that contains field "Promo Items"
    * */
    protected $_arrayWithSimpleAction = array(
        'buy_x_get_y_percent',
        'buy_x_get_y_fixdisc',
        'buy_x_get_y_fixed',
        'buy_x_get_n_percent',
        'buy_x_get_n_fixdisc',
        'buy_x_get_n_fixed',
        'setof_percent',
        'setof_fixed',
        'ampromo_items',
        'ampromo_cart',
        'ampromo_spent',
        'ampromo_product',
        'ampromo_eachn'
    );
    /*
    * array with all actions for "Product Set"
    * */
    protected $_arrayWithProductSet = array('setof_percent', 'setof_fixed');

    /**
     * Process sales rule form creation
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function handleFormCreation($observer)
    {
        $actionsSelect = $observer->getForm()->getElement('simple_action');
        if ($actionsSelect) {
            $actionsOptions = $actionsSelect->getValues();

            $actionsOptions[] = array(
                'value' => Mage::getSingleton('ampromo/rule_actionProvider')->getOptionsArray(),
                'label' => Mage::helper('ampromo')->__('Automatically add products to cart'),
            );

            $actionsSelect->setValues($actionsOptions);
            $actionsSelect->setOnchange('ampromo_hide_all();');

            $fldSet = $observer->getForm()->getElement('action_fieldset');

            $fldSet->addField(
                'ampromo_type',
                'select',
                array(
                    'name'   => 'ampromo_type',
                    'label'  => Mage::helper('ampromo')->__('Type'),
                    'values' => array(
                        0 => Mage::helper('ampromo')->__('All SKUs below'),
                        1 => Mage::helper('ampromo')->__('One of the SKUs below')
                    ),
                ),
                'discount_amount'
            );

            $fldSet->addField(
                'promo_sku',
                'text',
                array(
                    'name'  => 'promo_sku',
                    'label' => Mage::helper('ampromo')->__('Promo Items'),
                    'note'  => Mage::helper('ampromo')->__('Comma separated list of the SKUs'),
                ),
                'ampromo_type'
            );

            $fldSet->addField(
                'ampromo_auto_add_simple',
                'select',
                array(
                    'name' => 'ampromo_auto_add_simple',
                    'label' => Mage::helper('ampromo')
                        ->__('Auto add simple products automatically, without confirmation.'),
                    'values' => array(
                        self::CONFIG  => Mage::helper('ampromo')->__('As default'),
                        self::AUTO    => Mage::helper('ampromo')->__('Yes'),
                        self::NOTAUTO => Mage::helper('ampromo')->__('No'),
                    ),
                )
            );
        }

        return $this;
    }

    /**
     * Process quote item validation and discount calculation
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function handleValidation($observer)
    {
        $rule = $observer->getEvent()->getRule();
        $address = $observer->getEvent()->getAddress();
        $action = Mage::getSingleton('ampromo/actionRepository')->getActionBySimpleAction($rule->getSimpleAction());
        $eventItem = Mage::helper('ampromo')->getTriggeredItems($address, $rule);

        if (!$action) {
            return $this;
        }

        if ($action) {
            return $action->process(
                $rule,
                $observer->getEvent()->getItem(),
                $address,
                $observer->getEvent()->getQuote(),
                $observer->getEvent()->getQty(),
                $observer->getEvent()->getResult(),
                $eventItem
            );
        }
    }

    public function onCollectTotalsBefore($observer)
    {
        Mage::getSingleton('ampromo/registry')->reset();
    }

    /**
     * Revert 'deleted' status and auto add all simple products without required options
     * @param $observer
     * @return $this
     */
    public function onAddressCollectTotalsAfter($observer)
    {
        if ($this->_selfExecuted) {
            return true;
        }
            
        $quote = $observer->getQuoteAddress()->getQuote();

        $items = $quote->getAllItems();

        $realCount = 0;
        foreach ($items as $item) {
            if ($item->getIsPromo()) {
                $item->isDeleted(false);
                $this->resetWeee($item);
            } else {
                $realCount++;
            }
        }

        if ($realCount == 0) {
            $this->_selfExecuted = true;

            foreach ($items as $item) {
                $itemId = $item->getItemId();
                $quote->removeItem($itemId)->save();
            }
        }

        if (Mage::getStoreConfigFlag('ampromo/general/auto_add')) {
            $toAdd = Mage::getSingleton('ampromo/registry')->getPromoItems();

            if (is_array($toAdd)) {
                unset($toAdd['_groups']);

                foreach ($items as $item) {
                    $sku = $item->getProduct()->getData('sku');

                    if (!isset($toAdd[$sku])) {
                        continue;
                    }

                    //$qtyIncreased = isset($toAdd[$sku]['qtyIncreased']) ? $toAdd[$sku]['qtyIncreased'] : false;
                    /* weak code - for avoid issue with added to cart automatically */
                    $qtyIncreased = true;

                    if ($item->getIsPromo()) {
                        if (!$qtyIncreased) {
                            unset($toAdd[$sku]); // to allow to decrease qty
                        } else {
                            $toAdd[$sku]['qty'] -= $item->getQty();
                        }
                    }
                }

                $deleted = array();
                if ($observer->getQuoteAddress()->getAddressType() === 'shipping') {
                    $rulesIds = explode(',', $quote->getAppliedRuleIds());
                    $deleted = Mage::getSingleton('ampromo/registry')->getDeletedItems($rulesIds);
                }

                $this->_toAdd = array();

                foreach ($toAdd as $sku => $item) {
                    if ($item['qty'] > 0 && $item['auto_add'] && !isset($deleted[$sku])) {
                        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

                        if (isset($this->_toAdd[$product->getId()])) {
                            $this->_toAdd[$product->getId()]['qty'] += $item['qty'];
                        } else {
                            $this->_toAdd[$product->getId()] = array(
                                'product' => $product,
                                'qty' => $item['qty']
                            );
                        }
                    }
                }
            }
        }
    }

    public function resetWeee(&$item)
    {
        Mage::helper('weee')->setApplied($item, array());

        $item->setBaseWeeeTaxDisposition(0);
        $item->setWeeeTaxDisposition(0);

        $item->setBaseWeeeTaxRowDisposition(0);
        $item->setWeeeTaxRowDisposition(0);

        $item->setBaseWeeeTaxAppliedAmount(0);
        $item->setBaseWeeeTaxAppliedRowAmount(0);

        $item->setWeeeTaxAppliedAmount(0);
        $item->setWeeeTaxAppliedRowAmount(0);
    }

    /**
     * Mark item as deleted to prevent it's auto-addition
     * @param $observer
     */
    public function onQuoteRemoveItem($observer)
    {
        $id = (int)Mage::app()->getRequest()->getParam('id');
        $item = $observer->getEvent()->getQuoteItem();
        /** @var Amasty_Promo_Model_Registry $registry */
        $registry = Mage::getSingleton('ampromo/registry');
        if (($item->getId() == $id) && $item->getIsPromo() && !$item->getParentId()) {
            $sku = $item->getProduct()->getData('sku');
            $registry->deleteProduct($sku);
        } else {
            $rulesIds = explode(',', $item->getQuote()->getAppliedRuleIds());
            $registry->checkDeletedItems($rulesIds);
        }
    }

    public function decrementUsageAfterPlace($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            return $this;
        }

        // lookup rule ids
        $ruleIds = explode(',', $order->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);
        if ($ruleIds && Mage::getSingleton('admin/session')->isLoggedIn()) {
            $this->_setItemPrefix($order->getAllItems());
        }

        $ruleCustomer = null;
        $customerId = $order->getCustomerId();

        // use each rule (and apply to customer, if applicable)
        if (($order->getDiscountAmount() == 0) && (count($ruleIds) >= 1)) {
            foreach ($ruleIds as $ruleId) {
                if (!$ruleId) {
                    continue;
                }
                $rule = Mage::getModel('salesrule/rule');
                $rule->load($ruleId);
                if ($rule->getId()) {
                    $rule->setTimesUsed($rule->getTimesUsed() + 1);
                    $rule->save();

                    if ($customerId) {
                        $ruleCustomer = Mage::getModel('salesrule/rule_customer');
                        $ruleCustomer->loadByCustomerRule($customerId, $ruleId);

                        if ($ruleCustomer->getId()) {
                            $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() + 1);
                        } else {
                            $ruleCustomer
                                ->setCustomerId($customerId)
                                ->setRuleId($ruleId)
                                ->setTimesUsed(1);
                        }
                        $ruleCustomer->save();
                    }
                }
            }
            $coupon = Mage::getModel('salesrule/coupon');
            /** @var Mage_SalesRule_Model_Coupon */
            $coupon->load($order->getCouponCode(), 'code');
            if ($coupon->getId()) {
                $coupon->setTimesUsed($coupon->getTimesUsed() + 1);
                $coupon->save();
                if ($customerId) {
                    $couponUsage = Mage::getResourceModel('salesrule/coupon_usage');
                    $couponUsage->updateCustomerCouponTimesUsed($customerId, $coupon->getId());
                }
            }
        }
    }

    /**
     * Don't apply any discounts to free items
     * @param $observer
     */
    public function onProductAddAfter($observer)
    {
        $items = $observer->getItems();

        $this->_setItemPrefix($items);

        foreach ($items as $item) {
            if ($item->getIsPromo())
                $item->setNoDiscount(true);
        }
    }

    public function onAdminhtmlSalesOrderCreateProcessDataBefore($observer)
    {
        Mage::getSingleton('ampromo/registry')->backup();
    }

    public function onCheckoutCartUpdateItemsBefore($observer)
    {
        if (is_array($observer->getInfo())) {

            $items = $observer->getCart()->getQuote()->getAllVisibleItems();

            foreach ($observer->getInfo() as $itemId => $info) {
                if ($info['qty'] == 0) {
                    foreach ($items as $item) {
                        if ($item->getId() == $itemId) {
                            Mage::getSingleton('ampromo/registry')->deleteProduct($item->getSku());
                            break;
                        }
                    }
                }
            }
        }

        Mage::getSingleton('ampromo/registry')->backup();
    }

    /**
     * Remove all not allowed items
     * @param $observer
     */
    public function onCollectTotalsAfter($observer)
    {
        if (!$this->_onCollectTotalAfterBusy) {
            $this->_onCollectTotalAfterBusy = true;
            $quote = $observer->getQuote();
            if (!$quote || !$quote->getId())
                return;

            if ($quote->getIsFake()) {
                return;
            }

            Mage::helper('ampromo')->updateNotificationCookie();

            $allowedItems = Mage::getSingleton('ampromo/registry')->getPromoItems();
            $cart = Mage::getSingleton('checkout/cart');


            $customMessage = Mage::getStoreConfig('ampromo/general/message');

            foreach ($this->_toAdd as $item) {
                $product = $item['product'];
                //$productSku = strtolower($product->getSku());
                $productSku = $product->getSku();

                $ruleId = $allowedItems[$productSku] ? $allowedItems[$productSku]['rule_id'] : null;

                Mage::helper('ampromo')->addProduct(
                    $product,
                    false, false, false, $ruleId, array(),
                    $item['qty']
                );
            }

            $this->_toAdd = array();

            foreach ($quote->getAllItems() as $item) {
                if ($item->getIsPromo()) {
                    $ruleLabel = $item->getRule()->getStoreLabel();
                    $ruleMessage = !empty($ruleLabel) ? $ruleLabel : $customMessage;

                    if ($item->getParentItemId())
                        continue;

                    $sku = $item->getProduct()->getData('sku');
                    //$sku = strtolower($sku);

                    if (isset($allowedItems['_groups'][$item->getRuleId()])) // Add one of
                    {
                        if ($allowedItems['_groups'][$item->getRuleId()]['qty'] <= 0) {
                            $cart->removeItem($item->getId());
                        } else if ($item->getQty() > $allowedItems['_groups'][$item->getRuleId()]['qty']) {
                            $item->setQty($allowedItems['_groups'][$item->getRuleId()]['qty']);
                        }
                        if ($ruleMessage) {
                            $item->setMessage($ruleMessage);
                        }

                        $allowedItems['_groups'][$item->getRuleId()]['qty'] -= $item->getQty();
                    } else if (isset($allowedItems[$sku]) &&
                        $allowedItems[$sku]['rule_id'] == $item->getRuleId()
                    ) // Add all of
                    {
                        if ($allowedItems[$sku]['qty'] <= 0) {
                            $cart->removeItem($item->getId());
                        } else if ($item->getQty() > $allowedItems[$sku]['qty']) {
                            $item->setQty($allowedItems[$sku]['qty']);
                        }
                        if ($ruleMessage) {
                            $item->setMessage($ruleMessage);
                        }

                        $allowedItems[$sku]['qty'] -= $item->getQty();
                    } else {
                        $cart->removeItem($item->getId());
                    }
                }
            }

            $this->updateQuoteTotalQty($quote);
            $this->_onCollectTotalAfterBusy = false;
        }
    }

    public function updateQuoteTotalQty(Mage_Sales_Model_Quote $quote)
    {
        $quote->setItemsCount(0);
        $quote->setItemsQty(0);
        $quote->setVirtualItemsQty(0);

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $children = $item->getChildren();
            if ($children && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $child->getQty() * $item->getQty());
                    }
                }
            }

            if ($item->getProduct()->getIsVirtual()) {
                $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $item->getQty());
            }
            $quote->setItemsCount($quote->getItemsCount() + 1);
            $quote->setItemsQty((float)$quote->getItemsQty() + $item->getQty());
        }
    }

    public function onOrderPlaceBefore($observer)
    {
        $order = $observer->getOrder();

        $this->_setItemPrefix($order->getAllItems());
    }

    protected function _loadRule($id)
    {
        if (!isset($this->_rules[$id])) {
            $this->_rules[$id] = Mage::getModel('salesrule/rule')->load($id);
        }
        return $this->_rules[$id];
    }

	/**
	 * @param $items
	 */
    protected function _setItemPrefix($items)
    {
        $prefix = Mage::getStoreConfig('ampromo/general/prefix');
        foreach ($items as $item) {
            $buyRequest = $item->getBuyRequest();
	        $labelName  = $item->getLabelName();
	        if (isset($buyRequest['options']['ampromo_rule_id'])) {
                $rule = $this->_loadRule($buyRequest['options']['ampromo_rule_id']);
                $this->_generateNameWithLabel($rule, $item, $prefix);
            } elseif (isset($buyRequest['ampromo_rule_id'])
                      && !isset($labelName)
            ) {
	            $rule = $this->_loadRule($buyRequest['ampromo_rule_id']);
	            $this->_generateNameWithLabel($rule, $item, $prefix);
	            $item->setLabelName(1);
            }
        }
    }

    /**
     * @param $rule
     * @param $item
     * @param $prefix
     */
    protected function _generateNameWithLabel($rule, $item, $prefix)
    {
        $ruleLabel = $rule->getAmpromoPrefix();
        $rulePrefix = !empty($ruleLabel) ? $ruleLabel : $prefix;
        $item->setName($rulePrefix . ' ' . $item->getName());
    }

    public function onCartItemUpdateBefore($observer)
    {
        $request = Mage::app()->getRequest();

        $id = (int)$request->getParam('id');
        $item = Mage::getSingleton('checkout/cart')->getQuote()->getItemById($id);

        if ($item->getId() && $item->getIsPromo()) {
            $options = $request->getParam('options');
            $options['ampromo_rule_id'] = $item->getRuleId();
            $request->setParam('options', $options);
        }
    }

    public function onCheckoutSubmitAllAfter($observer)
    {
        Mage::getSingleton('ampromo/registry')->reset();
        Mage::helper('ampromo')->updateNotificationCookie(0);
    }

    public function salesRulePrepareSave($observer)
    {
        $this->_savePromoRuleImage($observer->getRequest(), 'ampromo_top_banner_img');
        $this->_savePromoRuleImage($observer->getRequest(), 'ampromo_after_name_banner_img');
        $this->_savePromoRuleImage($observer->getRequest(), 'ampromo_label_img');
    }

    public function saveBefore($observer)
    {
        $controllerAction = $observer->getRule()->getData();
        if ($controllerAction['simple_action'] == 'ampromo_cart') {
            $data = $observer->getRule()->getData();
            $r = array(
                'type' => 'salesrule/rule_condition_product_combine',
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'any',
                'conditions' =>
                    array(),
            );
            $data['actions_serialized'] = serialize($r);
            $observer->getRule()->setData($data);
        }
    }

    public function saveAfter($observer)
    {
        $rule = $observer->getRule();
        $conditions = $rule->getConditions()->asArray();
        if (!Mage::helper('core')->isModuleEnabled('Amasty_Rules')) {

            $unsafeIs = 0;
            if (isset($conditions['conditions'])) {
                $unsafeIs = $this->checkForIs($conditions['conditions']);
            }

            $actions = $rule->getActions()->asArray();
            if (isset($actions['conditions']) && !$unsafeIs) {
                $unsafeIs = $this->checkForIs($actions['conditions']);
            }

            if ($unsafeIs) {
                Mage::getSingleton('adminhtml/session')->addNotice('It is more safe to use `is one of` operator and not `is` for comparison.  Please correct if the rule does not work as expected.');
            }
        }

        if (Mage::getStoreConfig('ampromo/messages/show_stock_warning')) {
            $skuArray          = array();
            $checkActionForSku = array(array(), array());
            $promoSku          = $rule->getPromoSku();
            $trimPromoSku        = trim($promoSku);
            if (!empty($trimPromoSku)
                && in_array($rule->getSimpleAction(), $this->_arrayWithSimpleAction)
            ) {
                $skuArray = $this->_checkActionForPromoSku($rule);
            }

            $actions[] = $conditions;
            $checkActionForSku = $this->_checkActionForSku($actions);

            if ($skuArray) {
                $checkActionForSku[self::EQUALS] = array_merge($checkActionForSku[self::EQUALS], $skuArray);
            }
            if (!empty($checkActionForSku[self::EQUALS])
                || !empty($checkActionForSku[self::CONTAINS])
            ) {
                $skuArray = $this->_checkForSku($checkActionForSku);
            }
            $getParamBack = Mage::app()->getRequest()->getParam('back');

            if ($skuArray && $rule->getIsActive() && $getParamBack) {
                $this->_generateMessage($skuArray);
            }
        }
    }


    protected function _savePromoRuleImage($request, $file)
    {
        if ($data = $request->getPost()) {

            if (isset($data[$file]) && isset($data[$file]['delete'])) {
                $data[$file] = null;
            } else {

                if (isset($_FILES[$file]['name']) && $_FILES[$file]['name'] != '') {

                    $fileName = Mage::helper("ampromo/image")->upload($file);

                    $data[$file] = $fileName;
                } else {
                    if (isset($data[$file]['value']))
                        $data[$file] = basename($data[$file]['value']);
                }
            }

            $request->setPost($data);
        }
    }

    protected function checkForIs($array)
    {
        foreach ($array as $element) {
            if ($element['operator'] == '==' && strpos($element['value'], ',') !== FALSE) {
                return true;
            }
            if (isset($element['conditions'])) {
                $this->checkForIs($element['conditions']);
            }
        }
        return false;
    }

    /**
     * check rules "Buy X Get Y", "Buy X Get N Of Y"
     *
     * @param $rule
     *
     * @return array
     */
    protected function _checkActionForPromoSku($rule)
    {
        $strWithSku    = $rule->getPromoSku();
        $actions       = $rule->getActions()->getData('actions');
        $outOfStockSku = array();
        foreach ($actions as $action) {
            if ($action['attribute'] == "quote_item_sku") {
                $strWithItemsSku = $action['value'];
                $outOfStockSku   = $this->_convertAndFormat($strWithItemsSku);
            }
        }

        if ($strWithSku != "") {
            $arrayWithSku  = $this->_convertAndFormat($strWithSku);
            $outOfStockSku = array_merge($outOfStockSku, $arrayWithSku);
        }

        return array_unique($outOfStockSku);
    }

    /**
     * check products in rules on QTY <= 0 OR stock status "In Stock"
     * return array with product's sku that have QTY <= 0 OR stock status "Out Of Stock"
     *
     * @param $skusFromRules
     *
     * @return array
     */
    protected function _checkForSku($skusFromRules)
    {
        $strWithLikeSkus = "";
        $strWithEqSkus   = "";
        $arrayWithEqSkus = array();

        $collectionWithProducts =
            Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->joinField(
                    'qty',
                    'cataloginventory/stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->joinField(
                    'use_config_manage_stock',
                    'cataloginventory/stock_item',
                    'use_config_manage_stock',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->joinField(
                    'stock_status',
                    'cataloginventory/stock_status',
                    'stock_status',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->addFieldToFilter('use_config_manage_stock', 1)
                ->addFieldToFilter('type_id', array('nin' => array('bundle', 'configurable', 'grouped')));
        /*
         * get arrays with sku
         * like not working with arrays
         * generate string with all likes
         * */
        if (!empty($skusFromRules[self::EQUALS])) {
            $strWithLikeSkus = "e.sku IN ('" . implode("', '", $skusFromRules[self::EQUALS]) . "')";
            if (!empty($skusFromRules[self::CONTAINS])) {
                $strWithLikeSkus .= " OR ";
            }
        }

        if (!empty($skusFromRules[self::CONTAINS])) {
            foreach ($skusFromRules[self::CONTAINS] as $skuFromRules) {
                $arrayWithEqSkus[] = "e.sku LIKE '%" . $skuFromRules . "%'";
            }
            $strWithEqSkus = implode($arrayWithEqSkus, " OR ");
        }

        $strWithResultQuery = $strWithLikeSkus . $strWithEqSkus;
        $collectionWithProducts->getSelect()
                               ->where($strWithResultQuery)
                               ->where("at_qty.qty <= 0 OR at_stock_status.stock_status <> 1")
                               ->order('sku', 'ASC');

        $resultArray = $collectionWithProducts->getData();

        return $resultArray;
    }

    /**
     * check rules on shopping cart price rules(grid)
     * show message with product's sku that have QTY <= 0 OR stock status "Out Of Stock"
     *
     * @param $observer
     */
    public function prePromoQuoteIndex($observer)
    {
        if (Mage::getStoreConfig('ampromo/messages/show_stock_warning')) {
            $resultArray          = array();
            $actionsAndConditions = array();
            $rulesCollection      = Mage::getModel('salesrule/rule')
                                        ->getCollection()
                                        ->addFieldToSelect('actions_serialized')
                                        ->addFieldToSelect('conditions_serialized')
                                        ->addFieldToSelect('promo_sku')
                                        ->addFieldToSelect('simple_action')
                                        ->addFieldtoFilter('is_active', 1);

            $rulesData    = $rulesCollection->getData();
            $arrayWithSku = array();
            foreach ($rulesData as $rule) {
                if (isset($rule['promo_sku'])
                    && in_array($rule['simple_action'], $this->_arrayWithSimpleAction)
                ) {
                    $arrayWithSku = array_merge($arrayWithSku, $this->_convertAndFormat($rule['promo_sku']));
                }
                if (!in_array($rule['simple_action'], $this->_arrayWithProductSet)) {
                    $actionsAndConditions[] = unserialize($rule['actions_serialized']);
                    $actionsAndConditions[] = unserialize($rule['conditions_serialized']);
                }
            }
            $skuArray = $this->_checkActionForSku($actionsAndConditions, $arrayWithSku);
            if (!empty($skuArray[self::EQUALS])
                || !empty($skuArray[self::CONTAINS])
            ) {
                $resultArray = $this->_checkForSku($skuArray);
            }

            if ($resultArray) {
                $this->_generateMessage($resultArray);
            }
        }
    }

    /**
     * check sku in one rule and all rules in shopping cart price rules
     * generate message with sku
     *
     * @param $actionsAndConditions
     * @param null $promoSkus
     *
     * @return array
     */
    protected function _checkActionForSku($actionsAndConditions, $promoSkus = null)
    {
        $skus                = $this->_recGetArrayWithSkus($actionsAndConditions, 'value');
        $arrayWithOutOfStock = array(array(), array());
        /*
         * from recursive we get array(arrayWithOthers(), arrayWithLikes())
         * */
        if (!empty($skus)) {
            $count = count($skus);
            for ($i = 0; $i < $count; $i ++) {
                $skus[$i] = array_unique($skus[$i]);
                foreach ($skus[$i] as $sku) {
                    /*
                     * recursion may cause extraneous elements to appear
                     * filtering extraneous elements from array
                     * */
                    if (!is_array($sku)) {
                        $arrayWithOutOfStock[$i] = array_merge(
                            $arrayWithOutOfStock[$i],
                            $this->_convertAndFormat($sku)
                        );
                    }
                }
            }
        }
        if ($promoSkus) {
            $arrayWithOutOfStock[self::EQUALS] = array_merge($arrayWithOutOfStock[self::EQUALS], $promoSkus);
        }

        return $arrayWithOutOfStock;
    }

    /**
     * convert str to array
     * delete spaces in sku's array
     *
     * @param $sku
     *
     * @return array
     *
     */
    protected function _convertAndFormat($sku)
    {
        $skusFromRules = explode(',', $sku);
        $skusFromRules = array_map('trim', $skusFromRules);

        return $skusFromRules;
    }

    /**
     * generate message with product's sku that qty<=0 and out of stock
     *
     * @param $outOfStockSkus
     */
    protected function _generateMessage($outOfStockSkus)
    {
        $arrayWithLinks = array();
        if ($outOfStockSkus) {
            foreach ($outOfStockSkus as $outOfStockSku) {
                $url = Mage::helper('adminhtml')->getUrl('/catalog_product/edit',
                    array('id' => $outOfStockSku['entity_id']));
                $arrayWithLinks[] = '<a href="' . $url . '"target="_blank">' . $outOfStockSku['sku'] . '</a>';
            }
            $strWithLinks = implode($arrayWithLinks, ', ');
            if ($strWithLinks != "") {
                $message = Mage::helper('ampromo')->__(
                    "Please notice, the %s have stock quantity <= 0 or are \"Out of stock\". That may interfere in proper rule execution.",
                    $strWithLinks);
                Mage::getSingleton('adminhtml/session')->addWarning($message);
            }
        }
    }


    /**
     * get skus from tree conditions and actions
     *
     * @param $conditions
     * @param $searchFor
     *
     * @return array
     */
    protected function _recGetArrayWithSkus($conditions, $searchFor)
    {
        static $arrayWithSku = array(array(), array());
        static $arrayWithEqualSkus = array();
        foreach ($conditions as $key => $condition) {
            if ($key == $searchFor
                && is_string($condition)
                && $condition != ""
            ) {
                if ($conditions['attribute'] == 'sku'
                    || $conditions['attribute'] == 'quote_item_sku'
                ) {
                    if ($conditions['operator'] == "{}"
                        || $conditions['operator'] == "!{}"
                    ) {
                        $arrayWithEqualSkus[] = $condition;
                    } else {
                        $arrayWithSku[self::EQUALS][] = $condition;
                    }
                }
            }
            if (is_array($conditions[$key])) {
                $this->_recGetArrayWithSkus($condition, $searchFor);
            }
        }
        if (!empty($arrayWithEqualSkus)) {
            $arrayWithSku[self::CONTAINS] = array_unique($arrayWithEqualSkus);
        }

        if (!empty($arrayWithSku[self::EQUALS])
            || !empty($arrayWithSku[self::CONTAINS])
        ) {
            return $arrayWithSku;
        }

        return null;
    }

    /**
     * In Magento 1.9.3 varien/product.js was separated to varien/product.js and varien/product_options.js
     *
     * @param $observer
     */
    public function onLayoutGenerateBlocksAfter($observer)
    {
        if (!method_exists('Mage', 'getVersion')) {
            return;
        }

        if (version_compare(Mage::getVersion(), '1.9.3', '<')) {
            return;
        }

        /** @var Mage_Core_Model_Layout $layout */
        $layout = $observer->getLayout();

        if (!in_array('ampromo_items', $layout->getUpdate()->getHandles())) {
            return;
        }

        /** @var Mage_Page_Block_Html_Head $head */
        $head = $layout->getBlock('head');

        $head->addJs('varien/product_options.js');
    }
}
