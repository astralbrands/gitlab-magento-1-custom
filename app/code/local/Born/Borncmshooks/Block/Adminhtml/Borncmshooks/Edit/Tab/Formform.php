<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Tab_Formform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $save_form_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/saveform/");
      $get_forms_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getforms/");
      $get_fields_dropdown_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getfieldsfordropdown/");
      $sections = array(array('value' => 0, 'label' => 'Select Target Section'));
      $fields = array(array('value' => 0, 'label' => 'Select Target Field'));
      if($hook_id = $this->getHookId()){
          $form = null;
          $section_collection = Mage::getModel('borncmshooks/sections')
                            ->getCollection()
                            ->addFieldToFilter('hook_id', array('eq' => $hook_id));
          foreach($section_collection as $section){
              array_push($sections, 
                         array('value' => $section->getSectionId(), 
                               'label'     => Mage::helper('borncmshooks')->__("%s", $section->getName())));
          }
      }else{
          $form = Mage::getModel('borncmshooks/forms')->load($this->getFormtypeid());
          $section_collection = Mage::getModel('borncmshooks/sections')
                            ->getCollection()
                            ->addFieldToFilter('hook_id', array('eq' => $form->getHookId()));
          foreach($section_collection as $section){
              array_push($sections, 
                         array('value' => $section->getSectionId(), 
                               'label'     => Mage::helper('borncmshooks')->__("%s", $section->getName())));
          }
          
          $field_collection = Mage::getModel('borncmshooks/fields')
                             ->getCollection()
                             ->addFieldToFilter('section_id', array('eq' => $form->getSectionId()));
          foreach($field_collection as $field){
              array_push($fields, array('value' => $field->getFieldId(),
                                        'label' => Mage::helper('borncmshooks')->__("%s", $field->getName())));
          }
      }
 
      $form_rendered = new Varien_Data_Form();
      $this->setForm($form_rendered);
      $fieldset = $form_rendered->addFieldset('borncmshooks_formform', array('legend'=>null));
      $fieldset->addField('ajax_form_name', 'text', array(
          'label'   => Mage::helper('borncmshooks')->__('Form Name'),
          'name'    => 'ajax_form_name',
          'value'   => ($form == null ? '' : $form->getName()),
      ));
      
      $fieldset->addField('ajax_form_description', 'textarea', array(
          'label'   => Mage::helper('borncmshooks')->__('Form Description'),
          'name'    => 'ajax_form_description',
          'value'   => ($form == null ? '' : $form->getDescription()),
      ));
      
      $fieldset->addField('ajax_form_section', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Target Section'),
          'name'      => 'ajax_form_section',
          'value'     => ($form == null ? '' : $form->getSectionId()),
          'values'    => $sections,
          'onchange'  => 'getFieldsForDropdown(\'' . $get_fields_dropdown_action . '\')',
      ));
      
      $fieldset->addField('ajax_form_field', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Target Field'),
          'name'      => 'ajax_form_field',
          'value'     => ($form == null ? '' : $form->getFieldId()),
          'values'    => ($form == null ? '' : $fields),
          'disabled'  => ($form == null ? true : false),
      ));
      
      $fieldset->addField('ajax_form_status', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Status'),
          'name'      => 'ajax_form_status',
          'value'     => ($form == null ? '' : $form->getStatus()),
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
      
      if($form != null){
        $fieldset->addType('ajax_form_save_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
          $fieldset->addField('ajax_section_save_button', 'ajax_form_save_button', array(
              'name'      => 'ajax_section_save_button',
              'onclick'   => 'saveContent(\'' . $save_form_action . '\',\'form\',\'' . $get_forms_action . '\', ' . $form->getHookId() . ',' . $form->getFormId() .')',
              'title'     => "Apply",
              'class'     => 'save',
          ));
      }else{
          $fieldset->addType('ajax_form_save_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
          $fieldset->addField('ajax_section_save_button', 'ajax_form_save_button', array(
              'name'      => 'my_button',
              'onclick'   => 'saveContent(\'' . $save_form_action . '\',\'form\',\'' . $get_forms_action . '\', ' . $hook_id . ')',
              'title'     => "Save Form",
              'class'     => 'save',
          ));
      }
      
      if($form != null){
          $delete_link = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/deletecontent/");
          $show_add_element_form = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showaddelementform/");
          $form_element_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('form_id', array('eq' => $form->getFormId()))->setOrder('element_order', 'ASC');
          
          $fieldset1 = $form_rendered->addFieldset('borncmshooks_formelements', array('legend'=>null));
          
          foreach ($form_element_collection as $element) {
                $fieldset1->addType('applied_element_'.$element->getTypeId(), Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_element'));
                $fieldset1->addField('applied_element_'.$element->getTypeId(), 'applied_element_'.$element->getTypeId(), array(
                    'onclick'    => 'showAddElementForm(\''. $show_add_element_form .'\','.$form->getHookId().','.$form->getSectionId().','.$form->getFieldId().','.$form->getFormId().','.$element->getTypeId().')',
                    'deleteonclick'  => 'deleteContent(\''.$delete_link.'\', \'element\', '.$element->getTypeId().')',
                    'deleteelement'  => 'Del',
                    'elementname'    =>  $element->getLabel(),
                    'label'       => $element->getType(),
                    'elementorder'  => $element->getElementOrder(),
                ));
          }
          
          $fieldset1->addType('ajax_form_add_element_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
          $fieldset1->addField('ajax_form_add_element_button', 'ajax_form_add_element_button', array(
              'name'      => 'ajax_form_add_element_button',
              'onclick'   => 'showAddElementForm(\''. $show_add_element_form .'\','.$form->getHookId().','.$form->getSectionId().','.$form->getFieldId().','.$form->getFormId().')',
              'title'     => "Add Element",
              'class'     => 'add',
              'style'     => 'margin:10px'
          ));
      }
      
      return parent::_prepareForm();
  }
}