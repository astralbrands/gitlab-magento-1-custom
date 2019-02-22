<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$allTablesSql = 'SHOW TABLES';
$allTables = $installer->getConnection()->fetchCol($allTablesSql);

function renameTable($allTables, $inputTable, $outputTable, $installer)
{
    if (!in_array($outputTable, $allTables)) {
        if (in_array($inputTable, $allTables)) {
            $installer->run("
				RENAME TABLE `{$inputTable}` TO `{$outputTable}`
			");
        }
    }
}

$prefix = Mage::getConfig()->getTablePrefix();
$locatorTableLocation = $prefix . "amasty_amlocator_ip_location";
$locatorTableBlock = $prefix . "amasty_amlocator_block";
$geoTableLocation = $prefix . "amasty_geoip_location";
$geoTableBlock = $prefix . "amasty_geoip_block";

renameTable($allTables, $locatorTableLocation, $geoTableLocation, $installer);
renameTable($allTables, $locatorTableBlock, $geoTableBlock, $installer);

$installer->run("

UPDATE `{$installer->getTable('core/config_data')}`
			   SET path = 'amgeoip/import/block'
			   WHERE path = 'amlocator/import/block';

UPDATE `{$installer->getTable('core/config_data')}`
			   SET path = 'amgeoip/import/location'
			   WHERE path = 'amlocator/import/location';

");
$installer->endSetup();
