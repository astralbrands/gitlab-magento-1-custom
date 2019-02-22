<?php

class WeltPixel_Catalog_Block_Category_View_Attribute_Bottom extends WeltPixel_Catalog_Block_Category_View_Attribute
{
    public function _construct()
    {
        $this->_cacheTag = 'weltpixel_attribute_bottom';
        parent::_construct();
        $this->setData('attribute', 'wp_cat_bottom_desc');
    }
}
