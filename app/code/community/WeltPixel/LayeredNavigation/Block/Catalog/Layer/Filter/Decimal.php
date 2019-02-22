<?php

class WeltPixel_LayeredNavigation_Block_Catalog_Layer_Filter_Decimal extends Mage_Catalog_Block_Layer_Filter_Decimal {

    /**
     * Initialize Decimal Filter Model
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate('weltpixel/layerednavigation/catalog/layer/filter.phtml');
        $this->_filterModelName = 'catalog/layer_filter_decimal';
    }

    public function getFilter() {
        return $this->_filter;
    }

    public function getAttributeCode() {
        return $this->_filter->getAttributeCode();
    }

}
