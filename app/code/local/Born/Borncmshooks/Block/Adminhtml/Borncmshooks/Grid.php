<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('borncmshooksGrid');
      $this->setDefaultSort('borncmshooks_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('borncmshooks/borncmshooks')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('hook_id', array(
          'header'    => Mage::helper('borncmshooks')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'hook_id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('borncmshooks')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
      
      $this->addColumn('code', array(
          'header'    => Mage::helper('borncmshooks')->__('Code'),
          'align'     =>'left',
          'index'     => 'code',
      ));
      
      $this->addColumn('type', array(
          'header'    => Mage::helper('borncmshooks')->__('Type'),
          'align'     =>'left',
          'index'     => 'type',
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
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('borncmshooks')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('borncmshooks')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

		$this->addExportType('*/*/exportXml', Mage::helper('borncmshooks')->__('XML'));

      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('borncmshooks_id');
        $this->getMassactionBlock()->setFormFieldName('borncmshooks');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('borncmshooks')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('borncmshooks')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('export', array(
            'label'    => Mage::helper('borncmshooks')->__('Export'),
            'url'      => $this->getUrl('*/*/massExport')
        ));

        $statuses = Mage::getSingleton('borncmshooks/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('borncmshooks')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('borncmshooks')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

    /*
    protected function _prepareLayout()
    {
        $this->setChild('import_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Import'),
                    'onclick'   => $this->getJsObjectName().'.doimport()',
                    'class'   => 'task'
                ))
        );
        parent::_prepareLayout();
    }
*/

   public function getRowUrl($row)
   {
       return $this->getUrl('*/*/edit', array('id' => $row->getId()));
   }

    public function getMainButtonsHtml()
    {
        $html = '<form action="'.Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/import").'" method="post" enctype="multipart/form-data">';
        $html .= '<input name="form_key" type="hidden" value="'.Mage::getSingleton('core/session')->getFormKey().'" />';
        $html .= '<input type="file" name="import_file" />';
        $html .= '<button onclick="this.form.submit();"><span><span><span>Import</span></span></span></button>';
        $html .= '</form>';

        //$html .= $this->getChildHtml('import_button');
        $html .= parent::getMainButtonsHtml();
        return $html;
    }

    public function getXml()
    {
        $borncmshooksIds = Mage::registry('borncmshooksIds');

        $hookIds = array();

        $xml ='';
        $xml.= '<?xml version="1.0" encoding="UTF-8"?>';

        $xml .= '<root>';

        //output data as xml
        if ($borncmshooksIds)
            $collection = Mage::getModel('borncmshooks/borncmshooks')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
        else
            $collection = Mage::getModel('borncmshooks/borncmshooks')->getCollection();
        $xml.= '<borncmshooks><items>';
        foreach ($collection as $row){
            $hookIds[] = $row->getHookId();
            $xml.= $row->toXml();
        }
        $xml.='</items></borncmshooks>';

        $collection = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('in' => $hookIds));
        $xml.= '<sections><items>';
        foreach ($collection as $row){
            $xml.= $row->toXml();
        }
        $xml.='</items></sections>';

        $collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('hook_id', array('in' => $hookIds));
        $xml.= '<fields><items>';
        foreach ($collection as $row){
            $xml.= $row->toXml();
        }
        $xml.='</items></fields>';

        $collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('hook_id', array('in' => $hookIds));
        $xml.= '<forms><items>';
        foreach ($collection as $row){
            $xml.= $row->toXml();
        }
        $xml.='</items></forms>';

        $collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('hook_id', array('in' => $hookIds));
        $xml.= '<types><items>';
        foreach ($collection as $row){
            $xml.= $row->toXml();
        }
        $xml.='</items></types>';

        $collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('in' => $hookIds));
        $xml.= '<rows><items>';
        foreach ($collection as $row){
            $xml.= $row->toXml();
        }
        $xml.='</items></rows>';

        $collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('in' => $hookIds));
        $xml.= '<values><items>';
        foreach ($collection as $row){
            $xml.= $row->toXml();
        }
        $xml.='</items></values>';

        $xml .= '</root>';
        return $xml;
    }
}