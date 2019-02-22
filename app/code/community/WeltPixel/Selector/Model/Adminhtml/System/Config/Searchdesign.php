<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Searchdesign {

    public function toOptionArray() {
        return array(
            array(
                'value' => 'form.mini-v1.phtml',
                'label' => 'Version 1',
            ),
            array(
                'value' => 'form.mini-v2.phtml',
                'label' => 'Version 2',
            )
        );
    }

}