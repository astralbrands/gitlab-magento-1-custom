<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Font_Characterset {

    protected $_charsets = array(
        'cyrillic' => 'Cyrillic',
        'cyrillic-ext' => 'Cyrillic Extended',
        'greek' => 'Greek',
        'greek-ext' => 'Greek Extended',
        'khmer' => 'Khmer',
        'latin' => 'Latin',
        'latin-ext' => 'Latin Extende',
        'vietnamese' => 'Vietnamese',
    );

    public function toOptionArray() {

        $options = array();
       
        foreach ($this->_charsets as $id => $charset) :
            $options[] = array(
                'value' => $id,
                'label' => $charset
            );
        endforeach;

        return $options;
    }

}