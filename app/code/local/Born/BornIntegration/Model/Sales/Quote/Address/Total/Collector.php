<?php
class Born_BornIntegration_Model_Sales_Quote_Address_Total_Collector extends Mage_Sales_Model_Quote_Address_Total_Collector
{
    protected function _initModels()
    {
        $customer = (Mage::app()->getStore()->isAdmin()) ? Mage::getSingleton('adminhtml/session_quote')->getCustomer(): Mage::getSingleton('customer/session')->getCustomer();
        $customerTaxExemptNumber = ($customer) ? (string)$customer->getTaxExemptNumber(): '';
        $orderEditCustomerId = (int)Mage::registry('current_edit_order_customer_id');
        if($orderEditCustomerId > 0){
            $customerTaxExemptNumber = Mage::getModel('customer/customer')->load($orderEditCustomerId)->getTaxExemptNumber();
        }
        $removeTaxCodes = array('tax_shipping','tax','tax_giftwrapping');
        
        $totalsConfig = Mage::getConfig()->getNode($this->_totalsConfigNode);

        foreach ($totalsConfig->children() as $totalCode => $totalConfig) {
            $class = $totalConfig->getClassName();
            if (!empty($class)) {
                if(strlen($customerTaxExemptNumber) > 0 && in_array($totalCode, $removeTaxCodes)){
                   //continue;
                }
                $this->_models[$totalCode] = $this->_initModelInstance($class, $totalCode, $totalConfig);
            }
        }
        return $this;
    }
}

