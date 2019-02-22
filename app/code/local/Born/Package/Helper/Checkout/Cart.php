<?php

class Born_Package_Helper_Checkout_Cart extends Born_Package_Helper_Config
{

	public function getPromoMessage($subtotal)
	{
		$_storeId = Mage::app()->getStore()->getStoreId();

		$_isActive = Mage::getStoreConfig('born_package/cart_promo/active', $_storeId);
		if ($_isActive) {
			$_amount = Mage::getStoreConfig('born_package/cart_promo/amount', $_storeId);
			$_message = Mage::getStoreConfig('born_package/cart_promo/message', $_storeId);
			$_eligibleMessage = Mage::getStoreConfig('born_package/cart_promo/eligible_message', $_storeId);

			if(!is_numeric($_amount))
			{
				Mage::throwException('born_package/cart_promo/amount field value is not an number.');
				return;
			}

			if ($subtotal > $_amount) {
				return $_eligibleMessage;
			}

			if(strpos($_message, '%s')){
				$_remaining = $_amount - $subtotal;
				return str_replace('%s', $_remaining, $_message);
			}
		}
		return;
	}

	public function getCartBannerEnabled()
	{
		$path = 'born_package/cart_banner/enable';

		$config = $this->getConfig($path);

		if ($config) {
			return true;
		}

		return false;
	}

	public function getCartBannerTitle()
	{
		return $this->getConfig('born_package/cart_banner/title');
	}

	public function getCartBannerStaticBlockId()
	{
		return $this->getConfig('born_package/cart_banner/static_block_id');
	}

	public function getCartBannerProductSku()
	{
		return $this->getConfig('born_package/cart_banner/product_sku');
	}


}
?>