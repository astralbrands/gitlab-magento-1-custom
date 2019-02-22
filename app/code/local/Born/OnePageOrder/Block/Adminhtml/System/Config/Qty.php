<?php
class Born_OnePageOrder_Block_Adminhtml_System_Config_Qty extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function _prepareToRender() {
        $this->addColumn('qty_from', array(
            'label' => Mage::helper('bornintegration')->__('Qty From'),
            'style' => 'width:100px',
        ));
        $this->addColumn('qty_to', array(
            'label' => Mage::helper('bornintegration')->__('Qty To'),
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('bornintegration')->__('Add');
    }
}

