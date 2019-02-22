<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/22/13
 * Time   : 2:15 PM
 * File   : ${FILE_NAME}
 * Module : ${PROJECT_NAME}
 */ 
$installer = $this;

$installer->startSetup();

$statusTable        = $installer->getTable('sales/order_status');
$statusStateTable   = $installer->getTable('sales/order_status_state');
$statusLabelTable   = $installer->getTable('sales/order_status_label');

$configfile = Mage::getModuleDir('etc', 'Ebizmarts_SagePaymentsPro').DS.'config.xml';
$fileconfig = Mage::getModel('core/config_base');
$fileconfig->loadFile($configfile);
$statuses = $fileconfig->getNode('global/sales/order/statuses')->asArray();

$data = array();
foreach ($statuses as $code => $info) {
    $data[] = array(
        'status'    => $code,
        'label'     => $info['label']
    );
}
$installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);


$configfile = Mage::getModuleDir('etc', 'Ebizmarts_SagePaymentsPro').DS.'config.xml';
$fileconfig = Mage::getModel('core/config_base');
$fileconfig->loadFile($configfile);
$states = $fileconfig->getNode('global/sales/order/states')->asArray();

$data = array();
foreach ($states as $code => $info) {
    if (isset($info['statuses'])) {
        foreach ($info['statuses'] as $status => $statusInfo) {
            $data[] = array(
                'status'    => $status,
                'state'     => $code,
                'is_default'=> is_array($statusInfo) && isset($statusInfo['@']['default']) ? 1 : 0
            );
        }
    }
}

$installer->getConnection()->insertArray(
    $statusStateTable,
    array('status', 'state', 'is_default'),
    $data
);



$installer->run(
    "

	CREATE TABLE IF NOT EXISTS `{$this->getTable('sagepaymentspro_tokencard')}` (
	  `id` int(10) unsigned NOT NULL auto_increment,
      `customer_id` int(10) unsigned NOT NULL,
	  `token` varchar(38),
	  `status` varchar(15),
	  `card_type` varchar(255),
	  `last_four` varchar(4),
	  `expiry_date` varchar(4),
	  `status_detail` varchar(255),
      `vendor` varchar(255),
      `protocol` enum('server', 'direct'),
      `is_default` tinyint(1) unsigned NOT NULL default '0',
      `visitor_session_id` varchar(255),
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `{$this->getTable('sagepaymentspro_transaction')}` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
      `order_id` INT(11) UNSIGNED NULL,
      `store_id` SMALLINT(5),
      `response_status` VARCHAR(255) NOT NULL,
      `post_code_result` VARCHAR(255) NOT NULL,
      `response_status_detail` VARCHAR(255) NOT NULL,
      `cvv_indicator` VARCHAR(255) NOT NULL,
      `risk_indicator` VARCHAR(255) NOT NULL,
      `reference` VARCHAR(255) NOT NULL,
      `transaction_id`  VARCHAR(255) NOT NULL,
      `amount` DECIMAL(12,4),
      `type` enum('capture','authorize','refund','void','release'),
      `transaction_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`) )
    ENGINE = InnoDB DEFAULT CHARSET=utf8;
"
);




$installer->endSetup();