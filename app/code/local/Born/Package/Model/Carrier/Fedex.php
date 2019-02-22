<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Born_Package_Model_Carrier_Fedex extends Mage_Usa_Model_Shipping_Carrier_Fedex {

    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request) {
        parent::proccessAdditionalValidation($request);

        $_hideForPoBox = Mage::getStoreConfig('carriers/fedex/hide_for_po_boxes_only', Mage::app()->getStore()->getStoreId());
        
        if (!$_hideForPoBox) {
            return $this;
        }

        $address = array();
        $address[] = $request->getDestStreet();
        $address[] = $request->getDestCity();
        $address[] = $request->getDestCountryId();
        $address[] = $request->getDestPostcode();
        $address[] = $request->getDestRegionCode();
        $addressCombined = implode(' ', $address);


        if (Mage::helper('born_package/data')->isPOBoxAddress($addressCombined) || Mage::helper('born_package/data')->isAPOAddress($addressCombined))
            return false;

        return $this;

    }

}
