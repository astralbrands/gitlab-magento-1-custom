<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Relatedlocation
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'bottom',
                'label' => 'Bottom',
            ),
            array(
                'value' => 'right',
                'label' => 'Right',
            ),
            array(
                'value' => 'none',
                'label' => 'None',
            ),
        );
    }
}