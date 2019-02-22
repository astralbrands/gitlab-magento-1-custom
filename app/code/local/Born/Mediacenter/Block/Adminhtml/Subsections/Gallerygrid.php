<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Gallerygrid
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Gallerygrid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('galleryGrid');
        $this->setUseAjax(true); // Using ajax grid is important
        $this->setDefaultSort('id');
        $this->setRowClickCallback(false);
        $this->setDefaultFilter(array('id'=>1)); // By default we have added a filter for the rows, that in_products value to be 1
        $this->setSaveParametersInSession(false);  //Dont save paramters in session or else it creates problems
    }


    protected function _getSelectedGallery()   // Used in grid to return selected customers values.
    {
        $customers = array_keys($this->getSelectedGallery());
        return $customers;
    }

    public function getSelectedGallery()
    {
        // Customer Data
        $tm_id = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('mediacenter/images')->getCollection()->addFieldToSelect('id')->addFieldToFilter('parent_id', $tm_id);

        if (!isset($tm_id)) {
            $tm_id = 0;
        }
        $images = $collection->getData(); // This is hard-coded right now, but should actually get values from database.
        $custIds = array();

        foreach ($images as $image) {
            $custIds[$image['id']] = array('position' => $image['id']);
        }

        return $custIds;
    }

     public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/gallerygrid', array('_current' => true));
    } 

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('mediacenter/images_collection')->addFieldToFilter('parent_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {


        /* $this->addColumn('id', array(
				'header'            => Mage::helper('catalog')->__('Id'),
                'header_css_class'  => 'a-center',
                'type'              => 'checkbox',
                'align'             => 'center',
                'index'             => 'id'               
		)); 
                 $this->addColumn('action',
            array(
                'header'    => Mage::helper('mediacenter')->__('Action'),
                'width'     => '40px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('mediacenter')->__('View'),
                        'url'     => array('base'=>''*'/adminhtml_subsection/edit'),
                        'field'   => 'id',
                        'data-column' => 'action',
                    )
                )
               
        ));  */
        $this->addColumn('assign', array(
            'header' => Mage::helper('mediacenter')->__('Assigned'),
            'header_css_class' => 'a-center',
            'width' => '50px',
            'type' => 'options',
            'align' => 'center',
            'index' => 'assign',
            'options' => array(1 => "yes", 0 => "No"),
            'renderer' => 'Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Assign',
        ));
        $this->addColumn('grid_file_name', array(
            'header' => Mage::helper('mediacenter')->__('File Name'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'file_name',
        ));
        $this->addColumn('update_name', array(
            'header' => Mage::helper('mediacenter')->__('Name'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'name',
            'renderer' => 'Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Inline',
        ));
		$groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionArray();
        $this->addColumn('update_customer_group', array(
            'header' => Mage::helper('mediacenter')->__('Customer Groups'),
            'width' => '150px',
            'type' => 'options',
            'index' => 'media_customer_group',
			'values' => $groups,
            'renderer' => 'Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Multiselect',
			'filter' => false
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('mediacenter')->__('Type of file'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'type',
        ));
/*         $this->addColumn('type_logo', array(
            'header' => Mage::helper('mediacenter')->__('Logo'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'type',
            'filter' => false,
            'renderer' => 'Born_Mediacenter_Block_Adminhtml_Subsections_Renderer_Red',
        ));
 */
        /* 		$this->addColumn('position', array(
                    'header'            => Mage::helper('catalog')->__('Position'),
                    'name'              => 'position',
                    'width'             => 60,
                    'type'              => 'number',
                    'validate_class'    => 'validate-number',
                    'index'             => 'position',
                    'editable'          => true,
                    'edit_only'         => true
                )); */

        $this->addExportType('*/*/exportCsv', Mage::helper('mediacenter')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('mediacenter')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('grid_id');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('mediacenter')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete', array('' => '')),
            'confirm' => Mage::helper('mediacenter')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('assign', array(
            'label' => Mage::helper('mediacenter')->__('Assign'),
            'url' => $this->getUrl('*/*/massAssign', array('' => '')),
            'confirm' => Mage::helper('mediacenter')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('unassign', array(
            'label' => Mage::helper('mediacenter')->__('Unassign'),
            'url' => $this->getUrl('*/*/massUnassign', array('' => '')),
            'confirm' => Mage::helper('mediacenter')->__('Are you sure?')
        ));

        return parent::_prepareMassaction();;
    }

    public function getRowUrl($row)
    {
        //return false;
        return $this->getUrl('*/adminhtml_subsection/edit', array('id' => $row->getId()));
    }

    protected function getAdditionalJavascript()
    {
        return 'window.galleryGrid_massactionJsObject = galleryGrid_massactionJsObject;';
    }
}
