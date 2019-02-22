<?php

class WeltPixel_Setup_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Options extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('weltpixel/catalog/product/attribute/options.phtml');
    }

    public function getOptionValues() {
        $attributeType = $this->getAttributeObject()->getFrontendInput();
        $defaultValues = $this->getAttributeObject()->getDefaultValue();
        if ($attributeType == 'select' || $attributeType == 'multiselect') {
            $defaultValues = explode(',', $defaultValues);
        } else {
            $defaultValues = array();
        }

        switch ($attributeType) {
            case 'select':
                $inputType = 'radio';
                break;
            case 'multiselect':
                $inputType = 'checkbox';
                break;
            default:
                $inputType = '';
                break;
        }

        $values = $this->getData('option_values');
        if (is_null($values)) {
            $values = array();
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setAttributeFilter($this->getAttributeObject()->getId())
                    ->setPositionOrder('desc', true)
                    ->load();

            $helper = Mage::helper('core');
            foreach ($optionCollection as $option) {
                $value = array();
                if (in_array($option->getId(), $defaultValues)) {
                    $value['checked'] = 'checked="checked"';
                } else {
                    $value['checked'] = '';
                }

                $value['intype'] = $inputType;
                $value['id'] = $option->getId();
                $value['sort_order'] = $option->getSortOrder();
                if (Mage::helper('weltpixel_productswatch')->isColorSwatchEnabledOnLayeredNavigation()) :
                    $value['navigation_image'] = $option->getNavigationImage();
                endif;  
                if (Mage::helper('weltpixel_layerednavigation')->isColorSwatchEnabledOnProduct()) :
                    $value['product_image'] = $option->getProductImage();
                endif;                
                foreach ($this->getStores() as $store) {
                    $storeValues = $this->getStoreOptionValues($store->getId());
                    $value['store' . $store->getId()] = isset($storeValues[$option->getId()]) ? $helper->escapeHtml($storeValues[$option->getId()]) : '';
                }
                $values[] = new Varien_Object($value);
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }

}