<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/29/13
 * Time   : 11:20 AM
 * File   : SagePaymentsProToken.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Block_Payment_Form_SagePaymentsProToken extends Mage_Payment_Block_Form_Cc
{
    public function getTokenCards()
    {
        return $this->helper('ebizmarts_sagepaymentspro/token')->loadCustomerCards();
    }

    public function canUseToken()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return false;
        }
        $ret = Mage::getModel('ebizmarts_sagepaymentspro/token')->isEnabled();

        $ret = $ret && (Mage::getModel('checkout/type_onepage')->getCheckoutMethod() != 'guest');

        return $ret;
    }
    public function getMaxTokens()
    {
        return Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_TOKEN_MAX);
    }

}