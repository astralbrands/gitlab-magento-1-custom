<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 11/4/13
 * Time   : 12:18 PM
 * File   : mysql4-upgrade-3.0.0-3.0.1.php
 * Module : Ebizmarts_SagePaymentsPro
 */

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('sagepaymentspro_transaction')}` ADD COLUMN `token` varchar(38) ");


$installer->endSetup();