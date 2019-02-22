<?php

class Born_BornIntegration_Block_Adminhtml_System_Config_Sage extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {
    public function _prepareToRender() {
        $this->addColumn('store_code', array(
            'label' => Mage::helper('bornintegration')->__('Store Code'),
            'style' => 'width:100px',
        ));
        $this->addColumn('company_code', array(
            'label' => Mage::helper('bornintegration')->__('X3 Company'),
            'style' => 'width:100px',
        ));
        $this->addColumn('order_type', array(
            'label' => Mage::helper('bornintegration')->__('X3 Order Type'),
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('bornintegration')->__('Add');
    }
}