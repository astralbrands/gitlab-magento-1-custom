<?php

/**
 * Import the static blocks from the predefined install/blocks.csv file
 */

$moduleDir = Mage::getModuleDir('install', 'WeltPixel_Setup') . DS . 'install' . DS;
$options = array(
    'storeId' => 0,  // default all store views
    'importFile' => $moduleDir . 'blocks.csv'
);
$importModel = Mage::getModel('weltpixel_setup/import_block', $options);
$importModel->import(true);