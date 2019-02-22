<?php

class WeltPixel_LayeredNavigation_Model_Adminhtml_System_Config_Source_Navigation_Showpricefilter {

    public function toOptionArray() {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Default')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Default with From - To')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Dropdown')
            ),
            array(
                'value' => 3,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Slider')
            ),
            array(
                'value' => 4,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Slider with From - To')
            ),
            array(
                'value' => 5,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('From - To only')
            )
        );
    }

}