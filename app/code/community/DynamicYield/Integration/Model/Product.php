<?php

/**
 * Class DynamicYield_Integration_Model_Product
 */
class DynamicYield_Integration_Model_Product extends Mage_Catalog_Model_Product
{
    /**
     * A product model to be used in product collections for export
     */
    protected function _construct() {
        $this->_init("dynamicyield_integration/product");
    }
}
