<?php 

class Born_Package_Block_Adminhtml_System_Config_Form_Field_Links extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('title', array(
            'label' => Mage::helper('adminhtml')->__('Title'),
            'style' => 'width:200px',
        ));
        $this->addColumn('url_key', array(
            'label' => Mage::helper('adminhtml')->__('Url Key'),
            'style' => 'width:200px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Links');
        parent::__construct();
    }
}


 ?>