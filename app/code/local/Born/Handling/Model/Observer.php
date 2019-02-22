<?php
class Born_Handling_Model_Observer {
    public function calculateTotalsWithFee(Varien_Event_Observer $observer) {
        $orderVar = Mage::app()->getRequest()->getPost();
            if(!$observer->getEvent()->getSession()->getStoreId()){
            Mage::getSingleton('adminhtml/session')->unsIsAutoCalculates();
            }
            if(count($orderVar) > 0){
            $handling_fee = (isset($orderVar['order']['fee_amount']))? $orderVar['order']['fee_amount']: 0; //get Handling Fee from Post
            $order_create_model = $observer->getOrderCreateModel();
            $quote = $order_create_model->getQuote();
                if ($quote->getFeeAmount() != $handling_fee) {  
                Mage::getSingleton('adminhtml/session')->setIsAutoCalculates(1);
                $quote->setFeeAmount($handling_fee);
                $quote->setBaseFeeAmount($handling_fee);
                }
            }
    }

    public function invoiceSaveAfter(Varien_Event_Observer $observer) {
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getBaseFeeAmount()) {
            $order = $invoice->getOrder();
            $order->setFeeAmountInvoiced($order->getFeeAmountInvoiced() + $invoice->getFeeAmount());
            $order->setBaseFeeAmountInvoiced($order->getBaseFeeAmountInvoiced() + $invoice->getBaseFeeAmount());
        }
        return $this;
    }

    public function orderFee(Varien_Event_Observer $observer) {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        $order->setFeeAmount($quote->getFeeAmount())->setBaseFeeAmount($quote->getBaseFeeAmount())->save();
    }

}