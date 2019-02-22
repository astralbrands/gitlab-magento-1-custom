<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table sage_error_log(
entity_id int not null auto_increment, 
error_type varchar(255),
error_message text,
order_id varchar(50),
customer_id int(10),
incident_date TIMESTAMP,
primary key(entity_id)
);
		
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
	 