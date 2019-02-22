<?php

class WeltPixel_Newsletter_Model_Adminhtml_System_Config_Source_Display_Disableoptions {

    public function toOptionArray() {
        return array(
            array(
                'value' => 1,
                'label' => Mage::helper('weltpixel_newsletter')->__('Close button is clicked')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('weltpixel_newsletter')->__('From close button or clicked outside of the box')
            ),
        );
    }

}
