<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.9
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2014 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Block_Conversion_Abstract
 */
class Listrak_Remarketing_Block_Conversion_Abstract
    extends Listrak_Remarketing_Block_Require_Onescript
{
    private $_canRender = null;

    /* @var Mage_Sales_Model_Order $_order */
    private $_order = null;

    /* @var Mage_Customer_Model_Customer $_customer */
    private $_customer = null;

    /* @var Mage_Sales_Model_Order_Address $_billingAddress */
    private $_billingAddress = null;

    /**
     * Render block
     *
     * @return string
     */
    public function _toHtml()
    {
        return parent::_toHtml();
    }

    /**
     * Can render
     *
     * @return bool
     */
    public function canRender()
    {
        if ($this->_canRender == null) {
            $this->_canRender = parent::canRender()
                && $this->isOrderConfirmationPage();
        }

        return $this->_canRender;
    }

    /**
     * Get last order ID
     *
     * @return mixed
     */
    public function getOrderId()
    {
        return Mage::getSingleton('checkout/session')->getLastOrderId();
    }

    /**
     * Retrieve order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = Mage::getModel('sales/order')
                ->load($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Order confirmation number
     *
     * @return string
     */
    public function getOrderConfirmationNumber()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * Get ordered items
     *
     * @return Mage_Sales_Model_Order_Item[]
     */
    public function getOrderItems()
    {
        // fix the skus before returning the data
        $result = array();
        
        $getSkuForIds = array();
        foreach($this->getOrder()->getAllVisibleItems() as $item) {
            if (Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $item->getProductType()) {
                $getSkuForIds[] = $item->getProductId();
            }
        }
        
        $skusById = array();
        if (sizeof($getSkuForIds) > 0) {
            $skusById = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('entity_id', $getSkuForIds)
                    ->addAttributeToSelect(array('entity_id', 'sku'))
                    ->getItems();
        }

        /* @var Mage_Sales_Model_Order_Item $item */
        foreach ($this->getOrder()->getAllVisibleItems() as $item) {
            if (array_key_exists($item->getProductId(), $skusById)) {
                $product = $skusById[$item->getProductId()];
                $item->setSku($product->getSku());
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Get billing address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getBillingAddress()
    {
        if (!$this->_billingAddress) {
            $this->_billingAddress = $this->getOrder()->getBillingAddress();
        }

        return $this->_billingAddress;
    }

    /**
     * Get customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Mage::getModel('customer/customer')
                ->load($this->getOrder()->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * Get purchaser email
     *
     * @return string
     */
    public function getEmailAddress()
    {
        if ($this->getCustomer()->getId()) {
            return $this->getCustomer()->getEmail();
        } else {
            return $this->getOrder()->getCustomerEmail();
        }
    }

    /**
     * Get purchaser first name
     *
     * @return string
     */
    public function getFirstName()
    {
        if ($this->getCustomer()->getId()) {
            return $this->getCustomer()->getFirstname();
        } else {
            return $this->getBillingAddress()->getFirstname();
        }
    }

    /**
     * Get purchaser last name
     *
     * @return string
     */
    public function getLastName()
    {
        if ($this->getCustomer()->getId()) {
            return $this->getCustomer()->getLastname();
        } else {
            return $this->getBillingAddress()->getLastname();
        }
    }
}
