<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 10/8/13
 * Time   : 2:02 PM
 * File   : Servertype.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_System_Config_Servertype
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Ebizmarts_SagePaymentsPro_Model_Config::SERVER_REDIRECT,
                'label' => Mage::helper('ebizmarts_sagepaymentspro')->__('Redirect to SagePayments')
            ),
            array(
                'value' => Ebizmarts_SagePaymentsPro_Model_Config::SERVER_IFRAME,
                'label' => Mage::helper('ebizmarts_sagepaymentspro')->__('Modal, as a "Lightbox"')
            ),
        );
    }
}