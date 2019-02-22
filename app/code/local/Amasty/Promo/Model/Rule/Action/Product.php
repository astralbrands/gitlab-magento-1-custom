<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_Rule_Action_Product extends Amasty_Promo_Model_Rule_Action_ActionAbstract
{
    /**
     * @var string
     */
    protected $_actionName = 'ampromo_product';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return Mage::helper('ampromo')->__('Auto add the same product');
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
        try {
            $productToAdd = $this->_getParentItem($item);

            if ($productToAdd->getIsPromo() || $this->_skip($productToAdd, $address)) {
                return false;
            }

            $discountStep = max(1, $rule->getDiscountStep());
            $maxDiscountQty = 100000;
            if ($rule->getDiscountQty()) {
                $maxDiscountQty = (int)max(1, $rule->getDiscountQty());
            }

            $discountAmount = max(1, $rule->getDiscountAmount());
            $qty = min(floor($productToAdd->getQty() / $discountStep) * $discountAmount, $maxDiscountQty);

            if ($productToAdd->getParentItemId()) {
                return false;
            }

            if ($qty < 1) {
                return false;
            }

            if ($this->isConfigurableProcessed($productToAdd)) {
                return false;
            }

            Mage::getSingleton('ampromo/registry')->addPromoItem(
                $productToAdd->getSku(),
                $qty,
                $rule,
                $eventItem
            );

        } catch (Exception $e) {
            $hlp = Mage::helper('ampromo');
            $hlp->showMessage(
                $hlp->__(
                    'We apologize, but there is an error while adding free items to the cart: %s', $e->getMessage()
                )
            );

            return false;
        }

        return true;
    }
}