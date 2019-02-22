<?php
class Born_OrderEdit_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    protected $_quoteToOrderUpdate = array(
        'subtotal',
        'base_subtotal',
        'tax_amount',
        'base_tax_amount',
        'shipping_amount',
        'base_shipping_amount',
        'shipping_tax_amount',
        'base_shipping_tax_amount',
        'discount_amount',
        'base_discount_amount',
        'grand_total',
        'base_grand_total',
        'discount_description',
        'shipping_discount_amount',
        'base_shipping_discount_amount',
        'subtotal_incl_tax',
        'base_subtotal_incl_tax', //	base_subtotal_total_incl_tax
        'hidden_tax_amount',
        'base_hidden_tax_amount',
        'shipping_hidden_tax_amount',
        'base_shipping_hidden_tax_amnt',
        'shipping_incl_tax',
        'base_shipping_incl_tax'
    );
    
    protected $_quoteToOrderItemUpdate = array(
        'price',
        'base_price',
        'discount_percent',
        'discount_amount',
        'base_discount_amount',
        'tax_percent',
        'tax_amount',
        'base_tax_amount',
        'row_total',
        'base_row_total',
        'base_tax_before_discount',
        'tax_before_discount',
        'price_incl_tax',
        'base_price_incl_tax',
        'row_total_incl_tax',
        'base_row_total_incl_tax',
        'hidden_tax_amount',
        'base_hidden_tax_amount'
    );
    
    public function editAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function saveAction()
    {
        $updatedOrderItems = array();
        $deletedOrderItems = array();
        
        
        
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        
        if(!$order){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('born_orderedit')->__('Order does not exist.'));
            $this->_redirect('adminhtml/sales_order/index');
            return false;
        }
        Mage::register('current_edit_order_customer_id', $order->getCustomerId());
        if(!$order->canShip()){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('born_orderedit')->__('Order update operation is not allowed.'));
            $this->_redirect('adminhtml/sales_order/view', array('order_id'=>$orderId));
            return false;
        }
        $quoteId = $order->getQuoteId();
        
        $quote = Mage::getModel('sales/quote')->setStore(Mage::app()->getStore($order->getStoreId()))->load($quoteId);
        if(!$quote){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('born_orderedit')->__('Valid quote information not available for this order.'));
            $this->_redirect('adminhtml/sales_order/index');
            return false;
        }
        
        $updateItemInformation = $this->getRequest()->getParam('item', array());
        $removeItemInformation = $this->getRequest()->getParam('item_remove', array());
        $customPriceInformation = $this->getRequest()->getParam('custom_price', array());
        $shippingmethod = $this->getRequest()->getParam('order', array());
        $changesPerformed = false;
        $deletePerformed = false;
        $resource = Mage::getSingleton('core/resource');
                        $connection = $resource->getConnection('core_write');
        if(is_array($updateItemInformation) && count($updateItemInformation) > 0){
            try{
                foreach($updateItemInformation  as $orderItemId=>$qtyInfo){
                        $orderItem = $order->getItemById($orderItemId);
                        $quoteItemId = $orderItem->getQuoteItemId();
                    if(!array_key_exists($orderItemId, $removeItemInformation)){
                        if($qtyInfo['qty'] < $orderItem->getQtyOrdered()){
                            $quoteItemBuyRequest = $quote->getItemById($quoteItemId)->getBuyRequest();
                            $quoteItemBuyRequest->setQty($qtyInfo['qty']);
                            $quoteItemBuyRequest->setOriginalQty($qtyInfo['qty']);
                            $quote->updateItem($quoteItemId, $quoteItemBuyRequest);
                            if(array_key_exists($orderItemId, $customPriceInformation)){
                                if($customPriceInformation[$orderItemId] > 0){
                                    $quote->getItemById($quoteItemId)->setCustomPrice($customPriceInformation[$orderItemId])->setOriginalCustomPrice($customPriceInformation[$orderItemId]);
                                }
                            }
                            $changesPerformed = true;
                            $updatedOrderItems[$quoteItemId] = $orderItemId;
                        }elseif($qtyInfo['qty'] > $orderItem->getQtyOrdered()){
                            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('born_orderedit')->__("Qty specified for product '%s' is not allowed to update specified qty.", $orderItem->getName()));
                        }else{
                            if(array_key_exists($orderItemId, $customPriceInformation)){
                                if($customPriceInformation[$orderItemId] > 0){
                                    $quote->getItemById($quoteItemId)->setCustomPrice($customPriceInformation[$orderItemId])->setOriginalCustomPrice($customPriceInformation[$orderItemId]);
                                    $changesPerformed = true;
                                    $updatedOrderItems[$quoteItemId] = $orderItemId;
                                }
                            }
                        }
                    }else{
                        $quote->removeItem($quoteItemId);
                        
                        $orderItemTableName = $resource->getTableName('sales/order_item');
						/* To remove all child items along with parent product */
                        if($orderItem->getProductType() == 'configurable')
                            $connection->query("DELETE FROM `{$orderItemTableName}` WHERE `item_id`='".$orderItemId."' or `parent_item_id`='".$orderItemId."'");
                        else
                            $connection->query("DELETE FROM `{$orderItemTableName}` WHERE `item_id`='".$orderItemId."'");
                        $deletedOrderItems[] = $orderItemId;
                        $deletePerformed = true;
                    }
                }
            }catch(Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        if($changesPerformed || $deletePerformed){
                    if(isset($shippingmethod['shipping_method'])){
                        $quote->getShippingAddress()->setShippingMethod($shippingmethod['shipping_method']);
                    }
                    $quote->collectTotals()->save();
                    if(count($updatedOrderItems) > 0){
                        foreach($updatedOrderItems as $updatedQuoteItemId=>$updatedOrderItemId){
                            $quoteItem = $quote->getItemById($updatedQuoteItemId);
                            $order->getItemById($updatedOrderItemId)
                                ->setQtyOrdered($quoteItem->getQty())
                                ->setPrice($quoteItem->getPrice())
                                ->setBasePrice($quoteItem->getBasePrice())
                                ->setDiscountPercent($quoteItem->getDiscountPercent())
                                ->setDiscountAmount($quoteItem->getDiscountAmount())
                                ->setBaseDiscountAmount($quoteItem->getBaseDiscountAmount())
                                ->setTaxPercent($quoteItem->getTaxPercent())
                                ->setTaxAmount($quoteItem->getTaxAmount())
                                ->setBaseTaxAmount($quoteItem->getBaseTaxAmount())
                                ->setRowTotal($quoteItem->getRowTotal())
                                ->setBaseRowTotal($quoteItem->getBaseRowTotal())
                                ->setBaseTaxBeforeDiscount($quoteItem->getBaseTaxBeforeDiscount())
                                ->setTaxBeforeDiscount($quoteItem->getTaxBeforeDiscount())
                                ->setPriceInclTax($quoteItem->getPriceInclTax())
                                ->setBasePriceInclTax($quoteItem->getBasePriceInclTax())
                                ->setRowTotalInclTax($quoteItem->getRowTotalInclTax())
                                ->setBaseRowTotalInclTax($quoteItem->getBaseRowTotalInclTax())
                                ->setHiddenTaxAmount($quoteItem->getHiddenTaxAmount())
                                ->setBaseHiddenTaxAmount($quoteItem->getBaseHiddenTaxAmount());
                        }
                    }
                    $order->setSubtotal($quote->getShippingAddress()->getSubtotal())
                                    ->setTotalItemCount($quote->getItemsCount())
                                    ->setTotalQtyOrdered($quote->getItemsQty())
                                    ->setBaseTotalQtyOrdered($quote->getItemsQty())
                                    ->setBaseSubtotal($quote->getShippingAddress()->getBaseSubtotal())
                                    ->setTaxAmount($quote->getShippingAddress()->getTaxAmount())
                                    ->setBaseTaxAmount($quote->getShippingAddress()->getBaseTaxAmount())
                                    ->setShippingAmount($quote->getShippingAddress()->getShippingAmount())
                                    ->setBaseShippingAmount($quote->getShippingAddress()->getBaseShippingAmount())
                                    ->setShippingTaxAmount($quote->getShippingAddress()->getShippingTaxAmount())
                                    ->setBaseShippingTaxAmount($quote->getShippingAddress()->getBaseShippingTaxAmount())
                                    ->setDiscountAmount($quote->getShippingAddress()->getDiscountAmount())
                                    ->setBaseDiscountAmount($quote->getShippingAddress()->getBaseDiscountAmount())
                                    ->setGrandTotal($quote->getShippingAddress()->getGrandTotal())
                                    ->setBaseGrandTotal($quote->getShippingAddress()->getBaseGrandTotal())
                                    ->setDiscountDescription($quote->getShippingAddress()->getDiscountDescription())
                                    ->setShippingDiscountAmount($quote->getShippingAddress()->getShippingDiscountAmount())
                                    ->setBaseShippingDiscountAmount($quote->getShippingAddress()->getBaseShippingDiscountAmount())
                                    ->setSubtotalInclTax($quote->getShippingAddress()->getSubtotalInclTax())
                                    ->setBaseSubtotalInclTax($quote->getShippingAddress()->getSubtotalInclTax())
                                    ->setHiddenTaxAmount($quote->getShippingAddress()->getHiddenTaxAmount())
                                    ->setBaseHiddenTaxAmount($quote->getShippingAddress()->getBaseHiddenTaxAmount())
                                    ->setShippingHiddenTaxAmount($quote->getShippingAddress()->getShippingHiddenTaxAmount())
                                    ->setBaseShippingHiddenTaxAmnt($quote->getShippingAddress()->getBaseShippingHiddenTaxAmnt())
                                    ->setShippingInclTax($quote->getShippingAddress()->getShippingInclTax())
                                    ->setBaseShippingInclTax($quote->getShippingAddress()->getBaseShippingInclTax())
                                    ->setShippingMethod($quote->getShippingAddress()->getShippingMethod())
                                    ->setShippingDescription($quote->getShippingAddress()->getShippingDescription());
                        $order->save();
                        $allShipped = false;
                        $allInvoiced = false;
                        $allRefunded = false;
                        foreach($order->getAllVisibleItems() as $_orderItem){
                            if(!in_array($_orderItem->getId(),$deletedOrderItems)){
                            $allShipped = (boolean)($_orderItem->getQtyOrdered() == $_orderItem->getQtyShipped());
                            $allInvoiced = (boolean)($_orderItem->getQtyOrdered() == $_orderItem->getQtyInvoiced());
                            $allRefunded = (boolean)($_orderItem->getQtyOrdered() == $_orderItem->getQtyRefunded());
                            }
                        }
                        if($allShipped && $allInvoiced){
                            $table = $resource->getTableName('sales_flat_order');
                            $connection->query("UPDATE `{$table}` SET `state`='".Mage_Sales_Model_Order::STATE_COMPLETE."', `status`='".Mage_Sales_Model_Order::STATE_COMPLETE."' WHERE `entity_id`='".$order->getId()."'");
                        }
                        if($allRefunded){
                            $table = $resource->getTableName('sales_flat_order');
                            $connection->query("UPDATE `{$table}` SET `state`='".Mage_Sales_Model_Order::STATE_CLOSED."', `status`='".Mage_Sales_Model_Order::STATE_CLOSED."' WHERE `entity_id`='".$order->getId()."'");
                        }
                    Mage::getModel('bornintegration/observer')->updateSyncAttemptOrderEdit($order); // Reset order sync attempt counter
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('born_orderedit')->__('Order information has been updated successfully.'));
                }else{
                    if(isset($shippingmethod['shipping_method'])){
                        $quote->getShippingAddress()->setShippingMethod($shippingmethod['shipping_method']);
                        $quote->collectTotals()->save();
                    $order->setSubtotal($quote->getShippingAddress()->getSubtotal())
                                    ->setTotalItemCount($quote->getItemsCount())
                                    ->setTotalQtyOrdered($quote->getItemsQty())
                                    ->setBaseTotalQtyOrdered($quote->getItemsQty())
                                    ->setBaseSubtotal($quote->getShippingAddress()->getBaseSubtotal())
                                    ->setTaxAmount($quote->getShippingAddress()->getTaxAmount())
                                    ->setBaseTaxAmount($quote->getShippingAddress()->getBaseTaxAmount())
                                    ->setShippingAmount($quote->getShippingAddress()->getShippingAmount())
                                    ->setBaseShippingAmount($quote->getShippingAddress()->getBaseShippingAmount())
                                    ->setShippingTaxAmount($quote->getShippingAddress()->getShippingTaxAmount())
                                    ->setBaseShippingTaxAmount($quote->getShippingAddress()->getBaseShippingTaxAmount())
                                    ->setDiscountAmount($quote->getShippingAddress()->getDiscountAmount())
                                    ->setBaseDiscountAmount($quote->getShippingAddress()->getBaseDiscountAmount())
                                    ->setGrandTotal($quote->getShippingAddress()->getGrandTotal())
                                    ->setBaseGrandTotal($quote->getShippingAddress()->getBaseGrandTotal())
                                    ->setDiscountDescription($quote->getShippingAddress()->getDiscountDescription())
                                    ->setShippingDiscountAmount($quote->getShippingAddress()->getShippingDiscountAmount())
                                    ->setBaseShippingDiscountAmount($quote->getShippingAddress()->getBaseShippingDiscountAmount())
                                    ->setSubtotalInclTax($quote->getShippingAddress()->getSubtotalInclTax())
                                    ->setBaseSubtotalInclTax($quote->getShippingAddress()->getBaseSubtotalTotalInclTax())
                                    ->setHiddenTaxAmount($quote->getShippingAddress()->getHiddenTaxAmount())
                                    ->setBaseHiddenTaxAmount($quote->getShippingAddress()->getBaseHiddenTaxAmount())
                                    ->setShippingHiddenTaxAmount($quote->getShippingAddress()->getShippingHiddenTaxAmount())
                                    ->setBaseShippingHiddenTaxAmnt($quote->getShippingAddress()->getBaseShippingHiddenTaxAmnt())
                                    ->setShippingInclTax($quote->getShippingAddress()->getShippingInclTax())
                                    ->setBaseShippingInclTax($quote->getShippingAddress()->getBaseShippingInclTax())
                                    ->setShippingMethod($quote->getShippingAddress()->getShippingMethod())
                                    ->setShippingDescription($quote->getShippingAddress()->getShippingDescription());
                        $order->save();
                        $allShipped = false;
                        $allInvoiced = false;
                        $allRefunded = false;
                        foreach($order->getAllVisibleItems() as $_orderItem){
                            
                            if(!in_array($_orderItem->getId(),$deletedOrderItems)){
                                $allShipped = (boolean)($_orderItem->getQtyOrdered() == $_orderItem->getQtyShipped());
                                $allInvoiced = (boolean)($_orderItem->getQtyOrdered() == $_orderItem->getQtyInvoiced());
                                $allRefunded = (boolean)($_orderItem->getQtyOrdered() == $_orderItem->getQtyRefunded());
                            }
                        }
                        if($allShipped && $allInvoiced){
                            $table = $resource->getTableName('sales_flat_order');
                            $connection->query("UPDATE `{$table}` SET `state`='".Mage_Sales_Model_Order::STATE_COMPLETE."', `status`='".Mage_Sales_Model_Order::STATE_COMPLETE."' WHERE `entity_id`='".$order->getId()."'");
                        }
                        if($allRefunded){
                            $table = $resource->getTableName('sales_flat_order');
                            $connection->query("UPDATE `{$table}` SET `state`='".Mage_Sales_Model_Order::STATE_CLOSED."', `status`='".Mage_Sales_Model_Order::STATE_CLOSED."' WHERE `entity_id`='".$order->getId()."'");
                        }
                        
                        Mage::getModel('bornintegration/observer')->updateSyncAttemptOrderEdit($order); // Reset order sync attempt counter
                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('born_orderedit')->__('Order information has been updated successfully.'));
                    }else{
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('born_orderedit')->__('Information was not provided to update the order.'));
                    }
                }
        $this->_redirect('adminhtml/sales_order/view', array('order_id'=>$orderId));
    }
    
    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/edit');
    }
}

