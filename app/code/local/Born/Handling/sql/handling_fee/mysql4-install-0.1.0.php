<?php

$installer=$this;
$installer->startSetup();
$installer->addAttribute('order', 'fee_amount', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->addAttribute('order', 'base_fee_amount', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->addAttribute('quote', 'fee_amount', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->addAttribute('quote', 'base_fee_amount', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));


$installer->addAttribute('order', 'fee_amount_invoiced', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->addAttribute('order', 'base_fee_amount_invoiced', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));


$installer->addAttribute('order', 'fee_amount_refunded', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->addAttribute('order', 'base_fee_amount_refunded', array(
    'type' => 'varchar',
    'label' => 'Order Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));

// die('Here');
$installer->endSetup();
