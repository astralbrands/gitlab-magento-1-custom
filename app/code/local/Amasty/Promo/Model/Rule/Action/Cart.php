<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_Rule_Action_Cart extends Amasty_Promo_Model_Rule_Action_ActionAbstract
{
    /**
     * @var string
     */
    protected $_actionName = 'ampromo_cart';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return Mage::helper('ampromo')->__('Auto add promo items for the whole cart');
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
        $amount = max(1, $rule->getDiscountAmount());
        $qty = 0;
        if (!Mage::getStoreConfig('ampromo/limitations/skip_special_price')) {
            $qty = $amount;
        } else {
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$this->_skip($item, $address)) {
                    $qty = $amount;
                }
            }
        }
        return $qty;
    }
}