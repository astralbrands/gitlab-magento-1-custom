<?php

class Born_Handling_Model_Sales_Order_Total_Invoice_Fee extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $order = $invoice->getOrder();

        $feeAmountLeft = $order->getFeeAmount() - $order->getFeeAmountInvoiced();
        $baseFeeAmountLeft = $order->getBaseFeeAmount() - $order->getBaseFeeAmountInvoiced();
        $invoice->setFeeAmount($feeAmountLeft);
        $invoice->setBaseFeeAmount($baseFeeAmountLeft);
        $invoice->setGrandTotal($invoice->getGrandTotal()+$feeAmountLeft);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$baseFeeAmountLeft);

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $amount = $address->getFeeAmount();
        $address->addTotal(array(
            'code' => $this->getCode(),
            'title' => Mage::helper('handling')->__('Handling Fee'),
            'value' => $amount
        ));
        return $this;
    }

}
