<?php

class WeltPixel_LayeredNavigation_Model_Adminhtml_System_Config_Source_Navigation_Showcategories {

    public function toOptionArray() {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Default (only one category can be selected at a time)')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Dropdown')
            ),
            /*
            array(
                'value' => 2,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Tree')
            ),
            array(
                'value' => 3,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Checkbox')
            ),
            array(
                'value' => 4,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Checkbox with search')
            )*/
        );
    }

}