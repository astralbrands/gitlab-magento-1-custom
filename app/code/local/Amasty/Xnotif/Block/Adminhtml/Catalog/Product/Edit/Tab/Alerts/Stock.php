<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */ 
class Amasty_Xnotif_Block_Adminhtml_Catalog_Product_Edit_Tab_Alerts_Stock extends  Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts_Stock
{
    public function __construct()
    {
        parent::__construct();
        $this->setMassactionBlockName('amxnotif/adminhtml_catalog_product_edit_tab_alerts_stock_massaction');
    }

    protected function _prepareColumns()
    {
        /* using default translation*/
        $hlp = Mage::helper('catalog');
        $this->addColumn('firstname', array(
            'header'    => $hlp->__('First Name'),
            'index'     => 'firstname',
            'renderer'  => 'amxnotif/adminhtml_catalog_product_edit_tab_alerts_renderer_firstName',    
        ));

        $this->addColumn('lastname', array(
            'header'    => $hlp->__('Last Name'),
            'index'     => 'lastname',
            'renderer'  => 'amxnotif/adminhtml_catalog_product_edit_tab_alerts_renderer_lastName',
        ));

        $this->addColumn('email', array(
            'header'    => $hlp->__('Email'),
            'index'     => 'email',
            'renderer'  => 'amxnotif/adminhtml_catalog_product_edit_tab_alerts_renderer_email',
        ));

        $this->addColumn('add_date', array(
            'header'    => $hlp->__('Date Subscribed'),
            'index'     => 'add_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_date', array(
            'header'    => $hlp->__('Last Notification'),
            'index'     => 'send_date',
            'type'      => 'date'
        ));

        $this->addColumn('send_count', array(
            'header'    => $hlp->__('Send Count'),
            'index'     => 'send_count',
        ));

        $this->addColumn('action', array(
            'header'    => $hlp->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getAlertStockId',
            'actions'   => array(
                array(
                    'caption' => $hlp->__('Remove'),
                    'url'     => array(
                        'base'=>'adminhtml/amstock/delete',
                        'params'=>array('store'     => $this->getRequest()->getParam('store'))
                    ),
                    'field'   => 'alert_stock_id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'alert_stock_id',
        ));
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $hlp = Mage::helper('amxnotif');
        $this->setMassactionIdField('add_date');
        $this->getMassactionBlock()->addItem('subscription', array(
            'label'         => $hlp->__('Add Subscription'),
            'url'           => $this->getUrl('adminhtml/amstock/addbyemail',
                array(
                    'product_id' => $this->getRequest()->getParam('id'),
                    'store_id' => $this->getRequest()->getParam('store')
                )
            ),
            'additional'    => array('subscription_email' => array(
                'name'  => 'subscription_email',
                'type'  => 'text',
                'label' => $hlp->__('by Email:'),
            )),
        ));
        return $this;
    }

    protected function _prepareMassactionColumn()
    {
        $columnId = 'massaction';
        $massactionColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
            ->setData(array(
                'index'        => $this->getMassactionIdField(),
                'filter_index' => $this->getMassactionIdFilter(),
                'type'         => 'massaction',
                'name'         => $this->getMassactionBlock()->getFormFieldName(),
                'align'        => 'center',
                'is_system'    => true,
                //add no-display
                 'column_css_class'=>'no-display',
                 'header_css_class'=>'no-display',
            ));

        if ($this->getNoFilterMassactionColumn()) {
            $massactionColumn->setData('filter', false);
        }

        $massactionColumn->setSelected($this->getMassactionBlock()->getSelected())
            ->setGrid($this)
            ->setId($columnId);

        $oldColumns = $this->_columns;
        $this->_columns = array();
        $this->_columns[$columnId] = $massactionColumn;
        $this->_columns = array_merge($this->_columns, $oldColumns);
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/amstock/grid', array('_current'=>true));
    }
}
  
