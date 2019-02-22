<?php
class Born_Package_Block_Adminhtml_Sales_Order_Create_Form_Account extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Account
{
    public function getFormValues()
    {
        $data = $this->getCustomer()->getData();
        foreach ($this->getQuote()->getData() as $key => $value) {
            if (strpos($key, 'customer_') === 0) {
                if($key == 'customer_group_id'){
                    if($value  > 1){
                        $data[substr($key, 9)] = $value;
                    }else{
                        $data[substr($key, 9)] = Mage::getStoreConfig('customer/create_account/default_group', $this->getQuote()->getStoreId());
                    }
                }else{
                    $data[substr($key, 9)] = $value;
                }
            }
        }

        if ($this->getQuote()->getCustomerEmail()) {
            $data['email']  = $this->getQuote()->getCustomerEmail();
        }

        return $data;
    }
}

