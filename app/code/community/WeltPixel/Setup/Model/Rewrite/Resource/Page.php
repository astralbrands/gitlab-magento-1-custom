<?php

class WeltPixel_Setup_Model_Rewrite_Resource_Page extends Mage_Cms_Model_Resource_Page {

    protected function _getLoadSelect($field, $value, $object) {
        $select = Mage_Core_Model_Resource_Db_Abstract::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int) $object->getStoreId());
            $select->join(
                            array('cms_page_store' => $this->getTable('cms/page_store')), $this->getMainTable() . '.page_id = cms_page_store.page_id', array())
                    ->where('cms_page_store.store_id IN (?)', $storeIds)
                    ->order('cms_page_store.store_id DESC')
                    ->limit(1);
        }

        $ignoreActivationFlag = $object->getData('ignore_activation_flag');
        if (!isset($ignoreActivationFlag) || !$ignoreActivationFlag) {
            $select->where('is_active = ?', 1);
        }

        return $select;
    }
}
