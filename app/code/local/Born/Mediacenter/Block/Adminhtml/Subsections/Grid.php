<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Grid
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('born_subsection_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('mediacenter/subsections_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('mediacenter')->__('#'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'entity_id',
        ));

        $this->addColumn('subsection_name', array(
            'header' => Mage::helper('mediacenter')->__('Subsection'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'subsection_name',
        ));


        $this->addColumn('action',
            array(
                'header' => Mage::helper('mediacenter')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('mediacenter')->__('View'),
                        'url' => array('base' => '*/adminhtml_subsection/edit'),
                        'field' => 'id',
                        'data-column' => 'action',
                    )
                )

            ));


        $this->addExportType('*/*/exportCsv', Mage::helper('mediacenter')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('mediacenter')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $this->getMassactionBlock()->setUseSelectAll(false);

        return $this;
    }

    public function getRowUrl($row)
    {

        return $this->getUrl('*/adminhtml_subsection/edit', array('id' => $row->getId()));


    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}
