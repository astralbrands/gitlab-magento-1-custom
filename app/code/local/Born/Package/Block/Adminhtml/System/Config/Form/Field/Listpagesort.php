<?php 

class Born_Package_Block_Adminhtml_System_Config_Form_Field_Listpagesort extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('code', array(
            'label' => Mage::helper('adminhtml')->__('code'),
            'style' => 'width:100px',
        ));
        $this->addColumn('direction', array(
            'label' => Mage::helper('adminhtml')->__('Direction'),
            'style' => 'width:50px',
            ));
        $this->addColumn('label', array(
            'label' => Mage::helper('adminhtml')->__('Label'),
            'style' => 'width:150px',
            ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Item');
        parent::__construct();
    }
}

?>