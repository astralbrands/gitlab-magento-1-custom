<?php

/**
 * Import the static pages from the predefined install/pages.csv file
 */

$moduleDir = Mage::getModuleDir('install', 'WeltPixel_Setup') . DS . 'install' . DS;
$options = array(
    'storeId' => 0, // default all store views
    'importFile' => $moduleDir . 'pages.csv'
);
$importModel = Mage::getModel('weltpixel_setup/import_page', $options);
$importModel->import(true);