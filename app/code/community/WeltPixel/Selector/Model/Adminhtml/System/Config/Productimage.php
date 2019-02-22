<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Productimage
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'single',
                'label' => 'Single image',
            ),
            array(
                'value' => 'double',
                'label' => 'Double image',
            ),
        );
    }
}