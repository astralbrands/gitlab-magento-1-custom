<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Resource_Hreflang_Ee_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('enterprise_urlrewrite/url_rewrite', 'url_rewrite_id');
    }

    /**
     * @param array $productIds
     * @param array $storeIds
     * @return Varien_Db_Select
     */
    public function getSelect($productIds, $storeIds)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('main_table' => $this->getTable('enterprise_urlrewrite/url_rewrite')),
                array('store_id', 'entity_id' => 'value_id', 'request_path')
            )
            ->where('value_id IN(?)', $productIds)
            ->where('entity_type = (?)', Enterprise_Catalog_Model_Product::URL_REWRITE_ENTITY_TYPE)
            ->where('target_path LIKE ?', 'catalog/product/view/id/%')
            ->where('store_id IN(?)', $storeIds)
            ->where('is_system = ?', 1)
            ->order('store_id ASC'); //specific store values will overwrite defaults

        return $select;
    }
}
