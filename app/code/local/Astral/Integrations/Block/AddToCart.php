<?php

/**
 * @author Astral Brands
 * @package Astral_Integrations
 */

class Astral_Integrations_Block_AddToCart extends Astral_Integrations_Block_Common
{

    /**
     * Returns Product IDs which were added to the cart
     * @return array Array of product ids
     */
    public function getAddToCartIds () {
        $addToCartProductsIds = $this->getSessionAddToCart();
        $this->clearAddToCartEvent();
        return $addToCartProductsIds;
    }

    /**
     * Returns whether or not add to cart event should fire based on if add to cart
     * session variable is defined.
     * @return boolean Should event fire
     */
    public function shouldAddToCartEventFire() {
        $addToCart = $this->getSessionAddToCart();
        return !empty($addToCart) && count($addToCart) > 0;
    }

    /**
     * Gets add to cart session variable
     * @return Array Array of product ids stored for event
     */
    private function getSessionAddToCart() {
        $session = Mage::getSingleton("core/session", array("name" => "frontend"));
        $addToCart =  $session->getData('astral_add_to_cart') ?: array();
        return $addToCart;
    }

    /**
     * Clears the add to cart event variable
     * @return void
     */
    private function clearAddToCartEvent()
    {
        $session = Mage::getSingleton("core/session", array("name" => "frontend"));
        $session->setData('astral_add_to_cart', array());
    }
}
