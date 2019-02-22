<?php 

class Born_Package_Block_Checkout_Cart extends Mage_Checkout_Block_Cart
{

	public function getCartHelper()
	{
		return Mage::helper('born_package/checkout_cart');
	}
	public function getBannerProduct()
	{
		$_productSku = $this->getCartHelper()->getCartBannerProductSku();

		if (!$_productSku) {
			return;
		}

		$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $_productSku);

		if ($_product instanceof Mage_Catalog_Model_Product && !$_product->getId()) {
			return;
		}

		return $this->getProductHtml($_product);
	}

	public function getProductHtml($_product, $tagMessage = null)
	{
		if($_product){
			$block = $this->getLayout()->createBlock('borncmshooks/borncmshooks');
			$block_template = 'borncmshooks/blocks/productview.phtml';
			$block->setTemplate($block_template);
			$block->setData('product', $_product);
			if ($tagMessage) {
				$block->setData('tag_message', $tagMessage);
			}
			return $block->toHtml();
		}
		return;
	}

	public function getBannerEnabled()
	{
		return $this->getCartHelper()->getCartBannerEnabled();
	}

	public function getBannerTitle()
	{
		return $this->getCartHelper()->getCartBannerTitle();
	}

	public function getBannerBlock()
	{
		$_staticBlockId = $this->getCartHelper->getCartBannerStaticBlockId();

		if (!$_staticBlockId) {
			return;
		}

		return $this->getLayout()->createBlock('cms/block')->setBlockId($_staticBlockId)->toHtml();
	}
}

?>