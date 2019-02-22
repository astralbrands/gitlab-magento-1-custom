<?php

class WeltPixel_Setup_Model_Export_Page extends WeltPixel_Setup_Model_Export {

    public function __construct($storeId = 0) {
        $this->_entity = 'page';
        $this->_requiredColumns = array('title', 'identifier', 'content', 'content_heading', 'is_active', 'root_template', 'meta_keywords', 'meta_description','layout_update_xml','custom_theme', 'custom_root_template', 'custom_layout_update_xml','custom_theme_from', 'custom_theme_to' );
        $collection = Mage::getModel('cms/page')->getCollection()
                ->addStoreFilter($storeId);

        $this->setItems($collection);
    }  
}