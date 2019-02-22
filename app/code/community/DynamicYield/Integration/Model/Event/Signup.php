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
 * Class DynamicYield_Integration_Model_Event_Signup
 */
class DynamicYield_Integration_Model_Event_Signup extends DynamicYield_Integration_Model_Event_Abstract
{
    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $customer;

    /**
     * @return mixed
     */
    function getName() {
        return 'Signup';
    }

    /**
     * @return mixed
     */
    function getType() {
        return 'signup-v1';
    }

    /**
     * @return mixed
     */
    function getDefaultProperties() {
        return array('hashedEmail' => NULL);
    }

    /**
     * @return array
     */
    function generateProperties() {
        return array('hashedEmail' => hash('sha256', $this->customer->getEmail()));
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer) {
        $this->customer = $customer;
    }
}
