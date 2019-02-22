<?php
/**
 * Main workhorse TGCommerce class. Implements the TGCommerce shipping interface.
 *
 * @author      Paul Snell (paulsnell@singpost.com)
 * @category    TradeGobal
 * @package     TradeGlobal_TGCommerce
 * @copyright   Copyright (c) 2017 TradeGlobal
 */

class TradeGlobal_TGCommerce_Model_Shipping_Carrier_Tgcommerce
    extends TradeGlobal_TGCommerce_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'tgcommerce';
    
    /**
     * Code of the carrier's title in the config
     *
     * @var string
     */
    const CARRIER_TITLE = 'title';

    /**
     * Code of the sandbox enable flag from the backend configuration
     *
     * @var string
     */
    const SANDBOX = 'sandbox';

    /**
     * Code of the sandbox WSDL backend configuration
     *
     * @var string
     */
    const SANDBOX_WSDL = 'sandboxWsdl';

    /**
     * Code of the sandbox user account from the backend configuration
     *
     * @var string
     */
    const SANDBOX_ACCOUNT = 'sandboxAccount';

    /**
     * Code of the sandbox password from the backend configuration
     *
     * @var string
     */
    const SANDBOX_PASSWORD = 'sandboxPassword';

    /**
     * Code of the sandbox merchant code from the backend configuration
     *
     * @var string
     */
    const SANDBOX_MERCHANT_CODE = 'sandboxMerchantCode';

    /**
     * Code of the production WSDL backend configuration
     *
     * @var string
     */
    const PRODUCTION_WSDL = 'productionWsdl';

    /**
     * Code of the production user account from the backend configuration
     *
     * @var string
     */
    const PRODUCTION_ACCOUNT = 'productionAccount';

    /**
     * Code of the production password from the backend configuration
     *
     * @var string
     */
    const PRODUCTION_PASSWORD = 'productionPassword';

    /**
     * Code of the production merchant code from the backend configuration
     *
     * @var string
     */
    const PRODUCTION_MERCHANT_CODE = 'productionMerchantCode';
    
    /**
     * Code of the debug enabled flag from the backend
     *
     * @var string
     */
    const DEBUG_ACTIVE_FLAG = 'debugEnabled';
    
    /**
     * Code for the debug filename entry
     *
     * @var string
     */
    const DEBUG_FILE_NAME = 'debugLogFile';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    const RATE_REQUEST_GENERAL = 'general';

    /**
     * Purpose of rate request
     *
     * @var string
     */
    const RATE_REQUEST_CUSTOM = 'Custom';
    
    /**
     * Type of separator used between fees when parsing fee structure into string;
     * $customFeeString = 'Brokerage=0;Disbursement=0';
     * $shippingFeeString = 'Freight=8.29;FuelSurcharge=0.83;Insurance=10.50';
     * In these cases, we are using the character ';' as the separator (sans the '' of course)
     *
     * @var string
     */
    const FEE_FIELD_SEPARATOR = ';';

    const PRODUCT_TYPE_SIMPLE =         'simple';
    const PRODUCT_TYPE_BUNDLE =         'bundle';
    const PRODUCT_TYPE_VIRTUAL =        'virtual';
    const PRODUCT_TYPE_CONFIGURABLE =   'configurable';
    const PRODUCT_TYPE_GROUPED =        'grouped';
    const PRODUCT_TYPE_DOWNLOADABLE =   'downloadable';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * The Wsdl location URL
     *
     * @var string
     */
    protected $_wsdlUrl = null;
    
    /**
     * The carrier's title
     *
     * @var string
     */
    protected $_configCarrierTitle = null;

    /**
     * The account value from the backend
     *
     * @var string
     */
    protected $_account = null;

    /**
     * The password value from the backend
     *
     * @var string
     */
    protected $_password = null;

    /**
     * The merchantCode value from the backend
     *
     * @var string
     */
    protected $_merchantCode = null;

    /**
     * Rate request data
     *
     * @var Mage_Shipping_Model_Rate_Request|null
     */
    protected $_request = null;

    /**
     * Raw rate request data
     *
     * @var Varien_Object|null
     */
    protected $_rawRequest = null;

    /**
     * Rate result data
     *
     * @var Mage_Shipping_Model_Rate_Result|null
     */
    protected $_result = null;
    
    /**
     * Soap Client
     *
     * @var SoapClient
     */
    protected $_soapClient = null;

    /**
     * Path to wsdl file of rate service
     *
     * @var string
     */
    protected $_rateServiceWsdl;

    /**
     * Path to wsdl file of processOrder service
     *
     * @var string
     */
    protected $_orderServiceWsdl = null;

    /**
     * Path to wsdl file of ship service
     *
     * @var string
     */
    protected $_shipServiceWsdl = null;

    /**
     * Path to wsdl file of track service
     *
     * @var string
     */
    protected $_trackServiceWsdl = null;

    /**
     * SOAP Namespace
     *
     * @var string
     */
    protected $_soapNamespace = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    
    /**
     * Use Static Rate Request (NEVER ENABLE THIS ON PRODUCTION SERVER!!)
     * @var bool
     */
    protected $_useStaticRateRequest = false;
    
    /**
     * DEBUG MODE ACTIVE (Adds a lot of logging)
     * @var bool
     */
    protected $_debugActive = false;
    
    /**
     * Name of debug log file
     * @var string
     */
    protected $_debugFileName = 'system.log';


    public function __construct()
    {
        parent::__construct();
        if ($this->getConfigFlag(self::SANDBOX)) {
            $this->_wsdlUrl = $this->getConfigData(self::SANDBOX_WSDL);
            $this->_account = $this->getConfigData(self::SANDBOX_ACCOUNT);
            $this->_password = $this->getConfigData(self::SANDBOX_PASSWORD);
            $this->_merchantCode = $this->getConfigData(self::SANDBOX_MERCHANT_CODE);
        } else {
            $this->_wsdlUrl = $this->getConfigData(self::PRODUCTION_WSDL);
            $this->_account = $this->getConfigData(self::PRODUCTION_ACCOUNT);
            $this->_password = $this->getConfigData(self::PRODUCTION_PASSWORD);
            $this->_merchantCode = $this->getConfigData(self::PRODUCTION_MERCHANT_CODE);
        }
        $this->_configCarrierTitle = $this->getConfigData(self::CARRIER_TITLE);
        $this->_rateServiceWsdl = trim($this->_wsdlUrl);
        $this->_orderServiceWsdl = trim($this->_wsdlUrl);
        $this->_shipServiceWsdl = trim($this->_wsdlUrl);
        $this->_trackServiceWsdl = trim($this->_wsdlUrl);
        $this->_debugActive = $this->getConfigFlag(self::DEBUG_ACTIVE_FLAG);
        $this->_debugFileName = $this->getConfigData(self::DEBUG_FILE_NAME);
        $this->_debugLog('RateServiceWSDL:' . $this->_rateServiceWsdl);
       
      //  $wsdlBasePath = Mage::getModuleDir('etc', 'TradeGlobal_TGCommerce')  . DS . 'wsdl' . DS . 'tgcommerce' . DS;
       // $this->_shipServiceWsdl = $wsdlBasePath . 'ShipService_v41.wsdl';
      //  $this->_rateServiceWsdl = $wsdlBasePath . 'RateService_v41.wsdl';
      //  $this->_trackServiceWsdl = $wsdlBasePath . 'TrackService_v41.wsdl';
    }

    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @param bool|int $exceptions
     * @return SoapClient
     */
    protected function _createSoapClient($wsdl, $trace = true, $exceptions = true)
    {
        if (1) {
            $usernameNode = new \SoapVar($this->_account, XSD_STRING, null, null, 'Username', $this->_soapNamespace);
            $passwordNode = new \SoapVar($this->_password, XSD_STRING, null, null, 'Password', $this->_soapNamespace);
            $usernameTokenNode = new \SoapVar([$usernameNode, $passwordNode], SOAP_ENC_OBJECT, null, null, 'UsernameToken', $this->_soapNamespace);
            $securityNode = new \SoapVar([$usernameTokenNode], SOAP_ENC_OBJECT, null, null, 'Security', $this->_soapNamespace);
            $header = new \SoapHeader($this->_soapNamespace, 'Security', $securityNode, false);
        } else {
            $usernameNode = new \SoapVar('dware', XSD_STRING, null, null, 'Username', $this->_soapNamespace);
            $passwordNode = new \SoapVar('dware', XSD_STRING, null, null, 'Password', $this->_soapNamespace);
            $usernameTokenNode = new \SoapVar([$usernameNode, $passwordNode], SOAP_ENC_OBJECT, null, null, 'UsernameToken', $this->_soapNamespace);
            $securityNode = new \SoapVar([$usernameTokenNode], SOAP_ENC_OBJECT, null, null, 'Security', $this->_soapNamespace);
            $header = new \SoapHeader($this->_soapNamespace, 'Security', $securityNode, false);
        }
        /** @var SoapClient $soapClient */
        $soapClient = new SoapClient($wsdl, [
            'trace'      => $trace,
            'exceptions' => $exceptions
        ]);

        $soapClient->__setSoapHeaders($header);

        return $soapClient;
    }

    /**
     * Create rate soap client
     *
     * @return SoapClient
     */
    protected function _createRateSoapClient()
    {
        return $this->_createSoapClient($this->_rateServiceWsdl);
    }

    /**
     * Create process order soap client
     *
     * @return SoapClient
     */
    protected function _createOrderSoapClient()
    {
        return $this->_createSoapClient($this->_orderServiceWsdl, 1);
    }


    /**
     * Create ship soap client
     *
     * @return SoapClient
     */
    protected function _createShipSoapClient()
    {
        return $this->_createSoapClient($this->_shipServiceWsdl, 1);
    }

    /**
     * Create track soap client
     *
     * @return SoapClient
     */
    protected function _createTrackSoapClient()
    {
        return $this->_createSoapClient($this->_trackServiceWsdl, 1);
    }

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag($this->_activeFlag)) {
            return false;
        }
        $this->setRequest($request);

        $this->_getQuotes();

       // $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _getQuotes()
    {
        $this->_result = Mage::getModel('shipping/rate_result');
        $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));

        $response = $this->_doRatesRequest(self::RATE_REQUEST_GENERAL);
        $preparedGeneral = $this->_prepareRateResponse($response);
        if ($this->_result->getError() && $preparedGeneral->getError()) {
            return $this->_result->getError();
        }
        $this->_result->append($preparedGeneral);

        return $this->_result;
    }

    /**
     * Prepare and set request to this instance
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return TradeGlobal_TGCommerce_Model_Shipping_Carrier_TGCommerce
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        } else {
            $r->setService('ALL');
        }

        $r->setAccount($this->_account);
        $r->setPassword($this->_password);
        $r->setMerchantCode($this->_merchantCode);
        
        $r->setValue(round($request->getPackageValue(), 2));
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());
        $r->setCustomsValue($request->getPackageCustomsValue());
        $destStreet = Mage::helper('core/string')->substr(str_replace("\n", '', $request->getDestStreet()), 0, 35);
        $r->setDestStreet($destStreet ?: '260 Yonge Street');  // Will work with any address here, but it requires SOMETHING here
        $r->setDestStreetLine2($request->getDestStreetLine2());
        $r->setDestCity($request->getDestCity() ?: 'Toronto');
        
        if (!empty($request->getDestCountryId())) {
           $destCountry = $request->getDestCountryId();
           //$destCountry = 'CA';
        } else {
           $destCountry = self::USA_COUNTRY_ID;
           //$destCountry = 'CA';
        }
        
        // Handle puetro rico state for US as puerto rico country
        //for puerto rico, we will ship as international
        if ($destCountry == self::USA_COUNTRY_ID && ($request->getDestPostcode() == '00912'
                 || $request->getDestRegionCode() == self::PUERTORICO_COUNTRY_ID)
        ) {
           $destCountry = self::PUERTORICO_COUNTRY_ID;
        }
        
        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());
        
        $r->setDestState($request->getDestRegionCode());
   //     $r->setDestState('ON');

        if (!empty($request->getDestPostcode())) {
            $r->setDestPostal($request->getDestPostcode());
        } else {
            $r->setDestPostal('M5B2L9');
        }
       // $r->setDestPostal('M5B2L9');
    
        $r->setValue($request->getPackagePhysicalValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $r->setIsReturn($request->getIsReturn());

        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());
        
        $r->setBaseCurrency($request->getBaseCurrency()->getCurrencyCode());
        
        $shippingWeight = $request->getPackageWeight();
        $r->setWeight($shippingWeight);
        $r->setFreeMethodWeight($request->getFreeMethodWeight());
        
        $r->setOrderShipment($request->getOrderShipment());
        
        if ($request->getPackageId()) {
           $r->setPackageId($request->getPackageId());
        }
        
        $r->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $quote = $this->_setItemRequest($request, $r);                       // Parse the items in the order

        $r->setCustomerFirstname(($quote->getCustomerFirstname()?:'DummyFirst'));
        $r->setCustomerLastname(($quote->getCustomerLastname()?:'DummyLast'));
        $r->setCustomerEmail(($quote->getCustomerEmail()?:'DummyEmail@Dummy.com'));
        $r->setDestPhoneNumber($request->getDestPhoneNumber() ?: '55555555');
        $r->setDestPersonName($request->getDestPersonName());
        $r->setDestCompanyName($request->getDestCompanyName());
        $r->setCustomerId($quote->getCustomerId()?:'123');
        $r->setCartId($quote->getId());
        $r->setBuyCurrency($quote->getStoreCurrencyCode());       // TODO Verify the mechanism by which conversion works in Magento
        $r->setSellCurrency($quote->getStoreCurrencyCode());
        $r->setCurrencyConversionRate(1.000);                     // TODO Where would we get this value?

        $this->_rawRequest = $r;

        return $this;
    }

    private function _setItemRequest($request, $r) {
        $items = array();
        $cartTotal = 0;

        // Configurable items have both parent & child order items.  The parent contains the pricing information.  The child does not. So we use parent.
        // Bundled items contain parent & one or many children.  The children have pricing information.  The parent item will have an agregate SKU
        // if bundle has SKU "BDL1", & 2 Child items have SKU (ace1234 & scoot39) then parent SKU will be "BDL1-ace1234-scoot39".
        // The portal will not be able to recognize the parent SKU.  So we need to send child items for the bundle case.
        // We don't send virtual items at all.

        /* @var $item Mage_Sales_Model_Quote_Item */
        foreach ($request->getAllItems() as $key => $item) {
            if (!$item->getParentItemId()) {
                $parentType = $item->getProductType();
                $parentId = $item->getId();
            }
            switch ($item->getProductType()) {
                case self::PRODUCT_TYPE_VIRTUAL:       // Can't ship a virtual product.  Ignore it.
                case self::PRODUCT_TYPE_DOWNLOADABLE:  // Can't ship a downloadable product.  Ignore it.
                case self::PRODUCT_TYPE_BUNDLE:        // We want the children items for the bundle.  Ignore the parent.
                    $this->_debugLog("Detected rejected order item: " . $item->getId() . " of productType: " . $item->getProductType() . " with parentID: " . $item->getParentItemId());
                    break;
                case self::PRODUCT_TYPE_SIMPLE:        // This is a real item, but for configurable products, only the parent has pricing.  So ignore child in that case.
                    if ($item->getParentItemId() && $parentType == self::PRODUCT_TYPE_CONFIGURABLE) {
                        $this->_debugLog("Detected rejected order item: " . $item->getId() . " of productType: " . $item->getProductType() . " with parentID: " . $item->getParentItemId());
                        break;                         // Could do continue 2; here as well.  continue is like a break in a switch stamenet.  continue 2; hits foreach loop.
                    }
                case self::PRODUCT_TYPE_CONFIGURABLE:  // Always ship configurable items as the child has no price in it
                case self::PRODUCT_TYPE_GROUPED:       // Grouped items act like simple items, but they are named "grouped".  So these are basically just simple items.

                    if ($key == 0) {
                        $quote = $item->getQuote();
                    }
                    $vItem = new Varien_Object();
                    $vItem->setPriceBookPrice(0);
                    $vItem->setPrice($item->getPrice());
                    $vItem->setQuantity($item->getQty());
                    $vItem->setItemId($item->getId());
                    $vItem->setSku($item->getSku());
                    if ($item->getPrice() != 0) {                // SHOULD NEVER HIT THIS CASE!!!!
                        if (isset($items[$item->getSku()])) {    // Duplicate product in order can happen for case of grouped product and same simple or configurable product based on same simple product
                            $skuKey = $item->getSku();
                            $oldItem = $items[$skuKey];
                            $oldPrice = $oldItem->getPrice();
                            if ((float)$oldPrice != (float)$item->getPrice()) {   // Not sure if the price is always the same or not.  Lots of configurations. So check for the case where it isn't.
                                $message = "Found item with duplicate SKU && different price";
                                mage::log($message, Zend_Log::ERR);
                                $this->_debugLog($message);
                                $message = "Original ItemID: " . $oldItem->getItemId() . " and price: " . $oldPrice . ' vs new ItemID: ' . $item->getId() . ' and price: ' . $item->getPrice() . ' using new price.';
                                mage::log($message, Zend_Log::ERR);
                                $this->_debugLog($message);
                            }
                            $oldQty = $items[$skuKey]->getQuantity();
                            $items[$skuKey]->setQuantity($oldQty + $item->getQty());
                            $cartTotal += $oldPrice * $item->getQty();  // If the prices don't match, we'll log it and use the first one we got.
                            $message = 'SKU: ' . $item->getSku() . ' Adding Original ItemID: ' . $oldItem->getItemId() . " and price: " . $oldPrice . ' and Qty: ' . $oldQty . ' to new ItemID: ' . $item->getId() . ' with price: ' . $item->getPrice() . ' and with qty: ' . $item->getQty();
                            $this->_debugLog($message);
                        } else {
                            $items[$item->getSku()] = $vItem;
                            $cartTotal += $item->getPrice() * $item->getQty();
                        }
                    } else {
                        $message1 = "Detected zero-priced, non-virtual, non-child item with itemID: " . $item->getId();
                        $message2 = "Unable to quote this item because the portal will not accept zero-priced items.";
                        mage::log($message1, Zend_Log::ERR);
                        $this->_debugLog($message1);
                        mage::log($message2, Zend_Log::ERR);
                        $this->_debugLog($message2);
                    }
                    break;
                default:
                    $this->_debugLog("ProductType of productType: " . $item->getProductType() . " not recognized for OrderItemID : " . $item->getId());
            }
        }
        $r->setCartTotal($cartTotal);
        $r->setItems($items);                                   // items are indexed by the SKU
        $r->setQuoteId($item->getQuoteId());
        return $quote;
    }

    /**
     * Get result of request
     *
     * @return mixed
     */
    public function getResult()
    {
       return $this->_result;
    }

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     * @return array
     */
    protected function _formRateRequest($purpose)
    {
        $r = $this->_rawRequest;

        $accountNameField = array();
        $accountNameField['firstField'] = $r->getCustomerFirstname();
        $accountNameField['lastField'] = $r->getCustomerLastname();
        
        $shippingNameField = array();
        $shippingNameField['firstField'] = $r->getCustomerFirstname();
        $shippingNameField['lastField'] = $r->getCustomerLastname();
        
        $accountField = array();
        $accountField['emailField'] = $r->getCustomerEmail();
        $accountField['merchantAccountIdField'] = $r->getCustomerId(); // Despite it's name, the docs for TGCommerce say (Unique Identifier for Customer Account)
        $accountField['nameField'] = $accountNameField;
        
        $cart = array();
        $cart['accountField'] = $accountField;
        if ((float)$r->getValueWithDiscount() != (float)$r->getCartTotal()) {
            $cart['billTotalField'] = $r->getValueWithDiscount();
        } else {
            $cart['billTotalField'] = 0;
        }
        $cart['buyCurrencyCodeField'] = $r->getBuyCurrency();
        $cart['cartTotalField'] = $r->getCartTotal();
        $cart['exchangeRateField'] = $r->getCurrencyConversionRate();
        $cart['isReturnField'] = false;  // Returns not currently supported
        $cart['merchantCartIDField'] = $r->getCartId();
        $cart['merchantCodeField'] = $r->getMerchantCode();
        // $cart['merchantCodeField'] = 'DWARE';
        $cart['priceBookAmountField'] = 0;
        $cart['sellCurrencyCodeField'] = $r->getSellCurrency();
        
        $products = array();
        $cartItem = array();
        foreach ($r->getItems() as $item) {
           $product = array();
           $product['priceBookPriceField'] = $item->getPriceBookPrice();
           $product['priceField'] = $item->getPrice();
           $product['quantityField'] = $item->getQuantity();
           $product['sKUField'] = $item->getSku();
            //$product['sKUField'] = '886412626898';
            $products[] = $product;
        }
       $cartItem['CartItem'] = $products;
        
       $cart['productsField'] = $cartItem;
       //   $cart['productsField'] = $products;
        
        $shipToField = array();
        
        $shipToField['address1Field'] = $r->getDestStreet();
      //  $shipToField['address2Field'] = $r->setDestStreetLine2();
        $shipToField['cityField'] = $r->getDestCity();
        $shipToField['countryField'] = $r->getDestCountry();
        $shipToField['emailField'] = $r->getCustomerEmail();
        
        $shipToField['nameField'] = $shippingNameField;
        
        $shipToField['phoneField'] = $r->getDestPhoneNumber();
        $shipToField['postalCodeField'] = $r->getDestPostal();
        $shipToField['stateProvinceField'] = $r->getDestState();
        
        
        $cart['shipToAddressField'] = $shipToField;
        
        $cart['taxField'] = 0;
        
        $ratesRequest = array();
        
        $ratesRequest['cart'] = $cart;
        
        return $ratesRequest;
    }

    /**
     * Makes remote request to the carrier and returns a response
     *
     * @param string $purpose
     * @return mixed
     */
    protected function _doRatesRequest($purpose)
    {
        if ($this->_useStaticRateRequest) {
            $ratesRequest = $this->_useStaticRequest($purpose);
        } else {
            $ratesRequest = $this->_formRateRequest($purpose);
        }
        $requestString = serialize($ratesRequest);
        $response = $this->_getCachedQuotes($requestString);
        $this->_debugLog('Quote Cache Matched: ' . ($response === null ? "NO" : "YES"));
        //  $response = null;  // TODO: Set for testing.  Remove this for production so we get cacheing. BUT WE NEED TO PARSE THIS RESPONSE & NOT THE RAW XML to make that work!!!
        $debugData = array('get_quote_request' => $ratesRequest);
        if ($response === null) {
            try {
                $this->_soapClient = $this->_createRateSoapClient();
                $response = $this->_soapClient->GetQuote($ratesRequest);
                if ($this->_debugActive) {
                    mage::log('GetQuote SoapClientRequest:', Zend_Log::DEBUG, $this->_debugFileName);
                    mage::log($this->_soapClient->__getLastRequest(), Zend_Log::DEBUG, $this->_debugFileName);
                    mage::log('GetQuote SoapClientResponse:', Zend_Log::DEBUG, $this->_debugFileName);
                    mage::log($this->_soapClient->__getLastResponse(), Zend_Log::DEBUG, $this->_debugFileName);
                }
                //    $this->_debugLog('Response: ' . print_r($response,true));
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['get_quote_result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                Mage::dispatchEvent('tradeglobal_tgcommerce_getquote_exception', array('exception' => $e));
                $response = new stdClass();
                $message = $e->getMessage();
                if (isset($e->detail->ExceptionDetail->InnerException->Message)) {
                    $message .= '\n' . $e->detail->ExceptionDetail->InnerException->Message;
                }
                $response->error = $message;
                $response->code = $e->getCode();
                Mage::logException($e);
                mage::log('Exception GetQuote SoapClientRequest:', Zend_Log::ERR, $this->_debugFileName);
                mage::log($this->_soapClient->__getLastRequest(), Zend_Log::ERR, $this->_debugFileName);
                mage::log('Exception GetQuote SoapClientResponse:', Zend_Log::ERR, $this->_debugFileName);
                mage::log($this->_soapClient->__getLastResponse(), Zend_Log::ERR, $this->_debugFileName);
            }
        } else {
            $response = unserialize($response);
            $debugData['get_quote_cached_result'] = $response;
        }
        $this->_debugLog($debugData);
        return $response;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Mage_Shipping_Model_Rate_Result
     */
    protected function _prepareRateResponse($response)
    {
        $error = false;
        $errorMessage = null;
        $costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve rates';


        $result = Mage::getModel('shipping/rate_result');
        mage::log('Response follows:', Zend_Log::DEBUG, $this->_debugFileName);
        mage::log($response, Zend_Log::DEBUG, $this->_debugFileName);
        if (is_object($response) && !isset($response->error)) {
            $rates = $response->GetQuoteResult->quotesField->Quote;
            // mage::log("Rates:", Zend_Log::DEBUG, "TestMyLogging.log");
            // mage::log(print_r($rates, true), Zend_Log::DEBUG, "TestMyLogging.log");
            if (!is_array($rates)) {
                $rates = array($rates);  // If there's only one shipping method, it doesn't give us an array
            }
            foreach ((array)$rates as $rate) {
               $costOfGoods = $rate->cOGField;
               $package = $rate->transportationField->packageField->Package;
               if (is_array($package)) {
                  $package = current($package);
               }
               $itineraryStepField = $package->itineraryField->stepField->Step;
               $carrierMethodField = $itineraryStepField->methodField;
               $carrierCode = $carrierMethodField->carrierCodeField;
               $methodName = $carrierMethodField->carrierDescField;
               $title = $carrierMethodField->serviceDescField;
               $estDeliveryTime = $itineraryStepField->estDeliveryTimeField;
               $quoteId = $rate->quoteIdField;
               
               $customFee = (float)$rate->feesField->customField->totalField;
               $customDiscountField = $rate->feesField->customField->discountField;
               if (!empty($customDiscountField) && !empty((array)$customDiscountField)) {
                  $customDiscount = (float)$customDiscountField->Discount->amountField;
               } else {
                  $customDiscount = 0;
               }
               $customFeeString = $this->_parseFees($rate->feesField->customField->feeField->Fee);
               
               $importFee = (float)$rate->feesField->importField->totalField;
               $importDiscountField = $rate->feesField->importField->discountField;
               if (!empty($importDiscountField) && !empty((array)$importDiscountField)) {
                  $importDiscount = (float)$importDiscountField->Discount->amountField;
               } else {
                  $importDiscount = 0;
               }
               $importFeeString = $this->_parseFees($rate->feesField->importField->feeField->Fee);
               
               $serviceFee = (float)$rate->feesField->serviceField->totalField;
               $serviceDiscountField = $rate->feesField->serviceField->discountField;
               if (!empty($serviceDiscountField) && !empty((array)$serviceDiscountField)) {
                  $serviceDiscount = (float)$serviceDiscountField->Discount->amountField;
               } else {
                  $serviceDiscount = 0;
               }
               $serviceFeeString = $this->_parseFees($rate->feesField->serviceField->feeField->Fee);
               
               $shippingFee = (float)$rate->feesField->shippingField->totalField;
               $shippingDiscountField = $rate->feesField->shippingField->discountField;
               if (!empty($shippingDiscountField) && !empty((array)$shippingDiscountField)) {
                  $shippingDiscount = (float)$shippingDiscountField->Discount->amountField;
               } else {
                  $shippingDiscount = 0;
               }
               $shippingFeeString = $this->_parseFees($rate->feesField->shippingField->feeField->Fee);
               

                
               $shipMethod = Mage::getModel('shipping/rate_result_method');
               $shipMethod->setCarrier($this->_code);
               $shipMethod->setCarrierTitle($this->_configCarrierTitle);
               $shipMethod->setMethod($carrierCode);  // This is translated later to be the code ($rate->getCarrier . '_' . $rate->getMethod & must be unique, which method is NOT!!!
               $shipMethod->setMethodTitle($title);
               $shipMethod->setMethodDescription($title);
               $shipMethod->setDeliveryTime($estDeliveryTime);
               $shipMethod->setQuoteId($quoteId);
               $shipMethod->setCustomFee($customFee);
               $shipMethod->setCustomDiscount($customDiscount);
               $shipMethod->setCustomFeeString($customFeeString);
               $shipMethod->setImportFee($importFee);
               $shipMethod->setImportDiscount($importDiscount);
               $shipMethod->setImportFeeString($importFeeString);
               $shipMethod->setServiceFee($serviceFee);
               $shipMethod->setServiceDiscount($serviceDiscount);
               $shipMethod->setServiceFeeString($serviceFeeString);
               $shipMethod->setShippingFee($shippingFee);
               $shipMethod->setShippingDiscount($shippingDiscount);
               $shipMethod->setShippingFeeString($shippingFeeString);
               $nonShipFee = $customFee + $importFee + $serviceFee;
               $shipServiceFee = $shippingFee + $serviceFee;
               $customImportFee = $customFee + $importFee;
               $shipMethod->setNonShipFee($nonShipFee);
               $shipMethod->setCustomImportFee($customImportFee);
               $shipMethod->setShipServiceFee($shipServiceFee);
                
               $totalFee = $shippingFee + $nonShipFee;
               $shipMethod->setTotalFee($totalFee);
                // $shipMethod->setCost($fees);
                //  $shipMethod->setPrice($fees);
               $shipMethod->setCost($costOfGoods);
               $shipMethod->setPrice($totalFee);
               $shipMethod->setCogs($costOfGoods);
               $shipMethod->setExtendedDetail(true);
               $result->append($shipMethod);
               if ($this->_debugActive) {
                  $this->_logShipMethodInformation($shipMethod);
               }
               
            }
        } else if (isset($response->error)) {
            $error = true;
            $errorMessage = $response->error;
        } else {
           $error = true;
           $errorMessage = $this->getConfigData('specificerrmsg');
        }
        if ($error) {
            $this->_errorLog('Error: ' . $errorMessage);
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            if (empty($errorMessage)) {
                $error->setErrorMessage($errorTitle);
            } else {
                $error->setErrorMessage($errorMessage);
            }
            $result->append($error);
        }
        return $result;
    }
    
    protected function _parseFees($fees) {
       if (isset($fees->amountField)) { // Watch out for case where there's only one fee item as it doesn't use an array in that case
          $fees = array($fees);
       }
       $feeString = '';
       foreach ($fees as $fee) {
          if ($feeString != '' ) {
             $feeString .= self::FEE_FIELD_SEPARATOR;
          }
          $feeString .= $fee->typeField . '=' . $fee->amountField;
       }
       return $feeString;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }
        return $arr;
    }

    /**
     * Create a processOrder request to lock in quote
     *
     * @param Varien_Object $request
     * @return mixed
     */
    public function tgProcessOrder($request) {
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Shipping_Carrier_Tgcommerce.tgProcessOrder::START');
        $this->_setProcessOrderRequest($request);  // Passed Varien_Object by reference
        $response = $this->_doProcessOrder($request);
        $response = $this->_formProcessOrderResponse($response);
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Shipping_Carrier_Tgcommerce.tgProcessOrder::COMPLETE');
        return $response;
    }

    /**
     * Set process order request
     *
     * @param $r Varien_Object
     * @return void
     */
    protected function _setProcessOrderRequest($r)
    {
        
        $r->setAccount($this->_account);
        $r->setPassword($this->_password);
        $r->setMerchantCode($this->_merchantCode);

        if (!empty($r->getDestCountryId())) {
            $destCountry = $r->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        // Handle puetro rico state for US as puerto rico country
        //for puerto rico, we will ship as international
        if ($destCountry == self::USA_COUNTRY_ID && ($r->getDestPostCode() == '00912'
                || $r->getDestRegionCode() == self::PUERTORICO_COUNTRY_ID)
        ) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());

        $r->setDestState($r->getDestRegionCode());

        $r->setDestPostal($r->getDestPostCode());

        $this->_rawRequest = $r;
    }

    /**
     * Forming request for process order call
     *
     * @param Varien_Object $request
     * @return array
     */
    protected function _formProcessOrderRequest($request)
    {

        $order = array();
        $order['merchantCodeField'] = $request->getMerchantCode();
        $order['merchantOrderIdField'] = $request->getOrderId();
        $order['orderAttributesField'] = null;
        $order['paymentInfoField'] = null;
        $order['processPaymentField']= 'None';
        $order['quoteIdField'] = $request->getQuoteId();

        $shippingNameField = array();

        $shippingNameField['firstField'] = $request->getCustomerFirstname();
        $shippingNameField['lastField'] = $request->getCustomerLastname();
        if (isset($request->getCustomerMiddlename)) {
            $shippingNameField['middleField'] = $request->getCustomerMiddlename;
        }

        $shipToField = array();

        $shipToField['address1Field'] = $request->getDestStreet1();
        if ($request->getDestStreet2()) {
            $shipToField['address2Field'] = $request->getDestStreet2();
        }
        //  $shipToField['address2Field'] = $request->setDestStreetLine2();
        $shipToField['cityField'] = $request->getDestCity();
        $shipToField['countryField'] = $request->getDestCountry();
        $shipToField['emailField'] = $request->getCustomerEmail();

        $shipToField['nameField'] = $shippingNameField;

        $shipToField['phoneField'] = $request->getDestPhoneNumber();
        $shipToField['postalCodeField'] = $request->getDestPostal();
        $shipToField['stateProvinceField'] = $request->getDestState();


        $order['shipToAddressField'] = $shipToField;

        $processRequest = array();

        $processRequest['order'] = $order;

        return $processRequest;
    }

    /**
     * Makes remote request to the carrier and returns a response
     *
     * @param Varien_Object $request
     * @return mixed
     */
    protected function _doProcessOrder($request)
    {
        $processRequest = $this->_formProcessOrderRequest($request);

        $debugData = array('get_quote_request' => $processRequest);
        try {
            $this->_soapClient = $this->_createOrderSoapClient();
            $response = $this->_soapClient->ProcessOrder($processRequest);
            if ($this->_debugActive) {
                mage::log('ProcessOrder SoapClientRequest:', Zend_Log::DEBUG, $this->_debugFileName);
                mage::log($this->_soapClient->__getLastRequest(), Zend_Log::DEBUG, $this->_debugFileName);
                mage::log('ProcessOrder SoapClientResponse:', Zend_Log::DEBUG, $this->_debugFileName);
                mage::log($this->_soapClient->__getLastResponse(), Zend_Log::DEBUG, $this->_debugFileName);
            }
            $debugData['process_order_result'] = $response;
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            Mage::dispatchEvent('tradeglobal_tgcommerce_processorder_exception', array('exception' => $e));
            $response = new stdClass();
            $message = $e->getMessage();
            if (isset($e->detail->ExceptionDetail->InnerException->Message)) {
                $message .= '\n' . $e->detail->ExceptionDetail->InnerException->Message;
            }
            $response->error = $message;
            $response->code = $e->getCode();
            Mage::logException($e);
            mage::log('Exception ProcessOrder SoapClientRequest:', Zend_Log::ERR, $this->_debugFileName);
            mage::log($this->_soapClient->__getLastRequest(), Zend_Log::ERR, $this->_debugFileName);
            mage::log('Exception ProcessOrder SoapClientResponse:', Zend_Log::ERR, $this->_debugFileName);
            mage::log($this->_soapClient->__getLastResponse(), Zend_Log::ERR, $this->_debugFileName);
        }

        $this->_debugLog($debugData);
        return $response;
    }

    /**
     * Prepare processOrder response
     *
     * @param stdClass $response
     * @return bool
     */
    protected function _formProcessOrderResponse($response)
    {
        $r = $this->_rawRequest;
        $orderId = $r->getOrderId();

        $error = false;
        $errorMessage = null;
        $errorTitle = 'Unable to processOrder';

        if (is_object($response) && !isset($response->error)) {
            $responseOrderId = $response->ProcessOrderResult->orderIdField;
            if (trim($responseOrderId) != $orderId) {
                $error = true;
                $errorMessage = 'Received back orderId of: ' . $responseOrderId . ' which did not match submitted orderId of: ' . $orderId;
            }
        } else if (isset($response->error)) {
            $error = true;
            $errorMessage = $response->error;
        } else {
            $error = true;
            $errorMessage = $this->getConfigData('specificerrmsg');
        }
        if ($error) {
            $this->_errorLog('Error: ' . $errorMessage);
            return false;
        }
        return true;
    }

    protected function _errorLog($message) {
        mage::log($message, Zend_Log::ERR, $this->_debugFileName);
    }

    protected function _debugLog($message) {
        if ($this->_debugActive) {
            mage::log($message, Zend_Log::DEBUG, $this->_debugFileName);
        }
    }

    protected function _logShipMethodInformation($shipMethod) {
        mage::log("SHIP METHOD FOR " . $shipMethod->getCarrier() . ":" . $shipMethod->getMethodTitle, Zend_Log::DEBUG, $this->_debugFileName);
        mage::log("======================================================", Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('Carrier: ' . $shipMethod->getCarrier(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('CarrierTitle: ' . $shipMethod->getCarrierTitle(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ShipMethodCode: ' . $shipMethod->getMethod(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ShipMethodTitle: ' . $shipMethod->getMethodTitle(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('EstDeliveryTime: ' . $shipMethod->getDeliveryTime(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('QuoteId: ' . $shipMethod->getQuoteId(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('CustomFee: ' . $shipMethod->getCustomFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('CustomFeeDiscount: ' . $shipMethod->getCustomDiscount(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('CustomFeeString: ' . $shipMethod->getCustomFeeString(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ImportFee: ' . $shipMethod->getImportFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ImportFeeDiscount: ' . $shipMethod->getImportDiscount(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ImportFeeString: ' . $shipMethod->getImportFeeString(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ServiceFee: ' . $shipMethod->getServiceFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ServiceFeeDiscount: ' . $shipMethod->getServiceDiscount(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ServiceFeeString: ' . $shipMethod->getServiceFeeString(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ShippingFee: ' . $shipMethod->getShippingFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ShippingFeeDiscount: ' . $shipMethod->getShippingDiscount(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ShippingFeeString: ' . $shipMethod->getShippingFeeString(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('NonShippingFees(custom+import+service): ' . $shipMethod->getNonShipFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('CustomImportFee (custom+import): ' . $shipMethod->getCustomImportFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('ShipServiceFee (shipping+service): ' . $shipMethod->getShipServiceFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('TotalFees(custom+import+service+shipping): ' . $shipMethod->getTotalFee(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('CostOfGoodsSold: ' . $shipMethod->getExtCogs(), Zend_Log::DEBUG, $this->_debugFileName);
        mage::log('Price(same as totalFees): ' . $shipMethod->getPrice(), Zend_Log::DEBUG, $this->_debugFileName);
    }

    // UNIMPLEMENTED METHODS (STUBS) BELOW THIS LINE
    // ==========================================================================================================
    //

    /**
     * Get tracking
     * THIS IS JUST A STUB
     * NOT CURRENTLY IMPLEMENTED FOR TGCOMMERCE
     *
     * @param mixed $trackings
     * @return mixed
     */
    public function getTracking($trackings)
    {

    }

    /**
     * Set tracking request
     * THIS IS JUST A STUB
     * NOT CURRENTLY IMPLEMENTED FOR TGCOMMERCE
     *
     * @return void
     */
    protected function setTrackingRequest()
    {

    }


    /**
     * Parse tracking response
     * THIS IS JUST A STUB
     * NOT CURRENTLY IMPLEMENTED FOR TGCOMMERCE
     *
     * @param array $trackingValue
     * @param stdClass $response
     */
    protected function _parseTrackingResponse($trackingValue, $response)
    {
        $errorTitle = 'Unable to retrieve tracking';
        if (is_object($response)) {

        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }

        if (isset($resultArray)) {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier($this->_code);
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingValue);
            $tracking->addData($resultArray);
            $this->_result->append($tracking);
        } else {
           $error = Mage::getModel('shipping/tracking_result_error');
           $error->setCarrier($this->_code);
           $error->setCarrierTitle($this->getConfigData('title'));
           $error->setTracking($trackingValue);
           $error->setErrorMessage($errorTitle);
           $this->_result->append($error);
        }
    }

    /**
     * Get tracking response
     * THIS IS JUST A STUB
     * NOT CURRENTLY IMPLEMENTED FOR TGCOMMERCE
     *
     * @return string
     */
    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking){
                    if($data = $tracking->getAllData()){
                        if (!empty($data['status'])) {
                            $statuses .= Mage::helper('usa')->__($data['status']) . "\n<br/>";
                        } else {
                            $statuses .= Mage::helper('usa')->__('Empty response') . "\n<br/>";
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('usa')->__('Empty response');
        }
        return $statuses;
    }


    /**
     * Return delivery confirmation types of carrier
     * THIS IS JUST A STUB
     * NOT CURRENTLY IMPLEMENTED FOR TGCOMMERCE
     *
     * @param Varien_Object|null $params
     * @return array
     */
    public function getDeliveryConfirmationTypes(Varien_Object $params = null)
    {
        return $this->getCode('delivery_confirmation_types');
    }

    /**
     * Required function (declared in Abstract) for all default Magento USA shipping modules.
     * So even though we actually are using our own abstract class copied from theirs, we'll leave a stub in here
     * just for the sake of rigorous consistency.
     *
     * @param Varien_Object $request
     */
    protected function _doShipmentRequest(Varien_Object $request)
    {
        // TODO: Implement _doShipmentRequest() method.
    }
}
