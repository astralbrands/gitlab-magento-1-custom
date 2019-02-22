<?php

class WeltPixel_Setup_Model_Rewrite_Resource_Block extends Mage_Cms_Model_Resource_Block {

    protected function _getLoadSelect($field, $value, $object) {

        $select = Mage_Core_Model_Resource_Db_Abstract::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = array(
                (int) $object->getStoreId(),
                Mage_Core_Model_App::ADMIN_STORE_ID,
            );

            $select->join(
                            array('cbs' => $this->getTable('cms/block_store')), $this->getMainTable() . '.block_id = cbs.block_id', array('store_id')
                    )
                    ->where('cbs.store_id in (?) ', $stores)
                    ->order('store_id DESC')
                    ->limit(1);

            $ignoreActivationFlag = $object->getData('ignore_activation_flag');
            if (!isset($ignoreActivationFlag) || !$ignoreActivationFlag) {
                $select->where('is_active = ?', 1);
            }
        }

        return $select;
    }

}
