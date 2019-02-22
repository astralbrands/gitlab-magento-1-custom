<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xcoupon
 */

if (Mage::helper('ambase')->isModuleActive('Amasty_Coupons')) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Amasty_Xcoupon_Model_Salesrule_Mysql4_Rule_Collection_Coupons');
} else {
    class Amasty_Xcoupon_Model_Salesrule_Mysql4_Rule_Collection_Pure
        extends Mage_SalesRule_Model_Mysql4_Rule_Collection {}
}