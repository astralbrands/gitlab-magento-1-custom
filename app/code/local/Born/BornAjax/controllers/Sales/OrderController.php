<?php

class Born_BornAjax_Sales_OrderController extends Mage_Core_Controller_Front_Action {
	public function orderDetailsAction() {
		$result = array();
		$orderId = $this->getRequest()->getPost('order_id');
		$session = Mage::getSingleton('customer/session');

		if($session->isLoggedIn()) {
			$customerId = $session->getCustomer()->getId();
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			if($order->getCustomerId() === $customerId) {
				$shippingAddress = $order->getShippingAddress();
				$billingAddress = $order->getBillingAddress();
				$payment = $order->getPayment();

				$shippingDescription = $order->getShippingDescription();
				list($description, $shippingTitle) = explode(' - ', $shippingDescription);

				$items = $order->getAllVisibleItems();

				$productDetail = array();
				foreach($items as $item) {
					$productOptions = $item->getProductOptions();

					if($item->getProductType() == 'configurable') {
						foreach($productOptions['attributes_info'] as $attribute) {
							switch($attribute['label']) {
								case 'Color_Fabric': list($color, $fabric) = explode('_', $attribute['value']); break;
								case 'Size': $size = $attribute['value'];
							} 
						}	
						$simpleSku = $productOptions['simple_sku'];
						$simpleProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $simpleSku);

						$productDetail[] = array(
							'id' => $item->getProductId(),
							'name' => $item->getName(),
							'qty' => $item->getQtyOrdered(),
							'color' => $color,
							'fabric' => $fabric,
							'size' => $size,
							'price' => $item->getPrice(),
							'image' => (string)Mage::helper('catalog/image')->init($simpleProduct, 'thumbnail')->resize(300, 420),
						);
					} else {
						$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
						$productDetail[] = array(
							'id' => $item->getProductId(),
							'name' => $item->getName(),
							'qty' => $item->getQtyOrdered(),
							'price' => $item->getPrice(),
							'image' => (string)Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(300, 420)	
						);
					}
				}

        		$totals = Mage::getModel('sales/quote')->load($order->getQuoteId())->getTotals();
				$applied = Mage::helper('awraf/order')->getAppliedRafAmounts($order);
				$totaldetails = array();
				foreach($totals as $total) {
					$totaldetails[] = array(
						'code' => $total->getCode(),
						'value' => $total->getValue()
					);
				}
				if($applied && array_key_exists('discount', $applied) && $applied['discount']>0 ) {
					$totaldetails[] = array(
						'code' => 'Applied Discount For Referred Friends',
						'value' => $applied['discount']
					);
				}
				$response['status'] = 'SUCCESS';
				$response['order_detail'] = array(
					'shipping_info' => $shippingAddress->getData(),
					'billing_info' => $billingAddress->getData(),
					'shipping_method' => array(
						'title' => $shippingTitle,
						'rate' => $order->getShippingAmount()
					),
					'payment_method' => trim(preg_replace('/\s\s+/', ' ', Mage::helper('payment')->getInfoBlock($order->getPayment())->toHtml())),
					'items' => $productDetail,
					'totals' => $totaldetails,
					'rma' => Mage::helper('enterprise_rma')->canCreateRma($order)
				);
			} else {
				$response['status'] = 'ERROR';
				$response['message'] = 'Order does not belong to customer.';
			}
		} else {
			$response['status'] = 'ERROR';
			$response['message'] = 'Customer is not logged in.';
		}
		
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
		return $response; 
	}
}