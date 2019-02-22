<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


class Amasty_Xnotif_Block_Category_Subscribe extends Mage_Core_Block_Template
{
    /**
     * @var Amasty_Xnotif_Helper_Data
     */
    protected $_helper;

    public function __construct() {
        $this->_helper = Mage::helper('amxnotif');
        parent::__construct();
    }

    public function getHtmlClass() {
        return 'alert-stock link-stock-alert';
    }

    public function _toHtml()
    {
        $product = $this->getData('product');
        /* checking product and module settings*/
        if ($product->getData('amxnotif_hide_alert') == '1'
            || !$this->_helper->enableForCustomerGroup('stock')
            || (!Mage::helper('productalert')->isStockAlertAllowed() || !$product || $product->isAvailable())
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getSignupLabel()
    {
        return "Sign up to get notified when this product is back in stock";
    }
}

