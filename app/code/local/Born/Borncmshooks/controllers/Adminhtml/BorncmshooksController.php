<?php

class Born_Borncmshooks_Adminhtml_BorncmshooksController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('borncmshooks/items')
			->_addBreadcrumb(Mage::helper('borncmshooks')->__('Hook Manager'), Mage::helper('borncmshooks')->__('Hook Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
    
    public function formssearchindexAction() {
		$this->_initAction()
			->renderLayout();
	}
    
    public function formgridAction(){
		$this->loadLayout();
		$this->getLayout()->getBlock('newrow.grid');
        $this->getLayout()->getBlock('form.grid');
        $this->renderLayout();
	}

	public function filteredformgridAction(){
		$this->loadLayout();
        $this->getLayout()->getBlock('form.grid');
        $this->renderLayout();
	}
    
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('borncmshooks/borncmshooks')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('borncmshooks_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('borncmshooks/items');

			$this->_addBreadcrumb(Mage::helper('borncmshooks')->__('Hook Manager'), Mage::helper('borncmshooks')->__('Hook Manager'));
			$this->_addBreadcrumb(Mage::helper('borncmshooks')->__('Hook News'), Mage::helper('borncmshooks')->__('Hook News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit'))
				->_addLeft($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('borncmshooks')->__('Cms Content does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
    	public function editrowAction(){
            $id     = $this->getRequest()->getParam('id');
            $model  = Mage::getModel('borncmshooks/rows')->load($id);

            if ($model->getId() || $id == 0) {
                    $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                    if (!empty($data)) {
                            $model->setData($data);
                    }

                    Mage::register('borncmshooksdsrow_data', $model);

                    $this->loadLayout();
                    $this->_setActiveMenu('borncmshooks/items');

                    $this->_addBreadcrumb(Mage::helper('borncmshooks')->__('Row Manager'), Mage::helper('borncmshooks')->__('Row Manager'));
                    $this->_addBreadcrumb(Mage::helper('borncmshooks')->__('Row News'), Mage::helper('borncmshooks')->__('Row News'));

                    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                    $this->_addContent($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_editform'));
                    $this->_addLeft($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_editform_tabs'));

                    $this->renderLayout();
            } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('borncmshooks')->__('Row does not exist'));
                    $this->_redirect('*/*/');
            }
	}
    
        public function newrowAction(){
            $hook_id = $this->getRequest()->getParam('id');
            $this->loadLayout();
            $this->_setActiveMenu('borncmshooks/items');

            $this->_addBreadcrumb(Mage::helper('borncmshooks')->__('Row Manager'), Mage::helper('borncmshooks')->__('Row Manager'));
            $this->_addBreadcrumb(Mage::helper('borncmshooks')->__('Row News'), Mage::helper('borncmshooks')->__('Row News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_editform')->setData('hookid', $hook_id));
            $this->_addLeft($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_editform_tabs'));

            $this->renderLayout();
	}
        
	public function newAction() {
		$this->_forward('edit');
	}
 
    public function saveCmsContent($type,$target,$status, $id=null){
        if($id == null){
            $cms_content_model = Mage::getModel('borncmshooks/borncmshooks');
        }else{
            $cms_content_model = Mage::getModel('borncmshooks/borncmshooks')->load($id);
        }
        switch ($type) {
            case 'cms':
                    $page = Mage::getModel('cms/page')->load($target);
                    $cms_content_model->setName($page->getTitle())
                                      ->setCode($page->getPageId().$page->getIdentifier())
                                      ->setType($type)
                                      ->setStatus($status);
                    if ($cms_content_model->getCreatedTime() == NULL || $cms_content_model->getUpdateTime() == NULL) {
                        $cms_content_model->setCreatedTime(now())
                            ->setUpdateTime(now());
                    } else {
                        $cms_content_model->setUpdateTime(now());
                    }	
                    $cms_content_model->save();
                break;
            case 'category':
                    $category = Mage::getModel('catalog/category')->load($target);
                    $cms_content_model->setName($category->getName())
                                      ->setCode($category->getEntityId().$category->getUrlKey())
                                      ->setType($type)
                                      ->setStatus($status);
                    if ($cms_content_model->getCreatedTime() == NULL || $cms_content_model->getUpdateTime() == NULL) {
                        $cms_content_model->setCreatedTime(now())
                            ->setUpdateTime(now());
                    } else {
                        $cms_content_model->setUpdateTime(now());
                    }	
                    $cms_content_model->save();
                    
                break;
            case 'product':
                    $product = Mage::getModel('catalog/product')->load($target);
                    $cms_content_model->setName($product->getName())
                                      ->setCode($product->getEntityId().$product->getUrlKey())
                                      ->setType($type)
                                      ->setStatus($status);
                    if ($cms_content_model->getCreatedTime() == NULL || $cms_content_model->getUpdateTime() == NULL) {
                        $cms_content_model->setCreatedTime(now())
                            ->setUpdateTime(now());
                    } else {
                        $cms_content_model->setUpdateTime(now());
                    }	
                    $cms_content_model->save();
                break;
        }

        if($id!=null){
            $section_collection = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('eq' => $id));
            foreach ($section_collection as $section) {
                $section->setHookId($cms_content_model->getHookId());
            }
            $section_collection->walk('save');
            
            $fields_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('hook_id', array('eq' => $id));
            foreach ($fields_collection as $field) {
                $field->setHookId($cms_content_model->getHookId());
            }
            $fields_collection->walk('save');
            
            $rows_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('eq' => $id));
            foreach ($rows_collection as $row) {
                $row->setHookId($cms_content_model->getHookId());
            }
            $rows_collection->walk('save');
            
            $forms_collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('hook_id', array('eq' => $id));
            foreach ($forms_collection as $form) {
                $form->setHookId($cms_content_model->getHookId());
            }
            $forms_collection->walk('save');
            
            $types_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('hook_id', array('eq' => $id));
            foreach ($types_collection as $type) {
                $type->setHookId($cms_content_model->getHookId());
            }
            $types_collection->walk('save');
            
            $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('eq' => $id));
            foreach ($values_collection as $value) {
                $value->setHookId($cms_content_model->getHookId());
            }
            $values_collection->walk('save');
        }
         return $cms_content_model->getHookId();
    }
    
    public function saveRow($row_name,$row_hook_id,$row_section_id,$row_field_id,$row_form_id,$row_store_id,$row_status,$row_start_date,$row_end_date,$existing_row_id = false){
        if($existing_row_id == false){
            $row_data = Mage::getModel('borncmshooks/rows');
        }else{
            $row_data = Mage::getModel('borncmshooks/rows')->load($existing_row_id);
        }
        
        $code = strtolower(str_replace(" ", "-", $row_name));
        $row_data->setName($row_name)
                 ->setCode($code)
                 ->setHookId($row_hook_id)                
                 ->setSectionId($row_section_id)                
                 ->setFieldId($row_field_id)                
                 ->setFormId($row_form_id)                
                 ->setStoreId($row_store_id)
                 ->setStatus($row_status)
                 ->setStartDate($row_start_date)
                 ->setEndDate($row_end_date);
        if ($row_data->getCreatedTime() == NULL || $row_data->getUpdateTime() == NULL) {
            $row_data->setCreatedTime(now())->setUpdateTime(now());
        } else {
            $row_data->setUpdateTime(now());
        }
        $row_data->save();
        return $row_data->getRowId();
    }
    
    public function saveText($text_index,$text_value,$row_hook_id,$row_section_id,$row_field_id,$row_id){
        $value_id = Mage::getResourceModel('borncmshooks/values')->getIdThroughIds($row_hook_id,
                                                                                   $row_section_id,
                                                                                   $row_field_id,
                                                                                   $row_id,
                                                                                   $text_index);
        if($value_id == null){
            $value_data = Mage::getModel('borncmshooks/values');
        }else{
            $value_data = Mage::getModel('borncmshooks/values')->load($value_id);
        }
        
        $value_data->setContent($text_value)
                 ->setHookId($row_hook_id)                
                 ->setSectionId($row_section_id)                
                 ->setFieldId($row_field_id)                
                 ->setRowId($row_id);
        $value_data->setTypeId($text_index);
        if ($value_data->getCreatedTime() == NULL || $value_data->getUpdateTime() == NULL) {
            $value_data->setCreatedTime(now())->setUpdateTime(now());
        } else {
            $value_data->setUpdateTime(now());
        }
        $value_data->save();
        return;
    }
    
    public function saveTextarea($textarea_index,$textarea_value,$row_hook_id,$row_section_id,$row_field_id,$row_id){
        $value_id = Mage::getResourceModel('borncmshooks/values')->getIdThroughIds($row_hook_id,
                                                                                   $row_section_id,
                                                                                   $row_field_id,
                                                                                   $row_id,
                                                                                   $textarea_index);
        if($value_id == null){
            $value_data = Mage::getModel('borncmshooks/values');
        }else{
            $value_data = Mage::getModel('borncmshooks/values')->load($value_id);
        }
        
        $value_data->setContent($textarea_value)
                 ->setHookId($row_hook_id)                
                 ->setSectionId($row_section_id)                
                 ->setFieldId($row_field_id)                
                 ->setRowId($row_id);
        $value_data->setTypeId($textarea_index);
        if ($value_data->getCreatedTime() == NULL || $value_data->getUpdateTime() == NULL) {
            $value_data->setCreatedTime(now())->setUpdateTime(now());
        } else {
            $value_data->setUpdateTime(now());
        }
        $value_data->save();
        return;
    }
    
    public function saveWysiwyg($wysiwyg_index,$wysiwyg_value,$row_hook_id,$row_section_id,$row_field_id,$row_id){
        $value_id = Mage::getResourceModel('borncmshooks/values')->getIdThroughIds($row_hook_id,
                                                                                   $row_section_id,
                                                                                   $row_field_id,
                                                                                   $row_id,
                                                                                   $wysiwyg_index);
        if($value_id == null){
            $value_data = Mage::getModel('borncmshooks/values');
        }else{
            $value_data = Mage::getModel('borncmshooks/values')->load($value_id);
        }
        
        $value_data->setContent($wysiwyg_value)
                 ->setHookId($row_hook_id)                
                 ->setSectionId($row_section_id)                
                 ->setFieldId($row_field_id)                
                 ->setRowId($row_id);
        $value_data->setTypeId($wysiwyg_index);
        if ($value_data->getCreatedTime() == NULL || $value_data->getUpdateTime() == NULL) {
            $value_data->setCreatedTime(now())->setUpdateTime(now());
        } else {
            $value_data->setUpdateTime(now());
        }
        $value_data->save();
        return;
    }
    
    public function saveSelect($select_index,$select_value,$row_hook_id,$row_section_id,$row_field_id, $row_id){
        $value_id = Mage::getResourceModel('borncmshooks/values')->getIdThroughIds($row_hook_id,
                                                                                   $row_section_id,
                                                                                   $row_field_id,
                                                                                   $row_id,
                                                                                   $select_index);
        if($value_id == null){
            $value_data = Mage::getModel('borncmshooks/values');
        }else{
            $value_data = Mage::getModel('borncmshooks/values')->load($value_id);
        }
        
        $value_data->setContent($select_value)
                 ->setHookId($row_hook_id)                
                 ->setSectionId($row_section_id)                
                 ->setFieldId($row_field_id)                
                 ->setRowId($row_id);
        $value_data->setTypeId($select_index);
        if ($value_data->getCreatedTime() == NULL || $value_data->getUpdateTime() == NULL) {
            $value_data->setCreatedTime(now())->setUpdateTime(now());
        } else {
            $value_data->setUpdateTime(now());
        }
        $value_data->save();
        return;
    }

    public function saveCategories($categories_index,$categories_value,$row_hook_id,$row_section_id,$row_field_id, $row_id){
        $value_id = Mage::getResourceModel('borncmshooks/values')->getIdThroughIds($row_hook_id,
            $row_section_id,
            $row_field_id,
            $row_id,
            $categories_index);
        if($value_id == null){
            $value_data = Mage::getModel('borncmshooks/values');
        }else{
            $value_data = Mage::getModel('borncmshooks/values')->load($value_id);
        }

        $value_data->setContent(implode(",", (array)$categories_value))
            ->setHookId($row_hook_id)
            ->setSectionId($row_section_id)
            ->setFieldId($row_field_id)
            ->setRowId($row_id);
        $value_data->setTypeId($categories_index);
        if ($value_data->getCreatedTime() == NULL || $value_data->getUpdateTime() == NULL) {
            $value_data->setCreatedTime(now())->setUpdateTime(now());
        } else {
            $value_data->setUpdateTime(now());
        }
        $value_data->save();
        return;
    }


    public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			try {
                #let's save our row
                if(isset($data['row'])){
                        if(isset($data['stores'])) {
                            if(in_array('0',$data['stores'])){
                                $data['store_id'] = '0';
                            }
                            else{
                                $data['store_id'] = implode(",", $data['stores']);
                            }
                           unset($data['stores']);
                        }
                        #delete image
                        if(isset($data['image_delete'])){
                            $images = $data['image_delete'];
                            
                            $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('value_id', array('in' => $images));
                            foreach($values_collection as $value){
                                $value->delete();
                            }
                        }
                        
                        #saving row
                        if($data['row'] == 'new'){
                            $form_values = Mage::getModel('borncmshooks/forms')->load($data['row_form']);
                            $data['row_section'] = $form_values->getSectionId();
                            $data['row_field'] = $form_values->getFieldId();
                            $row_id = $this->saveRow($data['row_name'],
                                                     $data['hook_id'],
                                                     $data['row_section'],
                                                     $data['row_field'],
                                                     $data['row_form'],
                                                     $data['store_id'],
                                                     $data['row_status'],
                                                     $data['start_date'],
                                                     $data['end_date']);
                        }else{
                            $row_id = $this->saveRow($data['row_name'],$data['hook_id'],$data['row_section'],$data['row_field'],$data['row_form'],$data['store_id'],$data['row_status'], $data['start_date'], $data['end_date'], $data['row']);
                        }                       
                       #saving texts
                       if(isset($data['text'])){
                        foreach($data['text'] as $textIndex => $textValue){
                           $this->saveText($textIndex,$textValue,$data['hook_id'],$data['row_section'],$data['row_field'],$row_id);
                        }  
                       }
                       
                       #saving textareas
                       if(isset($data['textarea'])){
                        foreach($data['textarea'] as $textareaIndex => $textareaValue){
                               $this->saveTextarea($textareaIndex,$textareaValue,$data['hook_id'],$data['row_section'],$data['row_field'],$row_id);
                        }
                       }
                       
                       #saving wysiwyg
                       if(isset($data['wysiwyg'])){
                        foreach($data['wysiwyg'] as $wysiwygIndex => $wysiwygValue){
                               $this->saveWysiwyg($wysiwygIndex,$wysiwygValue,$data['hook_id'],$data['row_section'],$data['row_field'],$row_id);
                        }
                       }
                       
                       #saving selects
                       if(isset($data['select'])){
                         foreach($data['select'] as $selectIndex => $selectValue){
                           $this->saveTextarea($selectIndex,$selectValue,$data['hook_id'],$data['row_section'],$data['row_field'],$row_id);
                        }  
                       }

                    #saving selects
                    if(isset($data['categories'])){
                        foreach($data['categories'] as $categoriesIndex => $categoriesValue){
                            $this->saveCategories($categoriesIndex,$categoriesValue,$data['hook_id'],$data['row_section'],$data['row_field'],$row_id);
                        }
                    }

                       if($_FILES){
                           $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('eq' => $data['hook_id']))
                                                ->addFieldToFilter('section_id', array('eq' => $data['row_section']))
                                                ->addFieldToFilter('field_id', array('eq' => $data['row_field']))
                                                ->addFieldToFilter('row_id', array('in' => $row_id));
                           
                            foreach ($_FILES as $fileKey => $fileValue) {
                                foreach ($fileValue['name'] as $nameKey => $nameValue) {
                                    try {
                                        $new_uploader = new Varien_File_Uploader('image[' . $nameKey . ']');
                                        $new_uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'svg'));
                                        $new_uploader->setAllowRenameFiles(true);
                                        $new_uploader->setFilesDispersion(true);
                                        $new_path = Mage::getBaseDir('media') . DS . 'borncmshooks';
                                        $returned_save = $new_uploader->save($new_path, $nameValue);
                                        $value_id = Mage::getResourceModel('borncmshooks/values')->getIdThroughIds($data['hook_id'], $data['row_section'],$data['row_field'],$row_id,$nameKey);

                                        if($value_id == null){
                                            $new_images_model = Mage::getModel('borncmshooks/values');
                                        }else{
                                            $current_value_from_col = $values_collection->getItemsByColumnValue('value_id', $value_id);
                                            $current_value_obj = array_shift($current_value_from_col);
                                            $new_images_model = $current_value_obj;
                                        }
                                        
                                        $new_images_model->setContent($returned_save['file'])
                                                         ->setHookId($data['hook_id'])                
                                                         ->setSectionId($data['row_section'])                
                                                         ->setFieldId($data['row_field'])                
                                                         ->setRowId($row_id);
                                         $new_images_model->setTypeId($nameKey);
                                               if ($new_images_model->getCreatedTime() == NULL || $new_images_model->getUpdateTime() == NULL) {
                                                   $new_images_model->setCreatedTime(now())->setUpdateTime(now());
                                               } else {
                                                   $new_images_model->setUpdateTime(now());
                                               }
                                        $new_images_model->save();
                                    } catch (Exception $exc) {
                                        Mage::getSingleton('adminhtml/session')->addNotice($exc->getMessage());
                                    }
                                }
                            }
                        }
                       
                }else{
                    if($this->getRequest()->getParam('id')){
                        if(isset($data['cms_content_type'])){
                            $cms_content_id = $this->saveCmsContent($data['cms_content_type'], $data['cms_content_target'], $data['cms_content_status'], $this->getRequest()->getParam('id'));
                        } 
                        if(isset($data['existing_form_section']) && ($data['existing_form_section'] != '0')){
                            $old_section = Mage::getModel('borncmshooks/sections')->load($data['existing_form_section']);
                            $new_section = Mage::getModel('borncmshooks/sections')->setData($old_section->getData());
                            unset($new_section['section_id']);
                            $new_section->setHookId($this->getRequest()->getParam('id'));
                            $new_section->setCreatedTime(now())->setUpdateTime(now());
                            $new_section->save();
                            $new_section_id = $new_section->getSectionId();  # Retrieve newly created section id

                            $old_fields = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('section_id', array('eq' => $old_section['section_id']));
                            $old_forms = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('section_id', array('eq' => $old_section['section_id']));
                            $old_forms_ids = $old_forms->getColumnValues('form_id');
                            $old_types = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('section_id', array('eq' => $old_section['section_id']))
                                                                                              ->addFieldToFilter('form_id', array('in' => $old_forms_ids));
                            foreach($old_fields as $old_field){
                                $new_field = Mage::getModel('borncmshooks/fields')->setData($old_field->getData());
                                unset($new_field['field_id']);
                                $new_field->setHookId($this->getRequest()->getParam('id'));
                                $new_field->setSectionId($new_section_id);
                                $new_field->setCreatedTime(now())->setUpdateTime(now());
                                $new_field->save();
                                $new_field_id = $new_field->getFieldId();  # Retrieve newly created field id

                                $current_old_forms = $old_forms->getItemsByColumnValue('field_id', $old_field['field_id']);
                                foreach($current_old_forms as $old_form){
                                    $new_form = Mage::getModel('borncmshooks/forms')->setData($old_form->getData());
                                    unset($new_form['form_id']);
                                    $new_form->setHookId($this->getRequest()->getParam('id'));
                                    $new_form->setSectionId($new_section_id);
                                    $new_form->setFieldId($new_field_id);
                                    $new_form->setCreatedTime(now())->setUpdateTime(now());
                                    $new_form->save();
                                    $new_form_id = $new_form->getFormId();  # Retrieve newly created field id

                                    $current_old_types = $old_types->getItemsByColumnValue('form_id', $old_form['form_id']);
                                    foreach($current_old_types as $old_type){
                                        $new_type = Mage::getModel('borncmshooks/types')->setData($old_type->getData());
                                        unset($new_type['type_id']);
                                        $new_type->setHookId($this->getRequest()->getParam('id'));
                                        $new_type->setSectionId($new_section_id);
                                        $new_type->setFieldId($new_field_id);
                                        $new_type->setFormId($new_form_id);
                                        $new_type->setCreatedTime(now())->setUpdateTime(now());
                                        $new_type->save();
                                    }
                                }
                            }
                        }     
                    }else{
                        if(isset($data['cms_content_type'])){
                            $cms_content_id = $this->saveCmsContent($data['cms_content_type'], $data['cms_content_target'], $data['cms_content_status']);
                        }
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('borncmshooks')->__('Cms Content was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $cms_content_id));
                        return;
                }
                if ($this->getRequest()->getParam('backtohookfromrow')) {
                        $this->_redirect('*/*/edit', array('id' => $data['hook_id']));
                        return;
                }
                if ($this->getRequest()->getParam('backeditrow')) {
                        $this->_redirect('*/*/editrow', array('id' => $row_id, 'hookid' => Mage::getModel('borncmshooks/rows')->load($row_id)->getHookId()));
                        return;
                }
                
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('borncmshooks')->__('Unable to find cms content to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('borncmshooks/borncmshooks');
				$hook_id = $this->getRequest()->getParam('id');
                                
                                $section_collection = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
                                foreach ($section_collection as $section) {
                                    $section->delete();
                                }
                                
                                $field_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
                                foreach ($field_collection as $field) {
                                    $field->delete();
                                }
                                
                                $row_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
                                foreach ($row_collection as $row) {
                                    $row->delete();
                                }
                                
                                $form_collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
                                foreach ($form_collection as $form) {
                                    $form->delete();
                                }
                                
                                $type_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
                                foreach ($type_collection as $type) {
                                    $type->delete();
                                }
                                
                                $value_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
                                foreach ($value_collection as $value) {
                                    $value->delete();
                                }
                                
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('borncmshooks')->__('Cms Content was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function deleterowAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            $model = Mage::getModel('borncmshooks/rows');
             
            $model->load($this->getRequest()->getParam('id'));
            $hookid = $model->getHookId();
            try {
                $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('row_id', array('eq' => $model->getRowId()));
                foreach ($values_collection as $value) {
                    $value->delete();
                }
                $model->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('borncmshooks')->__('Row was successfully deleted'));
                $this->_redirect('*/*/edit', array('id' => $hookid));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $hookid));
            }
        }
        $this->_redirect('*/*/edit', array('id' => $hookid));
    }

    public function massDeleteAction() {
        $borncmshooksIds = $this->getRequest()->getParam('borncmshooks');
        if(!is_array($borncmshooksIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('borncmshooks')->__('Please select cms content(s)'));
        } else {
            try {
                $hook_collection = Mage::getModel('borncmshooks/borncmshooks')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                $section_collection = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                $field_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                $row_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                $form_collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                $type_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                $value_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                
                $hook_collection->walk('delete');
                $section_collection->walk('delete');
                $field_collection->walk('delete');
                $row_collection->walk('delete');
                $form_collection->walk('delete');
                $type_collection->walk('delete');
                $value_collection->walk('delete');
                
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('borncmshooks')->__(
                        'Total of %d record(s) were successfully deleted', count($borncmshooksIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massExportAction() {
        $borncmshooksIds = $this->getRequest()->getParam('borncmshooks');
        if(!is_array($borncmshooksIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('borncmshooks')->__('Please select cms content(s)'));
        } else {
            Mage::register('borncmshooksIds',$borncmshooksIds);
        }
        $this->exportXmlAction();
    }

    public function massStatusAction(){
        $borncmshooksIds = $this->getRequest()->getParam('borncmshooks');
        if(!is_array($borncmshooksIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select cms content(s)'));
        } else {
            try {
                $hook_collection = Mage::getModel('borncmshooks/borncmshooks')->getCollection()->addFieldToFilter('hook_id', array('in' => $borncmshooksIds));
                foreach ($hook_collection as $borncmshooks) {
                        $borncmshooks->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true);
                }
                $hook_collection->walk('save');
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($borncmshooksIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction(){
        $fileName   = 'borncmshooks.csv';
        $content    = $this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction(){
        $fileName   = 'borncmshooks.xml';
        $content    = $this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream'){
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        return;
    }
    
    public function typefetcherAction(){
        $type = $this->getRequest()->getParam('type');
        $response = null;
        switch ($type) {
            case 'cms':
                $pages = Mage::getModel('cms/page')->getCollection();
                foreach($pages as $page){
                    $response .= "<option value=\"" . $page->getId() . "\">" . $page->getTitle() . "</option>";
                }
                break;
            case 'category':
                $categories = Mage::getModel('catalog/category')->getCollection()
                                                                ->addAttributeToSelect('name');
                foreach($categories as $category){
                    if(($category->getEntityId != '1') || ($category->getEntityId()!= '2')){
                       $response .= "<option value=\"" . $category->getEntityId() . "\">" . $category->getName() . "</option>"; 
                    }
                }
                break;
                
            case 'product':
                $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('name')
                                                                              ->addAttributeToSelect('visibility')
                                                                              ->addFieldToFilter('visibility', array('eq' => 4));
                foreach($products as $product){
                       $response .= "<option value=\"" . $product->getEntityId() . "\">" . $product->getName() . "</option>"; 
                }
                break;
            
            default:
                $response = "<option>:(</option>";
                break;
        }
        $this->getResponse()->setBody($response);
    }
    
    public function savesectionAction(){
        $show_edit_content_form = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $get_fields = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getfields/");
        try {
            $section = $this->getRequest()->getParam('section');
            $section_order = $this->getRequest()->getParam('section_order');
            $section_status = $this->getRequest()->getParam('section_status');
            if($this->getRequest()->getParam('section_id')){
                $section_data = Mage::getModel('borncmshooks/sections')->load($this->getRequest()->getParam('section_id'));
            }else{
                $section_data = Mage::getModel('borncmshooks/sections');
            }
            $hook_id = $this->getRequest()->getParam('hook_id');
            $code = strtolower(str_replace(" ", "-", $section));
            $section_data->setName(ucwords($section))->setCode($code)->setHookId($hook_id)->setSectionOrder($section_order)->setStatus($section_status);
            if ($section_data->getCreatedTime() == NULL || $section_data->getUpdateTime() == NULL) {
                $section_data->setCreatedTime(now())
                    ->setUpdateTime(now());
            } else {
                $section_data->setUpdateTime(now());
            }
            $section_data->save();
            $section_id = $section_data->getSectionId();
            unset($section_order);
            unset($section_status);
            unset($section);
            unset($section_data);
            $response = array('show_form' => Mage::helper('core')->escapeHtml($show_edit_content_form), 'get_fields' => Mage::helper('core')->htmlEscape($get_fields), 'section_id' => $section_id);
            $response = json_encode($response);
            $this->getResponse()->setBody($response);
        } catch (Exception $exc) {
            $this->getResponse()->setBody($exc);
        }
    }
    public function getsectionsAction(){
        $hook_id = $this->getRequest()->getPost('hook_id');
        $delete_link = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/deletecontent/");
        $get_fields_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getfields/");
        $show_edit_content_form_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $response = null;
        $section_collection = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))->setOrder('section_order', 'ASC');
        foreach ($section_collection as $section) {
            $response .= "<div class=\"section\">";
            $response .= "<div class=\"delete-section\"><a href=\"javascript:void(0);\" onClick=\"deleteContent('" . $delete_link . "','section', " . $section->getSectionId() . ")\">Del</a></div>";
            $response .= "<div class=\"view-section\">";
            $response .= "<div class=\"section\"><a href=\"javascript:void(0);\" onclick=\"getFields('" . $show_edit_content_form_action . "', '" . $get_fields_action . "', " . $section->getSectionId() . ")\">" .$section->getName() . "</a></div>";
            $response .= "</div>";
            $response .= "</div>";
        }
        $this->getResponse()->setBody($response);
        return;
    }
    public function getsectionsfordropdownAction(){

        $hook_id = $this->getRequest()->getPost('hook_id');
        $response = null;
        $section_collection = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
        $response .= "<option value=\"0\">Select a Section</option>";
        foreach ($section_collection as $section) {
            $response .= "<option value=\"" . $section->getsection_id() . "\">" . $section->getName() . "</option>";
        }
        $this->getResponse()->setBody($response);
    }
    
    public function savefieldAction(){
        $show_edit_content_form = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $get_forms = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getforms/");
        $get_fields = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getfields/");
        try {
            $field = $this->getRequest()->getParam('field');
            $field_order = $this->getRequest()->getParam('field_order');
            $field_section = $this->getRequest()->getParam('field_section');
            $field_status = $this->getRequest()->getParam('field_status');
            if($this->getRequest()->getParam('field_id')){
                $field_data = Mage::getModel('borncmshooks/fields')->load($this->getRequest()->getParam('field_id'));
                $form_collection = Mage::getModel('borncmshooks/forms')
                                   ->getCollection()
                                   ->addFieldToFilter('field_id', array('eq', $field_data->getFieldId()));
                $row_collection = Mage::getModel('borncmshooks/rows')
                                   ->getCollection()
                                   ->addFieldToFilter('field_id', array('eq', $field_data->getFieldId()));
                $type_collection = Mage::getModel('borncmshooks/types')
                                   ->getCollection()
                                   ->addFieldToFilter('field_id', array('eq', $field_data->getFieldId()));
                $value_collection = Mage::getModel('borncmshooks/values')
                                   ->getCollection()
                                   ->addFieldToFilter('field_id', array('eq', $field_data->getFieldId()));
            }else{
                $field_data = Mage::getModel('borncmshooks/fields');
            }
            $hook_id = $this->getRequest()->getParam('hook_id');
            $code = strtolower(str_replace(" ", "-", $field));
            $field_data->setName(ucwords($field))->setCode($code)->setHookId($hook_id)->setSectionId($field_section)->setFieldOrder($field_order)->setStatus($field_status);
            if ($field_data->getCreatedTime() == NULL || $field_data->getUpdateTime() == NULL) {
                $field_data->setCreatedTime(now())
                    ->setUpdateTime(now());
            } else {
                $field_data->setUpdateTime(now());
            }
            
            $field_data->save();
            $field_id = $field_data->getFieldId();
            $section_id = $field_data->getSectionId();
            
            #updating section ID for forms belonging to this field (if changed)
            if(isset($form_collection)){
              if(count($form_collection)>0){
                foreach ($form_collection as $form) {
                    $form->setSectionId($field_data->getSectionId())->setUpdateTime(now());
                }
                $form_collection->walk('save');
                }  
            }
            
            #updating section ID for rows belonging to this field (if changed)
            if(isset($row_collection)){
              if(count($row_collection)>0){
                foreach ($row_collection as $row) {
                    $row->setSectionId($field_data->getSectionId())->setUpdateTime(now());
                }
                $row_collection->walk('save');
                }  
            }
            
            #updating section ID for types belonging to this field (if changed)
            if(isset($type_collection)){
              if(count($type_collection)>0){
                foreach ($type_collection as $type) {
                    $type->setSectionId($field_data->getSectionId())->setUpdateTime(now());
                }
                $type_collection->walk('save');
                }  
            }
            
            #updating values ID for forms belonging to this field (if changed)
            if(isset($value_collection)){
              if(count($value_collection)>0){
                foreach ($value_collection as $value) {
                    $value->setSectionId($field_data->getSectionId())->setUpdateTime(now());
                }
                $value_collection->walk('save');
                }  
            }
            
            unset($field_section);
            unset($field_status);
            unset($field);
            unset($field_data);
            $response = array('show_form' => Mage::helper('core')->escapeHtml($show_edit_content_form), 
                              'get_forms' => Mage::helper('core')->escapeHtml($get_forms), 
                              'get_fields' => Mage::helper('core')->escapeHtml($get_fields),
                              'section_id' => $section_id,
                              'field_id' => $field_id);
            $response = json_encode($response);
            $this->getResponse()->setBody($response);
        } catch (Exception $exc) {
            $this->getResponse()->setBody($exc);
        }
    }
    public function getfieldsAction(){
        $section_id = $this->getRequest()->getPost('section_id');
        $get_forms_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getforms/");
        $show_edit_content_form_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $delete_link = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/deletecontent/");
        $response = null;
        $field_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('section_id', array('eq' => $section_id))->setOrder('field_order', 'ASC');
        foreach ($field_collection as $field) {
            $response .= "<div class=\"field\">";
            $response .= "<div class=\"delete-field\"><a href=\"javascript:void(0);\" onClick=\"deleteContent('" . $delete_link . "','field', " . $field->getFieldId() . ")\">Del</a></div>";
            $response .= "<div class=\"view-field\">";
            $response .= "<div class=\"field\"><a href=\"javascript:void(0);\" onclick=\"getForms('" . $show_edit_content_form_action . "', '" . $get_forms_action . "'," . $field->getFieldId() . ")\">" .$field->getName() . "</a></div>";
            $response .= "</div>";
            $response .= "</div>";
        }
        $this->getResponse()->setBody($response);
    }
    public function getfieldsfordropdownAction(){
        $section_id = $this->getRequest()->getPost('section_id');
        $response = null;
        $field_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('section_id', array('eq' => $section_id));
        $response .= "<option value=\"0\">Select a Field</option>";
        foreach ($field_collection as $field) {
            $response .= "<option value=\"" . $field->getFieldId() . "\">" . $field->getName() . "</option>";
        }
        $this->getResponse()->setBody($response);
    }
    
    public function getformsfordropdownAction(){
        $field_id = $this->getRequest()->getPost('field_id');
        $response = null;
        $form_collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('field_id', array('eq' => $field_id));
        $response .= "<option value=\"0\">Select a Form</option>";
        foreach ($form_collection as $form) {
            $response .= "<option value=\"" . $form->getFormId() . "\">" . $form->getName() . "</option>";
        }
        $this->getResponse()->setBody($response);
    }
    
    public function saveformAction(){
        $get_forms = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getforms/");
        $show_edit_content_form = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $get_fields_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getfields/");
        try {
            $form = $this->getRequest()->getParam('form');
            $form_description = $this->getRequest()->getParam('form_description');
            $form_section = $this->getRequest()->getParam('form_section');
            $form_field = $this->getRequest()->getParam('form_field');
            $form_status = $this->getRequest()->getParam('form_status');
            if($this->getRequest()->getParam('form_id')){
                $form_data = Mage::getModel('borncmshooks/forms')->load($this->getRequest()->getParam('form_id'));
                
                $row_collection = Mage::getModel('borncmshooks/rows')
                                   ->getCollection()
                                   ->addFieldToFilter('form_id', array('eq', $form_data->getFormId()));
                $type_collection = Mage::getModel('borncmshooks/types')
                                   ->getCollection()
                                   ->addFieldToFilter('form_id', array('eq', $form_data->getFormId()));
                $value_collection = Mage::getModel('borncmshooks/values')
                                   ->getCollection()
                                   ->addFieldToFilter('section_id', array('eq', $form_data->getSectionId()))
                                   ->addFieldToFilter('field_id', array('eq', $form_data->getFieldId()));
     
            }else{
                $form_data = Mage::getModel('borncmshooks/forms');
            }
            $hook_id = $this->getRequest()->getParam('hook_id');
            $code = strtolower(str_replace(" ", "-", $form));
            $form_data->setName(ucwords($form))
                      ->setDescription($form_description)
                      ->setCode($code)
                      ->setHookId($hook_id)
                      ->setSectionId($form_section)
                      ->setFieldId($form_field)
                      ->setStatus($form_status);
            if ($form_data->getCreatedTime() == NULL || $form_data->getUpdateTime() == NULL) {
                $form_data->setCreatedTime(now())
                    ->setUpdateTime(now());
            } else {
                $form_data->setUpdateTime(now());
            }
            $form_data->save();
            $form_id = $form_data->getFormId();
            
            #updating section ID for rows belonging to this field (if changed)
            if(isset($row_collection)){
              if(count($row_collection)>0){
                foreach ($row_collection as $row) {
                    $row->setSectionId($form_data->getSectionId())
                        ->setFieldId($form_data->getFieldId())
                        ->setUpdateTime(now());
                }
                $row_collection->walk('save');
                }  
            }
            
            #updating section ID for types belonging to this field (if changed)
            if(isset($type_collection)){
              if(count($type_collection)>0){
                foreach ($type_collection as $type) {
                    $type->setSectionId($form_data->getSectionId())
                        ->setFieldId($form_data->getFieldId())
                        ->setUpdateTime(now());
                }
                $type_collection->walk('save');
                }  
            }
            
            #updating section ID for types belonging to this field (if changed)
            if(isset($value_collection)){
              if(count($value_collection)>0){
                foreach ($value_collection as $value) {
                    $value->setSectionId($form_data->getSectionId())
                        ->setFieldId($form_data->getFieldId())
                        ->setUpdateTime(now());
                }
                $value_collection->walk('save');
                }  
            }
            
            unset($form);
            unset($form_section);
            unset($form_field);
            unset($form_status);
            unset($form_data);
            
            $response = array('show_form' => Mage::helper('core')->htmlEscape($show_edit_content_form), 
                              'get_forms' => Mage::helper('core')->htmlEscape($get_forms), 
                              'get_fields' => Mage::helper('core')->htmlEscape($get_fields_action),
                              'form_id' => $form_id);
            $response = json_encode($response);
            
            $this->getResponse()->setBody($response);
        } catch (Exception $exc) {
            $this->getResponse()->setBody($exc->getTraceAsString());
        }
    }
    public function getformsAction(){
        $field_id = $this->getRequest()->getPost('field_id');
        $get_forms_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getforms/");
        $delete_link = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/deletecontent/");
        $show_edit_content_form_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $response = null;
        $form_collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('field_id', array('eq' => $field_id));
        foreach ($form_collection as $form) {
            $response .= "<div class=\"form\">";
            $response .= "<div class=\"delete-form\"><a href=\"javascript:void(0);\" onClick=\"deleteContent('" . $delete_link . "','form', " . $form->getFormId() . ")\">Del</a></div>";
            $response .= "<div class=\"view-form\">";
            $response .= "<div class=\"section\"><a href=\"javascript:void(0);\" onclick=\"getForm('" . $show_edit_content_form_action . "', '" . $get_forms_action . "'," . $form->getFormId() . ")\">" .$form->getName() . "</a></div>";
            $response .= "</div>";
            $response .= "</div>";
        }
        $this->getResponse()->setBody($response);
    }
    
    public function deletecontentAction(){
 
        $show_edit_content_form = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $get_fields_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getfields/");
        $get_sections_action = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getsections/");
        $get_forms = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getforms/");
        
        $type = $this->getRequest()->getParam('type');
        $type_id = $this->getRequest()->getParam('type_id');
        switch ($type) {
            case 'section':
                $section_data = Mage::getModel('borncmshooks/sections')->load($type_id);
                $hook_id = $section_data->getHookId();
       
                $field_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('section_id', array('eq' => $section_data->getSectionId()));
                $row_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('section_id', array('eq' => $section_data->getSectionId()));
                $form_collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('section_id', array('eq' => $section_data->getSectionId()));
                $type_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('section_id', array('eq' => $section_data->getSectionId()));
                $value_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('section_id', array('eq' => $section_data->getSectionId()));
                
                $field_collection->walk('delete');
                $row_collection->walk('delete');
                $form_collection->walk('delete');
                $type_collection->walk('delete');
                $value_collection->walk('delete');

                $section_data->delete();
                $response = array('show_form' => Mage::helper('core')->htmlEscape($show_edit_content_form), 
                              'get_forms' => Mage::helper('core')->htmlEscape($get_forms), 
                              'get_fields' => Mage::helper('core')->htmlEscape($get_fields_action),
                              'get_sections' => Mage::helper('core')->htmlEscape($get_sections_action),
                              'hook_id' => $hook_id);
                $response = json_encode($response);
                $this->getResponse()->setBody($response);
                
            break;
            
            case 'field':
                $profile = microtime(true);
                $field_data = Mage::getModel('borncmshooks/fields')->load($type_id);
                $section_id = $field_data->getSectionId();
                
                $row_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('field_id', array('eq' => $field_data->getFieldId()));
                $form_collection = Mage::getModel('borncmshooks/forms')->getCollection()->addFieldToFilter('field_id', array('eq' => $field_data->getFieldId()));
                $type_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('field_id', array('eq' => $field_data->getFieldId()));
                $value_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('field_id', array('eq' => $field_data->getFieldId()));
                
                $row_collection->walk('delete');
                $form_collection->walk('delete');
                $type_collection->walk('delete');
                $value_collection->walk('delete');
                
                //Mage::log(microtime(true)-$profile. ' elapsed', null, 'fieldDelete.log');
                $field_data->delete();
                
                $response = array('show_form' => Mage::helper('core')->htmlEscape($show_edit_content_form), 
                              'get_forms' => Mage::helper('core')->htmlEscape($get_forms), 
                              'get_fields' => Mage::helper('core')->htmlEscape($get_fields_action),
                              'section_id' => $section_id);
                $response = json_encode($response);
                $this->getResponse()->setBody($response);
            break;
            
            case 'form':
                $form_data = Mage::getModel('borncmshooks/forms')->load($type_id);
                $field_id = $form_data->getFieldId();
                
                $row_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('form_id', array('eq' => $form_data->getFormId()));
                $type_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('form_id', array('eq' => $form_data->getFormId()));
                $type_ids = $type_collection->getColumnValues('type_id');
                $value_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('type_id', array('in' => $type_ids));

                $row_collection->walk('delete');
                $type_collection->walk('delete');
                $value_collection->walk('delete');
                
                $form_data->delete();
                
                $response = array('show_form' => Mage::helper('core')->htmlEscape($show_edit_content_form), 
                              'get_forms' => Mage::helper('core')->htmlEscape($get_forms), 
                              'get_fields' => Mage::helper('core')->htmlEscape($get_fields_action),
                              'field_id' => $field_id);
                $response = json_encode($response);
                $this->getResponse()->setBody($response);
            break;
        
            case 'element':
                $element_data = Mage::getModel('borncmshooks/types')->load($type_id);
                $form_id = $element_data->getFormId();
                $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('type_id', array('eq' => $type_id));
                $values_collection->walk('delete');
                $element_data->delete();
                
                $response = array('show_form' => Mage::helper('core')->htmlEscape($show_edit_content_form), 
                              'get_forms' => Mage::helper('core')->htmlEscape($get_forms), 
                              'get_fields' => Mage::helper('core')->htmlEscape($get_fields_action),
                              'form_id' => $form_id);
                $response = json_encode($response);
                $this->getResponse()->setBody($response);
            break;
        }
    }
    
    public function applyelementAction(){
        $show_edit_content_form = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/showeditcontentform/");
        $get_forms = Mage::helper("adminhtml")->getUrl("borncmshooks/adminhtml_borncmshooks/getforms/");
        
        try {
            
            $type = $this->getRequest()->getParam('element_type');
            $label = $this->getRequest()->getParam('element_label');
            $description = $this->getRequest()->getParam('element_config');
            $order = $this->getRequest()->getParam('element_order');
            $hook_id = $this->getRequest()->getParam('hook_id');
            $section_id = $this->getRequest()->getParam('section_id');
            $field_id = $this->getRequest()->getParam('field_id');
            $form_id = $this->getRequest()->getParam('form_id');
            
            if($this->getRequest()->getParam('element_id')){
                $type_model = Mage::getModel('borncmshooks/types')->load($this->getRequest()->getParam('element_id'));
            }else{
                $type_model = Mage::getModel('borncmshooks/types');
            }
            
//            $type_model = Mage::getModel('borncmshooks/types');
            $type_model->setType($type)
                       ->setLabel($label)
                       ->setDescription($description)
                       ->setElementOrder($order)
                       ->setHookId($hook_id)
                       ->setSectionId($section_id)
                       ->setFieldId($field_id)
                       ->setFormId($form_id);
           if ($type_model->getCreatedTime() == NULL || $type_model->getUpdateTime() == NULL) {
                $type_model->setCreatedTime(now())
                    ->setUpdateTime(now());
            } else {
                $type_model->setUpdateTime(now());
            }
            $type_model->save();
            $response = array('show_form' => Mage::helper('core')->htmlEscape($show_edit_content_form), 'get_forms' => Mage::helper('core')->htmlEscape($get_forms), 'form_id' => $form_id);
            $response = json_encode($response);
            $this->getResponse()->setBody($response);
        } catch (Exception $exc) {
            $this->getResponse()->setBody($exc->getTraceAsString());
        }
    }
    
    public function showaddelementformAction(){
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_elementform')->setData('givenvalues', $this->getRequest()->getParams())->toHtml());
    }
    
    public function shownewcontentformAction()
    {
        $type = $this->getRequest()->getParam('type');
        $hook_id = $this->getRequest()->getParam('hook_id');
        $this->loadLayout();
        switch ($type) {
            case 'section':
                $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_sectionform')->setData('hook_id', $hook_id)->toHtml());
            break;
        
            case 'field':
                $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_fieldform')->setData('hook_id', $hook_id)->toHtml());
            break;

            case 'form':
                $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_formform')->setData('hook_id', $hook_id)->toHtml());
            break;
        }
    }
    
    public function showformelementsAction(){
        $params = $this->getRequest()->getParams();
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_editform_tab_form')->setData('givenvalues', $params)->toHtml());
    }
    
    public function showeditcontentformAction(){

        $type = $this->getRequest()->getParam('type');
        $type_id = $this->getRequest()->getParam('type_id');
        
        switch ($type) {
            case 'section':
                $this->loadLayout();
                $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_sectionform')->setData('sectiontypeid', $type_id)->toHtml());
            break;
        
            case 'field':
                $this->loadLayout();
                $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_fieldform')->setData('fieldtypeid', $type_id)->toHtml());
            break;

            case 'form':
                $this->loadLayout();
                $this->getResponse()->setBody($this->getLayout()->createBlock('borncmshooks/adminhtml_borncmshooks_edit_tab_formform')->setData('formtypeid', $type_id)->toHtml());
            break;
        }
    }
    public function importAction() {
        $uploads = new Zend_File_Transfer_Adapter_Http();
        $files  = $uploads->getFileInfo();
        $file = 'import_file';
        if ($uploads->isUploaded($file) && $uploads->isValid($file) && $uploads->receive($file)) {

            $info = $uploads->getFileInfo($file);
            $tmp  = $info[$file]['tmp_name'];
            $xmlImport = new Varien_Simplexml_Config();
            if ($xmlImport->loadFile($tmp)) {
                //import
                $ids = array();

                $transaction = Mage::getSingleton('core/resource')->getConnection('core_write');

                try {
                    $transaction->beginTransaction();
                    $items = $xmlImport->getXpath('/root/borncmshooks/items/item');
                    foreach ( $items as $item) {
                        $itemArray = $item->asArray();
                        $model = Mage::getModel('borncmshooks/borncmshooks');
                        foreach ($itemArray as $key => $value) {
                            if ($key != 'hook_id')
                                $model->setData($key, $value);
                            else
                                $origId = $value;
                        }
                        $model->save();
                        $ids['hook_id']['orig_'.$origId] = $model->getData('hook_id');
                    }



                    $items = $xmlImport->getXpath('/root/sections/items/item');
                    foreach ( $items as $item) {
                        $itemArray = $item->asArray();
                        $model = Mage::getModel('borncmshooks/sections');
                        foreach ($itemArray as $key => $value) {
                            if ($key != 'section_id')
                                $model->setData($key, $value);
                            else
                                $origId = $value;

                            if ($key == 'hook_id')
                                $model->setData('hook_id', $ids['hook_id']['orig_'.$value]);

                        }
                        $model->save();
                        $ids['section_id']['orig_'.$origId] = $model->getData('section_id');
                    }

                    $items = $xmlImport->getXpath('/root/fields/items/item');
                    foreach ( $items as $item) {
                        $itemArray = $item->asArray();
                        $model = Mage::getModel('borncmshooks/fields');
                        foreach ($itemArray as $key => $value) {
                            if ($key != 'field_id')
                                $model->setData($key, $value);
                            else
                                $origId = $value;

                            if ($key == 'hook_id')
                                $model->setData('hook_id', $ids['hook_id']['orig_'.$value]);
                            if ($key == 'section_id')
                                $model->setData('section_id', $ids['section_id']['orig_'.$value]);
                        }
                        $model->save();
                        $ids['field_id']['orig_'.$origId] = $model->getData('field_id');
                    }

                    $items = $xmlImport->getXpath('/root/forms/items/item');
                    foreach ( $items as $item) {
                        $itemArray = $item->asArray();
                        $model = Mage::getModel('borncmshooks/forms');
                        foreach ($itemArray as $key => $value) {
                            if ($key != 'form_id')
                                $model->setData($key, $value);
                            else
                                $origId = $value;

                            if ($key == 'hook_id')
                                $model->setData('hook_id', $ids['hook_id']['orig_'.$value]);
                            if ($key == 'section_id')
                                $model->setData('section_id', $ids['section_id']['orig_'.$value]);
                            if ($key == 'field_id')
                                $model->setData('field_id', $ids['field_id']['orig_'.$value]);
                        }
                        $model->save();
                        $ids['form_id']['orig_'.$origId] = $model->getData('form_id');
                    }

                    $items = $xmlImport->getXpath('/root/types/items/item');
                    foreach ( $items as $item) {
                        $itemArray = $item->asArray();
                        $model = Mage::getModel('borncmshooks/types');
                        foreach ($itemArray as $key => $value) {
                            if ($key != 'type_id')
                                $model->setData($key, $value);
                            else
                                $origId = $value;

                            if ($key == 'hook_id')
                                $model->setData('hook_id', $ids['hook_id']['orig_'.$value]);
                            if ($key == 'section_id')
                                $model->setData('section_id', $ids['section_id']['orig_'.$value]);
                            if ($key == 'field_id')
                                $model->setData('field_id', $ids['field_id']['orig_'.$value]);
                            if ($key == 'form_id')
                                $model->setData('form_id', $ids['form_id']['orig_'.$value]);
                        }
                        $model->save();
                        $ids['type_id']['orig_'.$origId] = $model->getData('type_id');
                    }

                    $items = $xmlImport->getXpath('/root/rows/items/item');
                    foreach ( $items as $item) {
                        $itemArray = $item->asArray();
                        $model = Mage::getModel('borncmshooks/rows');
                        foreach ($itemArray as $key => $value) {
                            if ($key != 'row_id')
                                $model->setData($key, $value);
                            else
                                $origId = $value;

                            if ($key == 'hook_id')
                                $model->setData('hook_id', $ids['hook_id']['orig_'.$value]);
                            if ($key == 'section_id')
                                $model->setData('section_id', $ids['section_id']['orig_'.$value]);
                            if ($key == 'field_id')
                                $model->setData('field_id', $ids['field_id']['orig_'.$value]);
                            if ($key == 'form_id')
                                $model->setData('form_id', $ids['form_id']['orig_'.$value]);
                        }
                        $model->save();
                        $ids['row_id']['orig_'.$origId] = $model->getData('row_id');
                    }


                    $items = $xmlImport->getXpath('/root/values/items/item');
                    foreach ( $items as $item) {
                        $itemArray = $item->asArray();
                        $model = Mage::getModel('borncmshooks/values');
                        foreach ($itemArray as $key => $value) {
                            if ($key != 'value_id')
                                $model->setData($key, $value);
                            else
                                $origId = $value;

                            if ($key == 'hook_id')
                                $model->setData('hook_id', $ids['hook_id']['orig_'.$value]);
                            if ($key == 'section_id')
                                $model->setData('section_id', $ids['section_id']['orig_'.$value]);
                            if ($key == 'field_id')
                                $model->setData('field_id', $ids['field_id']['orig_'.$value]);
                            if ($key == 'row_id')
                                $model->setData('row_id', $ids['row_id']['orig_'.$value]);
                            if ($key == 'type_id')
                                $model->setData('type_id', $ids['type_id']['orig_'.$value]);
                        }
                        $model->save();
                        $ids['value_id']['orig_'.$origId] = $model->getData('value_id');
                    }


                    $transaction->commit();
                    $message = $this->__('Import has been proceeded successfully.');
                    Mage::getSingleton('adminhtml/session')->addSuccess($message);
                } catch (Exception $e) {
                    $transaction->rollback();
                    $message = $this->__('Import is failed.');
                    Mage::getSingleton('adminhtml/session')->addError($message);
                }
            } else {
                $message = $this->__('Invalid XML format file.');
                Mage::getSingleton('adminhtml/session')->addError($message);
            }





            // here $tmp is the location of the uploaded file on the server
            // var_dump($info); to see all the fields you can use

        } else {
            $message = $this->__('Please upload a valid XML format file.');
            Mage::getSingleton('adminhtml/session')->addError($message);
        }


        $this->_redirect('*/*/');
    }
}