<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/26/13
 * Time   : 3:55 PM
 * File   : JavascriptVars.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Block_JavascriptVars extends Mage_Core_Block_Template
{
    public function __construct()
    {
        $this->assign('valid', $this->helper('ebizmarts_sagepaymentspro')->F91B2E37D34E5DC4FFC59C324BDC1157C());
    }
}