<?php
class Born_Shipping_Model_Carrier_Handcarry extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'employee_shipping';
    protected $_isFixed = true;
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active') || !$this->_checkCustomerGroups()) {
            return false;
        }
        
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeBoxes += $item->getQty() * $child->getQty();
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        $this->setFreeBoxes($freeBoxes);

        $result = Mage::getModel('shipping/rate_result');
        $shippingPrice = $this->getConfigData('price');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);
        $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('employee_shipping');
            $method->setCarrierTitle($this->getConfigData('free_shipping_name'));

            $method->setMethod('free');
            $method->setMethodTitle($this->getConfigData('free_shipping_name'));

            $method->setPrice(0);
            $method->setCost(0);

            $result->append($method);
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('employee_shipping');
            $method->setCarrierTitle('');

            $method->setMethod('paid');
            $method->setMethodTitle(implode(' - ', array($this->getConfigData('paid_shipping_carrier'), $this->getConfigData('paid_shipping_method'))));

            if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
            }


            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $result->append($method);
            return $result;
    }
    
    public function getAllowedMethods()
    {
        return array('employee_shipping_free'=>$this->getConfigData('free_shipping_name'), 'employee_shipping_paid'=> implode(' - ', array($this->getConfigData('paid_shipping_carrier'), $this->getConfigData('paid_shipping_method'))));
    }
    
    protected function _checkCustomerGroups()
    {
        $allowedGroups = $this->getConfigData('customer_groups');
        $allowedGroups = (strlen($allowedGroups) > 0) ? explode(',',$allowedGroups): array();
        if(Mage::app()->getStore()->isAdmin()){
            $customerGroupId = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getCustomerGroupId();
        }elseif(Mage::getSingleton('checkout/session')->getQuote()){
            $customerGroupId = Mage::getSingleton('checkout/session')->getQuote()->getCustomerGroupId();
        }else{
            $customerGroupId = null;
        }
        if(count($allowedGroups) <= 0){
            return false;
        }elseif(is_null($customerGroupId)){
            return false;
        }
        return (boolean)in_array($customerGroupId, $allowedGroups);
    }
}
