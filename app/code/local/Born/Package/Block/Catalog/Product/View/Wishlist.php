<?php 
class Born_Package_Block_Catalog_Product_View_Wishlist extends Mage_Core_Block_Template
{
	protected function getCurrentProduct()
	{
        if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) { 
        //Mage::log(__METHOD__, false, 'OptionSwatch.log');
        }
		$product = $this->getProduct();

		if (!($product && $product->getId())) 
		{
			$product = Mage::registry('current_product');
 
            if ($product && $product->getId()) {
                $this->setProduct($product);
            }
            else{
            	Mage::logException('Born_Package_Block_Catalog_Product_View_Wishlist:: Unable to obtain product info');
            }
        }
        return $this->getProduct();
	}
}