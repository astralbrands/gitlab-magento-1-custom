<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/29/13
 * Time   : 5:05 PM
 * File   : SageMethodServer.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_SageMethodServer extends Mage_Payment_Model_Method_Abstract
{
    protected $_code                    = 'sagepaymentsproserver';
    protected $_formBlockType           = 'ebizmarts_sagepaymentspro/payment_form_sagePaymentsPro';
    protected $_infoBlockType           = 'ebizmarts_sagepaymentspro/payment_info_sagePaymentsPro';

    /**
     * Availability options
     */
    protected $_isGateway = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = false;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $token = Mage::getModel('ebizmarts_sagepaymentspro/tokencard')->load($data->getSagepayTokenCcId());
        $info->setAdditionalInformation('token', $token->getToken());
        $info->setAdditionalInformation('remembertoken', $data->getRemembertoken()!=null ? 1 : 0);


        return $this;
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        if ($this->getInfoInstance()->getAdditionalInformation('token')) {
            $token = $this->getInfoInstance()->getAdditionalInformation('token');
        }
        if (!isset($token)) {
            $token = $this->getInfoInstance()->getAdditionalInformation('remembertoken');
        }
        if ($token) {
            Mage::getSingleton('checkout/session')->setToken($token);
        }
        return Mage::getUrl("sgusa/server/redirect/")."token/$token";
    }
    public function refund(Varien_Object $payment,$amount)
    {
        $transaction = Mage::getModel('ebizmarts_sagepaymentspro/transaction')
            ->loadByOrderId($payment->getOrder()->getId(), $payment->getOrder()->getStoreId());
        $api = Mage::getModel('ebizmarts_sagepaymentspro/api_sage');
        $rc = $api->reference($transaction, $payment, $amount, Ebizmarts_SagePaymentsPro_Model_Config::CODE_REFUND);
        $ret = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $rc);
        $xml = simplexml_load_string($ret);
        // @codingStandardsIgnoreStart
        // The xml is an XML returned by SagePayments
        if ((string)$xml->PaymentResponses->PaymentResponseType->Response->ResponseIndicator == 'A') {
            $payment->setTransactionType(Ebizmarts_SagePaymentsPro_Model_Config::TRANSACTION_TYPE_REFUND);
            Mage::getModel('ebizmarts_sagepaymentspro/transaction')->saveTransaction($payment, $xml, $amount);
        } else {
            Mage::throwException($xml->PaymentResponses->PaymentResponseType->Response->ResponseMessage);
        }
        // @codingStandardsIgnoreEnd
        return $this;

    }
    public function recurringFirst()
    {
        Mage::getSingleton('checkout/session')->setRecurring(true);
        return $this;

    }
    public function recurringOthers($oldOrder, $newOrder)
    {
        $transactions = Mage::getModel('ebizmarts_sagepaymentspro/transaction')->getCollection()
            ->addFieldToFilter('order_id', $oldOrder->getId());
        foreach ($transactions as $transaction) {
            $token = $transaction->getToken();
            break;
        }
        $info = Mage::getModel('payment/info');
        $this->setInfoInstance($info);
        $this->getInfoInstance()->setAdditionalInformation('token', $token);
        $this->sale($newOrder->getPayment(), $newOrder->getPayment()->getAmountOrdered());
        return $this;
    }
}