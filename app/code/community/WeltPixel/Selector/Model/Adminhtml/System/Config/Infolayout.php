<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Infolayout
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'twocolumns',
                'label' => 'Two Columns',
            ),
            array(
                'value' => 'onecolumn',
                'label' => 'One Column',
            ),
        );
    }
}