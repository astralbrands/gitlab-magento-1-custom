<?php 
class Astral_Optionswatch_Helper_Catalog_Product_Data extends Mage_Core_Helper_Abstract
{
	const SIZE_ATTRIBUTE_ID = 183;
	const PRODUCT_TYPE_ATTRIBUTE_ID = 223;

	public function getUsageIconClass($_usage)
	{
		$_config = $this->getUsageConfig();

		if ($_config && count($_config) > 2) {

            //Get the first value if it contains multiple values
			if (strpos($_usage, ',')) {
				$_usage = explode(',', $_usage);
				$_usage = array_shift($_usage);
			}

			switch ($_usage) {
				case array_shift($_config):
				return 'am-icon';
				break;
				case array_shift($_config):
				return 'pm-icon';
				break;
				case array_shift($_config):
				return 'ampm-icon';
				break;
				default:
				return;
				break;
			}
		}
		return;
	}

	public function getUsageConfig()
	{
		$_storeId = Mage::app()->getStore()->getStoreId();

        $_config = Mage::getStoreConfig('catalog/miscellaneous/cos_usage_values', $_storeId);

        if (strpos($_config, ',')) {
        	$_config = explode(',',$_config);
        }

        return $_config;
	}


	public function getReferFriendUrlKey()
	{
		$_storeId = Mage::app()->getStore()->getStoreId();

		$_config = '';

		return $_config;
	}

	public function getCategoryNameByProductSku($_sku)
	{
		$_product = null;
		$categoryIds = null;
		
		if ($_sku) {
			$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $_sku);

			if ($_product->getId()) {
				$categoryIds = $_product->getCategoryIds();
			}
		}
		if (count($categoryIds)) {
			array_shift($categoryIds);
			foreach ($categoryIds as $_categoryId) {
				$_firstCategoryName = $this->getCategoryNameById($_categoryId);
				if ($_firstCategoryName) {
					return $_firstCategoryName;
				}
			}
		}
		return;
	}

	public function getCategoryNameById($categoryId)
	{
		if ($categoryId) {
            $_category = Mage::getModel('catalog/category')->load($categoryId);
            if ($_category->getName()) {
            	return $_category->getName();
            }
		}

		return;
	}

	/**
	 * Show Size and Shade attribute for simple products
	 * @return array
	 */
	public function getCustomAttributes($item)
	{

		$_product = $item->getProduct();

		if ($_product->getTypeId() == 'simple') {
			$_product = Mage::getModel('catalog/product')->load($_product->getId());
		}elseif ($_product->getTypeId() == 'configurable') {
			$_simpleSku = $item->getSku();
			$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $_simpleSku);
		}

		
		if($_product->getId()){
			$_customAttributeCodes= array('shade', 'size');

			$_customAttributesValues = array();

			foreach($_customAttributeCodes as $_code){
				$_customAttributesValues[$_code] = $_product->getAttributeText($_code);
			}
			return $_customAttributesValues;
		}

		return;
	}

	public function getProductTypeText($productId)
	{
		if (!$productId) {
			return;
		}

		$attributeId = self::PRODUCT_TYPE_ATTRIBUTE_ID;


		$optionId = Mage::getModel('optionswatch/catalog_attribute_data')->getProductAttributeValue($productId, $attributeId);

		if (!$optionId) {
			return;
		}

		$optionText = Mage::getModel('optionswatch/swatch')->getOptionText($optionId, $attributeId);

		return $optionText;
	}

	public function getProductSizeText($product)
	{
		if (!$product) {
			return;
		}

		$productId = $product->getId();

		$attributeId = self::SIZE_ATTRIBUTE_ID;

		$optionId = Mage::getModel('optionswatch/catalog_attribute_data')->getProductAttributeInt($productId, $attributeId);

		if (!$optionId) {
			return;
		}

		$optionText = Mage::getModel('optionswatch/swatch')->getOptionText($optionId, $attributeId);

		return $optionText;
	}

	public function getCanShowPrice($product)
	{
		if (!$product || is_null($product)) {
			return;
		}

		$_catalogPermissions = Mage::getModel('optionswatch/enterprise_catalog_permissions_data')->getProductPermission($product);
		if(isset($_catalogPermissions)){
		foreach ($_catalogPermissions as $key => $permission) {
			if ($permission['grant_catalog_product_price'] == -2 ||
				$permission['grant_catalog_product_price'] != -1 ) {
				return false;
			}
		}
		}else{
		return false;
		}

		return true;
	}
}
?>