<?php
class Born_OnePageOrder_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getAvailableQuantity($_product)
	{
		$quantity = (int)$_product->getStockItem()->getQty();
		if(!$_product instanceof Mage_Catalog_Model_Product)
			return false;
		if($_product->getStockItem()->getUseConfigMinQty())
			$outOfStockLimit = (int)Mage::getStoreConfig('cataloginventory/item_options/min_qty');
		else
			$outOfStockLimit = (int)$_product->getStockItem()->getMinQty();
		return ($quantity - $outOfStockLimit);
	}
	public function getStockThresholdQuantity($_product)
	{
		if(!$_product instanceof Mage_Catalog_Model_Product)
			return false;
		if($_product->getStockItem()->getUseConfigNotifyStockQty())
			return (int)Mage::getStoreConfig('cataloginventory/options/stock_threshold_qty');
		else
			return (int)$_product->getStockItem()->getNotifyStockQty();
	}
    
}

