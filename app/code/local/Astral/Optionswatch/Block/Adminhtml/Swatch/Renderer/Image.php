<?php
class Astral_Optionswatch_Block_Adminhtml_Swatch_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
     
    public function render(Varien_Object $row)
    {
    	$html = null;
    	if ($row->getData($this->getColumn()->getIndex())) {
	        $html = '<img ';
	        $html .= 'id="' . $this->getColumn()->getId() . '" ';
	        $html .= 'src="' .  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $row->getData($this->getColumn()->getIndex()) . '"';
	        $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '" height="20px"/>';
	        
    	}
    	return $html;
    }
}