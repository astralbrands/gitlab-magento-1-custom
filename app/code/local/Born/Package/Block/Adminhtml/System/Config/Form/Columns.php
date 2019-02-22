<?php 
class Born_Package_Block_Adminhtml_System_Config_Form_Columns extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('csv_column', array(
            'label' => Mage::helper('adminhtml')->__('CSV Column'),
            'style' => 'width:120px',
        ));
        $this->addColumn('product_attribute', array(
            'label' => Mage::helper('adminhtml')->__('Product Attribute'),
            'style' => 'width:120px',
        ));
		$this->addColumn('sort_order', array(
            'label' => Mage::helper('adminhtml')->__('Sort Order'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Column');
        parent::__construct();
    }
}
