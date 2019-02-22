<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Gallerylocation
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'bottom',
                'label' => 'Bottom',
            ),
            array(
                'value' => 'left',
                'label' => 'Left',
            ),
            array(
                'value' => 'none',
                'label' => 'None',
            ),
        );
    }
}