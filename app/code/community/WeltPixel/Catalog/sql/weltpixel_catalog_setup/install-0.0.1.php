<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$entityType = 'catalog_category';
$group = 'Custom Design';

$code = 'wp_cat_top_desc';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'textarea',
    'type'              => 'text',
    'label'             => 'Extra description top',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'wysiwyg_enabled'   => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'Used by WeltPixel Catalog module',
    'sort_order'        => 200
));

$code = 'wp_cat_bottom_desc';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'textarea',
    'type'              => 'text',
    'label'             => 'Extra description bottom',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'wysiwyg_enabled'   => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'Used by WeltPixel Catalog module',
    'sort_order'        => 210
));

$installer->endSetup();
