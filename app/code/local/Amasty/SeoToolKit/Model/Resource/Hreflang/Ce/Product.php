<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Resource_Hreflang_Ce_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('core/url_rewrite', 'url_rewrite_id');
    }

    /**
     * @param array $productIds
     * @param array $storeIds
     * @param string|null $categoryId
     * @return Varien_Db_Select
     */
    public function getSelect($productIds, $storeIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main_table' => $this->getTable('core/url_rewrite')),
                array('store_id', 'entity_id' => 'product_id', 'request_path')
            )
            ->where('product_id IN (?)', $productIds)
            ->where('target_path LIKE ?', 'catalog/product/view/id/%')
            ->where('store_id IN(?)', $storeIds)
            ->where('is_system = ?', 1);

        $category = Mage::registry('current_category');
        if ($category) {
            $select->where('category_id = ?', $category->getId());
        } else {
            $select->where('category_id IS NULL');
        }

        return $select;
    }
}
