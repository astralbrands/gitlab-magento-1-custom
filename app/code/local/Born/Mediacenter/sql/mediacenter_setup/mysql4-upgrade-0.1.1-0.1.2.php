<?php
/**
 * To create table for storing media content
 */
$installer = $this;
$installer->startSetup();
$sql = <<<SQLTEXT
create table mediacenter_subsections_images (
    id int(5) AUTO_INCREMENT,
    name varchar(200),
    file_name varchar(500),
    parent_id int(5),
	position int(4),
	type varchar(10),
	description text,
	media_customer_group varchar(200),
    PRIMARY key(id,parent_id),
	FOREIGN KEY (parent_id) REFERENCES mediacenter_subsections(entity_id)
    );
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 