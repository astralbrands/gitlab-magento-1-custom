<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */

$check = 1;
try {
    if (class_exists("Mage_ProductAlert_Model_Resource_Stock_Customer_Collection")) {
        $autoloader = Varien_Autoload::instance();

        $autoloader->autoload('Amasty_Xnotif_Model_Resource_Stock_Customer_Collection_Resource');
        $check = 0;
    }
}
catch(Exception $ex){}

if ($check) {
    class Amasty_Xnotif_Model_Resource_Stock_Customer_Collection_Pure extends Mage_ProductAlert_Model_Mysql4_Stock_Customer_Collection {}
}
