<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Headerstyle
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'header-v1.0.phtml',
                'label' => 'Version 1',
            ),
            array(
                'value' => 'header-v2.0.phtml',
                'label' => 'Version 2',
            ),
              array(
                'value' => 'header-v3.0.phtml',
                'label' => 'Version 3',
            )
        );
    }
}