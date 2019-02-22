<?php

class WeltPixel_LayeredNavigation_Model_Adminhtml_System_Config_Source_Navigation_Options {

    /**
     * Get available options for the layered navigation position
     */
    public function toOptionArray() {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Left Column')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Top')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Right Column')
            ),
        );
    }

}