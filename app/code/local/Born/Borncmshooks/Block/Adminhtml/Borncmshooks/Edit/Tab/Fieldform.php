<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Tab_Fieldform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $save_field_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/savefield/");
      $get_fields_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getfields/");
      $sections = array();
      
      if($hook_id = $this->getHookId()){
          $field = null;
          $section_collection = Mage::getModel('borncmshooks/sections')
                            ->getCollection()
                            ->addFieldToFilter('hook_id', array('eq' => $hook_id));
          foreach($section_collection as $section){
              array_push($sections, 
                         array('value' => $section->getSectionId(), 
                               'label'     => Mage::helper('borncmshooks')->__("%s", $section->getName())));
          }
      }else{
          $field = Mage::getModel('borncmshooks/fields')->load($this->getFieldtypeid());
          $section_collection = Mage::getModel('borncmshooks/sections')
                            ->getCollection()
                            ->addFieldToFilter('hook_id', array('eq' => $field->getHookId()));
          foreach($section_collection as $section){
              array_push($sections, 
                         array('value' => $section->getSectionId(), 
                               'label'     => Mage::helper('borncmshooks')->__("%s", $section->getName())));
          }
      }
      
 
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('borncmshooks_fieldform', array('legend'=>Mage::helper('borncmshooks')->__('Field information')));
      $fieldset->addField('ajax_field_name', 'text', array(
          'label'   => Mage::helper('borncmshooks')->__('Field Name'),
          'name'    => 'ajax_field_name',
          'value'   => ($field == null ? '' : $field->getName()),
      ));

      if($field != null){
         $fieldset->addField('ajax_field_code', 'text', array(
              'label'   => Mage::helper('borncmshooks')->__('Field Code'),
              'name'    => 'ajax_field_code',
              'value'   => $field->getCode(),
              'disabled'=> true,
          ));
      }
     
      
      $fieldset->addField('ajax_field_order', 'text', array(
          'label'   => Mage::helper('borncmshooks')->__('Field Order'),
          'name'    => 'ajax_field_order',
          'value'   => ($field == null ? '' : $field->getFieldOrder()),
      ));
      
      $fieldset->addField('ajax_field_section', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Target Section'),
          'name'      => 'ajax_field_section',
          'value'     => ($field == null ? '' : $field->getSectionId()),
          'values'    => $sections,
      ));
      
      $fieldset->addField('ajax_field_status', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Status'),
          'name'      => 'ajax_field_status',
          'value'     => ($field == null ? '' : $field->getStatus()),
          'values'    => array(
              
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
      
      if($field != null){
        $fieldset->addType('ajax_field_save_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
          $fieldset->addField('ajax_section_save_button', 'ajax_field_save_button', array(
              'name'      => 'my_button',
              'name'      => 'my_button',
              'onclick'   => 'saveContent(\'' . $save_field_action . '\',\'field\',\'' . $get_fields_action . '\', ' . $field->getHookId() . ',' . $field->getFieldId() .')',
              'title'     => "Apply",
              'class'     => 'save',
          ));
      }else{
          $fieldset->addType('ajax_field_save_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
          $fieldset->addField('ajax_section_save_button', 'ajax_field_save_button', array(
              'name'      => 'my_button',
              'name'      => 'my_button',
              'onclick'   => 'saveContent(\'' . $save_field_action . '\',\'field\',\'' . $get_fields_action . '\', ' . $hook_id . ')',
              'title'     => "Save Field",
              'class'     => 'save',
          ));
      }
      
      return parent::_prepareForm();
  }
}