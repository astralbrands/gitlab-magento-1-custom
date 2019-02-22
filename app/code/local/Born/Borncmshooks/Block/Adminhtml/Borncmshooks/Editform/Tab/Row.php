<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Editform_Tab_Row extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form_elements = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showformelements/");
      
      $form = new Varien_Data_Form();
      $this->setForm($form);
      if($this->getRequest()->getParam('id')){
          $rowData = Mage::getModel('borncmshooks/rows')->load($this->getRequest()->getParam('id'));
          
          $fieldset = $form->addFieldset('borncmshooks_form', array('legend'=>Mage::helper('borncmshooks')->__('Row information')));
          $fieldset->addField('hook_id', 'hidden', array(
            'value'     => ''.$rowData->getHookId().'',
            'name'      => 'hook_id',
          ));
          
          $fieldset->addField('row_name', 'text', array(
            'label'   => Mage::helper('borncmshooks')->__('Row Name'),
            'name'    => 'row_name',
            'value'   => $rowData->getName(),
            'required' => true,
          ));

          if (!Mage::app()->isSingleStoreMode()) {
                $fieldset->addField('store_id', 'multiselect', array(
                    'name' => 'stores[]',
                    'label' => Mage::helper('borncmshooks')->__('Store View'),
                    'title' => Mage::helper('borncmshooks')->__('Store View'),
                    'required' => true,
                    'value' => $rowData->getStoreId(),
                    'values' => Mage::getSingleton('adminhtml/system_store')
                                 ->getStoreValuesForForm(false, true),
                ));
            }else {
                $fieldset->addField('store_id', 'hidden', array(
                    'name' => 'stores[]',
                    'value' => Mage::app()->getStore(true)->getId()
                ));
            }
            
            $fieldset->addField('row_status', 'select', array(
                'label'     => Mage::helper('borncmshooks')->__('Status'),
                'name'      => 'row_status',
                'value'     => $rowData->getStatus(),
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
                  'class'     => 'validate-select',
                'required'  => true,
            ));
          
          $fieldset->addField('row_section_show', 'text', array(
                'label'     => Mage::helper('borncmshooks')->__('Section'),
                'name'      => 'row_section_show',
                'value'     => Mage::getModel('borncmshooks/sections')->load($rowData->getSectionId())->getName(),
                'disabled'  => true,
            ));
          
          $fieldset->addField('row_section', 'hidden', array(
            'value'     => ''.$rowData->getSectionId().'',
            'name'      => 'row_section',
          ));
          
          $fieldset->addField('row_field_show', 'text', array(
                'label'     => Mage::helper('borncmshooks')->__('Field'),
                'name'      => 'row_field_show',
                'value'     => Mage::getModel('borncmshooks/fields')->load($rowData->getFieldId())->getName(),
                'disabled'  => true,
            ));
          
          $fieldset->addField('row_field', 'hidden', array(
            'value'     => ''.$rowData->getFieldId().'',
            'name'      => 'row_field',
          ));
          
          $fieldset->addField('row_form_show', 'text', array(
                'label'     => Mage::helper('borncmshooks')->__('Form'),
                'name'      => 'row_form_show',
                'value'     => Mage::getModel('borncmshooks/forms')->load($rowData->getFormId())->getName(),
                'disabled'  => true,
            ));
          
          $fieldset->addField('row_form', 'hidden', array(
            'value'     => ''.$rowData->getFormId().'',
            'name'      => 'row_form',
          ));
          
          $start_date = Mage::getModel('core/date')->timestamp($rowData->getStartDate());
            if($rowData->getStartDate() != NULL){
              $start_date = date('m/d/Y', $start_date);  
            }else{
                $start_date = Mage::helper('borncmshooks')->__('No Start Date');
            }
            
          $fieldset->addField('start_date', 'date', array(
                'label'     => Mage::helper('borncmshooks')->__('Active From'),
                'name'      => 'start_date',
                'value'     => $start_date,
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
              ));
            $end_date = Mage::getModel('core/date')->timestamp($rowData->getEndDate());
            if($rowData->getEndDate() != NULL){
              $end_date = date('m/d/Y', $end_date);  
            }else{
                $end_date = Mage::helper('borncmshooks')->__('No End Date');
            }
            
            $fieldset->addField('end_date', 'date', array(
                'label'     => Mage::helper('borncmshooks')->__('Active To'),
                'name'      => 'end_date',
                'value'     => $end_date,
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
              ));
          
      }else{
          $rowData = null;
          $hook_id = $this->getRequest()->getParam('hookid'); 
          
          $fieldset = $form->addFieldset('borncmshooks_form', array('legend'=>Mage::helper('borncmshooks')->__('Row information')));

          $sections = array(array('value' => '0', 'label' => 'Select a Section'));
          $section_collection = Mage::getModel('borncmshooks/sections')
                                ->getCollection()
                                ->addFieldToFilter('hook_id', array('eq' => $hook_id));
          foreach($section_collection as $section){
              array_push($sections, 
                         array('value' => $section->getSectionId(), 
                               'label'     => Mage::helper('borncmshooks')->__("%s", $section->getName())));
          }
          
          $fieldset->addField('hook_id', 'hidden', array(
            'value'     => ''.$this->getRequest()->getParam('hookid').'',
            'name'      => 'hook_id',
          ));

          $fieldset->addField('row_name', 'text', array(
              'label'   => Mage::helper('borncmshooks')->__('Row Name'),
              'name'    => 'row_name',
              'required' => true,
          ));

          if (!Mage::app()->isSingleStoreMode()) {
                $fieldset->addField('store_id', 'multiselect', array(
                    'name' => 'stores[]',
                    'label' => Mage::helper('borncmshooks')->__('Store View'),
                    'title' => Mage::helper('borncmshooks')->__('Store View'),
                    'required' => true,
                    'values' => Mage::getSingleton('adminhtml/system_store')
                                 ->getStoreValuesForForm(false, true),
                ));
            }else {
                $fieldset->addField('store_id', 'hidden', array(
                    'name' => 'stores[]',
                    'value' => Mage::app()->getStore(true)->getId()
                ));
            }

            $fieldset->addField('row_status', 'select', array(
              'label'     => Mage::helper('borncmshooks')->__('Status'),
              'name'      => 'row_status',
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
                'class'     => 'validate-select',
                'required'  => true,
          ));
            

            $form_model = Mage::getModel('borncmshooks/forms');
            $forms_collection = $form_model->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
            $forms = array(array('section' => Mage::helper('borncmshooks')->__('0'), 
                                 'field' => Mage::helper('borncmshooks')->__('0'), 
                                 'value' => Mage::helper('borncmshooks')->__('0'), 
                                 'label' => Mage::helper('borncmshooks')->__('Select A Form')));
            $counter = 1;
            foreach ($forms_collection as $formKey => $formValue) {
                $current_form = $form_model->load($formValue->getFormId());
                $forms[$counter]['value'] = $current_form->getFormId();
                $forms[$counter]['label'] = $current_form->getName();
                $forms[$counter]['section']  = $current_form->getSectionId();
                $forms[$counter]['field']  = $current_form->getFieldId();
                $counter++;
            }
            
            $fieldset->addField('start_date', 'date', array(
                'label'     => Mage::helper('borncmshooks')->__('Active From'),
//                'class'     => 'required-entry',
//                'required'  => true,
                'name'      => 'start_date',
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
              ));
            
            $fieldset->addField('end_date', 'date', array(
                'label'     => Mage::helper('borncmshooks')->__('Active To'),
//                'class'     => 'required-entry',
//                'required'  => true,
                'name'      => 'end_date',
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
              ));
            
            $fieldset->addType('row_form', Mage::getConfig()->getBlockClassName('borncmshooks/adminhtml_borncmshooks_editform_renderer_bornselect'));
            $fieldset->addField('row_form', 'row_form', array(
                'label'     => Mage::helper('borncmshooks')->__('Content Type'),
                'name'      => 'row_form',
                'value'     => 0,
                'class'     => 'required-entry',
                'class'     => 'validate-select',
                'onchange'  => 'showNewFormElements(\''.$form_elements.'\', '.$hook_id.')',
                'required'  => true,
                'values'    => $forms,
            ));
            
      }
      
      return parent::_prepareForm();
  }
}