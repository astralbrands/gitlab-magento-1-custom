<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$connection = $installer->getConnection();
/* @var $connection Varien_Db_Adapter_Pdo_Mysql */

$installer->startSetup();
$connection->insert($installer->getTable('cms/block'), array(
    'title'             => 'WelPixel Newsletter',  
    'identifier'        => 'weltpixel_newsletter',
    'content'           => '<p>Sign up for our newsletter</p>{{block type="core/template" name="weltpixel_newsletter_popup" template="weltpixel/newsletter/popup.phtml"}}',
    'creation_time'     => now(),
    'update_time'       => now(),
));
$connection->insert($installer->getTable('cms/block_store'), array(
    'block_id'   => $connection->lastInsertId(),
    'store_id'  => 0
));
$installer->endSetup();