<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/22/13
 * Time   : 3:57 PM
 * File   : PaymentAction.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_System_Config_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('ebizmarts_sagepaymentspro')->__('Payment')
            ),
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                'label' => Mage::helper('ebizmarts_sagepaymentspro')->__('Authorize')
            ),
        );
    }
}
