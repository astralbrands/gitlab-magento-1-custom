<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('order', 'purchase_order_number', array(
    'type' => 'varchar',
    'label' => 'Customer PO#',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->addAttribute('quote', 'purchase_order_number', array(
    'type' => 'varchar',
    'label' => 'Customer PO#',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true
));
$installer->endSetup();

