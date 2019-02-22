<?php 
class Born_Package_Block_Catalog_Navigation extends Mage_Catalog_Block_Navigation
{
	private $level = 3; 

	public function showCategoryFilters()
	{
		$currentCategory = $this->getCurrentCategory();
		
		if($currentCategory->getLevel() >= $this->level)
		{
			return true;
		}
		return false;
	}

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