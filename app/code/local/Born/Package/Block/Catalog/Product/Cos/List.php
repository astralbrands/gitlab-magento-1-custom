<?php 

class Born_Package_Block_Catalog_Product_Cos_List extends Born_Package_Block_Catalog_Product_List
{

    public function displayCustomList()
    {    
        $_currentCategory = $this->getLayer()->getCurrentCategory();

        if($this->isAllProductCategory($_currentCategory) && !$this->isFilterApplied()){
            return true;
        }

        return false;
    }

    protected function isAllProductCategory($_currentCategory)
    {
        //all-product category id = 102

        $_helper = Mage::helper('born_package/catalog_category_data');

        if ($_currentCategory->getGroupByCategoryId()){
            return true;
        }
        return false;
    }


}

?>
