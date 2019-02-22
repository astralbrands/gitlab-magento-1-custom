<?php
class Born_Borncmshooks_Block_Adminhtml_Sections
	extends Mage_Adminhtml_Block_Abstract
	{
		//    These two methods are invoked outside of the layout
	    protected function _construct() {
	        $this->setTemplate('borncmshooks/sections.phtml');
	        parent::_construct();
	    }
	}