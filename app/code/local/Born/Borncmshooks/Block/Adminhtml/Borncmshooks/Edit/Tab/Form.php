<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $type_note = null;
      $target_note = null;
      $status_note = null;
      if($this->getRequest()->getParam('id')){
          $hook_model = Mage::getModel('borncmshooks/borncmshooks')->load($this->getRequest()->getParam('id'));
          $type_note = "Our Current Type is <strong>" . $hook_model->getType() . "</strong>";
          $target_note = "Our Current target is <strong>" . $hook_model->getName() . "</strong>";
          $status_note = "Our Current status is " . ($hook_model->getStatus() == 1 ? "<strong>Enabled</strong>" : "<strong>Disabled</strong>");
      }
      $fetcher = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/typefetcher/");
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('borncmshooks_form', array('legend'=>Mage::helper('borncmshooks')->__('Hook information')));
     
      $fieldset->addField('cms_content_type', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Type'),
          'name'      => 'cms_content_type',
          'onchange'  => 'typeFetcher(\'' . $fetcher . '\')',
          'index'     => 'type',
          'note'      => $type_note,
          'values'    => array(
              array(
                  'value'     => '0',
                  'label'     => Mage::helper('borncmshooks')->__('Type'),
              ),
              
              array(
                  'value'     => 'cms',
                  'label'     => Mage::helper('borncmshooks')->__('CMS'),
              ),

              array(
                  'value'     => 'category',
                  'label'     => Mage::helper('borncmshooks')->__('Category'),
              ),
              
              array(
                  'value'     => 'product',
                  'label'     => Mage::helper('borncmshooks')->__('Product'),
              ),
          ),
      ));
      
      $fieldset->addField('cms_content_target', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Target'),
          'name'      => 'cms_content_target',
          'note'      => $target_note,
          'values'    => array(
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('borncmshooks')->__('Target'),
              ),
          ),
      ));

      $fieldset->addField('cms_content_status', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Status'),
          'name'      => 'cms_content_status',
          'note'      => $status_note,
          'values'    => array(
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('borncmshooks')->__('Status'),
              ),
              
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('borncmshooks')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('borncmshooks')->__('Disabled'),
              ),
          ),
      ));
     
      $hooked_sections = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('eq' => $this->getRequest()->getParam('id')));
      $hooked_sections_ids = array();
      $hooked_sections_names = array();
      foreach ($hooked_sections->getData() as $h_s_key => $h_s_value) {
          array_push($hooked_sections_ids, $h_s_value['section_id']);
          array_push($hooked_sections_names, $h_s_value['name']);
      }
      $all_existing_sections = Mage::getModel('borncmshooks/sections')->getCollection()
                                                                      ->addFieldToFilter('hook_id', array('neq' => $this->getRequest()->getParam('id')));
//                                                                      ->addFieldToFilter('section_id', array('in' => $hooked_sections_ids));
//                                                                      ->addFieldToFilter('name', array('nin' => $hooked_sections_names));

      $all_existing_sections_ids = null;
      $all_existing_sections_names = null;
      $sections = array(array('value' => 0, 'label' => 'Select a Section'));

      foreach ($all_existing_sections->getData() as $a_e_s_key => $a_e_s_value) {
          $all_existing_sections_ids = $a_e_s_value['section_id'];
          $all_existing_sections_names = $a_e_s_value['name'];
          array_push($sections, array('value' => $all_existing_sections_ids, 'label' => $all_existing_sections_names));
      }
      
      if($this->getRequest()->getParam('id')){
        $fieldset1 = $form->addFieldset('borncmshooks_form_section', array('legend'=>Mage::helper('borncmshooks')->__('Use Existing Hook Structure')));
        $fieldset1->addField('existing_form_section', 'select', array(
            'label'     => Mage::helper('borncmshooks')->__('Existing Sections'),
            'name'      => 'existing_form_section',
            'values'    => $sections,
        ));
      }
      
      if ( Mage::getSingleton('adminhtml/session')->getBorncmshooksData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBorncmshooksData());
          Mage::getSingleton('adminhtml/session')->setBorncmshooksData(null);
      } elseif ( Mage::registry('borncmshooks_data') ) {
          $form->setValues(Mage::registry('borncmshooks_data')->getData());
      }
      return parent::_prepareForm();
  }
}