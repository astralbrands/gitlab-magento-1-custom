<?php

class Born_Package_Model_Observer {

    public function saveType($event) {
        $order = $event->getOrder();
        $order_data = Mage::app()->getRequest()->getPost('order');
        $order_type = $order_data['account']['order_entry_type'];
        $order->setData('order_entry_type', $order_type);
        
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if($adminUser){
            $params = array();
            $params[0] = $adminUser->getFirstname();
            $params[1] = ' ';
            $params[2] = $adminUser->getLastname();
            $userString = implode('', $params);
            $order->setData('admin_username', $userString);
        }
        
        return $order->save();
    }

    public function saveTypeFrontend($event) {
        $order = $event->getOrder();
        $order_type = 'web';
        $order->setData('order_entry_type', $order_type);

        return $order->save();
    }

    public function checkNewVersionProductUpdate($event){
      
      return $event;
    }
    
    public function checkNewVersion($event){
      
      return $event;
    }    
    
    public function assignNewVersion($product) {
        $helper = Mage::helper('born_package');
        if ($product->getStatus()) {
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $qty = $stock->getQty();
            $threshHold = $helper->newVersionQtyThreshold(); //threshold qty either 0 or mark as out of stock qty

            if ($qty <= $threshHold || $product->getIsInStock() == 0) {
                try {
                    if ($product->getNewVersionEnable() && $product->getNewVersionSku()) {
                        $newProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $product->getNewVersionSku());
                        if ($newProduct) {
                            Mage::getModel('catalog/product_status')->updateProductStatus($product->getId(), Mage_Core_Model_App::ADMIN_STORE_ID, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                            Mage::getModel('catalog/product_status')->updateProductStatus($newProduct->getId(), Mage_Core_Model_App::ADMIN_STORE_ID, Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                        }
                    }
                } catch (Exception $e) {
                    Mage::throwException($e->getMessage());
                }
            }
        }
    }

    public function onSaveCheckNewVersion(Varien_Event_Observer $observer) {
        $helper = Mage::helper('born_package');
        if ($helper->enableNewVersionCheck()) {
            $product = $observer->getProduct();

            $this->assignNewVersion($product);
        }
    }

    public function onOrderCheckNewVersion(Varien_Event_Observer $observer) {
        $helper = Mage::helper('born_package');
        if ($helper->enableNewVersionCheck()) {
            $order = $observer->getEvent()->getOrder();
            $items = $order->getAllItems();

            foreach ($items as $item) {
                //load with admin scope to enable saving the product if required
                $product = Mage::getModel('catalog/product')->load($item->getProductId());

                $this->assignNewVersion($product);
            }
        }
    }

    public function canSaveCustomer(Varien_Event_Observer $observer) {

        $enabled = Mage::getStoreConfig('customer/b2b_settings/active');
        if ($enabled) {
            $helper = Mage::helper('born_package');
            $roleId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if ($helper->isB2BCustomer($roleId)) {
                //	$loaded_customer=Mage::getModel('customer/customer')->load($current_customer->getId());
                Mage::throwException('Please Contact Customer Care To Edit Your Data');
            }
        }
    }

    public function paymentCheck(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $method = $event->getMethodInstance();
        $quote= $event->getQuote();
        $result = $event->getResult();
        //   $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $code = 'authorizenet';

       //if (!$this->checkForRecurringProfile($quote)) {

        //   if ($method->getCode() == $code) {
        //       $result->isAvailable = false;
        //   }
       //}
    }

    private function checkForRecurringProfile($quote) {
        return;
    }
	/**
	* To change the status of order to "payment review" based on the limits set for PUR and COS sites
	*
	**/
	public function changestatus(Varien_Event_Observer $observer)
	{
		$storeCode = Mage::app()->getStore()->getCode();
		try {
			$event = $observer->getEvent();
			$order = $event->getOrder();
			if($storeCode == 'cosb2c_store') {
				if($order->getGrandTotal() > Mage::getStoreConfig('b2cpur_orderlimit/general/b2c_orderlimit')) {
					$order_no   = (string) $order->getRealOrderId(); 
					try {
					$order->loadByIncrementId($order_no);
					$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true);
					$order->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true);
					$order->save();
					} catch(Exception $e) {
						//Mage::log($e,NULL,'order_review.log');
					}
				}
			} else if($storeCode == 'pur_store') {
				if($order->getGrandTotal() > Mage::getStoreConfig('b2cpur_orderlimit/general/pur_orderlimit')) {
					$order_no   = (string) $order->getRealOrderId(); 
					try {
					$order->loadByIncrementId($order_no);
					$order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true);
					$order->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true);
					$order->save();
					} catch(Exception $e) {
						//Mage::log($order_no,NULL,'order_review_exception.log');
						//Mage::log($e,NULL,'order_review_exception.log');
					}
				}
			}
		} catch(Exception $e) {
			//Mage::log($storeCode,NULL,'order_review_exception.log');
		}
	}
	/**
	* Button to change the status of order from "payment_review" to actual status. It is a feature for admin to enable order * to go through X3 interface
	**/
	public function addStatusChangeButton()
	{
		$block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        if (!$block){
            return $this;
        }
		try {
			$order = Mage::registry('current_order');
			if($order->getId()) {
				if($order->getStatus() == 'payment_review') {
					$url = Mage::helper("adminhtml")->getUrl(
						"bornadmin/adminhtml_index/changestatus",
						array('order_id'=>$order->getId())
					);
					$block->addButton('change_status', array(
							'label'     => Mage::helper('sales')->__('Process Order'),
							'onclick'   => 'setLocation(\'' . $url . '\')',
							'class'     => 'go'
					));
				}
				return $this;
			}
		} catch(Exception $e) {
			//Mage::log($e,NULL,'order_status_button.log');
		}
	}
	/**
	* Button to change the status of order from "payment_review" to actual status. It is a feature for admin to enable order * to go through X3 interface
	**/
	public function addStatusCancelButton()
	{
		$block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        if (!$block){
            return $this;
        }
		try {
			$order = Mage::registry('current_order');
			if($order->getId()) {
				if($order->getStatus() == 'payment_review' || $order->getStatus() == 'holded') {
					$url = Mage::helper("adminhtml")->getUrl(
						"bornadmin/adminhtml_index/cancelorder",
						array('order_id'=>$order->getId())
					);
					$block->addButton('cancel_order', array(
							'label'     => Mage::helper('sales')->__('Cancel Order'),
							'onclick'   => 'setLocation(\'' . $url . '\')',
							'class'     => 'go'
					));
				}
				return $this;
			}
		} catch(Exception $e) {
			//Mage::log($e,NULL,'order_status_button.log');
		}
	}
        
	public function addStatusBypassButton()
	{
		$block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        if (!$block){
            return $this;
        }
		try {
			$order = Mage::registry('current_order');
			if($order->getId()) {
				if($order->getStatus() == 'payment_review' || $order->getStatus() == 'holded') {
					$url = Mage::helper("adminhtml")->getUrl(
						"bornadmin/adminhtml_index/bypassorder",
						array('order_id'=>$order->getId())
					);
					$block->addButton('bypass_order', array(
							'label'     => Mage::helper('sales')->__('Bypass Score'),
							'onclick'   => 'setLocation(\'' . $url . '\')',
							'class'     => 'go'
					));
				}
				return $this;
			}
		} catch(Exception $e) {
			//Mage::log($e,NULL,'order_status_button.log');
		}
	}        
}
