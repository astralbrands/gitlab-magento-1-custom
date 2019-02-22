<?php

class WeltPixel_Catalog_Block_Category_View_Attribute extends Mage_Core_Block_Template
{
    protected $_attributeData;
    protected $_cacheTag;

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('weltpixel/catalog/category/view/attribute.phtml');
        $this->addData(array(
            'cache_lifetime' => 7*24*60*60,
            'cache_tags'     => array(
                Mage_Catalog_Model_Category::CACHE_TAG
            ),
            'cache_key'      => $this->getCurrentCategory()->getId() . $this->_cacheTag
        ));
    }

    public function getCurrentCategory()
    {
        return Mage::registry('current_category');
    }

    public function getAttributeData()
    {
        if (is_null($this->_attributeData) && !is_null($this->getCurrentCategory())) {
            $this->_attributeData = $this->getCurrentCategory()->getData($this->getAttribute());
        }
        
        return $this->_attributeData;
    }
}
