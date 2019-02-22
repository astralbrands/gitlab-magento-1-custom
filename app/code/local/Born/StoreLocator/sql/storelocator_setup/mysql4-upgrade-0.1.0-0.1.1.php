<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

$installer = $this;

$installer->startSetup();
$installer->run("

ALTER TABLE `{$installer->getTable('storelocator/storelocator')}`
ADD `sort_order` int(11) DEFAULT NULL

"
);


$installer->endSetup(); 