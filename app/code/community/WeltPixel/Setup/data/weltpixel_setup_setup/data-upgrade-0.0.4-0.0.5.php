<?php

/**
 * Magento 1.9.2.2 & eneterprise 1.14.2.2 security patch table whitelisting
 */

$installer = $this;

$permissionBlockTable = false;
try {
    $permissionBlockTable = $installer->getTable('admin/permission_block');
	if ($permissionBlockTable) {
		$installer->getConnection()->insertMultiple(
			$permissionBlockTable,
			array(
				array('block_name' => 'newsletter/subscribe', 'is_allowed' => 1),
				array('block_name' => 'weltpixel/product_list', 'is_allowed' => 1),
				array('block_name' => 'weltpixel/product_new', 'is_allowed' => 1)
			)
		);
	}
} catch (Exception $ex) {
    Mage::log($ex->getMessage());
}


$installer->endSetup();
