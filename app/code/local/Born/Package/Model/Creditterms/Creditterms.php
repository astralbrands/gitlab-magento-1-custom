<?php 
class Born_Package_Model_Creditterms_Creditterms extends Mage_Payment_Model_Method_Abstract 
{
	protected $_code = 'creditterms';
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
        
    public function isAvailable($quote = null)
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            $customerId = $quote->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($customerId);

            return $customer->getCreditTerms();
        }
        if(!Mage::getSingleton('customer/session')->isLoggedIn()){
                return false;
        }else{
            //get customer
            $customer= Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getCreditTerms();
        }
    }
	 public function validate(){
       parent::validate();

       if(Mage::app()->getStore()->isAdmin())
       {
            $adminQuote = $this->_getSession();
            if($customerId = $adminQuote->getCustomerId())
            {
                $customer = Mage::getModel('customer/customer')->load($customerId);

                if ($customer->getCreditTerms()) {
                    return $this;
                }else{
                    Mage::throwException($this->_getHelper()->__('payment method not allowed'));
                }
            }
        }

	   if(!Mage::getSingleton('customer/session')->isLoggedIn()){
                Mage::throwException($this->_getHelper()->__('payment method not allowed'));
        }else{
            //get customer
            $customer= Mage::getSingleton('customer/session')->getCustomer();
             if($customer->getCreditTerms()){
				return $this;
			 }
			 else{
				Mage::throwException($this->_getHelper()->__('payment method not allowed'));
			 }
        }
	}

    protected function _getSession()
    {
        $session = null;
        if (Mage::app()->getStore()->isAdmin()) {
            $session = Mage::getSingleton('adminhtml/session_quote');
        } else {
            $session = Mage::getSingleton('checkout/session');
        }

        return $session;
    }
}