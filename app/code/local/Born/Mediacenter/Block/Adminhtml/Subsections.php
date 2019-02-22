<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections
 */
class Born_Mediacenter_Block_Adminhtml_Subsections extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {

        $this->_controller = 'adminhtml_subsections';
        $this->_blockGroup = 'mediacenter';
        $this->_headerText = Mage::helper('mediacenter')->__('Subsection/Media Manager');
        $this->_addButtonLabel = Mage::helper('mediacenter')->__('Add');

        parent::__construct();

    }

}
