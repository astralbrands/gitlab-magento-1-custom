<?php

class Born_Package_Block_Adminhtml_Sales_Order_Create_Customer_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid {
    protected function _prepareColumns()
    {
        $grid = parent::_prepareColumns();


        if (!Mage::app()->isSingleStoreMode()) {
            $grid->removeColumn('store_name');
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }
        return $this;
    }
}