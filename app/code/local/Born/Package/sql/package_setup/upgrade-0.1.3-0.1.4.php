<?php
$installer=new Mage_Sales_Model_Mysql4_Setup;
$installer->startSetup();
$installer->addAttribute('order', 'admin_username', array(
    'type'          => 'varchar',
    'label'         => 'Admin Username',
    'visible'       => true,
	'input'             => 'hidden',
    'required'      => false,
    'visible_on_front' => false,
	'user_defined'  =>  false
));
$installer->addAttribute('quote', 'admin_username', array(
    'type'          => 'varchar',
    'label'         => 'Admin Username',
    'visible'       => true,
	'input'             => 'hidden',
    'required'      => false,
    'visible_on_front' => false,
	'user_defined'  =>  false
));
$installer->endSetup();

