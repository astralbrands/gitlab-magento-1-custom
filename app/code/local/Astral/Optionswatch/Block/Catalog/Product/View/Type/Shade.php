<?php
class Astral_Optionswatch_Block_Catalog_Product_View_Type_Shade extends Astral_Optionswatch_Block_Catalog_Product_View_Type_Configurable 
{
	
	public function log($msg)
	{
		//Mage::log($msg,1,'yu.log');
	}

	public function getShadeAttribute()
	{
		$_product = $this->getProduct();

		foreach ($this->getAllowAttributes() as $attribute) 
		{
			if ($attribute->getAttributeId() == $this->getShadeAttributeId()) {
				return $attribute;
			}
		}
		return;
	}

	public function getSimpleProductInfo()
	{
		$_info = array();
		$isDefaultSet = false;

		foreach ($this->getAllowProducts() as $_allowProduct)
		{
			$productSku =  $_allowProduct->getSku();
			$productStock = $_allowProduct->isSaleable();
			$productName = $_allowProduct->getName();

			$_info[$_allowProduct->getShade()]['sku'] = $productSku;
			$_info[$_allowProduct->getShade()]['stock'] = $productStock;
			$_info[$_allowProduct->getShade()]['name'] = $productName;
			$_info[$_allowProduct->getShade()]['shadeValue'] = $_allowProduct->getShade();

			//Set first in stock item as default
			if (!$isDefaultSet && $productStock) {
				$_info[$_allowProduct->getShade()]['isDefault'] = 1;
				$isDefaultSet = true;
			}else{
				$_info[$_allowProduct->getShade()]['isDefault'] = 0;
			}
		}

		return $_info;
	}

	public function getShadeInfo()
	{
		$_attribute = $this->getShadeAttribute();
		$_configurableProduct = $this->getProduct();
		
		$prices = $_attribute->getPrices();

		if (is_array($prices)) 
		{
			$eavAttribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $_attribute->getAttributeId());
			$currentProductMedia = Mage::helper('optionswatch/data')->getConfigMediaImages($_configurableProduct);
			$_info = $this->getSimpleProductInfo();
			foreach ($prices as $value) 
			{
				if ($_info[$value['value_index']]) 
				{
					if( $eavAttribute->getAttributeCode() == "shade"){ //Hard coded for shade swatch
						$optionSwatch = Mage::getModel('optionswatch/swatch')->loadByOptionId($value['value_index'], null, $_configurableProduct->getSku());

						$optionImage = array();
						$swatchImage = '';
						$swatchDescription = '';
						if(array_key_exists( $value['label'],$currentProductMedia )){
							$optionImage = $currentProductMedia[$value['label']];
						}						
						if(!!$optionSwatch && !!$optionSwatch->getId() && !!$optionSwatch->getData('image_file')){
							$swatchImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA). $optionSwatch->getData('image_file');
							if($optionSwatch->getDescription()){
								$swatchDescription = $optionSwatch->getDescription();
							}
						}
						if(!$swatchImage){
							$swatchImage = $product->getSmallImageUrl();
						}
						$_info[$value['value_index']]['label'] = $value['label'];
						$_info[$value['value_index']]['optionSwatch'] = $swatchImage;
						$_info[$value['value_index']]['optionSwatchDescription'] = $swatchDescription;
						$_info[$value['value_index']]['mediaImage'] = $optionImage;

					}
				}
			}
			return $_info;
		}
		return;
	}

	public function getProductImageHtml($shade)
	{
		$html = '';
		$index = 0;

		foreach ($shade['mediaImage'] as $key => $image) {
			if ($index != 0) {
				$html .= ' ';
			}
			if ($key == 'video') {
				$html .= 'data-video-image="';
				$html .= $image . '"';
				$html .= ' ';
			}
			else{
				$html .= "data-product-image-" . $index++ . '="';
				$html .= $image . '"';
			}
		}
		return $html;
	}

	public function getStockClass($shade)
	{
		if ($shade['stock']) {
			return "";
		}
		return "out-of-stock";
	}

	public function getDefaultSwatchClass($shade)
	{
		if ($shade['isDefault']) {
			return "checked";
		}
		return "";
	}

	public function getDefaultShade($_shadeInfo)
	{
		foreach ($_shadeInfo as $shade) {
			if ($shade['isDefault']) {
				return $shade;
			}
		}
		return;
	}
}