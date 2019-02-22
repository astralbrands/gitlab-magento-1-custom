<?php
class Born_OrderEdit_Block_Adminhtml_Sales_Order_Edit_Shipping_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    protected $_order = null;
    
    public function getQuote()
    {
        $order  = $this->getOrder();
        $quote = Mage::getModel('sales/quote')->setStore(Mage::app()->getStore($order->getStoreId()))->load($order->getQuoteId());
        return $quote;
    }
    
    public function setOrder($order){
        $this->_order = $order;
        return  $this;
    }
    
    public function getOrder()
    {
        return $this->_order;
    }
}
