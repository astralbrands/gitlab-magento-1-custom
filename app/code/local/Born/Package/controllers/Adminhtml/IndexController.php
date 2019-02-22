<?php
class Born_Package_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	public function changeStatusAction(){
		$data = $this->getRequest()->getParams();
			if(isset($data['order_id'])){
			$order_id = $data['order_id'];
			}else{
			throw new Exception('No order ID supplied.');
			}		
			try {
			$order = Mage::getModel('sales/order')->load($data['order_id']);
				if($order->getId()) {
						
				$comments = $order->getStatusHistoryCollection(true)->getLastItem();
				$state='';
				$status = 'processing';
				$system_status = Mage::getResourceModel('sales/order_status_collection')->addStatusFilter($status);
				$system_data = $system_status->getData();
				$state = 'processing';
				$label = 'Processing';
				$order->setState($state, true);
				$order->setStatus($status, true);
				$order->save();
				Mage::getSingleton('adminhtml/session')->addSuccess('Order status changed to '.$label);
				}
			} catch(Exception $e) {
			//Mage::log($e->getMessage(),NULL,'order_review_exception.log');
			Mage::getSingleton('adminhtml/session')->addError('Some error occured');
			}
		$this->_redirectReferer();
    }
	
	public function cancelOrderAction(){
		$data = $this->getRequest()->getParams();
			if(isset($data['order_id'])){
			$order_id = $data['order_id'];
			}else{
			throw new Exception('No order ID supplied.');
			}
			try {
			$order = Mage::getModel('sales/order')->load($data['order_id']);
				if($order->getId()) {
				$comments = $order->getStatusHistoryCollection(true)->getLastItem();
				$state='';
				$status = 'canceled';
				$system_status = Mage::getResourceModel('sales/order_status_collection')->addStatusFilter($status);
				$system_data = $system_status->getData();
				$state = 'canceled';
				$label = 'Canceled';
				$order->setState($state, true);
				$order->setStatus($status, true);
				$order->save();
				Mage::getSingleton('adminhtml/session')->addSuccess('Order status changed to '.$label);					
				}				
			} catch(Exception $e) {
			//Mage::log($e->getMessage(),NULL,'order_review_exception.log');
			Mage::getSingleton('adminhtml/session')->addError('Some error occured');
			}
		$this->_redirectReferer();				
	}
	public function bypassOrderAction(){
		$data = $this->getRequest()->getParams();
			if(isset($data['order_id'])){
			$order_id = $data['order_id'];
			}else{
			throw new Exception('No order ID supplied.');
			}
			try {
			$order = Mage::getModel('sales/order')->load($data['order_id']);
				if($order->getId()) {
				$comments = $order->getStatusHistoryCollection(true)->getLastItem();
				$state='';
				$status = 'bypassscore';
				$system_status = Mage::getResourceModel('sales/order_status_collection')->addStatusFilter($status);
				$system_data = $system_status->getData();
				$state = 'processing';
				$label = 'Bypass Score';
				$order->setState($state, true);
				$order->setStatus($status, true);
				$order->save();
				Mage::getSingleton('adminhtml/session')->addSuccess('Order status changed to '.$label);					
				}				
			} catch(Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e);
			}
		$this->_redirectReferer();                        
        }	
}