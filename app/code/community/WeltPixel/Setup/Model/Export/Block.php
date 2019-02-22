<?php

class WeltPixel_Setup_Model_Export_Block extends WeltPixel_Setup_Model_Export {

    public function __construct($storeId = 0) {
        $this->_entity = 'block';
        $this->_requiredColumns = array('title', 'identifier', 'content', 'is_active');
        $collection = Mage::getModel('cms/block')->getCollection()
                ->addStoreFilter($storeId);

        $this->setItems($collection);
    }
}