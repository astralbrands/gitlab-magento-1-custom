<?php

class WeltPixel_Setup_Model_Import_Block extends WeltPixel_Setup_Model_Import {

    public function __construct($options) {
        $this->_entity = 'cms/block';
        $this->_storeId = $options['storeId'];
        $this->_importFile = $options['importFile'];
    }

}