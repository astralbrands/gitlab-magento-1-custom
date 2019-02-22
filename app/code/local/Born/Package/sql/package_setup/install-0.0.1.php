<?php
$installer=new Mage_Sales_Model_Mysql4_Setup;
$installer->startSetup();
$installer->addAttribute('order', 'order_entry_type', array(
    'type'          => 'varchar',
    'label'         => 'Order Type',
    'visible'       => true,
	'input'             => 'select',
    'required'      => false,
	'source'            => 'born_package/attribute_source_ordertype',
    'visible_on_front' => true,
	'user_defined'  =>  true
));
$installer->addAttribute('quote', 'order_entry_type', array(
    'type'          => 'varchar',
    'label'         => 'Order Type',
    'visible'       => true,
	'input'             => 'select',
    'required'      => false,
	'source'            => 'born_package/attribute_source_ordertype',
    'visible_on_front' => true,
	'user_defined'  =>  true
));
// die('Here');
$installer->endSetup();