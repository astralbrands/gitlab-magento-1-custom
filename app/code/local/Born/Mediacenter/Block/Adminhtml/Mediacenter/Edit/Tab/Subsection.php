<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Mediacenter_Edit_Tab_Subsection
 */
class Born_Mediacenter_Block_Adminhtml_Mediacenter_Edit_Tab_Subsection
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mediacenter_subsection');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'mediacenter/subsections_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->addFieldToSelect(array('subsection_id' => 'entity_id'))
            //->addFieldToSelect('created_at')
            ->addFieldToSelect('subsection_name')
            //->addFieldToSelect('is_active')            
            // ->setOrderFilter($this->getOrder())
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('subsection_id', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            // 'name'      => 'subsections',
            'values' => $this->_getSelectedSubsection(),
            'align' => 'center',
            'index' => 'subsection_id',
            'field_name' => 'subsection_id[]',
        ));


        /*$this->addColumn('subsection_id', array(
            'header'    => Mage::helper('mediacenter')->__('ID #'),
            'index'     => 'subsection_id',
            'width'     => '120px',
        ));*/


        $this->addColumn('subsection_name', array(
            'header' => Mage::helper('sales')->__('Subsection Name'),
            'index' => 'subsection_name',
        ));


        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    // public function getRowUrl($row)
    // {
    // return $this->getUrl('*/sales_order_invoice/view',
    // array(
    // 'invoice_id'=> $row->getId(),
    // 'order_id'  => $row->getOrderId()
    // )
    // );
    // }
// 
    // public function getGridUrl()
    // {
    // return $this->getUrl('*/*/invoices', array('_current' => true));
    // }

    protected function _getSelectedSubsection()
    {

        return Mage::registry('mediacenter_data')->getSubsectionId();
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('mediacenter')->__('Subsections');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Subsections');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
