<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xcoupon
 */
class Amasty_Xcoupon_Block_Adminhtml_Coupons extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amxcouponCoupons');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $id = $this->getRequest()->getParam('id');
        
        $coupons = Mage::getResourceModel('salesrule/coupon_collection')
            ->addFieldToFilter('rule_id', $id);
            
        $select = $coupons->getSelect();
        $select->where('is_primary IS NULL');
   
        $this->setCollection($coupons);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header'    => Mage::helper('salesrule')->__('Coupon'),
            'index'     => 'code',
        ));

        $this->addColumn('usage_limit', array(
            'header'    => Mage::helper('salesrule')->__('Uses per Coupon'),
            'index'     => 'usage_limit',
        ));

        $this->addColumn('usage_per_customer', array(
            'header'    => Mage::helper('salesrule')->__('Uses per Customer'),
            'index'     => 'usage_per_customer',
        ));

        $this->addColumn('times_used', array(
            'header'    => Mage::helper('salesrule')->__('Times Used'),
            'index'     => 'times_used',
        ));
        
        $this->addColumn('action', array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Delete'),
                        'url'     => array('base'=>'*/*/delete'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
        )); 
        
        $this->addExportType('*/*/exportCsv', Mage::helper('salesrule')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('salesrule')->__('XML'));
                
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        return $this->getUrl('*/amxcoupon_coupon/edit', array('id' => $row->getId()));
    }
      
}