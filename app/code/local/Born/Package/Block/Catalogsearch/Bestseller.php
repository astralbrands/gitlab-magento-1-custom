<?php

class Born_Package_Block_Catalogsearch_Bestseller extends Mage_Catalog_Block_Product_List
{
    protected $_categoryId;
    protected $_cartTitle;
    protected $_limit;

    public function __construct(){
        $storeId = Mage::app()->getStore()->getStoreId();
        $this->_categoryId = Mage::getStoreConfig('born_package/best_seller_setting/category_id',$storeId);
        $this->_cartTitle = Mage::getStoreConfig('born_package/best_seller_setting/title',$storeId);
    }

    public function getBestSellersCategory(){

        $category_id = $this->_categoryId;
        
        if($category_id){
            $category = Mage::getModel('catalog/category')->load($category_id);
            return $category;
        }
        return;
    }

    public function getBestSellersProducts() {

        $category = $this->getBestSellersCategory();

        $limit = $this->getDisplayLimit();

        if ($category->getIsActive()) {
            $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setPageSize($limit)
            ->addAttributeToSelect('*')
            ->addCategoryFilter($category)
            ->setOrder('position', 'ASC')
            ->load();

            return $products;
        }
    }
}