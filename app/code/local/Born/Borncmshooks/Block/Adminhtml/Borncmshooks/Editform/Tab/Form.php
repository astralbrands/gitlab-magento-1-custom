<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Editform_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $given_values = $this->getGivenvalues();
      
      $types_collection = Mage::getModel('borncmshooks/types')->getCollection()
                                                              ->addFieldToFilter('hook_id', array('eq' => $given_values['hook_id']))
                                                              ->addFieldToFilter('section_id', array('eq' => $given_values['section_id']))
                                                              ->addFieldToFilter('field_id', array('eq' => $given_values['field_id']))
                                                              ->addFieldToFilter('form_id', array('eq' => $given_values['form_id']))
                                                              ->setOrder('element_order', 'ASC');
      if($this->getRequest()->getParam('id')){
          $resource = Mage::getSingleton('core/resource');
          $form = new Varien_Data_Form();
          $this->setForm($form);
          $fieldset = $form->addFieldset('borncmshooks_elements', array('legend'=>null));
          $fieldset->addField('row', 'hidden', array(
            'value'     => $this->getRequest()->getParam('id'),
            'name'      => 'row',
          ));
          
          foreach($types_collection as $type){
            switch ($type->getType()) {
                case 'categories':
                    $textValue = Mage::getResourceModel('borncmshooks/values')
                        ->getContentThroughIds($type->getHookId(),
                            $type->getSectionId(),
                            $type->getFieldId(),
                            $this->getRequest()->getParam('id'),
                            $type->getTypeId());

                    $fieldset->addField('categories[' . $type->getTypeId() . ']', 'multiselect', array(
                        'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                        'required'  => false,
                        'value'     => $textValue,
                        'values'    => Mage::getModel('borncmshooks/source_category')->toOptionArray(),
                        'name'      => 'categories[' . $type->getTypeId() . ']',
                        'note'      => ucwords(str_replace("_", " ", $type->getLabel())),
                    ));

                    break;

                case 'text':
                    $textValue = Mage::getResourceModel('borncmshooks/values')
                                 ->getContentThroughIds($type->getHookId(),
                                                        $type->getSectionId(),
                                                        $type->getFieldId(),
                                                        $this->getRequest()->getParam('id'),
                                                        $type->getTypeId());

                    $fieldset->addField('text[' . $type->getTypeId() . ']', 'text', array(
                      'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                      'required'  => false,
                      'value'     => $textValue,
                      'name'      => 'text[' . $type->getTypeId() . ']',
                      'note'      => ucwords(str_replace("_", " ", $type->getLabel())),
                    ));

                    break;

                case 'textarea':
                    $textValue = Mage::getResourceModel('borncmshooks/values')
                                 ->getContentThroughIds($type->getHookId(),
                                                        $type->getSectionId(),
                                                        $type->getFieldId(),
                                                        $this->getRequest()->getParam('id'),
                                                        $type->getTypeId());
                                 
                    $fieldset->addField('textarea['. $type->getTypeId() . ']', 'textarea', array(
                      'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                      'required'  => false,
                      'value'     => $textValue,
                      'name'      => 'textarea[' . $type->getTypeId() . ']',
                      'note'      => ucwords(str_replace("_", " ", $type->getLabel())),
                    ));

                    break;

                case 'select':
                    $textValue = Mage::getResourceModel('borncmshooks/values')
                                 ->getContentThroughIds($type->getHookId(),
                                                        $type->getSectionId(),
                                                        $type->getFieldId(),
                                                        $this->getRequest()->getParam('id'),
                                                        $type->getTypeId());

                    $my_choices = array();
                    $choices = explode(",", $type->getDescription());
                    foreach ($choices as $choicekey => $choicevalue) {
                      $nodes = explode(":", trim($choicevalue));
                      array_push($my_choices, $nodes);
                    }

                    $choices = array();
                    $choices[0] = "Please Choose an Option";
                    foreach ($my_choices as $mckey => $mcvalue) {
                      $choices[$mcvalue[0]] = $mcvalue[1];
                    }
                    
                    $note = "";
                    $note_counter = 0;
                    foreach($choices as $choice_id => $choice_value){
                        if($note_counter != 0){
                          $note .= '<p style=\'display:block\'>'. $choice_value .' = '. $choice_id .'</p>'; 
                        }
                        $note_counter++;
                    }
                    
                    $fieldset->addField('select[' . $type->getTypeId() . ']', 'select', array(
                      'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                      'required'  => false,
                      'value'     => $textValue,
                      'values'    => $choices,
                      'name'      => 'select[' . $type->getTypeId() . ']',
                      'note'      => $note,
                    ));

                    break;

                case 'upload':
                    $textValue = Mage::getResourceModel('borncmshooks/values')
                                 ->getContentThroughIds($type->getHookId(),
                                                        $type->getSectionId(),
                                                        $type->getFieldId(),
                                                        $this->getRequest()->getParam('id'),
                                                        $type->getTypeId());

                    $image_id = Mage::getResourceModel('borncmshooks/values')
                                ->getIdThroughIds($type->getHookId(),
                                                        $type->getSectionId(),
                                                        $type->getFieldId(),
                                                        $this->getRequest()->getParam('id'),
                                                        $type->getTypeId());
                    if($textValue == null){
                        $fieldset->addField('image[' . $type->getTypeId() . ']', 'file', array(
                            'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                            'required'  => false,
                            'name'      => 'image['. $type->getTypeId() . ']',
                            'note'      => ':( Nothing here',
                            'class'     => 'input-file',
                          ));
                    }else{
                        $html = '<input class="borncmshooks_images_delete" name="image_delete[' . $type->getTypeId() . ']" value="' . $image_id . '" type="checkbox">';
                        $fieldset->addField('image[' . $type->getTypeId() . ']', 'file', array(
                            'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                            'required'  => false,
                            'name'      => 'image['. $type->getTypeId() . ']',
                            'note'      => $textValue,
                            'class'     => 'input-file',
                            'after_element_html' => '<div class="borncmshooks-thumbnail"><img src="'.Mage::getBaseUrl('media').'borncmshooks'.$textValue.'" style="width:150px;"></div><span>'. $html .' Delete This Image</span>',
                          ));
                    }

                    break;
                    
                    case 'editor':
                        $textValue = Mage::getResourceModel('borncmshooks/values')
                                 ->getContentThroughIds($type->getHookId(),
                                                        $type->getSectionId(),
                                                        $type->getFieldId(),
                                                        $this->getRequest()->getParam('id'),
                                                        $type->getTypeId());
                        
                    $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array( 'add_widgets' => true, 
                                                                                         'add_variables' => true, 
                                                                                         'add_images' => true,
                                                                                         'files_browser_window_url'=> $this->getBaseUrl().'admin/cms_wysiwyg_images/index/'));
                    $fieldset->addField('page_content', 'editor', array( 
                        'name' => 'wysiwyg['. $type->getTypeId() . ']', 
                        'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                        'config'=> $config,
                        'value' => $textValue,
                        'style' => 'width:700px; height:500px;', 
                        'wysiwyg' => true, 
                        'required' => true, 
                    ));
                    break;
            }
        }
          
      }else{
          $resource = Mage::getSingleton('core/resource');
          $form = new Varien_Data_Form();
          $this->setForm($form);
          $fieldset = $form->addFieldset('borncmshooks_elements', array('legend'=>null));
      
      
          $fieldset->addField('row', 'hidden', array(
            'value'     => 'new',
            'name'      => 'row',
          ));
      
          foreach($types_collection as $type){
            switch ($type->getType()) {
                case 'categories':
                    $textValue = Mage::getResourceModel('borncmshooks/values')
                        ->getContentThroughIds($type->getHookId(),
                            $type->getSectionId(),
                            $type->getFieldId(),
                            $this->getRequest()->getParam('id'),
                            $type->getTypeId());

                    $fieldset->addField('categories[' . $type->getTypeId() . ']', 'multiselect', array(
                        'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                        'required'  => false,
                        'value'     => $textValue,
                        'values'    => Mage::getModel('borncmshooks/source_category')->toOptionArray(),
                        'name'      => 'categories[' . $type->getTypeId() . ']',
                        'note'      => ucwords(str_replace("_", " ", $type->getLabel())),
                    ));

                    break;

                case 'text':
                    $fieldset->addField('text[' . $type->getTypeId() . ']', 'text', array(
                      'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                      'required'  => false,
                      'name'      => 'text[' . $type->getTypeId() . ']',
                      'note'      => ucwords(str_replace("_", " ", $type->getLabel())),
                    ));

                    break;

                case 'textarea':
                    $fieldset->addField('textarea['. $type->getTypeId() . ']', 'textarea', array(
                      'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                      'required'  => false,
                      'name'      => 'textarea[' . $type->getTypeId() . ']',
                      'note'      => ucwords(str_replace("_", " ", $type->getLabel())),
                    ));

                    break;

                case 'select':
                    $my_choices = array();
                    $choices = explode(",", $type->getDescription());
                    foreach ($choices as $choicekey => $choicevalue) {
                      $nodes = explode(":", trim($choicevalue));
                      array_push($my_choices, $nodes);
                    }

                    $choices = array();
                    $choices[0] = "Please Choose an Option";
                    foreach ($my_choices as $mckey => $mcvalue) {
                      $choices[$mcvalue[0]] = $mcvalue[1];
                    }
                    
                    $note = "";
                    $note_counter = 0;
                    foreach($choices as $choice_id => $choice_value){
                        if($note_counter != 0){
                          $note .= '<p style=\'display:block\'>'. $choice_value .' = '. $choice_id .'</p>'; 
                        }
                        $note_counter++;
                    }
                    
                    $fieldset->addField('select[' . $type->getTypeId() . ']', 'select', array(
                      'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                      'required'  => false,
                      'values'    => $choices,
                      'name'      => 'select[' . $type->getTypeId() . ']',
                      'note'      => $note,
                    ));

                    break;

                case 'upload':
                    $fieldset->addField('image[' . $type->getTypeId() . ']', 'file', array(
                      'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                      'required'  => false,
                      'name'      => 'image['. $type->getTypeId() . ']',
                      'note'      => ucwords(str_replace("_", " ", $type->getLabel())),
                    ));

                    break;
                
                case 'editor':
                    
                    $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array( 'add_widgets' => true, 
                                                                                         'add_variables' => true, 
                                                                                         'add_images' => true,
                                                                                         'files_browser_window_url'=> $this->getBaseUrl().'admin/cms_wysiwyg_images/index/'));
                    $fieldset->addField('page_content', 'editor', array( 
                        'name' => 'wysiwyg['. $type->getTypeId() . ']', 
                        'label'     => Mage::helper('borncmshooks')->__(ucwords(str_replace("_", " ", $type->getLabel()))),
                        'config'=> $config,
                        'style' => 'width:700px; height:500px;', 
                        'wysiwyg' => true, 
                        'required' => true,
                    ));
                    break;
            }
        }
      }
      return parent::_prepareForm();
  }
  
    public function _prepareLayout(){
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
        }
        return parent::_prepareLayout();
    }

}