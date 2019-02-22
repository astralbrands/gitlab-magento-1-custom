<?php

class WeltPixel_LayeredNavigation_Model_Adminhtml_System_Config_Source_Navigation_Orderby {

    public function toOptionArray() {
        return array(
            array(
                'value' => 1,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Ascendent')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Descendent')
            ),
        );
    }

}