<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Born_Package_Model_Carrier_Usps extends Mage_Usa_Model_Shipping_Carrier_Usps {
    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request) {

        parent::proccessAdditionalValidation($request);

        $_useForPoOnly = Mage::getStoreConfig('carriers/usps/use_for_po_boxes_only', Mage::app()->getStore()->getStoreId());
        
        if (!$_useForPoOnly) {
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
            return $this;

        return false;

    }
    
     /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return null
     */
    protected function _updateFreeMethodQuote($request)
    {
        if (!$request->hasFreeMethodWeight()) {
            return;
        }

        $freeMethod = $this->getConfigData($this->_freeMethod);
        if (!$freeMethod) {
            return;
        }
        $freeRateId = false;

        if (is_object($this->_result)) {
            foreach ($this->_result->getAllRates() as $i=>$item) {
                if ($item->getMethod() == $freeMethod) {
                    $freeRateId = $i;
                    break;
                }
            }
        }

        if ($freeRateId === false) {
            return;
        }
        $price = null;
        if ($request->getFreeMethodWeight() > 0) {
            $this->_setFreeMethodRequest($freeMethod);

            $result = $this->_getQuotes();
            if ($result && ($rates = $result->getAllRates()) && count($rates)>0) {
                if ((count($rates) == 1) && ($rates[0] instanceof Mage_Shipping_Model_Rate_Result_Method)) {
                    $price = $rates[0]->getPrice();
                }else{
					$price = 0;
				}
                if (count($rates) > 1) {
                    foreach ($rates as $rate) {
                        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Method
                            && $rate->getMethod() == $freeMethod
                        ) {
                            $price = $rate->getPrice();
                        }elseif($rate instanceof  Mage_Shipping_Model_Rate_Result_Error && $rate->getMethod() == $freeMethod){
							$price = 0;
						}
                    }
                }
            }
        } else {
            /**
             * if we can apply free shipping for all order we should force price
             * to $0.00 for shipping with out sending second request to carrier
             */
            $price = 0;
        }

        /**
         * if we did not get our free shipping method in response we must use its old price
         */
        if (!is_null($price)) {
            $this->_result->getRateById($freeRateId)->setPrice($price);
        }
    }
    
}