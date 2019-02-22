<?php 

class Born_Package_Block_Wishlist_Share_Wishlist extends Mage_Wishlist_Block_Share_Wishlist{

	public function getHeader()
	{
		return Mage::helper('wishlist')->__("%s's Favorites", $this->escapeHtml($this->getWishlistCustomer()->getFirstname()));
	}
}

?>