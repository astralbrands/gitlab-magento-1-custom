<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Infoformat
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'accordion',
                'label' => 'Accordion',
            ),
            array(
                'value' => 'list',
                'label' => 'List',
            ),
            array(
                'value' => 'tabs',
                'label' => 'Tabs',
            ),
        );
    }
}