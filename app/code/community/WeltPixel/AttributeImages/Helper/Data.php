<?php

class WeltPixel_AttributeImages_Helper_Data extends Mage_Core_Helper_Data {
    
    public function getAttributeImageByCode($attributeCode) {
        $model = Mage::getModel('weltpixel_attributeimages/attributeimages')->loadByAttribute('attribute_code', $attributeCode);
        if ($model && strlen(trim($model->getData('attribute_image')))) :
            return Mage::getBaseUrl('media') . $model->getData('attribute_image');
        endif;
        
        return false;
    }
    
     public function getAttributeImageById($attributeId) {
        $model = Mage::getModel('weltpixel_attributeimages/attributeimages')->loadByAttribute('attribute_id', $attributeId);
        if ($model && strlen(trim($model->getData('attribute_image')))) :
            return Mage::getBaseUrl('media') . $model->getData('attribute_image');
        endif;
        
        return false;
    }
    
}
