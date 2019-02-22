<?php
class Qualityunit_Pap_Model_PaypalIpn extends AW_Sarp2_Model_Engine_Paypal_Payment_Ipn {

    protected function _registerPaymentCapture() {
        try {
            //Mage::log('Postaffiliatepro: Loading PAP cookie from request');

            $pap = Mage::getModel('pap/pap');
            $visitorID = '';
            if ($this->_request['pap_custom'] != '') {
                $visitorID = $this->_request['pap_custom'];
            }
            
            $order = Mage::getModel('sales/order')->load($this->_request['custom']);

            //Mage::log("Postaffiliatepro: Starting registering sale for cookie '$visitorID'\n");
            //$pap->registerOrder($this->_getOrder(), $visitorID);
            $pap->registerOrder($order, $visitorID);
            //Mage::log('Postaffiliatepro: Sale registered successfully');
        }
        catch (Exception $e) {
            //Mage::log('Postaffiliatepro: An error occurred while registering PayPal sale: '.$e->getMessage());
        }

        parent::_registerPaymentCapture();
    }
}
