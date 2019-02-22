<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Editform_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('borncmshooksrow_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('borncmshooks')->__('Row Information'));
  }

  protected function _beforeToHtml()
  {

      $this->addTab('row_section', array(
          'label'     => Mage::helper('borncmshooks')->__('Row Information'),
          'title'     => Mage::helper('borncmshooks')->__('Row Information'),
          'content'   => $this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_editform_tab_row')->toHtml(),
          'active'    => true,
      ));
     
     if($this->getRequest()->getParam('id')){
         $row_info = Mage::getModel('borncmshooks/rows')->load($this->getRequest()->getParam('id'));
         $given_values = array('id' => $row_info->getRowId(),
                              'hook_id' => $row_info->getHookId(),
                              'section_id' => $row_info->getSectionId(),
                              'field_id' => $row_info->getFieldId(),
                              'form_id' => $row_info->getFormId());
         
         $this->addTab('form_section', array(
              'label'     => Mage::helper('borncmshooks')->__('Row Details'),
              'title'     => Mage::helper('borncmshooks')->__('Row Details'),
              'url'       => $this->getUrl('borncmshooks/adminhtml_borncmshooks/showformelements/', $given_values),
              'class'     => 'ajax',
              'active'    => true,
            ));
     }else{
         $this->addTab('form_section', array(
            'label'     => Mage::helper('borncmshooks')->__('Row Details'),
            'title'     => Mage::helper('borncmshooks')->__('Row Details'),
            'content'   => $this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_editform_tab_form')->toHtml(),
           ));
     }

      return parent::_beforeToHtml();
  }
}