<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Mediacenter
 */
class Born_Mediacenter_Block_Adminhtml_Mediacenter extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {

        $this->_controller = 'adminhtml_mediacenter';
        $this->_blockGroup = 'mediacenter';
        $this->_headerText = Mage::helper('mediacenter')->__('Mediacenter Manager');
        $this->_addButtonLabel = Mage::helper('mediacenter')->__('Add');

        parent::__construct();

    }


}
