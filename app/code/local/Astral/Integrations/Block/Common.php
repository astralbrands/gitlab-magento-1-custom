<?php

/**
 * @author Astral Brands
 * @package Astral_Integrations
 */

class Astral_Integrations_Block_Common extends Mage_Core_Block_Template {

    /**
     * Returns currency for current store
     * @return string
     */
    public function getCurrency() {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

}
