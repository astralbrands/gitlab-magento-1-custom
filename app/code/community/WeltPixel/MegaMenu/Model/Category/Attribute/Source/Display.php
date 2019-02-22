<?php

class WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const STRETCH = 0;
    const REPEAT = 1;

    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => self::STRETCH,
                    'label' => 'stretch',
                ),
                array(
                    'value' => self::REPEAT,
                    'label' => 'repeat',
                )
            );
        }

        return $this->_options;
    }
}
