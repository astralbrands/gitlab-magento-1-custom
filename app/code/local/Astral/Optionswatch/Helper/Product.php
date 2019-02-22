<?php 
class Astral_Optionswatch_Helper_Product extends Mage_Core_Helper_Abstract
{
	public function getProductAttributeSwatches($product,$attrCode){
		$swatches = array();
		if(!!$product->getData($attrCode)){		
			$options = explode(',', $product->getData($attrCode));
			foreach($options as $option){
		 		$swatch = Mage::getModel('optionswatch/swatch');
		 		$swatch->loadByOptionId($option);
		 		if(!!$swatch&&$swatch->getId()){
		 			array_push ( $swatches , $swatch );
		 		}
			}
		}
		return $swatches;
	}
	
	public function getSwatchByOptionId($optionId){
		$swatch = Mage::getModel('optionswatch/swatch');
		if($optionId){	 	
	 		$swatch->loadByOptionId($optionId);			
		}
		return $swatch;
	}
}