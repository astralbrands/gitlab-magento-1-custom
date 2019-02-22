<?php
class Born_BornIntegration_Model_Customer_Api_V2 extends Mage_Customer_Model_Customer_Api_V2
{
    public function sageCreate($customerData){
        $customerData = $this->_prepareData($customerData);
            try {
            $customer = Mage::getModel('customer/customer')
                ->setData($customerData)
                ->save();
            } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
            }
        $array = array();
        $array[0] = $customer->getIncrementId();
        $array[1] = $customer->getId();
        $result = implode("-", $array);
        return $result;
    }
    
    public function sageUpdate($customerIncrementId, $customerData){
        $resource = Mage::getSingleton('core/resource');
        $customerEntityTable = $resource->getTableName('customer/entity');
        $readAdapter = $resource->getConnection('core_read');        
        $query = "SELECT `entity_id` FROM `{$customerEntityTable}` WHERE `increment_id`='".$customerIncrementId."'";        
        $customerId = $readAdapter->fetchOne($query);
            if(!is_numeric($customerId)){
            $this->_fault('not_exists');
            }
        $customerData = $this->_prepareData($customerData);
        $customer = Mage::getModel('customer/customer')->load($customerId);
            if (!$customer->getId()) {
            $this->_fault('not_exists');
            }
            foreach ($this->getAllowedAttributes($customer) as $attributeCode=>$attribute) {
                if (isset($customerData[$attributeCode])) {
                $customer->setData($attributeCode, $customerData[$attributeCode]);
                }
            }
        $customer->save();
        return true;
    }
    
    /**
     * Prepare data to insert/update.
     * Creating array for stdClass Object
     * @param stdClass $data
     * @return array
     */
    protected function _prepareData($data){
        $_data = parent::_prepareData($data);
            if(array_key_exists('group_id', $_data)){
                if(!is_numeric($_data['group_id'])){
                    $resource = Mage::getSingleton('core/resource');
                    $groupTable = $resource->getTableName('customer/customer_group');
                    $readAdapter = $resource->getConnection('core_read');
                    $query = "SELECT `customer_group_id` FROM `{$groupTable}` WHERE `sage_code`='".$_data['group_id']."'";
                    $groupId = $readAdapter->fetchOne($query);
                    if(!is_numeric($groupId)){
                    $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid tier prices. The Sage group code "'.$_data['group_id'].'" is invalid for customer.'));
                    }else{
                    $_data['group_id'] = $groupId;
                    }
                }
            }
        return $_data;
    }
}