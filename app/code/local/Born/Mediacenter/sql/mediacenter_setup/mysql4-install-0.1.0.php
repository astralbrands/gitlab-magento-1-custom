<?php
/**
 *  To create table for mediacenter main sections
 */
$installer = $this;
$installer->startSetup();
$sql = <<<SQLTEXT
    create table mediacenter(
    entity_id int not null auto_increment,
    section_name varchar(100),
    is_active tinyint(5),
    subsection_id varchar(200),
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
	 