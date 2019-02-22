<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit_Tab_Elementform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $current_form_data = $this->getGivenvalues();
      $apply_element_to_form = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/applyelement/");
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('borncmshooks_elementform', array('legend'=>null));
      if(isset($current_form_data['element_id'])){
          $current_element = Mage::getModel('borncmshooks/types')->load($current_form_data['element_id']);
      }else{
          $current_element = null;
      }
      
      $fieldset->addType('remove_element_form_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
      $fieldset->addField('remove_element_form_button', 'remove_element_form_button', array(
            'name'      => 'remove_element_form_button',
            'onclick'   => 'removeAddElementForm()',
            'title'     => "Nevermind",
            'class'     => 'delete',
        ));
      
      $fieldset->addField('element_type', 'select', array(
          'label'     => Mage::helper('borncmshooks')->__('Element Type'),
          'name'      => 'element_type',
          'value'     => ($current_element == null ? '' : $current_element->getType()),
//          'onchange'  => 'typeFetcher(\'' . $fetcher . '\')',
          'values'    => array(
              array(
                  'value'     => '0',
                  'label'     => Mage::helper('borncmshooks')->__('Select a Type'),
              ),
              
              array(
                  'value'     => 'text',
                  'label'     => Mage::helper('borncmshooks')->__('Text'),
              ),
              
              array(
                  'value'     => 'textarea',
                  'label'     => Mage::helper('borncmshooks')->__('Textfield'),
              ),

              array(
                  'value'     => 'select',
                  'label'     => Mage::helper('borncmshooks')->__('Select'),
              ),
              
              array(
                  'value'     => 'upload',
                  'label'     => Mage::helper('borncmshooks')->__('Image'),
              ),

              array(
                  'value'     => 'editor',
                  'label'     => Mage::helper('borncmshooks')->__('WYSIWYG'),
              ),

              array(
                  'value'     => 'categories',
                  'label'     => Mage::helper('borncmshooks')->__('Categories'),
              ),
          ),
      ));
      
      $fieldset->addField('element_config', 'textarea', array(
          'label'   => Mage::helper('borncmshooks')->__('Element Config Data'),
          'name'    => 'element_config',
          'value'     => ($current_element == null ? '' : $current_element->getDescription()),
      ));
      
      $fieldset->addField('element_label', 'text', array(
          'label'   => Mage::helper('borncmshooks')->__('Element Label'),
          'name'    => 'element_label',
          'value'     => ($current_element == null ? '' : $current_element->getLabel()),
      ));
      
      $fieldset->addField('element_order', 'text', array(
            'label' => Mage::helper('borncmshooks')->__('Element Order'),
            'name' => 'element_order',
            'value'     => ($current_element == null ? '' : $current_element->getElementOrder()),
        ));
      
      
      $fieldset->addType('apply_element_button', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_edit_renderer_button'));
      $fieldset->addField('apply_element_button', 'apply_element_button', array(
            'name'      => 'apply_element_button',
            'onclick'   => 'applyElementToForm(\''.$apply_element_to_form.'\','.$current_form_data['hook_id'].','.$current_form_data['section_id'].','.$current_form_data['field_id'].','.$current_form_data['form_id'].''.($current_element == null ? '' : ','.$current_element->getTypeId().'').')',
            'title'     => "Apply Element",
            'class'     => 'add',
        ));
      
      
      return parent::_prepareForm();
  }
}