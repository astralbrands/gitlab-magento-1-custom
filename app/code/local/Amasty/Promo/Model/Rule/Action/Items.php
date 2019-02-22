<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_Rule_Action_Items extends Amasty_Promo_Model_Rule_Action_ActionAbstract
{
    /**
     * @var string
     */
    protected $_actionName = 'ampromo_items';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return Mage::helper('ampromo')->__('Auto add promo items with products');
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
}