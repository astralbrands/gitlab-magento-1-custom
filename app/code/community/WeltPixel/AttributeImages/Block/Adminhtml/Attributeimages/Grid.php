<?php

class WeltPixel_AttributeImages_Block_Adminhtml_Attributeimages_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('weltpixel_attributeimages_grid');
        $this->setDefaultSort('attribute_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }


    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addVisibleFilter();
//        ->addFieldToFilter('enable_layered_swatch', 1);
        
        $attributeTable = Mage::getSingleton('core/resource')->getTableName('weltpixel_attributeimages/attributeimages');
        $collection->getSelect()->joinLeft(array("t1" => $attributeTable), "main_table.attribute_id = t1.attribute_id", array("attribute_image" => "t1.attribute_image"));

        //echo $collection->getSelectSql(true);die;
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $helper = Mage::helper('weltpixel_attributeimages');
        
        $this->addColumn('attribute_id', array(
            'header' => $helper->__('Attribute Id'),
            'index'  => 'attribute_id',
            'filter_index' => 'main_table.attribute_id',
        ));

       $this->addColumn('attribute_code', array(
            'header'=> $helper->__('Attribute Code'),
            'sortable'=>true,
            'index'=>'attribute_code',
            'filter_index' => 'main_table.attribute_code',
        ));
       
       $this->addColumn('attribute_image', array(
            'header'=> $helper->__('Attribute Image'),
            'sortable'=> false,
            'filter' => false,
            'index' => 'attribute_image',
            'renderer'  => 'WeltPixel_AttributeImages_Block_Adminhtml_Attributeimages_Grid_Renderer_Image'
        ));



        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'=>$row->getAttributeId())
        );
    }

}