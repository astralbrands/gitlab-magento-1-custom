<?php 
//born_package/customer_account_dashboard_address
class Born_Package_Block_Customer_Account_Dashboard_Address extends Mage_Customer_Block_Account_Dashboard_Address
{
    public function getAddressHtml($address)
    {
        return $address->format('html');
    }

    public function getAdditionalAddresses()
    {
    	$addresses = $this->getCustomer()->getAdditionalAddresses();
    	return empty($addresses) ? false : $addresses;
    }

    public function getHasAddresses()
    {
    	$_customer = $this->getCustomer();
    	$_primaryShippingAddress = $_customer->getPrimaryShippingAddress();

    	if ($_primaryShippingAddress instanceof Varien_Object) {
    		return true;
    	}

    	$_primaryBillingAddress = $_customer->getPrimaryBillingAddress();
    	if ($_primaryBillingAddress instanceof Varien_Object) {
    		return true;
    	}

    	$_additionalAddresses = $this->getAdditionalAddresses();
    	if (!empty($_additionalAddresses)) {
    		return true;
    	}
    	return false;
    }
}

 ?>