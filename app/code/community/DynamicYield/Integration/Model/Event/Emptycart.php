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
 * Class DynamicYield_Integration_Model_Event_Emptycart
 */
class DynamicYield_Integration_Model_Event_Emptycart extends DynamicYield_Integration_Model_Event_Abstract
{
    /**
     * @var
     */
    protected $_cartItems;

    /**
     * @var
     */
    protected $_visibleItems;

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

        $defaultProperties = array();

        foreach ($this->_visibleItems as $item)
        {
            if($item->getProductType() == "grouped" || $item->getProductType() == "bundle") continue;
            $defaultProperties[] = array('value' => 0, 'currency' => NULL, 'productId' => '', 'quantity' => 0,);
        }

        return $defaultProperties;
    }

    /**
     * @return array
     */
    function generateProperties() {

        $properties = array();
        $prepareItems = array();

        foreach ($this->_cartItems as $item) {
            if($item->getProductType() == "grouped" || $item->getProductType() == "bundle" || isset($prepareItems[$item->getSku()])) {
                continue;
            }

            $product = $item->getProduct();

            if(!$product || !Mage::helper('dynamicyield_integration')->validateSku($product)) {
                continue;
            }

            $prepareItems[$item->getSku()] = array(
                'productId' => Mage::helper('dynamicyield_integration')->validateSku($product) ? $product->getSku() : $product->getData('sku'),
                'value' => round(Mage::helper('core')->currency($product->getFinalPrice(),false,false),2),
                'currency' => $item->getQuote()->getQuoteCurrencyCode(),
                'quantity' => $item->getQty()
            );
        }

        foreach ($prepareItems as $item) {
            $properties[] = $item;
        }

        return $properties;
    }

    /**
     * @param Mage_Sales_Model_Entity_Quote_Item_Collection $cartItems
     */
    public function setCartItems(Mage_Sales_Model_Entity_Quote_Item_Collection $cartItems) {
        $this->_cartItems = $cartItems;
    }

    /**
     * @param Mage_Sales_Model_Entity_Quote_Item_Collection $visibleItems
     */
    public function setVisibleItems(Mage_Sales_Model_Entity_Quote_Item_Collection $visibleItems) {
        $this->_visibleItems = $visibleItems;
    }

}
