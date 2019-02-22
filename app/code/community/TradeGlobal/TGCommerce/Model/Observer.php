<?php
/*
 *
 * Event handler.  Calls the 'placeOrder' method to finalize the shipment in the TGCommerce portal.
 *
 * @codepool   community
 * @package    TradeGlobal
 * @module     TradeGlobal TGCommerce
 *
 * @author     Paul Snell
 * @copyright  Copyright (c) TradeGlobal 2017
 */
class TradeGlobal_TGCommerce_Model_Observer {

    /**
     * Module Name
     *
     * @var string
     */
    const MODULE_NAME = 'TradeGlobal_TGCommerce';

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'tgcommerce';

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CONFIG_PATH = 'carriers/';

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
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Store Identifier
     *
     * @var string
     */
    protected $_store = null;

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

    public function __construct() {
        $this->_debugActive = $this->getConfigFlag(self::DEBUG_ACTIVE_FLAG);
        $this->_debugFileName = $this->getConfigData(self::DEBUG_FILE_NAME);
        $this->_store = Mage::app()->getStore();
	}

    /**
     * Retrieve config flag for store by field
     *
     * @param string $field
     * @return bool
     */
    public function getConfigFlag($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = self::CONFIG_PATH . $this->_code . '/' . $field;
        return Mage::getStoreConfigFlag($path, $this->_store);
    }

    /**
     * Retrieve information from carrier configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = self::CONFIG_PATH . $this->_code . '/' . $field;
        return Mage::getStoreConfig($path, $this->_store);
    }

	public function isTgcommerceOrder($shippingMethod) {

        $this->_debugLog("Checking for TGC Shipping method". $shippingMethod);
		$len = strlen($this->_code);
		if (strlen($shippingMethod) > $len && substr($shippingMethod,0,$len) == $this->_code) {
			$isTGC = true;
		} else {
            $isTGC = false;
        }
        $this->_debugLog('Is a TGC Order? ' . ($isTGC?'YES':'NO'));
		return $isTGC;

	}

    public function tgSaveShipPlaceOrder($observer) {
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Observer.tgSaveShipPlaceOrder::START');
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();
        $orderId = $order->getRealOrderId();

        $shippingMethod = $order->getShippingMethod();
        if ($this->isTgcommerceOrder($shippingMethod)) {

            /* @var $orderAddress Mage_Sales_Model_Address
            //  $orderAddress = $order->getShippingAddress();  // works but address does not contain rates
             *
             * /* @var $quote Mage_Sales_Model_Quote
             */
            $quote = $order->getQuote();

            /* @var $shippingAddress Mage_Sales_Model_Quote_Address */
            $shippingAddress = $quote->getShippingAddress();

            // $rates = $shippingAddress->getAllShippingRates();  // Also works, but there's a built in method to get the exact rate we want
            $rate = $shippingAddress->getShippingRateByCode($shippingMethod);
            if (!$rate) {
                $message = 'Unable to locate a rate for shippingMethod: ' . $shippingMethod . ' against orderId:' . $orderId;
                $this->_debugLog('Exception: ' . $message);
                throw new Exception($message);
            }
            $processOrderRequest = $this->_prepareProcessOrderRequest($orderId, $shippingAddress, $rate);
            $success = $this->_tgProcessOrder($processOrderRequest);
            if ($success) {
                $this->_tgUpdateShipment($rate);
            }
        }
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Observer.tgSaveShipPlaceOrde::COMPLETE');
    }

    /**
     *
     *
     * @param string $orderId
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param TradeGlobal_TGCommerce_Model_Sales_Quote_Address_Rate $rate
     * @return Varien_Object $r
     */
    protected function _prepareProcessOrderRequest($orderId, $shippingAddress, $rate) {
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Observer._formProcessOrderRequest::START');
        $r = new Varien_Object();
        $r->setOrderId($orderId);
        $fullStreetAddress = str_replace("\n", '', $shippingAddress->getStreetFull());
        $r->setDestStreet1(substr($fullStreetAddress, 0, 35));
        if (strlen($fullStreetAddress) > 35) {
            $r->setDestStreet2(substr($fullStreetAddress, 35, 35));
        }
        $r->setDestCity($shippingAddress->getCity());
        $r->setDestCountryId($shippingAddress->getCountryId());
        $r->setDestRegionCode($shippingAddress->getRegionCode());
        $r->setDestPostCode($shippingAddress->getPostcode());

        $r->setCustomerFirstname($shippingAddress->getFirstname());
        $r->setCustomerLastname($shippingAddress->getLastname());
        $r->setCustomerMiddlename($shippingAddress->getMiddlename());
        $r->setDestPhoneNumber($shippingAddress->getTelephone());
        $r->setCustomerEmail($shippingAddress->getEmail());


        $r->setQuoteId($rate->getExtQuoteId());

        $this->_debugLog('TradeGlobal_TGCommerce_Model_Observer._formProcessOrderRequest::COMPLETE');
        return $r;
    }

    protected function _tgProcessOrder($processOrderRequest) {
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Shipping_Carrier_Tgcommerce._tgProcessOrder::START');
        /* @var $carrier TradeGlobal_TGCommerce_Model_Shipping_Carrier_Tgcommerce */
        $carrier = mage::getSingleton('tgcommerce/shipping_carrier_tgcommerce');  // Singleton is still specific to the thread.  So no concurrency issues.
        // $carrier = new TradeGlobal_TGCommerce_Model_Shipping_Carrier_Tgcommerce();
        $result = $carrier->tgProcessOrder($processOrderRequest);
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Shipping_Carrier_Tgcommerce._tgProcessOrder::COMPLETE');
        return $result;
    }

    /**
     * @param TradeGlobal_TGCommerce_Model_Sales_Quote_Address_Rate $rate
     */
    protected function _tgUpdateShipment($rate) {
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Observer._tgUpdateShipment::START');
        try{
            $rate->setExtRateSelected(1);
            $rate->setExtBookSuccess(1);
            $rate->save();
        }
        catch(Exception $e) {
            $this->_errorLog("Error attempting to update update rate in Magento for rateID: " . $rate->getRateId());
            $this->_errorLog("Exception:" . $e->getMessage());
            Mage::logException($e);
        }
        $this->_debugLog('TradeGlobal_TGCommerce_Model_Observer._tgUpdateShipment::COMPLETE');
    }


    protected function _errorLog($message) {
        mage::log($message, Zend_Log::ERR, $this->_debugFileName);
    }

    protected function _debugLog($message) {
        if ($this->_debugActive) {
            mage::log($message, Zend_Log::DEBUG, $this->_debugFileName);
        }
    }

    //**** RESERVED FOR FUTURE USE BELOW THIS LINE ******

    public function salesOrderShipmentTrackSaveAfter($observer) {
        $order = $observer->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if ($this->isTgcommerceOrder($shippingMethod)) {

            /* @var $orderAddress Mage_Sales_Model_Address
            //  $orderAddress = $order->getShippingAddress();  // works but address does not contain rates
             *
             * /* @var $quote Mage_Sales_Model_Quote
             */
            $quote = $order->getQuote();

            /* @var $shippingAddress Mage_Sales_Model_Quote_Address */
            $shippingAddress = $quote->getShippingAddress();
            $rates = Mage::getModel('tgcommerce/quote_address_rate')->getCollection();
            $rates->addFieldToFilter('address_id', $shippingAddress->getId());
            $rates->addFieldToFilter('ext_rate_selected', 1);
            if (count($rates) != 1) {
                // well that's a serious issue! Better do something about it.
            }
        }
    }
}
?>
