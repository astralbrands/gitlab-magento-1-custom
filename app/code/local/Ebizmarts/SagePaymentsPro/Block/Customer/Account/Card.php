<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/26/13
 * Time   : 1:13 AM
 * File   : Card.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Block_Customer_Account_Card extends Mage_Core_Block_Template
{
    protected $_cards = null;

    public function getCustomerCards()
    {
        if ($this->_cards===null) {

            $this->_cards = $this->helper('ebizmarts_sagepaymentspro/token')->loadCustomerCards();

        }

        return $this->_cards;
    }
}