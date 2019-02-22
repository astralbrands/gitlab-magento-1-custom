<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$entityType = 'catalog_category';
$group = 'WeltPixel Menu';

$code = 'wp_title_image';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'image',
    'type'              => 'varchar',
    'label'             => 'Title Image:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'This image will be displayed above the title',
    'backend'           => 'catalog/category_attribute_backend_image',
    'sort_order'        => 77
));

$installer->endSetup();
