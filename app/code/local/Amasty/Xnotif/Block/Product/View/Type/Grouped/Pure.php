<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


if (Mage::helper('core')->isModuleEnabled('Amasty_Stockstatus')) {
    $autoloader = Varien_Autoload::instance();

    $autoloader->autoload('Amasty_Xnotif_Block_Product_View_Type_Grouped_Stockstatus');
}
else {
    class Amasty_Xnotif_Block_Product_View_Type_Grouped_Pure extends Mage_Catalog_Block_Product_View_Type_Grouped {}
}
