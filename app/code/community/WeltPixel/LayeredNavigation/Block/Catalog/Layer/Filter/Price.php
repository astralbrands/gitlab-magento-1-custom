<?php

class WeltPixel_LayeredNavigation_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price {

    /**
     * Initialize Price filter module
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate('weltpixel/layerednavigation/catalog/layer/price-filter.phtml');
        $this->_filterModelName = 'catalog/layer_filter_price';
    }

    public function getAttributeCode() {
        return $this->_filter->getAttributeCode();
    }

}
