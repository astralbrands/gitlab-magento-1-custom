<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Resource_Hreflang_Page extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('cms/page', 'page_id');
    }

    /**
     * @param array $pageValues
     * @param array $storeIds
     * @return Varien_Db_Select
     */
    public function getSelect($pageValues, $storeIds)
    {
        $relField = Mage::helper('amseotoolkit/hrefurl')->getCmsRelationField();
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main_table' => $this->getTable('cms/page')),
                array(
                    'request_path' => 'main_table.identifier',
                    'store_id' => 'page_store.store_id',
                    'entity_id' => "main_table.$relField"
                )
            )
            ->joinInner(
                array('page_store' => $this->getTable('cms/page_store')),
                'main_table.page_id = page_store.page_id',
                array()
            )
            ->where("main_table.`$relField` IN (?)", $pageValues)
            ->where('main_table.is_active = ?', 1)
            ->where('page_store.store_id IN(?)', $storeIds);

        return $select;
    }
}
