<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Addtocart
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'lightbox',
                'label' => 'Open product in lightbox',
            ),
            array(
                'value' => 'page',
                'label' => 'Open product page',
            ),
        );
    }
}