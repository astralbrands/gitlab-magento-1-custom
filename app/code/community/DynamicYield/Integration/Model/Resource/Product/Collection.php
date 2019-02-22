<?php

/**
 * Class DynamicYield_Integration_Model_Resource_Catalog_Product_Collection
 */
class DynamicYield_Integration_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Disable flat catalog for product export collection
     *
     * @return bool
     */
    public function isEnabledFlat() {
        if (Mage::registry('use_product_eav')) {
            return false;
        }
        return parent::isEnabledFlat();
    }

}
