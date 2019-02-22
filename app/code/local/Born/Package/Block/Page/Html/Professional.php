<?php 

class Born_Package_Block_Page_Html_Professional extends Mage_Page_Block_Html_Header
{
	public function getProfessionalCategory()
	{
		$_professionalCategoryId = $this->getProfessionalIdFromConfig();

		$_storeCategories = Mage::helper('catalog/category')->getStoreCategories();

		foreach ($_storeCategories as $_category) {
			if ($_category->getId() == $_professionalCategoryId && $_category->getIsActive()) {
				return $_category;
			}
		}
		return;
	}

	public function getProfessionalIdFromConfig()
	{
		$path = 'catalog/miscellaneous/professional_category_id';
		$config = Mage::getStoreConfig($path);

		return $config;
	}

	//@deprecated
	public function getProfessionalIdFromCache()
	{
		$cache = Mage::app()->getCache();

		if (!$cache) {
			return $this->getProfessionalCategoryId();
		}

		if ($cache->load("professional_cat_id")) {
			return unserialize($cache->load("professional_cat_id"));
		}else{
			$_professionalCategoryId = $this->getProfessionalCategoryId();

			$cache->save(serialize($_professionalCategoryId), "professional_cat_id", array("professional_cat_id"), 10);
			return unserialize($cache->load("professional_cat_id"));
		}

		return;
	}

}

?>