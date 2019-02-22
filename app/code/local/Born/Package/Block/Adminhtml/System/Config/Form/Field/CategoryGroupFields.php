<?php 

class Born_Package_Block_Adminhtml_System_Config_Form_Field_Categorygroupfields extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('group_code', array(
            'label' => Mage::helper('adminhtml')->__('Group Code'),
            'style' => 'width:150px',
        ));
        $this->addColumn('group_title', array(
            'label' => Mage::helper('adminhtml')->__('Group Title'),
            'style' => 'width:150px',
        ));
        $this->addColumn('value_id', array(
            'label' => Mage::helper('adminhtml')->__('Value Id'),
            'style' => 'width:50px',
            ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Item');
        parent::__construct();
    }
}


 ?>