<?php
$installer = $this;
$installer->startSetup();
$installer->run("
            ALTER TABLE `{$this->getTable('storelocator/storelocator')}` DROP COLUMN `short_description`;
        ");
$installer->endSetup();
