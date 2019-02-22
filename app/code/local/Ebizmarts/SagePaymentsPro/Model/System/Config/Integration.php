<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/23/13
 * Time   : 2:54 PM
 * File   : Integration.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_System_Config_Integration
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Ebizmarts_SagePaymentsPro_Model_Config::INTEGRATION_DIRECT,
                'label' => Mage::helper('ebizmarts_sagepaymentspro')->__('Direct')
            ),
            array(
                'value' => Ebizmarts_SagePaymentsPro_Model_Config::INTEGRATION_SERVER,
                'label' => Mage::helper('ebizmarts_sagepaymentspro')->__('Server')
            ),
        );
    }
}
