<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Model_Event_Removefromcart
 */
class DynamicYield_Integration_Model_Event_Removefromcart extends DynamicYield_Integration_Model_Event_Abstract
{
    /**
     * @var Mage_Sales_Model_Quote_Item
     */
    protected $cartItem;

    /**
     * @return mixed
     */
    function getName() {
        return 'Remove from Cart';
    }

    /**
     * @return mixed
     */
    function getType() {
        return 'remove-from-cart-v1';
    }

    /**
     * @return mixed
     */
    function getDefaultProperties() {
        return array('value' => 0, 'currency' => NULL, 'productId' => '', 'quantity' => 0,);
    }

    /**
     * @return array
     */
    function generateProperties() {
        return array(
            'productId' => Mage::helper('dynamicyield_integration')->validateSku($this->cartItem) ? $this->cartItem->getProduct()->getSku() : $this->cartItem->getProduct()->getData('sku'),
            'value' => round(Mage::helper('core')->currency($this->cartItem->getProduct()->getFinalPrice(),false,false),2),
            'currency' => $this->cartItem->getQuote()->getQuoteCurrencyCode(),
            'quantity' => $this->cartItem->getQty()
        );
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $cartItem
     */
    public function setCartItem(Mage_Sales_Model_Quote_Item $cartItem) {
        $this->cartItem = $cartItem;
    }
}
