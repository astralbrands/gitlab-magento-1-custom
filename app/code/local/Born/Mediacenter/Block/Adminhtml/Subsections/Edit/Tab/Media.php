<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tab_Media
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tab_Media
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mediacenter_subsection');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
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

    public function getTabLabel()
    {
        return Mage::helper('mediacenter')->__('Media');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Media');
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
