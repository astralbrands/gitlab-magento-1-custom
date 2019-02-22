<?php

class WeltPixel_Selector_Model_Adminhtml_System_Config_Pagelayout
{

    protected $_options = null;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = Mage::getSingleton('page/source_layout')->toOptionArray();
        }

        return $this->_options;
    }

}