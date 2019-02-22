<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Tab_Sectionform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $save_section_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/savesection/");
      $get_sections_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getsections/");
      if($hook_id = $this->getHookId()){
          $section = null;
      }else{
          $section = Mage::getModel('borncmshooks/sections')->load($this->getSectiontypeid());
      }
 
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('borncmshooks_sectionform', array('legend'=>Mage::helper('borncmshooks')->__('Section information')));
      
      $fieldset->addField('ajax_section_name', 'text', array(
          'label'   => Mage::helper('borncmshooks')->__('Section Name'),
          'name'    => 'ajax_section_name',
          'value'   => ($section == null ? '' : $section->getName()),
      ));
      
      if($section != null){
            $fieldset->addField('ajax_section_code', 'text', array(
              'label'   => Mage::helper('borncmshooks')->__('Section Code'),
              'name'    => 'ajax_section_code',
              'value'   => $section->getCode(),
              'disabled' => true,
            ));

            $fieldset->addField('ajax_section_hook', 'text', array(
              'label'   => Mage::helper('borncmshooks')->__('Section Belongs To'),
              'name'    => 'ajax_section_hook',
              'value'   => Mage::getModel('borncmshooks/borncmshooks')->load($section->getHookId())->getName(),
              'disabled' => true,
            ));
      }
      
      $fieldset->addField('ajax_section_order', 'text', array(
          'label'   => Mage::helper('borncmshooks')->__('Section Order'),
          'name'    => 'ajax_section_order',
          'value'   => ($section == null ? '' : $section->getSectionOrder()),
      ));
		
      $fieldset->addField('ajax_section_status', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Status'),
          'name'      => 'ajax_section_status',
          'value'     => ($section == null ? '' : $section->getStatus()),
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
      
      if($section != null){
        $fieldset->addType('ajax_section_save_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
          $fieldset->addField('ajax_section_save_button', 'ajax_section_save_button', array(
              'name'      => 'my_button',
              'name'      => 'my_button',
              'onclick'   => 'saveContent(\'' . $save_section_action . '\',\'section\',\'' . $get_sections_action . '\', ' . $section->getHookId() . ',' . $section->getSectionId() .')',
              'title'     => "Apply",
              'class'     => 'save',
          ));
      }else{
          $fieldset->addType('ajax_section_save_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
          $fieldset->addField('ajax_section_save_button', 'ajax_section_save_button', array(
              'name'      => 'my_button',
              'name'      => 'my_button',
              'onclick'   => 'saveContent(\'' . $save_section_action . '\',\'section\',\'' . $get_sections_action . '\', ' . $hook_id . ')',
              'title'     => "Save Section",
              'class'     => 'save',
          ));
      }
     
      return parent::_prepareForm();
  }
}