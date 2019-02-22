<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Resource_Hreflang_Ce_Category extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('core/url_rewrite', 'url_rewrite_id');
    }

    /**
     * @param array $categoryIds
     * @param array $storeIds
     * @return Varien_Db_Select
     */
    public function getSelect($categoryIds, $storeIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main_table' => $this->getTable('core/url_rewrite')),
                array('store_id', 'entity_id' => 'category_id', 'request_path')
            )
            ->where('category_id IN(?)', $categoryIds)
            ->where('product_id IS NULL')
            ->where('target_path LIKE ?', 'catalog/category/view/id/%')
            ->where('store_id IN(?)', $storeIds)
            ->where('is_system = ?', 1);

        return $select;
    }
}
