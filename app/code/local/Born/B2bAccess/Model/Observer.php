<?php
class Born_B2bAccess_Model_Observer
{
    protected $_restrictActions = array(
      'bornajax_customer_account_myinfoSave',
      'customer_address_new',
      'customer_address_edit',
      'customer_address_formPost',
        'customer_account_edit',
      'customer_address_delete'
    );
    
    
    public function b2bRestriction(Varien_Event_Observer $observer)
    {
        $storeId = Mage::app()->getStore()->getId();
        
        $moduleEnabled = (boolean)Mage::getStoreConfig('b2baccess_restriction/b2baccess/enabled',$storeId);
        $b2bGroups = Mage::getStoreConfig('b2baccess_restriction/b2baccess/restrict_groups');
        $b2bGroups = (strlen($b2bGroups) > 0) ? explode(',',$b2bGroups): array();
        $currentFullAction = $observer->getEvent()->getControllerAction()->getFullActionName();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        if($moduleEnabled)
        {
            if($customerGroupId == Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
            {
                if(in_array($currentFullAction,array('customer_account_create','customer_account_createpost'))){
                    Mage::app()->getFrontController()->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
                    Mage::app()->getFrontController()->getResponse()->setHeader('Status','404 Page not found');
                    $request = Mage::app()->getRequest();
                    $request->initForward()
                            ->setControllerName('indexController')
                            ->setModuleName('Mage_Cms')
                            ->setActionName('defaultNoRoute')
                            ->setDispatched(false);
                }
            }else{
                if(in_array($customerGroupId, $b2bGroups) && in_array($currentFullAction, $this->_restrictActions)){
                    Mage::app()->getFrontController()->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
                    Mage::app()->getFrontController()->getResponse()->setHeader('Status','404 Page not found');
                    $request = Mage::app()->getRequest();
                    $request->initForward()
                            ->setControllerName('indexController')
                            ->setModuleName('Mage_Cms')
                            ->setActionName('defaultNoRoute')
                            ->setDispatched(false);
                }
            }
        }else{
            if($customerGroupId != Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
            {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $addressCount = $customer->getAddressesCollection()->count();
                if(($addressCount > 2 && in_array($currentFullAction, array('customer_address_new'))) || ($addressCount  <= 2 && in_array($currentFullAction, array('customer_address_delete')))){
                    Mage::app()->getFrontController()->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
                    Mage::app()->getFrontController()->getResponse()->setHeader('Status','404 Page not found');
                    $request = Mage::app()->getRequest();
                    $request->initForward()
                            ->setControllerName('indexController')
                            ->setModuleName('Mage_Cms')
                            ->setActionName('defaultNoRoute')
                            ->setDispatched(false);
                }
            }
        }
        return $this;
    }
}

