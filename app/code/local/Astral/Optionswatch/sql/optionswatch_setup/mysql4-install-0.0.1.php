<?php
$installer = $this;
$installer->startSetup();
 
//Create optionswatch table
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('catalog_product_option_swatch')};
CREATE TABLE {$this->getTable('catalog_product_option_swatch')} (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `option_id` int(10) UNSIGNED NOT NULL,
  `option_value` varchar(64) DEFAULT NULL,
  `option_code` varchar(64) NOT NULL,
  `description` text,
  `image_file` varchar(64) NOT NULL,
  `store_id` smallint(5) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `attribute_id` int(10) DEFAULT NULL,
  `attribute_code` varchar(64) DEFAULT NULL,
  `color_code` varchar(64) DEFAULT NULL,
  `filter_image_file` varchar(64) NOT NULL,
  `cta_link` varchar(255) DEFAULT NULL,
  `product_sku` varchar(64) NOT NULL,
  `sort_order` int(10) NOT NULL,
  `default_option` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_CATALOG_OPTION_SWATCH_OPTION_ID` (`option_id`),
  KEY `FK_CATALOG_OPTION_SWATCH_STORE_ID` (`store_id`),
  KEY `FK_CATALOG_OPTION_SWATCH_SKU_CON` (`product_sku`),
  CONSTRAINT `FK_CATALOG_OPTION_SWATCH_OPTION_ID_CON` FOREIGN KEY (`option_id`) REFERENCES `eav_attribute_option` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_OPTION_SWATCH_SKU_CON` FOREIGN KEY (`product_sku`) REFERENCES `catalog_product_entity` (`sku`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_CATALOG_OPTION_SWATCH_STORE_ID_CON` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='catalog_product_option_swatch';
");