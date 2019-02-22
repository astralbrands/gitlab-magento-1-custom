<?php

class WeltPixel_Selector_Block_Head extends Mage_Core_Block_Template {

    protected function _prepareLayout() {
        $headBlock = $this->getLayout()->getBlock('head');

        $stickyHeader = Mage::helper('weltpixel_selector')->applyStickyHeader();
        
        if ($stickyHeader) {
            $headBlock->addItem('skin_js','js/weltpixel/sticky-header.js');
        }
        
        return parent::_prepareLayout();
    }

}