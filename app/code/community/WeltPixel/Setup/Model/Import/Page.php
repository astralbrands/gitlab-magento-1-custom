<?php

class WeltPixel_Setup_Model_Import_Page extends WeltPixel_Setup_Model_Import {

    public function __construct($options) {
        $this->_entity = 'cms/page';
        $this->_storeId = $options['storeId'];
        $this->_importFile = $options['importFile'];
    }
}