<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Mediacenter_Grid
 */
class Born_Sagelog_Block_Adminhtml_Logging_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('born_sagelog_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('sagelog/logging_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('sagelog')->__('Id'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'entity_id',
        ));
        $this->addColumn('order_id', array(
            'header' => Mage::helper('sagelog')->__('Order Increment Id'),
            'width' => '100px',
            'type' => 'text',
            'index' => 'order_id',
        ));
        $this->addColumn('type', array(
            'header' => Mage::helper('sagelog')->__('Error Type'),
            'width' => '100px',
            'type' => 'text',
            'index' => 'error_type',
        ));
        $this->addColumn('error_message', array(
            'header' => Mage::helper('sagelog')->__('Error Message'),
            'width' => '400px',
            'type' => 'text',
            'index' => 'error_message',
        ));
        $this->addColumn('incident_date', array(
            'header' => Mage::helper('sagelog')->__('Updated at'),
            'width' => '100px',
            'type' => 'datetime',
            'index' => 'incident_date',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sagelog')->__('CSV'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('sagelog')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete', array('' => '')),
            'confirm' => Mage::helper('sagelog')->__('Are you sure?')
        ));

        return parent::_prepareMassaction();
    }

    public function getRowUrl($row) {

        return $this->getUrl('*/adminhtml_sageerrorlog/view', array('id' => $row->getId()));
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/', array('_current' => true));
    }

}
