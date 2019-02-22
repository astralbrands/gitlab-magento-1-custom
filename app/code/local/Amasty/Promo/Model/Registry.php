<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Model_Registry
{
    protected $_hasItems = false;
    protected $_locked = false;

    protected static $_sessionBackup = false;

    /**
     * @param $sku
     * @param $qty
     * @param $rule
     * @param null $eventItem
     * @return bool|void
     * @throws Mage_Core_Model_Store_Exception
     */
    public function addPromoItem($sku, $qty, $rule, $eventItem = null)
    {
        $ruleId = $rule->getId();

        if (Mage::getStoreConfig('ampromo/limitations/skip_promo_item_add')) {
            $quoteItems = $this->_getSession()->getQuote()->getAllItems();
            $promoItems = $this->_getSession()->getAmpromoItems();

            if (array_key_exists('_groups', $promoItems)) {
                unset($promoItems['_groups']);
            }
            $countPromoItems = count($promoItems);
            foreach ($quoteItems as $item) {
                if (!is_array($sku)) {
                    if (($item->getIsAmpromoGift() && stripos($item->getSku(), $sku) !== false)
                        || $countPromoItems > 0
                    ) {
                        return false;
                    }
                } else {
                    if (($item->getIsAmpromoGift() && !in_array($item->getSku(), $sku)) || $countPromoItems > 0) {
                        return false;
                    }
                }
                if (!$eventItem) {
                    return false;
                }
            }
        }

        if ($this->_locked) {
            return;
        }

        if (!$this->_hasItems) {
            $this->reset();
        }

        $this->_hasItems = true;

        $items = $this->_getSession()->getAmpromoItems();
        if ($items === null) {
            $items = array('_groups' => array());
        }

        $autoAdd = false;

        if (!is_array($sku)) {
            if (Mage::getStoreConfigFlag('ampromo/general/auto_add')) {
                $collection = Mage::getResourceModel('catalog/product_collection')
                    ->addAttributeToSelect(array('status', 'required_options'))
                    ->addAttributeToFilter('sku', $sku)
                    ->setPage(1, 1);

                $product = $collection->getFirstItem();

                $currentWebsiteId = Mage::app()->getStore()->getWebsiteId();

                if (!Mage::app()->getStore()->isAdmin()) {
                    if (!is_array($product->getWebsiteIds())
                        || !in_array($currentWebsiteId, $product->getWebsiteIds())
                    ) {
                        // Ignore products from other websites
                        return;
                    }
                }

                if ($product && $product->isSalable()) {
                    $autoAddTypes = array('simple', 'virtual', 'bundle');
                    $canAutoAddNonFree = Mage::getStoreConfigFlag('ampromo/general/add_nonfree');

                    if (Mage::getStoreConfigFlag('ampromo/general/auto_add_downloadable')) {
                        $autoAddTypes[] = 'downloadable';
                    }

                    if (in_array($product->getTypeId(), $autoAddTypes)
                        && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                        && ($this->isProductFree($product, $rule) || $canAutoAddNonFree)
                        && !$rule->getAmpromoMinPrice() > 0
                    ) {
                        $autoAdd = true;
                    }
                }
            }

            if (isset($items[$sku])) {
                $items[$sku]['qty'] += $qty;
            } else {
                $items[$sku] = array(
                    'sku' => $sku,
                    'qty' => $qty,
                    'auto_add' => $autoAdd,
                    'rule_id' => $ruleId
                );
            }
        } else {
            $items['_groups'][$ruleId] = array(
                'sku' => $sku,
                'qty' => $qty,
                'rule_id' => $ruleId
            );
        }

        $this->_checkChanges($items);

        $this->_getSession()->setAmpromoItems($items);

    }

    /**
     * check for auto add simple product
     * @param $rule
     *
     * @return bool
     */
    protected function _checkForAutoAdd($rule)
    {
        $autoAdd = (int)$rule->getAmpromoAutoAddSimple();

        if ($autoAdd === Amasty_Promo_Model_Observer::CONFIG) {
            return Mage::getStoreConfigFlag('ampromo/general/auto_add');
        } elseif ($autoAdd === Amasty_Promo_Model_Observer::AUTO) {
            return true;
        } else if ($autoAdd === Amasty_Promo_Model_Observer::NOTAUTO) {
            return false;
        }

        return false;
    }


    /**
     * Is product Free
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_SalesRule_Model_Rule $rule
     *
     * @return bool
     */
    protected function isProductFree($product, $rule)
    {
        $price = Mage::helper("ampromo")->getDiscountPrice($rule, $product->getFinalPrice(), $product);

        return $price == 0;
    }

    /**
     * @param $items
     */
    protected function _checkChanges(&$items)
    {
        $beforeItems = $this->_getSession()->getAmpromoItemsBefore();

        if (is_array($items) && is_array($beforeItems)) {
            foreach ($items as $sku => &$item) {
                if (!empty($sku) && ($sku !== '_groups')) {
                    if (isset($beforeItems[$sku]) && $beforeItem = $beforeItems[$sku]) {
                        $item['qtyIncreased'] = $beforeItem['qty'] < $item['qty'];
                    } else {
                        $item['qtyIncreased'] = true;
                    }
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getPromoItems()
    {
        return $this->_getSession()->getAmpromoItems();
    }

    public function backup()
    {
        if (!self::$_sessionBackup) {
            $this->_getSession()->setAmpromoItemsBefore($this->_getSession()->getAmpromoItems());
            self::$_sessionBackup = true;
        }
    }

    public function reset()
    {
        if ($this->_hasItems) {
            $this->_locked = true;
            return;
        }

        $this->_getSession()->setAmpromoItems(array('_groups' => array()));
    }

    /**
     * compare state of deleted items based on applied rules
     * @param $beforeDeletedItems
     * @param array $rulesIds
     */
    protected function _checkDeletedItems(&$beforeDeletedItems, array $rulesIds)
    {
        if (is_array($beforeDeletedItems)) {
            $deletedItems = array();
            foreach ($beforeDeletedItems as $sku => $config) {
                // check that rule related to deletion still applied
                $ruleId = is_array($config) && array_key_exists('rule_id', $config) ?
                    $config['rule_id'] :
                    null;

                if (in_array($ruleId, $rulesIds)) {
                    $deletedItems[$sku] = $config;
                }
            }

            $beforeDeletedItems = $deletedItems;

            $this->_getSession()->setAmpromoDeletedItems($deletedItems);
        }
    }

    protected function _getSession()
    {
        $session = null;

        if (Mage::helper('ampromo')->applyToAdminOrders()) {
            $session = Mage::getSingleton('adminhtml/session_quote');
        } else {
            $session = Mage::getSingleton('checkout/session');
        }

        return $session;
    }

    public function getLimits()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->_getSession()->getQuote();

        $allowed = $this->getPromoItems();

        foreach ($quote->getAllItems() as $item) {
            /** @var Mage_Sales_Model_Quote_Item $item */

            $sku = $item->getProduct()->getData('sku');

            //$sku = strtolower($sku);
            if ($item->getIsPromo()) {
                if (isset($allowed['_groups'][$item->getRuleId()])) {
                    if ($item->getParentItem()) {
                        continue;
                    }

                    $allowed['_groups'][$item->getRuleId()]['qty'] -= $item->getQty();
                    if ($allowed['_groups'][$item->getRuleId()]['qty'] <= 0) {
                        unset($allowed['_groups'][$item->getRuleId()]);
                    }
                } else if (isset($allowed[$sku])) {
                    $allowed[$sku]['qty'] -= $item->getQty();

                    if ($allowed[$sku]['qty'] <= 0) {
                        unset($allowed[$sku]);
                    }
                }
            }
        }

        return $allowed;
    }

    /**
     * keeping information about declined promo items connected to rule
     * @param $sku
     */
    public function deleteProduct($sku)
    {
        $promoItems = $this->getPromoItems();
        $ruleId = array_key_exists($sku, $promoItems) ? $promoItems[$sku]['rule_id'] : null;

        $deletedItems = Mage::getSingleton('checkout/session')->getAmpromoDeletedItems();

        if (!$deletedItems) {
            $deletedItems = array();
        }

        $deletedItems[$sku] = array('rule_id' => $ruleId);

        Mage::getSingleton('checkout/session')->setAmpromoDeletedItems($deletedItems);
    }

    public function restore($sku)
    {
        $deletedItems = $this->_getSession()->getAmpromoDeletedItems();

        if (!$deletedItems || !isset($deletedItems[$sku])) {
            return;
        }

        unset($deletedItems[$sku]);

        $this->_getSession()->setAmpromoDeletedItems($deletedItems);
    }

    /**
     * fetch deleted items info from session
     * @param array $rulesIds
     * @return array
     */
    public function getDeletedItems(array $rulesIds)
    {
        $deletedItems = $this->_getSession()->getAmpromoDeletedItems();

        if (!$deletedItems) {
            $deletedItems = array();
        }

        $this->_checkDeletedItems($deletedItems, $rulesIds);

        return $deletedItems;
    }

    /**
     * compare state of deleted items based on applied rules
     *
     * @param array $rulesIds
     */
    public function checkDeletedItems($rulesIds)
    {
        $deletedItems = $this->_getSession()->getAmpromoDeletedItems();
        $this->_checkDeletedItems($deletedItems, $rulesIds);
    }
}
