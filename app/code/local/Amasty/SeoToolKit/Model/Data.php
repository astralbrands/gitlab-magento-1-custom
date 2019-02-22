<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Model_Data
{
    /** @var int */
    protected $_storeId;

    public function __construct()
    {
        $this->_storeId = Mage::app()->getStore()->getId();
    }

    /**
     * Product collection
     *
     * @param null $storeId
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getProductCollection($storeId = null)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('url_key');
        $collection->addAttributeToSelect('thumbnail');
        $collection->addAttributeToSelect('thumbnail_label');
        $collection->addAttributeToSelect('url_path');
        $collection->addAttributeToSelect('image');
        $collection->addStoreFilter($storeId ? $storeId : $this->_storeId);
        $collection->addUrlRewrite();
        if ($storeId) {
            $collection->setStoreId($storeId);
            Mage::unregister('amseotoolkit_store_id');
            Mage::register('amseotoolkit_store_id', $storeId);
        }

        $collection->addAttributeToFilter('status', 1);
        $collection->addAttributeToFilter('visibility', array('in' => array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
        )));

        return $collection;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
        /** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $collection = Mage::getResourceModel('catalog/category_collection');

        $rootId = Mage::app()->getStore($this->_storeId)->getRootCategoryId();

        $collection
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToSelect('image')
            ->addFieldToFilter('path', array('like' => "1/$rootId/%"))
            ->addAttributeToFilter('level', array('gt' => 1));

        $collection->addAttributeToFilter('is_active', 1);
        $collection->addUrlRewriteToResult();

        return $collection;
    }
}
