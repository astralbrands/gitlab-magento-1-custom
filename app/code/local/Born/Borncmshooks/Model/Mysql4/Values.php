<?php

class Born_Borncmshooks_Model_Mysql4_Values extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the value_id refers to the key field in your database table.
        $this->_init('borncmshooks/values', 'value_id');
    }

    public function getContentThroughIds($hook_id,$section_id,$field_id,$row_id,$type_id){
    	$resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $value_id = null;
        try {
            $collection = Mage::getModel('borncmshooks/values')->getCollection()
                                                        ->addFieldToFilter('hook_id', $hook_id)
                                                        ->addFieldToFilter('section_id', $section_id)
                                                        ->addFieldToFilter('field_id', $field_id)
                                                        ->addFieldToFilter('row_id', $row_id)
                                                        ->addFieldToFilter('type_id', $type_id);

            $value_id = $collection->getFirstItem()->getContent();
        } catch (Exception $exc) {
            Zend_Debug::dump($exc->getTraceAsString());
        }
        return $value_id;
    }

    public function getIdThroughIds($hook_id,$section_id,$field_id,$row_id,$type_id){
    	$resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $value_id = null;
        try {
            $collection = Mage::getModel('borncmshooks/values')->getCollection()
                                                        ->addFieldToFilter('hook_id', $hook_id)
                                                        ->addFieldToFilter('section_id', $section_id)
                                                        ->addFieldToFilter('field_id', $field_id)
                                                        ->addFieldToFilter('row_id', $row_id)
                                                        ->addFieldToFilter('type_id', $type_id);
            $value_id = $collection->getFirstItem()->getValueId();
        } catch (Exception $exc) {
            Zend_Debug::dump($exc->getTraceAsString());
        }
        return $value_id;
    }
}