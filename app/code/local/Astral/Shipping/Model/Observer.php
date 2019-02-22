<?php
class Astral_Shipping_Model_Observer{
    public function hideShippingMethods( Varien_Event_Observer $observer ){
            if (Mage::getDesign()->getArea() ===   Mage_Core_Model_App_Area::AREA_FRONTEND){
            $quote              = $observer->getEvent()->getQuote();
            $address            = $quote->getShippingAddress();
            $store              = Mage::app()->getStore($quote->getStoreId());
            $carriers           = Mage::getStoreConfig('carriers', $store);
            $hiddenMethodCode   = 'freeshipping';
            $cc = $quote->getCouponCode();
            $sfs = false;
                if(!isset($cc)||empty($cc)) {
                $this->hideFreeShipping($observer);
                }elseif(isset($cc)&&!empty($cc)){
                $oCoupon = Mage::getModel('salesrule/coupon')->load($quote->getCouponCode(), 'code');
                $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
                $sfs = $oRule->getData('simple_free_shipping');
                    if ($sfs == 0) {
                        $this->hideFreeShipping($observer);
                    }elseif($sfs == 1||$sfs == 2){
                        $this->addFreeShipping($observer);
                        $address->setShippingMethod('freeshipping_freeshipping');
                    }
                }
            }
        return;
    }

    public function hideFreeShipping(Varien_Event_Observer $observer){
        $hiddenMethodCode = 'freeshipping';
		$quote              = $observer->getEvent()->getQuote();
		$address            = $quote->getShippingAddress();
		$store              = Mage::app()->getStore($quote->getStoreId());	
		$carriers           = Mage::getStoreConfig('carriers', $store);		
            foreach ($carriers as $carrierCode => $carrierConfig){
                if( $carrierCode ==  $hiddenMethodCode ){
                $store->setConfig("carriers/{$carrierCode}/active", '0');
                }
            }
        return;
    }

    public function addFreeShipping(Varien_Event_Observer $observer){
        $hiddenMethodCode = 'freeshipping';
		$quote              = $observer->getEvent()->getQuote();
		$address            = $quote->getShippingAddress();
		$store              = Mage::app()->getStore($quote->getStoreId());	
		$carriers           = Mage::getStoreConfig('carriers', $store);		
			foreach ($carriers as $carrierCode => $carrierConfig){
				if( $carrierCode ==  $hiddenMethodCode ){
                $store->setConfig("carriers/{$carrierCode}/active", '1');
				}
			}
		return;
    }
}