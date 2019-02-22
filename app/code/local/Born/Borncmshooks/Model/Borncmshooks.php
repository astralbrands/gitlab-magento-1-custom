<?php

class Born_Borncmshooks_Model_Borncmshooks extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('borncmshooks/borncmshooks');
    }

    public function getPageCode(){
        
        if(Mage::app()->getFrontController()->getRequest()->getModuleName() == 'catalog'){
            $cat_id = Mage::app()->getFrontController()->getRequest()->getParam('id');
            $category = Mage::getModel('catalog/category')->load($cat_id);
            $url_key = strtolower($category->getUrlKey());
            $page_code = $cat_id.$url_key;
        }elseif(Mage::app()->getFrontController()->getRequest()->getModuleName() == 'cms'){
            $cms_page_id = Mage::getSingleton('cms/page')->getPageId();
            $cms_page_identifier = strtolower(Mage::getSingleton('cms/page')->getIdentifier());
            $page_code = $cms_page_id . $cms_page_identifier;
        }
        return $page_code;
    }
            
    public function getAllData($code=null){
      if($code == null){
        $code = $this->getPageCode();
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($code, 'code')->getHookId();
        
      }else{
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($code , 'code')->getHookId();
      }
      $response = null;
      
      $sections_collection = Mage::getModel('borncmshooks/sections')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                               ->addFieldToFilter('status', array('eq' => '1'))
                                               ->setOrder('section_order', 'ASC');
      $section_ids = $sections_collection->getColumnValues('section_id');
      $fields_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('section_id', array('in' => $section_ids))
                                             ->addFieldToFilter('status', array('eq' => '1'))
                                             ->setOrder('field_order', 'ASC');
     $field_ids = $fields_collection->getColumnValues('field_id'); 
     $rows_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('section_id', array('in' => $section_ids))
                                             ->addFieldToFilter('field_id', array('in' => $field_ids))
                                             ->addFieldToFilter('status', array('eq' => '1'))
                                             ->setOrder('row_order', 'ASC');
     $row_ids = $rows_collection->getColumnValues('row_id');
     $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('section_id', array('in' => $section_ids))
                                             ->addFieldToFilter('field_id', array('in' => $field_ids))
                                             ->addFieldToFilter('row_id', array('in' => $row_ids));
     
     $types_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('section_id', array('in' => $section_ids))
                                             ->addFieldToFilter('field_id', array('in' => $field_ids));
     
    foreach ($sections_collection as $section) {
      $current_section_code = $section->getCode();
      $response[$current_section_code] = array();
      $current_fields = $fields_collection->getItemsByColumnValue('section_id', $section->getSectionId());

      foreach ($current_fields as $field) {
        $current_field_code = $field->getCode();
        $response[$current_section_code][$current_field_code] = array();

        $current_rows = $rows_collection->getItemsByColumnValue('field_id', $field->getFieldId());
        foreach ($current_rows as $row) {
            $time = Mage::getModel('core/date')->timestamp(time());
            $start_date = Mage::getModel('core/date')->timestamp($row->getStartDate());
            $end_date = Mage::getModel('core/date')->timestamp($row->getEndDate());
            if($time >= $start_date && $time <= $end_date){
                $store_id = Mage::app()->getStore()->getId();
                $row_stores_id = explode(',', $row->getStoreId());
                if(in_array($store_id, $row_stores_id) || in_array('0', $row_stores_id)){
                  $current_row_code = $row->getCode();
                  $response[$current_section_code][$current_field_code][$current_row_code] = array();
                  $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('row_id', array('eq' => $row->getRowId()));
                  $current_values = $values_collection->getItemsByColumnValue('row_id', $row->getRowId());
                  foreach ($current_values as $value) {
                    $current_value_from_col = $types_collection->getItemsByColumnValue('type_id', $value->getTypeId());
                    $current_value_obj = array_shift($current_value_from_col);
                    $current_value_label = $current_value_obj->getLabel();
                    $response[$current_section_code][$current_field_code][$current_row_code][$current_value_label] = $value->getContent();
                  }
                  $response[$current_section_code][$current_field_code][$current_row_code]['created_time'] = $row->getCreatedTime();
                }
            }
        }                                            
      }                                            
    }
    return $response;
    }
    
    public function getSection($section_name, $page_code=null){
      if($page_code == null){
        $page_code = $this->getPageCode();
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($page_code, 'code')->getHookId();
      }else{
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($page_code, 'code')->getHookId();
      }
      
      $response = null;
      $sections_collection = Mage::getModel('borncmshooks/sections')
	      ->getCollection()
	      ->addFieldToFilter('hook_id', array('eq' => $hook_id))
	      ->addFieldToFilter('code', array('eq' => $section_name))
	      ->addFieldToFilter('status', array('eq' => '1'))
	      ->setOrder('section_order', 'ASC');
      $section_ids = $sections_collection->getColumnValues('section_id');
      
      $fields_collection = Mage::getModel('borncmshooks/fields')
	      ->getCollection()
	      ->addFieldToFilter('hook_id', array('eq' => $hook_id))
	      ->addFieldToFilter('section_id', array('in' => $section_ids))
	      ->addFieldToFilter('status', array('eq' => '1'))
	      ->setOrder('field_order', 'ASC');
     $field_ids = $fields_collection->getColumnValues('field_id');
      
     $rows_collection = Mage::getModel('borncmshooks/rows')
	      ->getCollection()
	      ->addFieldToFilter('hook_id', array('eq' => $hook_id))
	      ->addFieldToFilter('section_id', array('in' => $section_ids))
	      ->addFieldToFilter('field_id', array('in' => $field_ids))
	      ->addFieldToFilter('status', array('eq' => '1'))
	      ->setOrder('row_order', 'ASC');
     $row_ids = $rows_collection->getColumnValues('row_id');
     
     $values_collection = Mage::getModel('borncmshooks/values')
	      ->getCollection()
	      ->addFieldToFilter('hook_id', array('eq' => $hook_id))
	      ->addFieldToFilter('section_id', array('in' => $section_ids))
	      ->addFieldToFilter('field_id', array('in' => $field_ids))
	      ->addFieldToFilter('row_id', array('in' => $row_ids));
     
     $types_collection = Mage::getModel('borncmshooks/types')
	      ->getCollection()
	      ->addFieldToFilter('hook_id', array('eq' => $hook_id))
	      ->addFieldToFilter('section_id', array('in' => $section_ids))
	      ->addFieldToFilter('field_id', array('in' => $field_ids));

    foreach ($sections_collection as $section) {
      $current_section_code = $section->getCode();
      $response[$current_section_code] = array();

      $current_fields = $fields_collection->getItemsByColumnValue('section_id', $section->getSectionId());

      foreach ($current_fields as $field) {
        $current_field_code = $field->getCode();
        $response[$current_section_code][$current_field_code] = array();
        $current_rows = $rows_collection->getItemsByColumnValue('field_id', $field->getFieldId());

        foreach ($current_rows as $row) {
            $time = Mage::getModel('core/date')->timestamp(time());
            $start_date = Mage::getModel('core/date')->timestamp($row->getStartDate());
            $end_date = Mage::getModel('core/date')->timestamp($row->getEndDate());
            if($time >= $start_date && $time <= $end_date){
                $store_id = Mage::app()->getStore()->getId();
                $row_stores_id = explode(',', $row->getStoreId());
                if(in_array($store_id, $row_stores_id) || in_array('0', $row_stores_id)){
                  $current_row_code = $row->getCode();
                  $response[$current_section_code][$current_field_code][$current_row_code] = array();
                  $current_values = $values_collection->getItemsByColumnValue('row_id', $row->getRowId());
                  foreach ($current_values as $value) {
                    $current_value_from_col = $types_collection->getItemsByColumnValue('type_id', $value->getTypeId());
                    $current_value_obj = array_shift($current_value_from_col);
                    $current_value_label = $current_value_obj->getLabel();
                    $response[$current_section_code][$current_field_code][$current_row_code][$current_value_label] = $value->getContent();
                  }
                  $response[$current_section_code][$current_field_code][$current_row_code]['created_time'] = $value->getCreatedTime();
                }
            }
        }                                            
      }                                            
    }
    return $response;
    }
    
    public function getField($field_name, $page_code=null){
      if($page_code == null){
        $page_code = $this->getPageCode();
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($page_code, 'code')->getHookId();
      }else{
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($page_code, 'code')->getHookId();
      }
      $response = null;
      $fields_collection = Mage::getModel('borncmshooks/fields')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('code', array('eq' => $field_name))
                                           ->addFieldToFilter('status', array('eq' => '1'))
                                           ->setOrder('field_order', 'ASC');

      $field_ids = $fields_collection->getColumnValues('field_id');
      
      $rows_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('field_id', array('in' => $field_ids))
                                             ->addFieldToFilter('status', array('eq' => '1'))
                                             ->setOrder('row_order', 'ASC');
      $row_ids = $rows_collection->getColumnValues('row_id');
     
      $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('field_id', array('in' => $field_ids))
                                             ->addFieldToFilter('row_id', array('in' => $row_ids));
     
      $types_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('field_id', array('in' => $field_ids));
     
    foreach ($fields_collection as $field) {
      $current_field_code = $field->getCode();
      $response[$current_field_code] = array();

      $current_rows = $rows_collection->getItemsByColumnValue('field_id', $field->getFieldId());
      foreach ($current_rows as $row) {
          $time = Mage::getModel('core/date')->timestamp(time());
            $start_date = Mage::getModel('core/date')->timestamp($row->getStartDate());
            $end_date = Mage::getModel('core/date')->timestamp($row->getEndDate());
            if($time >= $start_date && $time <= $end_date){
                $store_id = Mage::app()->getStore()->getId();
                $row_stores_id = explode(',', $row->getStoreId());
                if(in_array($store_id, $row_stores_id) || in_array('0', $row_stores_id)){
                  $current_row_code = $row->getCode();
                  $response[$current_field_code][$current_row_code] = array();
                  $response[$current_field_code][$current_row_code]['row_id'] = $row->getRowId();
                  $current_values = $values_collection->getItemsByColumnValue('row_id', $row->getRowId());
                  foreach ($current_values as $value) {
                    $current_value_from_col = $types_collection->getItemsByColumnValue('type_id', $value->getTypeId());
                    $current_value_obj = array_shift($current_value_from_col);
                    $current_value_label = $current_value_obj->getLabel();
                    $response[$current_field_code][$current_row_code][$current_value_label] = $value->getContent();
                  }
                  $response[$current_field_code][$current_row_code]['created_time'] = $row->getCreatedTime();
            }
        }
      }                                            
    }
    return $response;   
    }
    
    public function getRow($row_name, $page_code=null){
      if($page_code == null){
        $page_code = $this->getPageCode();
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($page_code, 'code')->getHookId();
      }else{
        $hook_id = Mage::getModel('borncmshooks/borncmshooks')->load($page_code, 'code')->getHookId();
      }
      $response = null;

    $rows_collection = Mage::getModel('borncmshooks/rows')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                         ->addFieldToFilter('code', array('eq' => $row_name))
                                         ->addFieldToFilter('status', array('eq' => '1'))
                                         ->setOrder('row_order', 'ASC');
    $row_ids = $rows_collection->getColumnValues('row_id');
     
    $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id))
                                             ->addFieldToFilter('row_id', array('in' => $row_ids));
     
    $types_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('hook_id', array('eq' => $hook_id));
    
    foreach ($rows_collection as $row) {
        $time = Mage::getModel('core/date')->timestamp(time());
        $start_date = Mage::getModel('core/date')->timestamp($row->getStartDate());
        $end_date = Mage::getModel('core/date')->timestamp($row->getEndDate());
        if($time >= $start_date && $time <= $end_date){
            $store_id = Mage::app()->getStore()->getId();
            $row_stores_id = explode(',', $row->getStoreId());
            if(in_array($store_id, $row_stores_id) || in_array('0', $row_stores_id)){
              $current_row_code = $row->getCode();
              $response[$current_row_code] = array();
              $current_values = $values_collection->getItemsByColumnValue('row_id', $row->getRowId());
              foreach ($current_values as $value) {    
                  $current_value_from_col = $types_collection->getItemsByColumnValue('type_id', $value->getTypeId());
                  $current_value_obj = array_shift($current_value_from_col);
                  $current_value_label = $current_value_obj->getLabel();
                  $response[$current_row_code][$current_value_label] = $value->getContent();
              }
            }
        }
    }                                                
    return $response;   
    }
    
    public function getRowById($row_id){
    $response = null;
    $row = Mage::getModel('borncmshooks/rows')->load($row_id);
        $time = Mage::getModel('core/date')->timestamp(time());
        $start_date = Mage::getModel('core/date')->timestamp($row->getStartDate());
        $end_date = Mage::getModel('core/date')->timestamp($row->getEndDate());
        if($time >= $start_date && $time <= $end_date){
            $store_id = Mage::app()->getStore()->getId();
            $row_stores_id = explode(',', $row->getStoreId());
            if(in_array($store_id, $row_stores_id) || in_array('0', $row_stores_id)){
              $current_row_code = $row->getCode();
              $response[$current_row_code] = array();
              $values_collection = Mage::getModel('borncmshooks/values')->getCollection()->addFieldToFilter('row_id', array('eq' => $row->getRowId()));
              $type_ids = $values_collection->getColumnValues('type_id');
              $types_collection = Mage::getModel('borncmshooks/types')->getCollection()->addFieldToFilter('type_id', array('in' => $type_ids));
              foreach ($values_collection as $value) {
              $current_value_from_col = $types_collection->getItemsByColumnValue('type_id', $value->getTypeId());
              $current_value_obj = array_shift($current_value_from_col);
              $current_value_label = $current_value_obj->getLabel();
                $response[$current_row_code][$current_value_label] = $value->getContent();
              }
            }       
        }
    return $response;   
    }
    
}