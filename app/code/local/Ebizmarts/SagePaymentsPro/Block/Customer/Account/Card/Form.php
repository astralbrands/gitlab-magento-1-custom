<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/26/13
 * Time   : 1:14 AM
 * File   : Form.php
 * Module : Ebizmarts_SagePaymentsPro
 */

class Ebizmarts_SagePaymentsPro_Block_Customer_Account_Card_Form extends Mage_Core_Block_Template
{
    public function _construct() 
    {
        parent::_construct();
        $this->setTemplate('ebizmarts/sagepaymentspro/customer/card/form.phtml');
    }

    public function getCcAvailableTypes() 
    {
        $types = Mage::getModel('ebizmarts_sagepaymentspro/config')->getCcTypesSagePayments();

        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths() 
    {
        $months = array();
        $months[0] = $this->__('Month');
        $months = array_merge($months, Mage::getModel('payment/config')->getMonths());
        return $months;
    }

    public function getStartCcYears() 
    {
        return Mage::getBlockSingleton('payment/form_cc')->getSsStartYears();
    }

    public function getCcYears() 
    {
        $years = Mage::getModel('ebizmarts_sagepaymentspro/config')->getYears();
        $years = array(0 => $this->helper('ebizmarts_sagepaymentspro')->__('Year')) + $years;

        return $years;
    }
}