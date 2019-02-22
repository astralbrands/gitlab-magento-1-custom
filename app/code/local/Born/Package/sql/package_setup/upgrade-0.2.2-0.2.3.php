<?php
/**
 *  To create table to save skipped item details in cron job
 */
$installer = $this;
$installer->startSetup();
$sql = <<<SQLTEXT
    create table cron_skipped_items(
    entity_id int not null auto_increment primary key,
    order_increment_id varchar(50),
	child_item_id int(10),
	child_item_sku varchar(64),
	parent_item_id int(10),	
	updated_at TIMESTAMP
    );
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 