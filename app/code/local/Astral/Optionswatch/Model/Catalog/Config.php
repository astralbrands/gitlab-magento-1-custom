<?php 

class Astral_Optionswatch_Model_Catalog_Config extends Mage_Catalog_Model_Config
{
	public function getAttributeUsedForSortByArray()
	{

		$options = array(
			'position'  => Mage::helper('catalog')->__('Position'),
			'created_at'  => Mage::helper('catalog')->__('Newest')
			);
		foreach ($this->getAttributesUsedForSortBy() as $attribute) {
			/* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
			$options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
		}

		return $options;
	}
}

 ?>