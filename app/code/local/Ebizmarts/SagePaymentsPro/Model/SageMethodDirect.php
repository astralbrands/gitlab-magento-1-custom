<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/22/13
 * Time   : 4:42 PM
 * File   : SageMethod.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_SageMethodDirect extends Mage_Payment_Model_Method_Cc
{
    protected $_code                    = 'sagepaymentsprodirect';
    protected $_formBlockType           = 'ebizmarts_sagepaymentspro/payment_form_sagePaymentsPro';
    protected $_infoBlockType           = 'ebizmarts_sagepaymentspro/payment_info_sagePaymentsPro';


    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canSaveCc                 = false;

    protected $_useToken                = false;
    protected $_recurring               = false;

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();

        if ($data->getCcNumber()!=null && $data->getCcNumber() != '') {
            $info->setCcType($data->getCcType())
                ->setCcOwner($data->getCcOwner())
                ->setCcLast4(substr($data->getCcNumber(), -4))
                ->setCcNumber($data->getCcNumber())
                ->setCcCid($data->getCcCid())
                ->setCcExpMonth($data->getCcExpMonth())
                ->setCcExpYear($data->getCcExpYear())
                ->setCcSsIssue($data->getCcSsIssue())
                ->setCcSsStartMonth($data->getCcSsStartMonth())
                ->setCcSsStartYear($data->getCcSsStartYear());
            $info->setAdditionalInformation('remembertoken', $data->getRemembertoken()!=null ? 1 : 0);
        } else {
            $token = Mage::getModel('ebizmarts_sagepaymentspro/tokencard')->load($data->getSagepayTokenCcId());
            $info->setCcCid($data->getCcCid())
                ->setCcExpMonth(substr($token->getExpiryDate(), 0, 2))
                ->setCcExpYear('20'.substr($token->getExpiryDate(), -2))
                ->setCcType($token->getCardType())
                ->setCcLast4($token->getLastFour());
            $info->setAdditionalInformation('token', $token->getToken());
        }

        return $this;    
    }
    public function validate()
    {
        $data = $this->getInfoInstance();

        if ($data->getCcNumber()!=null && $data->getCcNumber() != '') {
            return parent::validate();
        }
    }
    protected function _getApi()
    {
        return Mage::getModel('ebizmarts_sagepaymentspro/api_sage');
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if ($payment->getLastTransId()) {
            return $this->release($payment, $amount);
        } else {
            return $this->sale($payment, $amount);
        }
    }
    protected function sale(Varien_Object $payment, $amount)
    {
        Mage::getSingleton('checkout/session')->setMd(null)
            ->setAcsurl(null)
            ->setPareq(null);

        $token = $this->getInfoInstance()->getAdditionalInformation('token');
        if ($token) {
            $this->_useToken = true;
        } else {
            $remembertoken = $this->getInfoInstance()->getAdditionalInformation('remembertoken');
            if ($remembertoken) {
                $token = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->addToken($payment);
            }
        }
        $payment->setAnetTransType(Ebizmarts_SagePaymentsPro_Model_Config::REQUEST_TYPE_PAYMENT);
        $payment->setAmount($amount);
        $payment->setTransactionType(Ebizmarts_SagePaymentsPro_Model_Config::TRANSACTION_TYPE_CAPTURE);
        $payment->setOperationType(Ebizmarts_SagePaymentsPro_Model_Config::CODE_PAYMENT);
        $payment->setCcOwner($this->getInfoInstance()->getCcOwner());
        if (!$token&&$this->_recurring) {
            $token = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->addToken($payment, false);
        }
        if ($this->_useToken) {
            $result =  Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')
                ->postRequestWithToken($payment, $amount, $token);
        } else {
            $result = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->postRequestWithoutToken($payment);
        }
        $this->_processSaleResponse($result, $payment, $amount, $token);

        return $this;

    }
    protected function _processSaleResponse($result, $payment, $amount, $token)
    {
        $error = false;

        if ($result->getResponseStatus() == Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_APPROVED) {
            // set the payment data
            $payment->setLastTransId($result->getPostCodeResult());
            $payment->setTransactionType(Ebizmarts_SagePaymentsPro_Model_Config::TRANSACTION_TYPE_CAPTURE);
            Mage::getModel('ebizmarts_sagepaymentspro/transaction')
                ->saveTransactionDirect($payment, $result, $amount, $token);
            Mage::dispatchEvent('sagepaymentspro_after_pay', array('order'=>$payment->getOrder()));
        } else {
            if ($result->getResponseStatusDetail()) {
                $error = '';
                if ($result->getResponseStatus() == Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_NOTAUTHED) {
                    $error = Mage::helper('ebizmarts_sagepaymentspro')
                        ->__('Your credit card can not be authenticated: ');
                } elseif ($result->getResponseStatus() ==
                    Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_REJECTED) {
                    $error = Mage::helper('ebizmarts_sagepaymentspro')->
                    __(
                        Mage::getStoreConfig(
                            Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_ERROR_MSG,
                            $payment->getOrder()->getStoreId()
                        )
                    );
                }
                $errorDesc = Mage::helper('ebizmarts_sagepaymentspro')
                    ->getResponseCodeDescription($result->getPostCodeResult());
                $error .= Mage::helper('ebizmarts_sagepaymentspro')->__($errorDesc);
            } else {
                $error = Mage::helper('ebizmarts_sagepaymentspro')->__('Error in capturing the payment');
            }
        }
        if ($error !== false) {
            if ($this->_recurring) {
                $this->setPaymentOK(false);
                $this->setPaymentsDetail($error);
            } else {
                Mage::throwException($error);
            }
        } else {
            $this->setPaymentOK(true);
        }

    }
    public function release(Varien_Object $payment, $amount)
    {
        $error = false;
        $payment->setTransactionType(Ebizmarts_SagePaymentsPro_Model_Config::TRANSACTION_TYPE_RELEASE);
        if ($amount>0) {
            $payment->setOperationType(Ebizmarts_SagePaymentsPro_Model_Config::CODE_PRIOR_AUTH_SALE);
            $result = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->postReference($payment, $amount);

            if (is_object($result)) {
                switch ($result->getResponseStatus()) {
                    case Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_APPROVED:
                        Mage::getModel('ebizmarts_sagepaymentspro/transaction')
                            ->saveTransactionDirect($payment, $result, $amount, null);
                        break;
                    default:
                        $error = Mage::helper('ebizmarts_sagepaymentspro')
                            ->__('Error at SagePayments in authorizing the payment');
                        break;
                }
            }

        } else {
            $error = Mage::helper('ebizmarts_sagepaymentspro')->__('Error releasing the payment');
        }

        if ($error !== false) {
            $error .= "\r\n". $result->getResponseStatusDetail();
            Mage::throwException($error);
        }

        return $this;

    }

    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::getSingleton('checkout/session')->setMd(null)
            ->setAcsurl(null)
            ->setPareq(null);

        $token = $this->getInfoInstance()->getAdditionalInformation('token');
        if ($token) {
            $this->_useToken = true;
        } else {
            $remembertoken = $this->getInfoInstance()->getAdditionalInformation('remembertoken');
            if ($remembertoken) {
                Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->addToken($payment);
            }
        }

        $payment->setAnetTransType(Ebizmarts_SagePaymentsPro_Model_Config::REQUEST_TYPE_AUTHORIZE);
        $payment->setAmount($amount);
        $payment->setOperationType(Ebizmarts_SagePaymentsPro_Model_Config::CODE_AUTHORIZE);
        $payment->setCcOwner($this->getInfoInstance()->getCcOwner());
        if (!$token&&$this->_recurring) {
            $token = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->addToken($payment, false);
        }
        if ($this->_useToken) {
            $result =  Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')
                ->postRequestWithToken($payment, $amount, $token);
        } else {
            $result = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->postRequestWithoutToken($payment);
        }
        $this->_processAuthorizeResponse($result, $payment, $amount, $token);
        return $this;
    }
    protected function _processAuthorizeResponse($result, $payment, $amount, $token)
    {
        $error = false;
        if ($result->getResponseStatus() == Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_APPROVED) {
            // set the payment data
            $payment->setLastTransId($result->getTrnSecuritykey());
            $payment->setTransactionType(Ebizmarts_SagePaymentsPro_Model_Config::TRANSACTION_TYPE_AUTHORIZE);
            Mage::getModel('ebizmarts_sagepaymentspro/transaction')
                ->saveTransactionDirect($payment, $result, $amount, $token);
            Mage::dispatchEvent('sagepaymentspro_after_pay', array('order'=>$payment->getOrder()));
        } else {
            if ($result->getResponseStatusDetail()) {
                $error = '';
                if ($result->getResponseStatus() == Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_NOTAUTHED) {
                    $error = Mage::helper('ebizmarts_sagepaymentspro')
                        ->__('Your credit card can not be authenticated: ');
                } elseif ($result->getResponseStatus() ==
                    Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_REJECTED) {
                    $error = Mage::helper('ebizmarts_sagepaymentspro')
                        ->__(
                            Mage::getStoreConfig(
                                Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_ERROR_MSG,
                                $payment->getOrder()->getStoreId()
                            )
                        );
                }
                $error .= $result->getResponseStatusDetail();
            } else {
                $error = Mage::helper('ebizmarts_sagepaymentspro')->__('Error in capturing the payment');
            }
        }
        if ($error !== false) {
            if ($this->_recurring) {
                $this->setPaymentOK(false);
                $this->setPaymentsDetail($error);
            } else {
                Mage::throwException($error);
            }
        } else {
            $this->setPaymentOK(true);
        }
    }
    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract|void
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $error = false;
        $payment->setTransactionType(Ebizmarts_SagePaymentsPro_Model_Config::TRANSACTION_TYPE_REFUND);
        if ($amount>0) {
            $payment->setOperationType(Ebizmarts_SagePaymentsPro_Model_Config::CODE_REFUND);
            $result = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->postReference($payment, $amount);

            if (is_object($result)) {
                switch ($result->getResponseStatus()) {
                    case Ebizmarts_SagePaymentsPro_Model_Config::RESPONSE_CODE_APPROVED:
                        Mage::getModel('ebizmarts_sagepaymentspro/transaction')
                            ->saveTransactionDirect($payment, $result, $amount, null);
                        break;
                    default:
                        $error = Mage::helper('ebizmarts_sagepaymentspro')
                            ->__('Error at SagePayments in refund the payment');
                        break;
                }
            }

        } else {
            $error = Mage::helper('ebizmarts_sagepaymentspro')->__('Error in refunding the payment');
        }

        if ($error !== false) {
            $error .= "\r\n". $result->getResponseStatusDetail();
            Mage::throwException($error);
        }

        return $this;
    }
    public function recurringFirst()
    {
        $this->_recurring = true;
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