<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Contactstyle
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'contacts/form.phtml',
                'label' => 'Version 1',
            ),
            array(
                'value' => 'contacts/form_v2.phtml',
                'label' => 'Version 2',
            )
        );
    }
}