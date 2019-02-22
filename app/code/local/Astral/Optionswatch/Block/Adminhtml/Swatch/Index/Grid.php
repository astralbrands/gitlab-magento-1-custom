<?php
class Astral_Optionswatch_Block_Adminhtml_Swatch_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('optionswatchGrid'); 
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection(){
        $collection = Mage::getModel('optionswatch/swatch')->getCollection();
		$this->setCollection($collection);
        return parent::_prepareCollection();
    }

//    protected function _getStore(){
//        $storeId = (int) $this->getRequest()->getParam('store', 0);
//        return Mage::app()->getStore($storeId);
//    }

    protected function _prepareColumns(){        
        $this->addColumn('id', array(
            'header'    => Mage::helper('optionswatch')->__('ID'),
            'sortable'  => true,
            'width'     => '20',
            'index'     => 'id'
        ));
        $this->addColumn('default_option', array(
            'header'    => Mage::helper('optionswatch')->__('Default'),
            'sortable'  => true,
            'width'     => '20',
            'index'     => 'default_option'
        ));
        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('optionswatch')->__('Sort Order'),
            'sortable'  => true,
            'width'     => '20',
            'index'     => 'sort_order'
        ));        
        
        $this->addColumn('image_file', array(
                  'header'    => Mage::helper('optionswatch')->__('Option Image'),
                  'align'     =>'left',
                  'index'     => 'image_file',
            'width'     => '50',
                  'renderer'  => 'optionswatch/adminhtml_swatch_renderer_image',
                  'attr1'     => 'value1'
        ));

        $this->addColumn('filter_image_file', array(
                  'header'    => Mage::helper('optionswatch')->__('Option Filter Image'),
                  'align'     =>'left',
                  'index'     => 'filter_image_file',
            'width'     => '50',
                  'renderer'  => 'optionswatch/adminhtml_swatch_renderer_image',
                  'attr1'     => 'value1'
        )); 
		
       $this->addColumn('attribute_code', array(
            'header'    => Mage::helper('optionswatch')->__('Attribute Code'),
             'index'         => 'attribute_code',
           'width'     => '100',
            'sortable'  => true
        ));        
        
        
         $this->addColumn('option_value', array(
            'header'    => Mage::helper('optionswatch')->__('Option Name'),
             'index'         => 'option_value',
            'sortable'  => true,
         	'searchable' =>false
        ));
		
		//color code
		$this->addColumn('color_code', array(
            'header'    => Mage::helper('optionswatch')->__('Color Code'),
             'index'         => 'color_code',
            'sortable'  => true,
         	'searchable' =>false
        ));

        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('optionswatch')->__('Product Sku'),
            'index'         => 'product_sku',
            'sortable'  => true,
            'width'     => '100',
            'searchable' =>false
            ));


       $this->addColumn('created_at', array(
            'header'        => Mage::helper('optionswatch')->__('Created At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'created_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('optionswatch')->__('Updated At'),
            'align'         => 'center',
            'width'         => '150px',
            'index'         => 'updated_at',
        	'type'      	=> 'datetime',
            'gmtoffset' 	=> true
        ));
        
        $this->addColumn('status', array(
            'header'        => Mage::helper('optionswatch')->__('Status'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'status',
            'type'			=> 'options',
             'options' => array('1'=>'Enable','0'=>'Disable'),
        	'sortable'  => false
        ));        
                
        $this->addColumn('action',
		array(
                'header'    => Mage::helper('optionswatch')->__('Action'),
		        'width'     => '100px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
						array(
                        'caption' => Mage::helper('optionswatch')->__('Edit'),
                        'url'  => 	array(
                                		'base'=>'optionswatch/adminhtml_swatch/edit'
									),					
                        'field'   => 'id'
                        ),
                        array(
                        'caption' => Mage::helper('optionswatch')->__('Delete'),
                        'url'     => array(
                                		'base'=>'optionswatch/adminhtml_swatch/delete'
									),	
                        'field'   => 'id',
                        'confirm'	=> 'Are you sure?'
                        )
                        ),
			                'filter'    => false,
			                'sortable'  => false,
                            'is_system' => true                      	
                        ));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
    
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}
    
}