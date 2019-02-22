<?php

class WeltPixel_Catalog_Block_Category_View_Attribute_Top extends WeltPixel_Catalog_Block_Category_View_Attribute
{
    public function _construct()
    {
        $this->_cacheTag = 'weltpixel_attribute_top';
        parent::_construct();
        $this->setData('attribute', 'wp_cat_top_desc');
    }
}
