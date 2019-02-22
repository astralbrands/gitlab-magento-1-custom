<?php 

class Born_Package_Block_Page_Html_Cos_Header extends Mage_Page_Block_Html_Header
{
	public function isB2bCustomer()
	{
		$_customer = Mage::getSingleton('customer/session')->getCustomer();

		if(Mage::getSingleton('customer/session')->isLoggedIn()){

			$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

			$_b2bGroupIds = $this->getB2bCustomerGroups();

			#TODO: add is b2b group yes/no drop down to customer group panel in admin
			#

			if (in_array($groupId, $_b2bGroupIds)) {
				# code...
			}
		}

		return true; //temporary
	}


	public function getB2bCustomerGroups()
	{
		#TODO: return an array of b2b customer gorup ids
		return null;
	}
}

?>