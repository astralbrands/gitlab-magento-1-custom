<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Model_Event_Addpromocode
 */
class DynamicYield_Integration_Model_Event_Addpromocode extends DynamicYield_Integration_Model_Event_Abstract
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    /**
     * @return mixed
     */
    function getName() {
        return 'Promo Code Entered';
    }

    /**
     * @return mixed
     */
    function getType() {
        return 'enter-promo-code-v1';
    }

    /**
     * @return mixed
     */
    function getDefaultProperties() {
        return array('code' => NULL);
    }

    function generateProperties() {
        return array('code' => $this->quote->getCouponCode());
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    public function setQuote(Mage_Sales_Model_Quote $quote) {
        $this->quote = $quote;
    }
}
