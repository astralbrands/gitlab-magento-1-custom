<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
if (Mage::helper('core')->isModuleEnabled('Amasty_Stockstatus')) {
    $autoloader = Varien_Autoload::instance();

    $autoloader->autoload('Amasty_Xnotif_Block_Product_View_Type_Configurable_Stockstatus');
}
elseif (Mage::helper('core')->isModuleEnabled('Amasty_Conf')) {
    $autoloader = Varien_Autoload::instance();

    $autoloader->autoload('Amasty_Xnotif_Block_Product_View_Type_Configurable_Conf');
}
else{
    class Amasty_Xnotif_Block_Product_View_Type_Configurable_Pure extends Mage_Catalog_Block_Product_View_Type_Configurable {}
}
