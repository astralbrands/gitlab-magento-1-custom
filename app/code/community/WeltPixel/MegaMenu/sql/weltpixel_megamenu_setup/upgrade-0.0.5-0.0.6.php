<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$entityType = 'catalog_category';
$group = 'WeltPixel Menu';

$code = 'wp_custom_link';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'text',
    'type'              => 'varchar',
    'label'             => 'Custom Link:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => '1. Use \'http://\' to create external link <br/>2. Use \'/\' to create link to home page <br/>3. Use \'#\' to disable link  ',
    'sort_order'        => 115
));

$installer->endSetup();
