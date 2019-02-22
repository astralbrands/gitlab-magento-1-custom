<?php
/**
 * @author Astral Brands Team
 * @package Astral_Integrations
 */

class Astral_Integrations_Model_Observer {

    public function addToCart($observer) {
        //Pixel
        $pixelId = Mage::helper('astral_integrations_helper')->getFacebookPixelId();
        if(isset($pixelId) && !empty($pixelId)) {
            $_product = $observer->getEvent()->getProduct();
            $productId = $_product->getId();
            $quantity = $_product->getQty() ?: 1;

            //Check if product added to cart is invisible
            if ($_product->getVisibility == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                return;
            }

            $session = Mage::getSingleton("core/session",  array("name"=>"frontend"));

            $addToCart = $session->getData("astral_add_to_cart") ?: array();

            //Iterate over array to see if product has already been added before.
            $hasBeenAdded = false;
            foreach($addToCart as $productRow => $attributes) {
                if ($attributes['product_id'] == $productId) {
                    $addToCart[$productRow]['quantity'] = $attributes['quantity'] + $quantity;
                    $hasBeenAdded = true;
                    break;
                }
            }

            if (!$hasBeenAdded) {
                $addToCart[] = array(
                    'product_id' => $productId,
                    'quantity' => $quantity
                );
            }

            $session->setData("astral_add_to_cart", $addToCart);
        }
    }
}