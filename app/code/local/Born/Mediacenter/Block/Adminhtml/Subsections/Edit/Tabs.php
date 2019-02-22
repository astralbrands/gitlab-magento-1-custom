<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tabs
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('subsections_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('mediacenter')->__('Subsections'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => Mage::helper('mediacenter')->__('General'),
            'title' => Mage::helper('mediacenter')->__('General'),
            'content' => $this->getLayout()->createBlock('mediacenter/adminhtml_subsections_edit_tab_general')->toHtml()
        ));

        $this->addTab('media_upload', array(
            'label' => Mage::helper('mediacenter')->__('Media Upload'),
            'title' => Mage::helper('mediacenter')->__('Media Upload'),
            'content' => $this->getLayout()->createBlock('mediacenter/adminhtml_subsections_edit_tab_uploadmedia')->toHtml()
        ));

        $this->addTab('assign_media', array(
            'label' => Mage::helper('mediacenter')->__('Manage Media'),
            'title' => Mage::helper('mediacenter')->__('Manage Media'),
            'url' => $this->getUrl('*/*/gallerygrid', array('_current' => true)),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }
}