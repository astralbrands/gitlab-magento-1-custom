<?php 

class Born_Package_Block_Catalog_Product_Cos_Customlist extends Born_Package_Block_Catalog_Product_Customlist
{

    public function getCategoryById($catedoryId)
    {
        return Mage::getModel('catalog/category')->load($catedoryId);
    }

    protected function getGroupCategoryIds()
    {
        $_category = $this->getLayer()->getCurrentCategory();

        if ($_category->getGroupByCategoryId() && is_numeric($_category->getGroupByCategoryId())) {

            $_groupParentCategory = $this->getCategoryById($_category->getGroupByCategoryId());

            if ($_groupParentCategory->getId()) {
                $_groupCategoryIds = explode(',', $_groupParentCategory->getChildren());

                return $_groupCategoryIds;
            }
            else{
                //Mage::log('Unable to load category id ' . $_category->getGroupByCategoryId() . ' in ' . $_category->getUrl());
            }
        }
    }


    //@deprecated
    //Get all of the product type categories
    protected function getGroupCategory()
    {
        $_currentCategory = $this->getLayer()->getCurrentCategory();

        $_helper = Mage::helper('born_package/catalog_category_data');
        $_groupListConfigs = $_helper->getGroupListConfig(); //array: category_id, group_code

        foreach ($_groupListConfigs as $_config) {
            if ($_config['category_id'] == $_currentCategory->getId()){
                //array: category_id, group_code

                $_categoryGroupId = $_helper->getCategoryGroupByCode($_config['group_code'], 'value_id');

                $_parentCategory = $_currentCategory->getParentCategory();
                $_childCategoryIds = $_parentCategory->getChildren();

                $groupCategoryIds = array();

                foreach (explode(',', $_childCategoryIds) as $_childId) {
                    $_childCategory = Mage::getModel('catalog/category')->load($_childId);

                    if ($_childCategory->getCategoryGroup() == $_categoryGroupId) {
                        $groupCategoryIds[] = $_childId;
                    }

                }
                return $groupCategoryIds;
            }
        }
        return;
    }


}

?>