<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Tab_Rowsgrid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {

      parent::__construct();
      $this->setId('formGrid');
      $this->setUseAjax(true);
      $this->setDefaultSort('row_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(false);
      }

  protected function _prepareCollection()
  {
      $field = Mage::getSingleton('core/resource')->getTableName('born_cms_hook_fields');

       $collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('eq' => $this->getRequest()->getParam('id')));
       $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('row_id', array(
          'header'    => Mage::helper('borncmshooks')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'row_id',
          'sortable'      => true,
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('borncmshooks')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
          'sortable'      => true,
      ));
      
      
      $this->addColumn('code', array(
          'header'    => Mage::helper('borncmshooks')->__('Code'),
          'align'     =>'left',
          'index'     => 'code',
          'sortable'      => true,
      ));

      if (!Mage::app()->isSingleStoreMode()) {
          $this->addColumn('store_id', array(
              'header'        => Mage::helper('borncmshooks')->__('Store View'),
              'index'         => 'store_id',
              'type'          => 'store',
              'store_all'     => true,
              'store_view'    => true,
              'sortable'      => true,
              'filter_condition_callback' => array($this,
                  '_filterStoreCondition'),
          ));
      }      
      
      $this->addColumn('start_date', array(
          'header'    => Mage::helper('borncmshooks')->__('Active From'),
          'align'     =>'left',
          'index'     => 'start_date',
          'sortable'      => true,
      ));
      $this->addColumn('end_date', array(
          'header'    => Mage::helper('borncmshooks')->__('Active To'),
          'align'     =>'left',
          'index'     => 'end_date',
          'sortable'      => true,
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('borncmshooks')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
		
		 $this->addExportType('*/*/exportCsv', Mage::helper('borncmshooks')->__('CSV'));
		 $this->addExportType('*/*/exportXml', Mage::helper('borncmshooks')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/editrow', array('id' => $row->getId(), 'hookid' => $row->getHookId()));
  }

  public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/filteredformgrid', array('_current'=>true));
    }
    
    protected function _filterStoreCondition($collection, $column){
    if (!$value = $column->getFilter()->getValue()) {
        return;
    }
    $this->getCollection()->addStoreFilter($value);
}

}