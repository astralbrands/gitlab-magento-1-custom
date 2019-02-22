<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/30/13
 * Time   : 6:39 PM
 * File   : Sage.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_Api_Sage
{
    const SALE      = 11;
    const AUTHORIZE = 12;

    protected function getStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }
    public function getCheckoutUrl($token,$callbackUrl)
    {
        Mage::helper('ebizmarts_sagepaymentspro')->log(__METHOD__);

        $session        = Mage::getSingleton('checkout/session');
        $quote          = Mage::getModel('sales/quote')->load($session->getQuoteId());
        $orderId        = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order         = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        $shippingAmount = $order->getShippingAmount();
        $taxAmount      = $order->getTaxAmount();

        $country        = $quote->getBillingAddress()->getCountryId();
        $userEmail      = $quote->getBillingAddress()->getEmail();
        $userFirstName  = $quote->getBillingAddress()->getFirstname();
        $userLastName   = $quote->getBillingAddress()->getLastname();
        $state          = $this->getRegion(
            $quote->getBillingAddress()->getRegion(),
            $quote->getBillingAddress()->getCountryId()
        );
        $city           = $quote->getBillingAddress()->getCity();
        $streetOne        = $quote->getBillingAddress()->getStreet(1);
        $streetTwo        = $quote->getBillingAddress()->getStreet(2);
        $zipCode        = $quote->getBillingAddress()->getPostcode();
        $amount         = substr($quote->getBaseGrandTotal(), 0, -2);
        $ordernum       = $quote->getReservedOrderId();
        $storeId        = $quote->getStoreId();
        $appId          = Mage::getModel('ebizmarts_sagepaymentspro/config')->getAppId();
        $mId            = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_M_ID, $storeId);
        $mKey           = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_MKEY, $storeId);
        $url            = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_URL_ENVELOPE, $storeId);
        $phone          = $quote->getBillingAddress()->getTelephone();
        $paymentAction  = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_PAYMENT_ACTION, $storeId);
        $date           =  Mage::getSingleton('core/date')->gmtDate('Y-m-d-H-i-s');
        $trnId          = substr($ordernum . '-' . $date, 0, 40);
        if ($paymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE) {
            $action = self::SALE;
        } else {
            $action = self::AUTHORIZE;
        }
        $xml = "<?xml version='1.0' encoding='utf-16'?>
<Request_v1 xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
            xmlns:xsd='http://www.w3.org/2001/XMLSchema'>
    <Application>
        <ApplicationID>$appId</ApplicationID>
        <LanguageID>EN</LanguageID>
    </Application>
    <Payments>
        <PaymentType>
            <Merchant>
                <MerchantID>$mId</MerchantID>
                <MerchantKey>$mKey</MerchantKey>
            </Merchant>
            <TransactionBase>
                <TransactionID>$trnId</TransactionID>
                <TransactionType>$action</TransactionType>
                <Reference1>Order $ordernum</Reference1>
                <Amount>$amount</Amount>
            </TransactionBase>
            <Customer>
                <Name>
                    <FirstName>$userFirstName</FirstName>
                    <MI> </MI>
                    <LastName>$userLastName</LastName>
                </Name>
                <Address>
                    <AddressLine1>$streetOne</AddressLine1>
                    <AddressLine2>$streetTwo</AddressLine2>
                    <City>$city</City>
                    <State>$state</State>
                    <ZipCode>$zipCode</ZipCode>
                    <Country>$country</Country>
                    <EmailAddress>$userEmail</EmailAddress>
                    <Telephone>$phone</Telephone>
                    <Fax></Fax>
                </Address>
            </Customer>
            <Level3>
                <Level2>
                </Level2>
                <ShippingAmount>$shippingAmount</ShippingAmount>
                <DestinationZipCode>$zipCode</DestinationZipCode>
                <DestinationCountryCode>$country</DestinationCountryCode>
                <VATNumber>$ordernum</VATNumber>
                <DiscountAmount>0</DiscountAmount>
                <DutyAmount>0</DutyAmount>
                <NationalTaxAmount>0</NationalTaxAmount>
                <VATInvoiceNumber>$ordernum</VATInvoiceNumber>
                <VATTaxAmount>$taxAmount</VATTaxAmount>
                <VATTaxRate>0</VATTaxRate>
            </Level3>";

        if ($token == 1) {
            $xml .= "<VaultStorage><Service>CREATE</Service></VaultStorage>";
        } elseif ($token) {
            $xml .= "<VaultStorage><GUID>$token</GUID><Service>RETRIEVE</Service></VaultStorage>";
        }
        $xml .= "<Postback>
<HttpsUrl>$callbackUrl</HttpsUrl>
</Postback>
        </PaymentType>
    </Payments>";
        if (Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CUSTOM_THEME, $storeId)) {
            $xml .= "<UI>".Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CUSTOM_THEME_DATA, $storeId).
                "</UI>";
        }
        $xml .= "</Request_v1>";

        return $xml;
    }

    public function reference($transaction,$payment,$amount,$type)
    {

        $storeId        = $payment->getOrder()->getStoreId();
        $appId          = Mage::getModel('ebizmarts_sagepaymentspro/config')->getAppId();
        $mId            = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_M_ID, $storeId);
        $mKey           = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_MKEY, $storeId);
        $url            = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_URL_SEVD, $storeId);
        $transactionId  = $transaction->getTransactionId();
        $reference      = $transaction->getReference();


        $xml = "<?xml version='1.0' encoding='utf-16'?>
<Request_v1 xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema'>
<Application>
<ApplicationID>$appId</ApplicationID>
<LanguageID>EN</LanguageID>
</Application>
<Payments>
<PaymentType>
<Merchant>
<MerchantID>$mId</MerchantID>
<MerchantKey>$mKey</MerchantKey>
</Merchant>
<TransactionBase>
<TransactionID>$transactionId</TransactionID>
<TransactionType>$type</TransactionType>
<Amount>$amount</Amount>
<VANReference>$reference</VANReference>
</TransactionBase>
</PaymentType>
</Payments>
</Request_v1>";
        Mage::helper('ebizmarts_sagepaymentspro')->log($xml);
        // @codingStandardsIgnoreStart

        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($curlSession, CURLOPT_POST, 1);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, 'Request='.urlencode($xml));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($curlSession);
        // @codingStandardsIgnoreEnd
        list( $continue, $contents ) = explode("\r\n\r\n", $response, 2);
        Mage::helper('ebizmarts_sagepaymentspro')->log($contents);

        return $contents;

    }

    public function decrypt($msg)
    {

        $url            = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_URL_OPENENVELOPE);
        // @codingStandardsIgnoreStart

        $curlSession    = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($curlSession, CURLOPT_POST, 1);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, 'Request='.urlencode($msg));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($curlSession);
        // @codingStandardsIgnoreEnd
        $count = substr_count($response, "\r\n\r\n");
        switch ($count) {
            case 2:
                list( $continue, $header, $contents ) = explode("\r\n\r\n", $response, 3);
                break;
            case 1:
                list($continue,$aux) = explode("\r\n\r\n", $response, 2);
                parse_str($aux, $contents);
                break;
        }
        Mage::helper('ebizmarts_sagepaymentspro')->log($contents);

        return $contents;
    }
    public function obtainToken($data)
    {
        Mage::helper('ebizmarts_sagepaymentspro')->log(__METHOD__);

        $storeId = $this->getStoreId();
        $mId  = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_M_ID, $storeId);
        $mKey = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_MKEY, $storeId);
        $appId          = Mage::getModel('ebizmarts_sagepaymentspro/config')->getAppId();
        $lanid = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_LANID, $storeId);
        $cardNumber     = $data['CARDNUMBER'];
        $cardNumberAux  = substr_replace($data['CARDNUMBER'], "XXXXXXXXXXXXX", 0, strlen($data['CARDNUMBER'])-3);
        $expirationDate = $data['EXPIRATION_DATE'];

        $xml = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<VaultCreditCardTokenRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance"'.
            ' xmlns="http://schemas.datacontract.org/2004/07/wapiGateway.Models">';
        $xml .= "<ApplicationId>$appId</ApplicationId>";
        $xml .= "<CardExpirationDate>$expirationDate</CardExpirationDate>";
        $xml .= "<CardNumber>$cardNumber</CardNumber>";
        $xml .= "</VaultCreditCardTokenRequest>";

        $xmlAux = '<?xml version="1.0" encoding="utf-8"?>';
        $xmlAux .= '<VaultCreditCardTokenRequest xmlns:i="http://www.w3.org/2001/XMLSchema-instance"'.
            ' xmlns="http://schemas.datacontract.org/2004/07/wapiGateway.Models">';
        $xmlAux .= "<ApplicationId>$appId</ApplicationId>";
        $xmlAux .= "<CardExpirationDate>$expirationDate</CardExpirationDate>";
        $xmlAux .= "<CardNumber>$cardNumberAux</CardNumber>";
        $xmlAux .= "</VaultCreditCardTokenRequest>";

        Mage::helper('ebizmarts_sagepaymentspro')->log($xmlAux);

        $rc = $this->_sendTokenRequest($xml);
        Mage::helper('ebizmarts_sagepaymentspro')->log($rc);

        Mage::helper('ebizmarts_sagepaymentspro')->log($rc);
        $result = Mage::getModel('ebizmarts_sagepaymentspro/entity_result');
        // @codingStandardsIgnoreStart
        $result->setGuid($rc->Token);
        $result->setResult($rc->Result);
        // @codingStandardsIgnoreEnd
        return $result;

    }
    public function removeToken($token) 
    {
        $storeId = $this->getStoreId();
        $mId  = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_M_ID, $storeId);
        $mKey = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_MKEY, $storeId);
        $appId          = Mage::getModel('ebizmarts_sagepaymentspro/config')->getAppId();
        $lanid = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_LANID, $storeId);

        $xml = "<?xml version='1.0' encoding='utf-16'?>
<Request_v1 xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema'>
<Application>
<ApplicationID>$appId</ApplicationID>
<LanguageID>$lanid</LanguageID>
</Application>
<VaultOperation>
<Merchant>
<MerchantID>$mId</MerchantID>
<MerchantKey>$mKey</MerchantKey>
</Merchant>
<VaultStorage>
<Service>DELETE</Service>
<GUID>$token</GUID>
</VaultStorage>
<VaultID>2341234-12431243-2341235</VaultID>
</VaultOperation>
</Request_v1>";

        Mage::helper('ebizmarts_sagepaymentspro')->log($xml);

        $rc = $this->_sendTokenRequest($xml);
        Mage::helper('ebizmarts_sagepaymentspro')->log($rc);

        $result = Mage::getModel('ebizmarts_sagepaymentspro/entity_result');
        // @codingStandardsIgnoreStart
        $result->setGuid($rc->Token);
        $result->setResult($rc->Result);
        // @codingStandardsIgnoreEnd
        return $result;

    }
    protected function _sendTokenRequest($xml)
    {
        Mage::helper('ebizmarts_sagepaymentspro')->log(__METHOD__);
        Mage::helper('ebizmarts_sagepaymentspro')->log($xml);

        $storeId = $this->getStoreId();
        $url = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_URL_RESTFULL, $storeId);
        $mId  = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_M_ID, $storeId);
        $mKey = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_MKEY, $storeId);
        $hash = base64_encode(
            hash_hmac(Ebizmarts_SagePaymentsPro_Model_Config::HMAC_SHA1_ALGORITHM, 'POST'.$url.$xml, $mKey, true)
        );
        // @codingStandardsIgnoreStart
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        curl_setopt(
            $curlSession, CURLOPT_HTTPHEADER,
            array("Content-Type: application/xml; charset=utf-8","Content-length: ".strlen($xml),
            "Accept: application/xml","Authentication: ".$mId.":".$hash)
        );
        curl_setopt($curlSession, CURLOPT_POST, 1);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($curlSession);
        // @codingStandardsIgnoreEnd
        list($header, $body) = explode("\r\n\r\n", $response, 2);
        $xmlResponse = simplexml_load_string($body);
        Mage::helper('ebizmarts_sagepaymentspro')->log($xmlResponse);

        return $xmlResponse;
    }
    public function postRequestForXmlAPI($xml)
    {
        Mage::helper('ebizmarts_sagepaymentspro')->log(__METHOD__);
        Mage::helper('ebizmarts_sagepaymentspro')->log($xml);

        $storeId = $this->getStoreId();
        $url            = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_URL_SEVD, $storeId);
        // @codingStandardsIgnoreStart
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($curlSession, CURLOPT_POST, 1);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, 'Request='.urlencode($xml));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($curlSession);
        // @codingStandardsIgnoreEnd
        $response = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $response);
        $start = strpos($response, "<?xml");
        $rc = simplexml_load_string(substr($response, $start));
        Mage::helper('ebizmarts_sagepaymentspro')->log($rc);

        $result = Mage::getModel('ebizmarts_sagepaymentspro/entity_result');
        // @codingStandardsIgnoreStart
        $result->setPostCodeResult($rc->PaymentResponses->PaymentResponseType->Response->ResponseIndicator);
        $result->setResponseStatusDetail($rc->PaymentResponses->PaymentResponseType->Response->ResponseCode);
        $result->setResponseStatus($rc->PaymentResponses->PaymentResponseType->Response->ResponseMessage);
        $result->setCvvIndicator($rc->PaymentResponses->PaymentResponseType->TransactionResponse->CVVResult);
        $result->setTrnSecuritykey($rc->PaymentResponses->PaymentResponseType->TransactionResponse->VANReference);
        // @codingStandardsIgnoreEnd

        return $result;
    }
    public function postRequest($data, $operation)
    {
        Mage::helper('ebizmarts_sagepaymentspro')->log(__METHOD__);
        if (isset($data['C_STATE']) && $data['C_STATE']=='') {
            $data['C_STATE'] = '__';
        }
        $auxdata = $data;
        if (isset($data['C_CARDNUMBER'])) {
            $auxdata['C_CARDNUMBER'] = substr_replace(
                $auxdata['C_CARDNUMBER'], "XXXXXXXXXXXXX", 0,
                strlen($auxdata['C_CARDNUMBER'])-3
            );
        }
        Mage::helper('ebizmarts_sagepaymentspro')->log($auxdata);
        $storeId = $this->getStoreId();
        try {
            $client = new SoapClient(
                Mage::getStoreConfig(
                    Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_DIRECT_URL,
                    $storeId
                )
            );
            $res = $this->_assignResult($client, $operation, $data);
        }
        catch(exception $e) {
            Mage::throwException(
                "Communication error: your server is not capable to communicate with Sage Payment".
                " Solutions server"
            );
        }

        Mage::helper('ebizmarts_sagepaymentspro')->log($res);

        $rDoc= new DOMDocument();
        $rDoc->loadXML($res);
        $result = $this->_getResult($rDoc);
        return $result;

    }
    protected function _getResult($rDoc)
    {
        $result = new Varien_Object();
        $approvalIndicator = $rDoc->getElementsByTagName('APPROVAL_INDICATOR');
        if ($approvalIndicator->length>0) {
            $result->setResponseStatus($approvalIndicator->item(0)->nodeValue);
        }
        $code = $rDoc->getElementsByTagName('CODE');
        if ($code->length>0) {
            $result->setPostCodeResult($code->item(0)->nodeValue);
        }
        $message = $rDoc->getElementsByTagName('MESSAGE');
        if ($message->length >0) {
            $result->setResponseStatusDetail($message->item(0)->nodeValue);
        }
        $cvvIndicator = $rDoc->getElementsByTagName('CVV_INDICATOR');
        if ($cvvIndicator->length>0) {
            $result->setCvvIndicator($cvvIndicator->item(0)->nodeValue);
        }
        $avsIndicator = $rDoc->getElementsByTagName('AVS_INDICATOR');
        if ($avsIndicator->length>0) {
            $result->setAvsIndicator($avsIndicator->item(0)->nodeValue);
        }
        $riskIndicator = $rDoc->getElementsByTagName('RISK_INDICATOR');
        if ($riskIndicator->length>0) {
            $result->setRiskIndicator($riskIndicator->item(0)->nodeValue);
        }
        $reference = $rDoc->getElementsByTagName('REFERENCE');
        if ($reference->length>0) {
            $result->setTrnSecuritykey($reference->item(0)->nodeValue);
        }
        $orderNumber = $rDoc->getElementsByTagName('ORDER_NUMBER');
        if ($orderNumber->length>0) {
            $result->setOrderNum($orderNumber->item(0)->nodeValue);
        }
        return $result;

    }
    protected function _assignResult($client,$operation,$data)
    {
        // @codingStandardsIgnoreStart
        switch ($operation) {
            case Ebizmarts_SagePaymentsPro_Model_Config::CODE_PAYMENT:
                $rc = $client->BANKCARD_SALE($data);
                $res = $rc->BANKCARD_SALEResult->any;
                break;
            case Ebizmarts_SagePaymentsPro_Model_Config::CODE_AUTHORIZE:
                $rc = $client->BANKCARD_AUTHONLY($data);
                $res = $rc->BANKCARD_AUTHONLYResult->any;
                break;
            case Ebizmarts_SagePaymentsPro_Model_Config::CODE_REFUND:
                $rc = $client->BANKCARD_CREDIT($data);
                $res = $rc->BANKCARD_CREDITResult->any;
                break;
            case Ebizmarts_SagePaymentsPro_Model_Config::CODE_PRIOR_AUTH_SALE:
                $rc = $client->BANKCARD_PRIOR_AUTH_SALE($data);
                $res = $rc->BANKCARD_PRIOR_AUTH_SALEResult->any;
                break;
            case Ebizmarts_SagePaymentsPro_Model_Config::CODE_VOID:
                $rc = $client->BANKCARD_VOID($data);
                $res = $rc->BANKCARD_VOIDResult->any;
            default:
                break;
        }
        return $res;
        // @codingStandardsIgnoreEnd
    }
    public function postRequestSevd($data)
    {
        Mage::helper('ebizmarts_sagepaymentspro')->log(__METHOD__);
        Mage::helper('ebizmarts_sagepaymentspro')->log($data);

        $storeId        = Mage::app()->getStore()->getId();
        $url            = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_URL_SEVD, $storeId);

        // @codingStandardsIgnoreStart
        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_HEADER, 1);
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($curlSession, CURLOPT_POST, 1);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, 'Request='.urlencode($data));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($curlSession);
        // @codingStandardsIgnoreEnd

        //Mage::log($response, null, "SagePayments_Response.log", true);
        list( $continue, $contents ) = explode("\r\n\r\n", $response, 2);
        $response = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1utf-8', $response);
        $start = strpos($response, "<?xml");
        $rc = simplexml_load_string(substr($response, $start));
        Mage::helper('ebizmarts_sagepaymentspro')->log($rc);
        $result = Mage::getModel('ebizmarts_sagepaymentspro/entity_result');
        // @codingStandardsIgnoreStart
        // The result is an XML returned by SagePayments
        $result->setPostCodeResult($rc->PaymentResponses->PaymentResponseType->Response->ResponseCode);
        $result->setResponseStatusDetail($rc->PaymentResponses->PaymentResponseType->Response->ResponseMessage);
        $result->setResponseStatus($rc->PaymentResponses->PaymentResponseType->Response->ResponseIndicator);
        $result->setCvvIndicator($rc->PaymentResponses->PaymentResponseType->TransactionResponse->CVVResult);
        $result->setTrnSecuritykey($rc->PaymentResponses->PaymentResponseType->TransactionResponse->VANReference);
        // @codingStandardsIgnoreEnd
        return $result;

    }
    protected function getRegion($region,$country)
    {
        if (!$region||$region=='') {
            return $country;
        } else {
            return $country;
        }
    }

}