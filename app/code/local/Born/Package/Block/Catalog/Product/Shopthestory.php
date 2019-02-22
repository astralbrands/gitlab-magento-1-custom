<?php

class Born_Package_Block_Catalog_Product_Shopthestory extends Born_Package_Block_Catalog_Product_List
{
    protected $_skus = '';

    public function getProductCollection() {
        if ($this->_skus){
            $productSkus = explode(',',$this->_skus);
            if (is_array($productSkus) && $productSkus)
            $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
            $collection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToFilter('sku', array('in' => $productSkus))
                ->addAttributeToSelect($attributes)
                //->setPageSize(3)
                //->setCurPage(1)
            ;


            return $collection;
        }
        return;
    }


    public function setProductsBySkus($skus) {
        $this->_skus = $skus;
    }
}


?>