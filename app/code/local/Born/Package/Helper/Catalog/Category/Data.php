<?php 

class Born_Package_Helper_Catalog_Category_Data extends Mage_Core_Helper_Abstract
{

	public function getCategoryGroups()
	{
		$_configPath = 'catalog/category_groups/links';
		$_storeId = Mage::app()->getStore();
		$_configs = Mage::getStoreConfig($_configPath, $_storeId);
		$_configs = unserialize($_configs);

		return $_configs;
	}

	public function getGroupByValueId($valueId)
	{
		$_groups = $this->getCategoryGroups();

		foreach ($_groups as $_group) {
			if ($_group['value_id'] == $valueId) {
				return $_group;
			}
		}

		return;
	}

	public function getGroupTitleByValueId($valueId)
	{
		$_group = $this->getGroupByValueId($valueId);

		if ($_group) {
			return $_group['group_title'];
		}
	}

	public function getGroupListConfig()
	{
		$_path = 'catalog/category_groups/group_listing';
		$_configs = Mage::getStoreConfig($_path);

		$_configs = unserialize($_configs);

		return $_configs;
	}

	public function getCategoryGroupByCode($_groupCode, $_key=null)
	{
		$_allCategoryGroups = $this->getCategoryGroups();

		foreach($_allCategoryGroups as $_group)
		{
			if($_group['group_code'] == $_groupCode){
				if($_key && $_group[$_key]){
					return $_group[$_key];
				}

				return $_group;
			}
		}
	}

}
?>