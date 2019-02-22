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
 * Class DynamicYield_Integration_Model_Config_Source_Productattribute
 */
class DynamicYield_Integration_Model_Config_Source_Productattribute
{
    /**
     * @return array
     */
    protected function getAttributes() {

        $out = array();

        /**
         * @var $collection Mage_Catalog_Model_Entity_Attribute[]
         */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');

        foreach ($collection as $attribute) {
            $out[] = array("label" => $attribute->getName() . " (" . $attribute->getFrontend()->getLabel() . ")", "value" => $attribute->getId());
        }
        $out[] = array(
            "label" => DynamicYield_Integration_Helper_Data::FINAL_PRICE,
            "value" => DynamicYield_Integration_Helper_Data::FINAL_PRICE
        );
        /**
         *Set default attributes
         */
        if (Mage::getStoreConfig('dyi_config/product_feed/additional_attributes') == NULL) $this->setDefaultAttributes();

        return $out;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return $this->getAttributes();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        $arrayHelper = Mage::helper('dynamicyield_integration/array');

        return $arrayHelper->pluck($this->toOptionArray(), 'label', 'value');
    }

    /**
     * Preselect default attributes in admin
     */
    public function setDefaultAttributes() {
        $default = array('name', 'url', 'sku', 'group_id', 'price', 'in_stock', 'categories', 'image_url', 'meta_keywords');

        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('attribute_code', array('in' => $default,));

        $default_attributes = array();
        foreach ($collection as $attribute) {
            $default_attributes[] = $attribute->getId();
        }

        $config = Mage::getConfig();
        $config->setNode('default/dyi_config/product_feed/additional_attributes', implode(",", $default_attributes));
        $config->saveConfig('dyi_config/product_feed/additional_attributes', implode(",", $default_attributes));
    }
}
