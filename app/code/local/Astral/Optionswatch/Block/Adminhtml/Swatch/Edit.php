<?php
class Astral_Optionswatch_Block_Adminhtml_Swatch_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {	
	
	public function __construct(){
		parent::__construct();
    	$this->_controller = 'adminhtml_swatch';
   	    $this->_blockGroup = 'optionswatch';
   	    $this->_headerText = Mage::helper('optionswatch')->__('Edit Option Information');
    }
    
    public function getHeaderText() {
    	return Mage::helper('optionswatch')->__('Product Attribute Option Information');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
}