<?php 
class Born_Package_Block_Catalog_Product_View_Cos_Media extends Born_Package_Block_Catalog_Product_View_Media
{
	public function getBaseImageLabel()
	{          
		$_product = $this->getProduct();
		if ($_product->isConfigurable()) {
			$_label = $_product->getData('image_label');
			return $_label;
		}
		return;
	}

	public function isMediaClass($image, $optionLabels)
	{          
		$product = $this->getProduct();
		$_defaultImageLabel = $this->getBaseImageLabel();

		if ($product->isConfigurable()) {
			if (is_array($optionLabels) && count($optionLabels) && $image->getLabel()) {
				if (in_array($image->getLabel(), $optionLabels) && $image->getLabel() != $_defaultImageLabel) {
					return false;
				}
			}
		}
		return $this->isGalleryImageVisible($image);
	}
}

 ?>