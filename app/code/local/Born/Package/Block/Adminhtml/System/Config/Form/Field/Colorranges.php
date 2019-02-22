<?php 

class Born_Package_Block_Adminhtml_System_Config_Form_Field_Colorranges extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('code', array(
            'label' => Mage::helper('adminhtml')->__('Color Code'),
            'style' => 'width:100px',
        ));
        $this->addColumn('label', array(
            'label' => Mage::helper('adminhtml')->__('Label'),
            'style' => 'width:150px',
        ));
        $this->addColumn('color_hex', array(
            'label' => Mage::helper('adminhtml')->__('Color Hex'),
            'style' => 'width:150px',
            ));
        $this->addColumn('sort_order', array(
            'label' => Mage::helper('adminhtml')->__('Sort Order'),
            'style' => 'width:50px',
            ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Item');
        parent::__construct();
    }
}


 ?>