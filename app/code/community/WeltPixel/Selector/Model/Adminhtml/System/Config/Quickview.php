<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Quickview
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'version1',
                'label' => 'Lightbox',
            ),
            array(
                'value' => 'version2',
                'label' => 'Inline',
            ),
        );
    }
}