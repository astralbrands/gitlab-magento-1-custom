<?php
$installer = $this;

$installer->startSetup();
$installer->run("
		ALTER TABLE `{$installer->getTable('customer/customer_group')}` DROP INDEX `CUSTOMER_GROUPS_SAGE_CODE_IDX`;
    ");
$installer->endSetup();

?>