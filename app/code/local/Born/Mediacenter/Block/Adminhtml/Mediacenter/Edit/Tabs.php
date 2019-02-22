<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Mediacenter_Edit_Tabs
 */
class Born_Mediacenter_Block_Adminhtml_Mediacenter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('mediacenter_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('mediacenter')->__('Mediacenter'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => Mage::helper('mediacenter')->__('General'),
            'title' => Mage::helper('mediacenter')->__('General'),
            'content' => $this->getLayout()->createBlock('mediacenter/adminhtml_mediacenter_edit_tab_general')->toHtml()
        ));

        $this->addTab('subsections', array(
            'label' => Mage::helper('mediacenter')->__('Subsections'),
            'title' => Mage::helper('mediacenter')->__('Subsections'),
            'content' => $this->getLayout()->createBlock('mediacenter/adminhtml_mediacenter_edit_tab_subsection')->toHtml()
        ));

        return parent::_beforeToHtml();
    }
}