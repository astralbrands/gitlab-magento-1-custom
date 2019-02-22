<?php

class WeltPixel_LayeredNavigation_Block_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Block_Layer_Filter_Attribute {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('weltpixel/layerednavigation/catalog/layer/filter.phtml');
        $this->_filterModelName = 'catalog/layer_filter_attribute';
    }

    public function getFilter() {
        return $this->_filter;
    }

    public function getAttributeCode()
    {
        return $this->_filter->getAttributeCode();
    }
}