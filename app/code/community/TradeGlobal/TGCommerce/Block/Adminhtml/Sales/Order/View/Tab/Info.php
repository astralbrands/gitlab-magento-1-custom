<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order information tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TradeGlobal_TGCommerce_Block_Adminhtml_Sales_Order_View_Tab_Info
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CONFIG_PATH = 'carriers/';

    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'tgcommerce';

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
        parent::__construct();
        $this->_debugActive = $this->getConfigFlag(self::DEBUG_ACTIVE_FLAG);
        $this->_debugFileName = $this->getConfigData(self::DEBUG_FILE_NAME);
        $this->_store = Mage::app()->getStore();
    }

    public function getShippingRateInfo($order) {
        $shippingMethod = $order->getShippingMethod();
        if ($this->_isTgcommerceOrder($shippingMethod)) {

            /* @var $orderAddress Mage_Sales_Model_Address
            $orderAddress = $order->getShippingAddress();  // works but address does not contain rates
          */
            $quoteId = $order->getQuoteId();
            /* @var $quote Mage_Sales_Model_Quote */
            $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);
           // $this->_debugLog("Quote Found: " . print_r($quote, true));

            /* @var $shippingAddress Mage_Sales_Model_Quote_Address */
            $shippingAddress = $quote->getShippingAddress();
         //   $rates = Mage::getModel('tgcommerce/quote_address_rate')
           //     ->getCollection()
             //   ->addFilter('address_id', $shippingAddress->getId());
           // $rates = $shippingAddress->getAllShippingRates();  // Also works, but there's a built in method to get the exact rate we want
           $rate = $shippingAddress->getShippingRateByCode($shippingMethod);
            if (!$rate) {
                $message = 'Unable to locate a rate for shippingMethod: ' . $shippingMethod . ' against orderId:' . $order->getOrderId();
                //$this->_debugLog('Exception: ' . $message); Should not be a exception but a logged event. TG holds no histories apparently
                //throw new Exception($message);
                Mage::log($rate);
            }
            return $rate;
        }

    }
    protected function _isTgcommerceOrder($shippingMethod) {

        $this->_debugLog("Checking for TGC Shipping method: ". $shippingMethod);
        $len = strlen($this->_code);
        if (strlen($shippingMethod) > $len && substr($shippingMethod,0,$len) == $this->_code) {
            $isTGC = true;
        } else {
            $isTGC = false;
        }
        $this->_debugLog('Is a TGC Order? ' . ($isTGC?'YES':'NO'));
        return $isTGC;

    }

    protected function _debugLog($message) {
        if ($this->_debugActive) {
            mage::log($message, Zend_Log::DEBUG, $this->_debugFileName);
        }
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



}
