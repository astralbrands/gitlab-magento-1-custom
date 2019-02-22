<?php
class WeltPixel_Checkout_Block_Cart extends Mage_Checkout_Block_Cart
{
    public function getRemainingAmount() 
    {
      $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();  
      $subtotal = $totals['subtotal']->getValue();
      if(isset($totals['discount']) && $totals['discount']->getValue()) {
        $discount = $totals['discount']->getValue();
        } else {
            $discount = 0;
        }
      $total = $subtotal + $discount;
      $min = Mage::getStoreConfig("carriers/freeshipping/free_shipping_subtotal");
      $val = $min-$total;
      $formattedPrice = Mage::helper('core')->currency($val, true, false);
      if ($val < 0) {
           return $this->__("You've got free shipping - Woo Hoo!");    
      } else {   
            return $this->__('%s away from FREE SHIPPING', $formattedPrice);
      }
    }
}