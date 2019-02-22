<?php 

class Born_Package_Model_Catalog_Category_Data extends Born_Package_Model_Catalog_Attribute_Data 
{
	const CATEGORY_INT_TABLE = 'catalog_category_entity_int';
	const CATEGORY_ENTITY_TABLE = 'catalog_category_entity';
	const ONE_PAGE_ORDER_ATTRIBUTE_ID = 299;

	public function getCategoryAttributeValueInt($entityId, $attributeId)
	{
		$tableName = self::CATEGORY_INT_TABLE;

		return $this->getAttributeValue($entityId, $attributeId, $tableName);
	}

	public function getOnePageOrderValue($entityId)
	{
		$attributeId = self::ONE_PAGE_ORDER_ATTRIBUTE_ID;

		return $this->getCategoryAttributeValueInt($entityId, $attributeId);
	}

	public function getCategoryEntity($categoryId)
	{
		if (!$categoryId || !is_numeric($categoryId)) {
			return;
		}

		$entityId = $categoryId;
		$tableName = self::CATEGORY_ENTITY_TABLE;

		try {
			$_query = "SELECT  * FROM {$tableName} WHERE entity_id='{$entityId}';";

			$attributes = $this->_getConnection()->fetchAll($_query);

			if ($attributes && is_array($attributes) && count($attributes) > 0) {
				$attributes = array_shift($attributes);
			}

			return $attributes;

		} catch (Exception $e) {
			Mage::logException($e);
			return;
		}

		return;		
	}

}

 ?>