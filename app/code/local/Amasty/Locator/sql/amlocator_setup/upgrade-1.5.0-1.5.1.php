<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */


$this->startSetup();

$categoryTable = $this->getTable('amlocator/table_location_category');
$productTable = $this->getTable('amlocator/table_location_product');
$storeTable = $this->getTable('amlocator/table_location_store');
$locationTable = $this->getTable('amlocator/table_location');
$productEntityTable = $this->getTable('catalog_product_entity');
$categoryEntityTable = $this->getTable('catalog_category_entity');
$coreStoreTable = $this->getTable('core_store');

$this->run("
    ALTER TABLE `{$categoryTable}` ADD CONSTRAINT `FK_AMLOCATOR_CATEGORY_LOCATION_ID_TO_LOCATION_ID` FOREIGN KEY (`location_id`) REFERENCES `{$locationTable}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `{$categoryTable}` MODIFY `category_id` int(10) unsigned;
    ALTER TABLE `{$categoryTable}` ADD CONSTRAINT `FK_AMLOCATOR_CATEGORY_ID_TO_CATEGORY_ENTITY_ID` FOREIGN KEY (`category_id`) REFERENCES `{$categoryEntityTable}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `{$productTable}` ADD CONSTRAINT `FK_AMLOCATOR_PRODUCT_LOCATION_ID_TO_LOCATION_ID` FOREIGN KEY (`location_id`) REFERENCES `{$locationTable}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `{$productTable}` MODIFY `product_id` int(10) unsigned;
    ALTER TABLE `{$productTable}` ADD CONSTRAINT `FK_AMLOCATOR_PRODUCT_ID_TO_PRODUCT_ENTITY_ID` FOREIGN KEY (`product_id`) REFERENCES `{$productEntityTable}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `{$storeTable}` ADD CONSTRAINT `FK_AMLOCATOR_STORE_LOCATION_ID_TO_LOCATION_ID` FOREIGN KEY (`location_id`) REFERENCES `{$locationTable}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ALTER TABLE `{$storeTable}` MODIFY `store_id` smallint(5) unsigned;
    ALTER TABLE `{$storeTable}` ADD CONSTRAINT `FK_AMLOCATOR_STORE_ID_TO_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$coreStoreTable}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->endSetup();
