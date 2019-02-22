<?php
$installer = $this;

$installer->startSetup();
$installer->run("
        ALTER TABLE `{$installer->getTable('customer/customer_group')}` ADD COLUMN `sage_code` varchar(15) NULL;
        ALTER TABLE `{$installer->getTable('customer/customer_group')}` ADD UNIQUE KEY `CUSTOMER_GROUPS_SAGE_CODE_IDX` (`sage_code`);
    ");
$installer->endSetup();

