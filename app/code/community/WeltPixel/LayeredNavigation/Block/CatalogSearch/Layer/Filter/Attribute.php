<?php

class WeltPixel_LayeredNavigation_Block_CatalogSearch_Layer_Filter_Attribute
    extends Mage_CatalogSearch_Block_Layer_Filter_Attribute
{

    /**
     * Set filter model name
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('weltpixel/layerednavigation/catalog/layer/filter.phtml');
        $this->_filterModelName = 'catalogsearch/layer_filter_attribute';
    }

    public function getFilter()
    {
        return $this->_filter;
    }

    public function getAttributeCode()
    {
        return $this->_filter->getAttributeCode();
    }

}
