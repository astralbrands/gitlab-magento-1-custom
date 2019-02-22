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
 * Class DynamicYield_Integration_Model_Event_Addtowishlist
 */
class DynamicYield_Integration_Model_Event_Addtowishlist extends DynamicYield_Integration_Model_Event_Abstract
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /**
     * @return mixed
     */
    function getName() {
        return 'Add to Wishlist';
    }

    /**
     * @return mixed
     */
    function getType() {
        return 'add-to-wishlist-v1';
    }

    /**
     * @return mixed
     */
    function getDefaultProperties() {
        return array('productId' => NULL,);
    }

    /**
     * @return array
     */
    function generateProperties() {
        return array(
            'productId' => Mage::helper('dynamicyield_integration')->validateSku($this->product) ? $this->product->getSku() : $this->product->getData('sku')
        );
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProduct(Mage_Catalog_Model_Product $product) {
        $this->product = $product;
    }
}
