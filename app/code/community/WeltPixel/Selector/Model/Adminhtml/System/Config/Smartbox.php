<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Smartbox {

    public function toOptionArray() {
        $options = array();
        $options[] = array(
            'value' => 0,
            'label' => Mage::helper('weltpixel_selector')->__('Please select')
        );

        $collection = Mage::getResourceModel('catalog/product_attribute_collection')                
                ->addVisibleFilter()
                ->setOrder('frontend_label', 'ASC');
        
        foreach ($collection->getItems() as $attr) {
            $options[] = array(
                'value' => $attr->getData('attribute_code'),
                'label' => $attr->getData('frontend_label')
            );
        }
        
        return $options;
    }

}