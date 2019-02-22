<?php 

class Born_Package_Block_Page_Html extends Mage_Page_Block_Html
{
	public function getShowOnAccountPage()
	{
		$actionName = Mage::app()->getFrontController()->getAction()->getFullActionName();
		$allowedActionNames = $this->getFullActionNames();

		foreach ($allowedActionNames as $_name) {
			if($actionName == $_name['names']){
				return true;
			}
		}

		return false;
	}

	protected function getFullActionNames()
	{
		$_storeId = Mage::app()->getStore()->getStoreId();

		$_config = Mage::getStoreConfig('customer/account_page_banner/items',$_storeId);

		$_config = unserialize($_config);

		return $_config;
	}
} 

?>