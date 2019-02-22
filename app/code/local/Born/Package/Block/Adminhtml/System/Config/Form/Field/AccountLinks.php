<?php 

class Born_Package_Block_Adminhtml_System_Config_Form_Field_AccountLinks extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('nav_path', array(
            'label' => Mage::helper('adminhtml')->__('Navigation Item Path'),
            'style' => 'width:200px',
        ));
        $this->addColumn('page_path', array(
            'label' => Mage::helper('adminhtml')->__('Page Path'),
            'style' => 'width:200px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Item');
        parent::__construct();
    }
}


 ?>