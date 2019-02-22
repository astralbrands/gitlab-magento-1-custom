<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/22/13
 * Time   : 3:56 PM
 * File   : CreditCards.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_System_Config_CreditCards
{
    public function toOptionArray()
    {
        $options =  array();

        foreach (Mage::getSingleton('ebizmarts_sagepaymentspro/config')->getCcTypesSagePayments() as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }
}