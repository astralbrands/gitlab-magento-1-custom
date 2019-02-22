<?php

class WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display_Mode extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    const FULL_WIDTH = 0;
    const SECTIONED = 1;
    const DROPDOWN = 2;

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => self::FULL_WIDTH,
                    'label' => Mage::helper('weltpixel_megamenu')->__('Full width')
                ),
                array(
                    'value' => self::SECTIONED,
                    'label' => Mage::helper('weltpixel_megamenu')->__('Sectioned')
                ),
                array(
                    'value' => self::DROPDOWN,
                    'label' => Mage::helper('weltpixel_megamenu')->__('Dropdown')
                )
            );
        }

        return $this->_options;
    }

}
