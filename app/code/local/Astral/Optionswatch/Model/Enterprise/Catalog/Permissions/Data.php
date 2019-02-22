<?php 
class Astral_Optionswatch_Model_Enterprise_Catalog_Permissions_Data extends Astral_Optionswatch_Model_Catalog_Attribute_Data
{
	const ENTERPRISE_CATALOGPERMISSIONS_INDEX_TABLE = 'enterprise_catalogpermissions_index';

	public function getProductPermission($product)
	{	
		    if (is_null($product))
		    {
			return;
		    }
		$_categoryIds = $product->getCategoryIds();
		    if ($_categoryIds && is_array($_categoryIds) && count($_categoryIds) > 0)
		    {
			    if ($_customer = Mage::helper('customer')->getCustomer())
			    {
				$_groupId = $_customer->getGroupId();
				$_websiteId = $_customer->getWebsiteId();
			    }
			$_tableName = $this->_getIndexTableName();
			$results = array();
			    foreach ($_categoryIds as $categoryId)
			    {
				$_query = "SELECT  * FROM {$_tableName} WHERE category_id = '{$categoryId}' AND website_id = '{$_websiteId}' AND customer_group_id = '{$_groupId}';";
				$_result = null;
				    try {
					$_result = $this->_getConnection()->fetchAll($_query);
				    } catch (Exception $e) {
					Mage::logException($e);
					return;
				    }
				    foreach ($_result as $key => $data)
				    {
					$results[] = $data;
				    }
			    }
			    if (count($results) > 0)
			    {
				return $results;
			    }
			return;
		    }
	}

	protected function _getIndexTableName()
	{
		return self::ENTERPRISE_CATALOGPERMISSIONS_INDEX_TABLE;
	}
}