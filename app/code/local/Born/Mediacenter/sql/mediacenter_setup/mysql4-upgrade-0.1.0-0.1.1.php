<?php
/**
 *  To create table for mediacenter subsections
 */
$installer = $this;
$installer->startSetup();
$sql = <<<SQLTEXT
    create table mediacenter_subsections(
    entity_id int not null auto_increment,
    subsection_name varchar(100),
    media_id varchar(200),
    is_active tinyint(5),
    created_at timestamp,
    customer_group varchar(200),
    primary key(entity_id)

);

		
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 