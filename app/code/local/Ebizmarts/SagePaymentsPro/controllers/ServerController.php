<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/30/13
 * Time   : 6:15 PM
 * File   : ServerController.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_ServerController extends Mage_Core_Controller_Front_Action
{
    protected $_session;

    protected function _getApi()
    {
        return Mage::getModel('ebizmarts_sagepaymentspro/api_sage');
    }
    public function redirectAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ebizmarts_sagepaymentspro/checkout_formpost')->toHtml()
        );
    }
    public function returnAction()
    {
        $params = $this->getRequest()->getParams();
        $this->_session = Mage::getSingleton('checkout/session');
        if (isset($params['status_code'])) {
            $this->_restoreCart($params['status_description']);
            Mage::getSingleton('checkout/session')->addError($params['status_description']);
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
        } else {
            $this->_session = Mage::getSingleton('checkout/session');
            if ($this->_session->getSagePaymentsError()) {
                $this->_restoreCart($params['status_description']);
                Mage::getSingleton('checkout/session')->addError($this->_session->getSagePaymentsErrorDescription());
                $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            } else {
                $order = Mage::getModel('sales/order')->loadByIncrementId($this->_session->getLastRealOrderId());
                $order->sendNewOrderEmail();
                Mage::dispatchEvent('sagepaymentspro_after_pay', array('order' => $order));
                $this->_redirect('checkout/onepage/success', array('_secure' => true));
            }
        }
    }
    public function callbackAction()
    {
        $params = $this->getRequest()->getParams();
        $this->_session = Mage::getSingleton('checkout/session');
        $this->_session->unsSagePaymentsError();
        $this->_session->unsSagePaymentsErrorDescription();
        if (isset($params['response'])) {
            $ret = $params['response'];
            if (!is_array($ret)) {
                $ret = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $ret);
                $xml = simplexml_load_string($ret);
            }
            // @codingStandardsIgnoreStart
            if (isset($xml->Response->ResponseIndicator) && (string)$xml->Response->ResponseIndicator == 'A') {
                $order = Mage::getModel('sales/order')->loadByIncrementId($this->_session->getLastRealOrderId());
                $payment = $order->getPayment();
                $order->setStatus('sagepayments_ok');
                $quote = Mage::getModel('sales/quote')->load($this->_session->getQuoteId());
                $storeId = $quote->getStoreId();
                if ($xml->TransactionResponse->TransactionPaymentType=='CREDITCARD') {
                    $payment->setCcType(
                        $this->_getCreditCardType((string)$xml->TransactionResponse->PaymentDescription, "short")
                    );
                    $payment->setCcLast4(substr((string)$xml->TransactionResponse->PaymentDescription, -4));
                } elseif ($xml->TransactionResponse->TransactionPaymentType=='VIRTUALCHECK') {
                    $payment->setCcType('Virtual Check');
                    $payment->setCcLast4($xml->TransactionResponse->ACHTransactionClass);
                }
                if (Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_PAYMENT_ACTION, $storeId) ==
                    Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE) {
                    $payment->setLastTransId($xml->TransactionResponse->VANReference);
                    $payment->setNoCapture(true);
                    $payment->setTransactionType('capture');
                } else {
                    $payment->setTransactionType('authorize');
                }
                Mage::getModel('ebizmarts_sagepaymentspro/transaction')->saveTransaction(
                    $payment, $xml,
                    $xml->TransactionResponse->Amount, $this->_session->getToken()
                );
                $order->save();
                $this->_session->unsToken();
                if (isset($xml->VaultResponse)) {
                    $tokenCard = Mage::getModel('ebizmarts_sagepaymentspro/tokencard');
                    $storeId = $payment->getStoreId();

                    $tokenCard->setCustomerId($payment->getOrder()->getCustomerId())
                        ->setToken($xml->VaultResponse->GUID)
                        ->setStatus("SUCCESS")
                        ->setCardType(
                            $this->_getCreditCardType(
                                (string)$xml->VaultResponse->PaymentDescription, "long"
                            )
                        )
                        ->setLastFour(substr($xml->VaultResponse->Last4, -4))
                        ->setExpiryDate($xml->VaultResponse->ExpirationDate)
                        ->setStatusDetail("SUCCESS")
                        ->setVendor(Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_M_ID, $storeId))
                        ->setIsDefault(false)
                        ->setVisitorSessionId();
                    $tokenCard->save();

                }
            } else {
                if (isset($xml->Response->ResponseMessage)) {
                    $this->_session->setSagePaymentsError(1);
                    $this->_session->setSagePaymentsErrorDescription((string)$xml->Response->ResponseMessage);
                } else {
                    $this->_session->setSagePaymentsError(1);
                    $this->_session->setSagePaymentsErrorDescription('unknown error');
                }
            }
            // @codingStandardsIgnoreEnd
        }
    }
    protected function _restoreCart($msg = null)
    {
        $quote = Mage::getModel('sales/quote')->load($this->_session->getQuoteId());
        $quote->setIsActive(true)->save();
        $this->_cancelOrder($msg);
    }

    /**
     * @param null $msg
     */
    protected function _cancelOrder($msg = null)
    {
        if ($this->_session->getLastRealOrderId()) {
            $orderId = $this->_session->getLastRealOrderId();
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order->getId()) {
                if ($msg) {
                    $order->addStatusHistoryComment($msg);
                }
                $order->cancel()->save();
            }
        }
    }
    protected function _getCreditCardType($ccNum,$mode="long")
    {
        if (preg_match("/^5[1-5][0-9,A-Z]{14}$/", $ccNum))
            return ($mode == "long") ? "Mastercard" : "MC";

        if (preg_match("/^4[0-9,A-Z]{12}([0-9,A-Z]{3})?$/", $ccNum))
            return ($mode == "long") ? "Visa" : "VI";

        if (preg_match("/^3[47][0-9,A-Z]{13}$/", $ccNum))
            return ($mode == "long") ? "American Express" :"AE";

        if (preg_match("/^3(0[0-5]|[68][0-9])[0-9,A-Z]{11}$/", $ccNum))
            return ($mode == "long") ? "Diners Club" : "DC";

        if (preg_match("/^6011[0-9,A-Z]{12}$/", $ccNum))
            return ($mode == "long") ? "Discover" :"DI";

        if (preg_match("/^(3[0-9]{4}|2131|1800)[0-9,A-Z]{11}$/", $ccNum))
            return ($mode == "long") ? "JCB" : "JC";
    }

}