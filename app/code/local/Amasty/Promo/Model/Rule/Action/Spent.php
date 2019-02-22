<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_Rule_Action_Spent extends Amasty_Promo_Model_Rule_Action_ActionAbstract
{
    /**
     * @var string
     */
    protected $_actionName = 'ampromo_spent';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return Mage::helper('ampromo')->__('Auto add promo items for every $X spent');
    }

    /**
     * @param Mage_Rule_Model_Rule $rule
     * @param Mage_Sales_Model_Quote $quote
     * @param $address
     *
     * @return float|int|mixed
     */
    public function getFreeItemsQty($rule, $quote, $address)
    {
        $amount = max(1, $rule->getDiscountAmount());
        $step = $rule->getDiscountStep();
        $skipSpecialPrice = (bool)Mage::getStoreConfig('ampromo/limitations/skip_special_price');

        $hasOnlySpecialPrice = false;
        if ($skipSpecialPrice) {
            $hasOnlySpecialPrice = $this->hasOnlyItemsWithSpecialPrice($quote);
        }

        if (!$step || $hasOnlySpecialPrice) {
            return 0;
        }

        $qty = floor(Mage::helper("ampromo/calc")->getQuoteSubtotal($quote, $rule) / $step) * $amount;

        $max = $rule->getDiscountQty();
        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return boolean
     */
    protected function hasOnlyItemsWithSpecialPrice($quote)
    {
        $hasOnlySpecialPrice = true;
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getProduct()->getSpecialPrice()) {
                $hasOnlySpecialPrice = false;
                break;
            }
        }

        return $hasOnlySpecialPrice;
    }

}