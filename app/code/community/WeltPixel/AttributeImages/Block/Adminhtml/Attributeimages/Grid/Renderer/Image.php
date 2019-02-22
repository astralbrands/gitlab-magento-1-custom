<?php

class WeltPixel_AttributeImages_Block_Adminhtml_Attributeimages_Grid_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    } 
    protected function _getValue(Varien_Object $row)
    {       
        $val = $row->getData($this->getColumn()->getIndex());
        if (strlen(trim($val))) :
            $url = Mage::getBaseUrl('media') . $val; 
            $out = "<img src=". $url ." width='75px' />"; 
        else:
            $out = Mage::helper('weltpixel_attributeimages')->__('No image was uploaded');
        endif;
        return $out;
    }
}