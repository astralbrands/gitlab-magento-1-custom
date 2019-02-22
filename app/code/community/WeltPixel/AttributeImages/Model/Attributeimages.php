<?php

class WeltPixel_AttributeImages_Model_AttributeImages extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('weltpixel_attributeimages/attributeimages');
    }
    
    
    public function loadByAttribute($attribute, $value)
    {
       
        $collection = $this->getResourceCollection()
            ->addFieldToFilter($attribute, $value);
            //->setPageSize(1);
        
        foreach ($collection as $object) {
            return $object;
        }
        return false;
    }
}