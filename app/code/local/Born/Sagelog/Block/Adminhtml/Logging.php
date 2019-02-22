<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Mediacenter
 */
class Born_Sagelog_Block_Adminhtml_Logging extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_logging';
        $this->_blockGroup = 'sagelog';
        $this->_headerText = Mage::helper('sagelog')->__('Error log');
        parent::__construct();
		$this->_removeButton('add');

    }


}
