<?php 

class Born_Package_Block_Catalog_Product_List_Toolbar extends WeltPixel_Custom_Block_Catalog_Product_List_Toolbar
{

	protected function getSortingItems()
	{
		$_path = 'catalog/listing_page_sort/items';
		$_storeId = Mage::app()->getStore()->getStoreId();

		//keys: code, direction, label
		$_items = Mage::getStoreConfig($_path, $_storeId);

		if ($_items) {
			return unserialize($_items);
		}

		return;
	}

	protected function getCustomOrders()
	{

		$_orders = $this->getSortingItems();

		if ($_orders) {
			return $_orders;
		}
		elseif ($this->getAvailableOrders()) {
			$_orders = array();
			$_tempOrders = $this->getAvailableOrders();
			$_defaultDirection = $this->_direction;

			foreach ($_tempOrders as $key => $order) 
			{
				$_orders[] = array(
					'code' => $key, 
					'direction' => $_defaultDirection,
					'label' => $order
					);
			}

			return $_orders;
		}

		return;
	}


    public function isOrderCurrent($order, $direction=null)
    {
    	if (!$direction) {
    		return ($order == $this->getCurrentOrder());
    	}
    	else{
    		return ($order == $this->getCurrentOrder()) && ($direction == $this->getCurrentDirection());
    	}
    }


}

?>