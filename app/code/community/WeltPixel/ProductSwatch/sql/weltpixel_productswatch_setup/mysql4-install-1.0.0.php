<?php

$installer = $this;

$tableOption = $this->getTable('eav_attribute_option');

$installer->startSetup();

/**
 * Image used on product detail page
 */
$installer->run("
ALTER TABLE `{$tableOption}`
    ADD `product_image` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
");

$installer->endSetup();
