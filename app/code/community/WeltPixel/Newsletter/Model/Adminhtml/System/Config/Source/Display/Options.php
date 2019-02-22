<?php

class WeltPixel_Newsletter_Model_Adminhtml_System_Config_Source_Display_Options {

    /**
     * Get available options for the popup display in backend for module configuration
     */
    public function toOptionArray() {
        return array(
            array(
                'value' => 0,
                'label' => Mage::helper('weltpixel_newsletter')->__('Just Home Page')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('weltpixel_newsletter')->__('All Pages')
            ),
        );
    }

}
