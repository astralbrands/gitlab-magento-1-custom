<?php
/* 
 * 
 * Updates the 'sales_flat_quote_shipping_rate' table to hold the custom/extended TGCommerce values
 * 
 * @codepool   community
 * @package    TradeGlobal
 * @module     TradeGlobal TGCommerce
 *
 * @author     Paul Snell 
 * @copyright  Copyright (c) TradeGlobal 2017
 */

/* @var $installer TradeGlobal_TGCommerce_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();


$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_quote_id', 			'varchar(50) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_cogs',       			'decimal(10,2) DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_custom_fee', 			'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_custom_discount', 	'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_custom_string', 		'varchar(80) DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_import_fee', 			'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_import_discount', 	'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_import_string', 		'varchar(80) DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_service_fee', 		'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_service_discount', 	'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_service_string', 		'varchar(80) DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_shipping_fee', 		'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_shipping_discount', 	'decimal(10,2) DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_shipping_string', 	'varchar(80) DEFAULT NULL');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_rate_selected', 		'tinyint(1) default 0');
$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_book_success', 		'tinyint(1) default 0');

$installer->getConnection()->addColumn($this->getTable('sales/quote_address_shipping_rate'), 'ext_delivery_estimate', 	'varchar(80) DEFAULT NULL');

$connection = $installer->getConnection()->addKey(
	$installer->getTable('sales/quote_address_shipping_rate'), 'ext_quote_id', array('ext_quote_id')
);

$connection = $installer->getConnection()->addKey(
	$installer->getTable('sales/quote_address_shipping_rate'), 'ext_rate_selected', array('ext_rate_selected')
);

$installer->endSetup();
