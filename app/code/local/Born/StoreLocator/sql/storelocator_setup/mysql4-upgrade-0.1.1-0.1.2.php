<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('storelocator/storelocator')}` ADD `store_code` varchar(25) NOT NULL DEFAULT '';
");
$installer->endSetup();

