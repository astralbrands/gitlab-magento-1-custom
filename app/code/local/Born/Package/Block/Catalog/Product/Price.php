<?php 
class Born_Package_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
	protected $_originalPriceConfig = 'born_b2bprice/original_price_setting/hide_strikethrough_price';
        
        public function getProductMinimalPrice($product = null)
        {
            $productCollection = $product->getCollection();
            $productCollection->addAttributeToSelect(array('min_price'))
                ->addAttributeToFilter('entity_id', $product->getId())
                ->addPriceData();
                //$product->addData($productCollection->getFirstItem()->getData());
			if($productCollection->getFirstItem()){
				return $productCollection->getFirstItem()->getMinimalPrice();
			}else{
				return 0;
			}
        }
        
	public function getFormattedRegularPrice($regularPrice)
	{
		$formattedPrice = $this->__('was') . ' ' . $regularPrice;
		return $formattedPrice;
	}

	protected function getShowDefaultTierPricing($_product)
	{
		$this->setShowTierForSimple($this->getConfig('catalog/show_default_tier_message/enable_simple'));
		$this->setShowTierForConfigurable($this->getConfig('catalog/show_default_tier_message/enable_configurable'));

		switch ($_product->getTypeId()) {
			case 'simple':
			if ($this->getShowTierForSimple()) {
				return true;
			}
			break;
			case 'configurable':
			if ($this->getShowTierForConfigurable()) {
				return true;
			}
			break;
			default:
				return true;
			break;
		}

		return false;
	} 


	public function getConfig($path)
	{
		if ($path) {
			$_storeId = Mage::app()->getStore()->getStoreId();
			$_config = Mage::getStoreConfig($path, $_storeId);
			
			if ($_config) {
				return true;
			}
		}

		return false;
	}
	
	public function getPriceConfig() {
		return $this->getConfig($this->_originalPriceConfig);
	}

}