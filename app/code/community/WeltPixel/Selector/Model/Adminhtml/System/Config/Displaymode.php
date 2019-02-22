<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Displaymode
{
    const BOXED = 1;
    const FULL_WIDTH = 0;

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::BOXED,
                'label' => Mage::helper('weltpixel_selector')->__('Boxed')
            ),
            array(
                'value' => self::FULL_WIDTH,
                'label' => Mage::helper('weltpixel_selector')->__('Full width')
            ),
        );
    }
}