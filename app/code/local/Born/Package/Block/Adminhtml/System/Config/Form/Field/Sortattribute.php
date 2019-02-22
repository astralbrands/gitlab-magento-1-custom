<?php 

class Born_Package_Block_Adminhtml_System_Config_Form_Field_Sortattribute extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('attribute_code', array(
            'label' => Mage::helper('adminhtml')->__('Attribute Code'),
            'style' => 'width:250px',
            ));
        $this->addColumn('sort_order', array(
            'label' => Mage::helper('adminhtml')->__('Sort Order'),
            'style' => 'width:50px',
            ));
        $this->addColumn('max_items', array(
            'label' => Mage::helper('adminhtml')->__('Max Items'),
            'style' => 'width:50px',
            ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Item');
        parent::__construct();
    }
}


?>