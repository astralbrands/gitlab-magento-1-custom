<?php 

class Born_Package_Model_Wishlist_Wishlist extends Mage_Wishlist_Model_Wishlist{

	public function deleteItem(Mage_Catalog_Model_Product $product)
	{
		$item = null;
		foreach ($this->getItemCollection() as $_item) {
			if ($_item->representProduct($product)) {
				$item = $_item;
				break;
			}
		}
		if($item){
			$item->delete();
			return true;
		}
		return false;
	}
}

?>