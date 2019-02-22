<?php

class WeltPixel_LayeredNavigation_Model_Adminhtml_System_Config_Source_Navigation_Itemsdisplay {

    public function toOptionArray() {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Default')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('weltpixel_layerednavigation')->__('Show More and Less')
            ),
//            array(
//                'value' => 2,
//                'label' => Mage::helper('weltpixel_layerednavigation')->__('Show More and Less with search')
//            ),
//            array(
//                'value' => 3,
//                'label' => Mage::helper('weltpixel_layerednavigation')->__('Scroll Bar')
//            ),
//            array(
//                'value' => 4,
//                'label' => Mage::helper('weltpixel_layerednavigation')->__('Scroll Bar with search')
//            )
        );
    }

}