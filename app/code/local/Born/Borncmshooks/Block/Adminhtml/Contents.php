<?php
class Born_Borncmshooks_Block_Adminhtml_Contents
	extends Mage_Adminhtml_Block_Abstract
	{
		//    These two methods are invoked outside of the layout
	    protected function _construct() {
	        $this->setTemplate('borncmshooks/contents.phtml');
	        parent::_construct();
	    }
	}