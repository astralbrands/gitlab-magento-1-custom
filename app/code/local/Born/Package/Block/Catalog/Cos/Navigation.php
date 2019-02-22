<?php 
class Born_Package_Block_Catalog_Cos_Navigation extends Mage_Catalog_Block_Navigation
{

	private $_groupInfo = array();


	protected function getCustomCategory()
	{
		$_currentCategory = $this->getCurrentCategory();

		//Used to display the navigation for All Products category
		if ($_currentCategory->getGroupByCategoryId() && is_numeric($_currentCategory->getGroupByCategoryId())) {

			$_groupParentCategory = Mage::getModel('catalog/category')->load($_currentCategory->getGroupByCategoryId());

            if ($_groupParentCategory->getId()) {
            	return $_groupParentCategory;
            }
            else{
                //Mage::log('Unable to load category id ' . $_category->getGroupByCategoryId() . ' in ' . $_currentCategory->getUrl());
            }
        }
        else
        {
        	$_parentId = $_currentCategory->getParentId();
			$_parentCategory = Mage::getModel('catalog/category')->load($_parentId);

			//show_category_filter
			if ($_parentCategory->getShowCategoryFilter()) {
				return $_parentCategory;
			}

        }
        return;
	}

	protected function getFirstCategory($categories)
	{
		$_currentCategory = $this->getCurrentCategory();

		if (strpos($categories->getChildren(), $_currentCategory->getId())) {
			return;
		}

		return $_currentCategory;
	}

	/**
	 * @deprecated
	 */
	protected function setGroupInfo()
	{	
		$_helper = Mage::helper('born_package/catalog_category_data');
		$_categoryGroupsConfig = $_helper->getCategoryGroups();
		$_groupListConfigs = $_helper->getGroupListConfig();
		$_currentCategory = $this->getCurrentCategory();

		foreach ($_groupListConfigs as $_config) {
			if ($_currentCategory->getId() == $_config['category_id']) {
				$this->_groupInfo['group_code'] = $_config['group_code'];
			}
		}
		foreach($_categoryGroupsConfig as $_groupConfig){
			if($this->_groupInfo['group_code'] == $_groupConfig['group_code']){
				$this->_groupInfo['value_id'] = $_groupConfig['value_id'];
			}
		}
	}

	/**
	 * @deprecated
	 */
	protected function getGroupInfo()
	{
		if (!$this->_groupInfo) {
			$this->setGroupInfo();
		}
		return $this->_groupInfo;
	}

	/**
	 * @deprecated
	 */
	public function showCategoryFilters()
	{
		$_helper = Mage::helper('born_package/catalog_category_data');
		$_groupListConfigs = $_helper->getGroupListConfig();
		$_currentCategory = $this->getCurrentCategory();

		foreach ($_groupListConfigs as $_config) {
			if ($_currentCategory->getId() == $_config['category_id']) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @deprecated
	 */
	public function getCustomCategories()
	{
		if ($this->showCategoryFilters()) {
			$_currentCategory = $this->getCurrentCategory();
			$_parentId = $_currentCategory->getParentId();
			$_parentCategory = Mage::getModel('catalog/category')->load($_parentId);

			return $_parentCategory->getChildrenCategories();
		}

		return;
	}

	/**
	 * @deprecated
	 */
	public function showCategory($_categoryId)
	{
		$_category = Mage::getModel('catalog/category')->load($_categoryId);
		$_categoryGroup = $_category->getCategoryGroup();

		$_groupInfo = $this->getGroupInfo();

		if ($_groupInfo && $_categoryGroup != 0) {
			if (array_search($_categoryGroup, $_groupInfo)) {
				return true;
			}
		}
	}
	/**
	 * @deprecated
	 */
	public function getLevelThreeCategory()
	{
		$category = $this->getCurrentCategory();

		while($category->getLevel() > $this->level)
		{
			$parentId = $category->getParentId();
			$category = Mage::getModel('catalog/category')->load($parentId);
		}
		return $category;
	}
}

?>