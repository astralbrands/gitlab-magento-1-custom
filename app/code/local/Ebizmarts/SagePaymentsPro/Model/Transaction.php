<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/23/13
 * Time   : 1:28 PM
 * File   : Transaction.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_Transaction extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_sagepaymentspro/transaction');
    }
    // @codingStandardsIgnoreStart
    // The result is an XML returned by SagePayments
    public function saveTransaction(Varien_Object $payment, $result,$amount = 0,$token = 0)
    {
        $this->setOrderId($payment->getOrder()->getId())
            ->setStoreId($payment->getOrder()->getStoreId())
            ->setResponseStatus($result->PaymentResponses->PaymentResponseType->Response->ResponseIndicator)
            ->setPostCodeResult($result->PaymentResponses->PaymentResponseType->Response->ResponseCode)
            ->setResponseStatusDetail($result->PaymentResponses->PaymentResponseType->Response->ResponseMessage)
            ->setCvvIndicator($result->PaymentResponses->PaymentResponseType->TransactionResponse->CVVResult)
            ->setRiskIndicator($result->PaymentResponses->PaymentResponseType->TransactionResponse->AVSResult)
            ->setAvs($result->PaymentResponses->PaymentResponseType->TransactionResponse->AVSResult)
            ->setReference($result->PaymentResponses->PaymentResponseType->TransactionResponse->VANReference)
            ->setTransactionId($result->PaymentResponses->PaymentResponseType->TransactionResponse->TransactionID)
            ->setAmount($amount)
            ->setToken($token)
            ->setType($payment->getTransactionType());
        if (isset($result->PaymentResponses->PaymentResponseType->VaultResponse)) {
            $this->setToken($result->PaymentResponses->PaymentResponseType->VaultResponse->GUID);
        }
        $this->save();
        return $this;
    }
    // @codingStandardsIgnoreEnd

    public function saveTransactionDirect(Varien_Object $payment, $result,$amount,$token=null)
    {

        if ($payment->getOrder()->getId()) {
            $id = $payment->getOrder()->getEntityId();
        } else {
            $id = $payment->getOrder()->getIncrementId();
        }
        $this->setOrderId($id)
            ->setStoreId($payment->getOrder()->getStoreId())
            ->setResponseStatus($result->getResponseStatus())
            ->setPostCodeResult($result->getPostCodeResult())
            ->setResponseStatusDetail($result->getResponseStatusDetail())
            ->setCvvIndicator($result->getCvvIndicator())
            ->setRiskIndicator($result->getRiskIndicator())
            ->setReference($result->getTrnSecuritykey())
            ->setToken($token)
            ->setAmount($amount)
            ->setAvs($result->getAvsIndicator())
            ->setType($payment->getTransactionType());
        $this->save();
        return $this;

    }
    public function loadByOrderId($orderId,$storeId) 
    {
        $this->_getResource()->loadByOrderId($this, $orderId, $storeId);
        return $this;
    }
}