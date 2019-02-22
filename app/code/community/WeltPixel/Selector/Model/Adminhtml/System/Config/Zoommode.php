<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Zoommode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'aside',
                'label' => 'Aside',
            ),
            array(
                'value' => 'inside',
                'label' => 'Inside',
            ),
        );
    }
}