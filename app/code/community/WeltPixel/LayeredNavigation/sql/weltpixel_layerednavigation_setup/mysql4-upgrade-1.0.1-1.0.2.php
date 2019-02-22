<?php

$installer = $this;

$tableOption = $this->getTable('eav_attribute_option');

$installer->startSetup();

$installer->run("
ALTER TABLE `{$tableOption}`
   DROP `product_image`;
");

$installer->endSetup();