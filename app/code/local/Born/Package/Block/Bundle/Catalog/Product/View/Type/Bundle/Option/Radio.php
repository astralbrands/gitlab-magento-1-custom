<?php 
class Born_Package_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Radio extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Radio
{

	protected function getSwatchImage($_product)
	{
            //Mage::log(__METHOD__, false, 'OptionSwatch.log');
		$_storeId = Mage::app()->getStore()->getStoreId();
		$_attributeCode = 'shade';
		$_attributeId = $this->getAttributeId($_product, $_attributeCode);

		$_optionSwatch =  $_baseUrl . Mage::getModel('optionswatch/swatch')->loadByOptionId($_attributeId);
		if(!!$_optionSwatch && !!$_optionSwatch->getId() && !!$_optionSwatch->getData('image_file')){
			$swatchImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). $_optionSwatch->getData('image_file');
			return $_swatchImage;
		}
		return;
	}

	protected function getAttributeId($_product, $attributeCode)
	{
            //Mage::log(__METHOD__, false, 'OptionSwatch.log');
		$_storeId = Mage::app()->getStore()->getStoreId();
		$_resource = Mage::getSingleton('catalog/product')->getResource();

		$attributeId = $_resource->getAttributeRawValue($_product->getId(), $attributeCode, $_storeId);

		return $attributeId;
	}

	protected function getProductSubtext($_product)
	{
            //Mage::log(__METHOD__, false, 'OptionSwatch.log');
		$_bornProductListHelper = Mage::helper('born_package/catalog_product_list');
		$_subText = $_bornProductListHelper->getSubText($_product);

		return $_subText;

	}

	protected function getProductUsage($_selection)
	{
            //Mage::log(__METHOD__, false, 'OptionSwatch.log');
		$_attributeValue = Mage::getModel('born_package/catalog_attribute_data')->getUsageValueByEntityId($_selection->getId());

		if ($_attributeValue) {
			$uageClassName = Mage::helper('born_package/catalog_product_data')->getUsageIconClass($_attributeValue);
			return $uageClassName;
		}
		return;
	}
}