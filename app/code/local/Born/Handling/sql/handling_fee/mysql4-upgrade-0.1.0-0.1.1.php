<?php

$installer=$this;
$installer->startSetup();
$installer->addAttribute('invoice', 'fee_amount', array(
    'type' => 'varchar',
    'label' => 'Invoice Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->addAttribute('invoice', 'base_fee_amount', array(
    'type' => 'varchar',
    'label' => 'Invoice Type',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
// die('Here');
$installer->endSetup();
