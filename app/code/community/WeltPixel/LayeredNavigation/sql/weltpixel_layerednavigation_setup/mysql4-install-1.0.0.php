<?php

$installer = $this;

$tableOption = $this->getTable('eav_attribute_option');

$installer->startSetup();

/**
 * Image used in layered navigation
 */
$installer->run("
ALTER TABLE `{$tableOption}`
    ADD `navigation_image` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
");

/**
 * Image used in product detail page
 */
$installer->run("
ALTER TABLE `{$tableOption}`
    ADD `product_image` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
");

$installer->endSetup();
