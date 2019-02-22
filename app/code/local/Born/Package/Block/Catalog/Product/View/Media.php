<?php 
class Born_Package_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{

	private $_attributeOptionLabels = array();

	public function getBaseImageLabel()
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		$product = $this->getProduct();
		if ($product->isConfigurable()) {
			$skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
			$allProducts = $product->getTypeInstance(true)
			->getUsedProducts(null, $this->getProduct());
			foreach ($allProducts as $product) {
				if ($product->isSaleable() || $skipSaleableCheck) {
					//first allowed product will be default
					$shadeLabel = $product->getAttributeText('shade');
					return $shadeLabel;
				}
			}
		}
		return;
	}

	public function getStyleHtml($index)
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		if($index != 0){
			$html = 'style="display: none;"';
			return $html;
		}
		return '';
	}

	public function getDisplayImage($_image, $_defaultLabel)
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		$product = $this->getProduct();
		if ($product->isConfigurable()) {
			if ($this->isGalleryImageVisible($_image) && ($_image->getLabel() == $_defaultLabel || !$_image->getLabel())){
				return true;
			}
		}
		else{
			return $this->isGalleryImageVisible($_image);
		}

		return;
	}
	public function getDataLabel($dataLabel)
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		$attributeOptionLabels = $this->getOptionLabels();

		$_defaultLabel = 'media';

		if ($attributeOptionLabels && $dataLabel) {
			foreach ($attributeOptionLabels as $label) {
				if ($dataLabel == $label) {
					return $dataLabel;
				}
			}
		}
		return $_defaultLabel;
	}

	/**
	 * @retuns array
	 */
	public function getOptionLabels()
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		if (empty($this->_attributeOptionLabels)) {

			$product = $this->getProduct();

			if ($this->getProduct()->getTypeId() == 'configurable') {
				$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

				$attributeOptions = array();
				foreach ($productAttributeOptions as $productAttribute) {
					foreach ($productAttribute['values'] as $attribute) {
						$attributeOptions[$attribute['value_index']] = $attribute['store_label'];
					}
				}
				$this->_attributeOptionLabels = $attributeOptions;
			}
		}

		return $this->_attributeOptionLabels;
	}
}