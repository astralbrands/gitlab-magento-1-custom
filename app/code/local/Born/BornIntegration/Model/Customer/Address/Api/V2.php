<?php
class Born_BornIntegration_Model_Customer_Address_Api_V2 extends Mage_Customer_Model_Address_Api_V2
{
    public function sageCreate($incrementId, $addressData){
        $customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('increment_id', array('eq'=>$incrementId))->getFirstItem();
            if (!$customer->getId()) {
            $this->_fault('customer_not_exists');
            }
        $address = Mage::getModel('customer/address');
            foreach ($this->getAllowedAttributes($address) as $attributeCode=>$attribute) {
                if (isset($addressData->$attributeCode)) {
                    if($attributeCode == 'region_id'){
                        if(!is_numeric($addressData->$attributeCode)){
                        $region = Mage::getModel('directory/region')->loadByCode($addressData->$attributeCode, $addressData->country_id);
                            if($region){
                            $address->setData($attributeCode, $region->getId());
                            }else{
                            $address->setData('region',$addressData->$attributeCode);
                            }
                        }
                    }else{
                    $address->setData($attributeCode, $addressData->$attributeCode);
                    }
                }
            }
            if (isset($addressData->is_default_billing)) {
            $address->setIsDefaultBilling($addressData->is_default_billing);
            }
            if (isset($addressData->is_default_shipping)) {
            $address->setIsDefaultShipping($addressData->is_default_shipping);
            }
        $address->setCustomerId($customer->getId());
        $valid = $address->validate();
            if (is_array($valid)) {
            $this->_fault('data_invalid', implode("\n", $valid));
            }
            try {
            $address->save();
            } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            }
        return $address->getId();
    }
    
    public function sageUpdate($incrementId, $sageAddressCode, $addressData){
        $customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('increment_id', array('eq'=>$incrementId))->getFirstItem();
            if (!$customer->getId()) {
            $this->_fault('customer_not_exists');
            }
            $address = Mage::getModel('customer/address')->getCollection()->addFieldToFilter('parent_id', array('eq'=>$customer->getId()))->addFieldToFilter('address_code', array('eq'=>$sageAddressCode))->getFirstItem();
            if (!$address->getId()) {
            $this->_fault('not_exists');
            }
            foreach ($this->getAllowedAttributes($address) as $attributeCode=>$attribute) {
                if (isset($addressData->$attributeCode)) {
                    if($attributeCode == 'region_id'){
                        if(!is_numeric($addressData->$attributeCode)){
                        $region = Mage::getModel('directory/region')->loadByCode($addressData->$attributeCode, $addressData->country_id);
                            if($region){
                            $address->setData($attributeCode, $region->getId());
                            }else{
                            $address->setData('region',$addressData->$attributeCode);
                            }
                        }
                    }else{
                    $address->setData($attributeCode, $addressData->$attributeCode);
                    }
                }
            }
            if (isset($addressData->is_default_billing)) {
            $address->setIsDefaultBilling($addressData->is_default_billing);
            }
            if (isset($addressData->is_default_shipping)) {
            $address->setIsDefaultShipping($addressData->is_default_shipping);
            }
        $valid = $address->validate();
            if (is_array($valid)) {
            $this->_fault('data_invalid', implode("\n", $valid));
            }

            try {
            $address->save();
            } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            }
        return true;
    }
}