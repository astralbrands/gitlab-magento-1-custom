<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('storelocator/storelocator')}` ADD `is_elite` char(1) DEFAULT 0;
    ALTER TABLE `{$installer->getTable('storelocator/storelocator')}` ADD `short_description` varchar(150) DEFAULT '';
");
$installer->endSetup();

