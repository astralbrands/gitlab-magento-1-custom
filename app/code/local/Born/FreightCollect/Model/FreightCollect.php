<?php

class Born_FreightCollect_Model_FreightCollect extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
	protected $_code = 'freightcollect';
	
	protected $_isFixed = true;
	
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		$_fee = Mage::getStoreConfig('carriers/freightcollect/handling_fee');
		
		if (!$this -> getConfigFlag('active')) {
	        return false;
	    }
	    
	    $result = Mage::getModel('shipping/rate_result');
	    $method = Mage::getModel('shipping/rate_result_method');
	
	    $method -> setCarrier('freightcollect');
	    $method -> setCarrierTitle($this -> getConfigData('title'));
	
	    $method -> setMethod('freightcollect');
	    $method -> setMethodTitle($this -> getConfigData('name'));
	
	    $method -> setPrice($_fee);
	    $method -> setCost($_fee);
	
	    $result -> append($method);
	    
	    return $result;
	}
	
	public function getAllowedMethods()
	{
		return array('freightcollect' => $this -> getConfigData('name'));
	}
}
