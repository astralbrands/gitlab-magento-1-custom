<?php

class WeltPixel_AttributeImages_Model_Resource_AttributeImages extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('weltpixel_attributeimages/attributeimages', 'id');
    }

}