<?php 

class Born_Package_Block_Catalog_Product_Customlist extends Born_Package_Block_Catalog_Product_List
{
	
    public function groupProducts($sortFlag = true) {

        $products = $this->getLoadedProductCollection();
        
        $subCategories = $this->getGroupCategoryIds();

        $_currentCategoryId = $this->getLayer()->getCurrentCategory()->getId();

        $result = array();
        foreach($products as $product) {

            $categories = $product->getCategoryIds();
            $_isProductAdded = false;
            foreach($categories as $category) {


                if(in_array($category, $subCategories) && $this->showProduct($_isProductAdded)) {

                    if(!isset($result[$category])) {
                        $result[$category] = array();
                    }
                    $result[$category][] = $product;
                    $_isProductAdded = true;
                }
            }
            if(!$_isProductAdded){
                //Products that do not belong to any categories will be stored in the $_currentCategoryId array key
                if(!isset($result[$_currentCategoryId])) {
                    $result[$_currentCategoryId] = array();
                }
                $result[$_currentCategoryId][] = $product;
                $_isProductAdded = true;
            }
        }

        //Move the $_currentCategoryId items to the end of the $result array
        if (isset($result[$_currentCategoryId])) {
            $_tempStorage = $result[$_currentCategoryId];
            unset($result[$_currentCategoryId]);
            $result[$_currentCategoryId] = $_tempStorage;
        }

        if ($sortFlag) {
            $result = $this->getSortCategoryProductCollection($result);
        }
        
        return $result;
    }

    public function getSortCategoryProductCollection($productCollectionArray)
    {
        $categoryIds = array_keys($productCollectionArray);

        $sortedProductCollectionArray = array();

        $currentCategoryId = $this->getLayer()->getCurrentCategory()->getId();

        if ($categoryIds && is_array($categoryIds) && count($categoryIds) > 0) {
            $sortedCategoryIds = $this->getSortedCategories($categoryIds);  

            foreach ($sortedCategoryIds as $key => $id) 
            {

                if (isset($productCollectionArray[$id]) && $productCollectionArray[$id]) {
                    $sortedProductCollectionArray[$id] = $productCollectionArray[$id];    
                }
            }

            if (isset($parentCategoryProducts)) {
                $sortedProductCollectionArray[$currentCategoryId] = $parentCategoryProducts;
            }

            return $sortedProductCollectionArray;
        }

        return false;
    }

    public function getCategoryById($catedoryId)
    {
        return Mage::getModel('catalog/category')->load($catedoryId);
    }

    protected function getGroupCategoryIds($sortFlag = true)
    {
        $currentCategory = $this->getLayer()->getCurrentCategory();

        $childrenCategoryIds = $currentCategory->getChildren();

        $childrenCategoryIds = explode(',', $childrenCategoryIds);

        return $childrenCategoryIds;
    }

    protected function getSortedCategories($categoryIds)
    {
        if (is_null($categoryIds)) {
            return;
        }

        $tempArray = array();
        $currentCategoryId = $this->getLayer()->getCurrentCategory()->getId();

        foreach ($categoryIds as $id) {

            if ($currentCategoryId == $id) {
                $_addCurrentCategoryId = true;
            }else{
                $tempArray[$this->getCategoryPosition($id)] = $id;
            }
        }

        ksort($tempArray);

        if (isset($_addCurrentCategoryId)) {
            $tempArray[] = (int)$currentCategoryId;
        }

        $categoryIds = $tempArray;

        return $categoryIds;

    }

    protected function getCategoryPosition($categoryId)
    {
        $rowData =  Mage::getModel('born_package/catalog_category_data')->getCategoryEntity($categoryId);

        if ($rowData && isset($rowData['position']) && $rowData['position']) {
            return $rowData['position'];
        }

        return;
    }

    protected function showProduct($_isProductAdded)
    {
        $_storeId = Mage::app()->getStore()->getStoreId();

        $_displayOnce = Mage::getStoreConfig('catalog/category_groups/listing_display_product_once', $_storeId);

        if ($_isProductAdded) {
            if ($_displayOnce) {
                return false;
            }
            else{
                return true;
            }
        }

        return true;
    }
}


?>