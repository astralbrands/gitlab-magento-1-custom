<?php
$installer = new Mage_Sales_Model_Mysql4_Setup;

$installer->startSetup();
$installer->getConnection()->query("UPDATE `{$installer->getTable('eav_attribute')}` SET `is_required`='1', `frontend_class`='validate-select' WHERE `attribute_code`='order_entry_type'");
$installer->endSetup();

