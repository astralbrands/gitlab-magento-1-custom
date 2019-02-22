<?php

class Born_Package_Helper_Country extends Mage_Core_Helper_Abstract
{

   public function getAllowedCountriesByCustomerGroup($store=null) {
        if (Mage::app()->getStore()->isAdmin()) {
            $params = Mage::app()->getRequest()->getParams();
            if (isset($params['order']['account']['group_id'])) //if group id is selected manually
                $customerGroupId = $params['order']['account']['group_id'];
            elseif ($customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer()) { //get current selected customer
                if ($customer->getData('group_id'))
                    $customerGroupId  = $customer->getData('group_id');
                else
                    return false;
            } else
                return false;
        } elseif(!Mage::getSingleton('customer/session')->isLoggedIn())
            return false;
        else
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        if (!$store)
            $store = Mage::app()->getStore()->getId();
        $groups = Mage::getStoreConfig('born_general/country_setting/customer_groups', $store);
        $groups = explode(',', $groups);
        if (!in_array($customerGroupId, $groups))
            return false;

       $allowCountries = explode(',', (string)Mage::getStoreConfig('born_general/country_setting/additional_allow', $store));

        return $allowCountries;

    }


}