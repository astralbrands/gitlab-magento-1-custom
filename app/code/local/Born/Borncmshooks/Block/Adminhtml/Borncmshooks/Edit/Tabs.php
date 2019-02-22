<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('borncmshooks_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('borncmshooks')->__('Hook Information'));
  }

  protected function _beforeToHtml()
  {
      $id = $this->getRequest()->getParam('id');
      $admin_user_email_domain = false;
      try {
          $admin_user_role = Mage::getSingleton('admin/session')->getUser()->getRole()->getRoleName();
          $admin_user_email = explode("@",Mage::getSingleton('admin/session')->getUser()->getEmail(),2);
          $admin_user_email_domain = $admin_user_email[1];
      } catch (Exception $exc) {
          $admin_user_email = true;
      }
//      if(($admin_user_email_domain == 'born.com')){
            $this->addTab('form_section', array(
                'label'     => Mage::helper('borncmshooks')->__('Hook Information'),
                'title'     => Mage::helper('borncmshooks')->__('Hook Information'),
                'content'   => $this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_form')->toHtml(),
            ));
//      }
     
      if(isset($id)){
          $hook_name = Mage::getModel('borncmshooks/borncmshooks')->load($id)->getName();
//          if(($admin_user_email_domain == 'born.com')){
              $this->addTab('section_manage_section', array(
                'label'     => Mage::helper('borncmshooks')->__('Hook Structure'),
                'title'     => Mage::helper('borncmshooks')->__('Hook Structure'),
                'content'   => $this->getLayout()->createBlock('borncmshooks/adminhtml_sections')->toHtml(),
              ));
//          }
          
          $this->addTab('form_grid', array(
              'label'     => Mage::helper('borncmshooks')->__($hook_name . ' Content'),
              'title'     => Mage::helper('borncmshooks')->__($hook_name . ' Content'),
              'url'       => $this->getUrl('borncmshooks/adminhtml_borncmshooks/formgrid/', array('_current' => true, 'form_id' => 'content', 'field_id' => 'content')),
              'class'     => 'ajax',
              'active'    => true,
            ));
          
      }
      return parent::_beforeToHtml();
  }
}