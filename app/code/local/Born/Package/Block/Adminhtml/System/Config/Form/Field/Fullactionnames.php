<?php 

class Born_Package_Block_Adminhtml_System_Config_Form_Field_Fullactionnames extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('names', array(
            'label' => Mage::helper('adminhtml')->__('Full Action Names'),
            'style' => 'width:400px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Item');
        parent::__construct();
    }
}


 ?>