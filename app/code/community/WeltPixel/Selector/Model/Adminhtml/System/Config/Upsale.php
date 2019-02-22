<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Upsale
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'related',
                'label' => 'Display Related Items',
            ),
            array(
                'value' => 'cms',
                'label' => 'Display CMS Block (Block Id: weltpixel_replace_related)',
            ),
            array(
                'value' => 'replace',
                'label' => 'Display CMS Block if no Related Products available (Block Id: weltpixel_replace_related)',
            ),
        );
    }
}