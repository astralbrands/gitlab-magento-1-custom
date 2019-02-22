<?php
/**
 * @author Astral Brands Team
 * @package Astral_Integrations
 */


class Astral_Integrations_Helper_CartDecorator extends Mage_Core_Helper_Abstract {

    public function getCriteoCartViewEventArray($cart) {
        
        $criteoCart = array();
        if(isset($cart) && !empty($cart)) {

            $criteoCart['event'] = 'viewBasket';
            $criteoCart['item'] = array();
            foreach($cart->getAllVisibleItems() as $item) {
                $row = array();
                $row['id'] = $item->getSku();
                $row['price'] = number_format($item->getPrice(), 2);
                $row['quantity'] = $item->getQty();
                $criteoCart['item'][] = $row;
            }
        }
        
        return $criteoCart;
    }
 
    public function getPixelInitiatCheckoutArray($cart) {

        $pixelCart = array();

        if(isset($cart) && !empty($cart)) {

            $pixelCart['currency'] = 'USD';
            $pixelCart['value'] = $cart->getGrandTotal();
            $pixelCart['num_items'] = 0;
            $pixelCart['content_ids'] = array();
            $pixelCart['contents'] = array();


            foreach($cart->getAllVisibleItems() as $item) {
                $row = array();
                //Load full product obj, (no way around this)
                //TODO: see if better way to do this
                $product = $item->getProduct();
                if( $product->getTypeId() == 
                Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {
                    $skuArray = explode('-', $product->getSku());
                    if (count($skuArray) > 0) {
                        $pixelCart['content_ids'][] = $skuArray[0];
                        $row['id'] = $skuArray[0];
                    } else {
                        $row['id'] = $item->getSku();
                        $pixelCart['content_ids'][] = $item->getSku();
                    }
                } else {
                    $row['id'] = $item->getSku();
                    $pixelCart['content_ids'][] = $item->getSku();
                }
                //Get Contents
                $row['price'] = number_format($item->getPrice(), 2);
                $row['quantity'] = $item->getQty();
                $pixelCart['contents'][] = $row;
                $pixelCart['num_items'] += $item->getQty();
            }
        }
        return $pixelCart;
    }

}
