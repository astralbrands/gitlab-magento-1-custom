<?php

class WeltPixel_GoogleCards_Model_Adminhtml_System_Config_Description {
    
    public function toOptionArray() {
        $options = array();

        $options[] = array(
            'value' => 0,
            'label' => Mage::helper('weltpixel_googlecards')->__('Short Description')
        );

        $options[] = array(
            'value' => 1,
            'label' => Mage::helper('weltpixel_googlecards')->__('Long Description')
        );

        return $options;
    }
}