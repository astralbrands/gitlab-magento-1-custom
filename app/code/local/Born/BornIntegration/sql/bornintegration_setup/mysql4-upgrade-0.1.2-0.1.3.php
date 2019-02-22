<?php

/* Create new attribute to manage order X3 sync try count */
$installer = $this;
$installer->startSetup();
$installer->addAttribute('order', 'sync_attempt', array(
    'type' => 'int',
    'label' => 'X3 Sync Attempt',
    'visible' => true,
    'input' => 'text',
    'required' => false,
    'user_defined' => true,
    'default' => 0
));
$installer->endSetup();

