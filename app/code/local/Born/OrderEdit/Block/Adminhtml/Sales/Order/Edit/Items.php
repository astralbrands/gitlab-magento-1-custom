<?php
class Born_OrderEdit_Block_Adminhtml_Sales_Order_Edit_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    /**
     * Retrieve required options from parent
     */
    protected function _beforeToHtml()
    {
        $orderId = $this->getRequest()->getParam('order_id', null);
        if (!$orderId) {
            Mage::throwException(Mage::helper('adminhtml')->__('Order id not found.'));
        }
        $order = Mage::getModel('sales/order')->load($orderId);
        $this->setOrder($order);
        parent::_beforeToHtml();
    }

    /**
     * Retrieve order items collection
     *
     * @return unknown
     */
    public function getItemsCollection()
    {
        return $this->getOrder()->getItemsCollection();
    }
}

