<?php

class WeltPixel_AttributeImages_Block_Adminhtml_Attributeimages extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_blockGroup = 'weltpixel_attributeimages';
        $this->_controller = 'adminhtml_attributeimages';
        $this->_headerText = Mage::helper('weltpixel_attributeimages')->__('Add images to your attributes');

        parent::__construct();
        $this->_removeButton('add');
    }

}