<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$entityType = 'catalog_category';
$group = 'WeltPixel Menu';

$code = 'wp_cat_right_block';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'textarea',
    'type'              => 'text',
    'label'             => 'Right block',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
	'wysiwyg_enabled'   => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'Applies only to top categories',
    'sort_order'        => 10
));

$code = 'wp_cat_top_block';
$this->addAttribute($entityType, $code, array(
    'group'            => $group,
    'input'            => 'textarea',
    'type'             => 'text',
    'label'            => 'Top block',
    'visible'          => true,
    'required'         => false,
    'visible_on_front' => true,
	'wysiwyg_enabled'  => true,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'       => 20
));

$code = 'wp_cat_bottom_block';
$this->addAttribute($entityType, $code, array(
    'group'            => $group,
    'input'            => 'textarea',
    'type'             => 'text',
    'label'            => 'Bottom block',
    'visible'          => true,
    'required'         => false,
    'visible_on_front' => true,
	'wysiwyg_enabled'  => true,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'       => 30
));

$code = 'wp_no_columns';
$this->addAttribute($entityType, $code, array(
    'group'            => $group,
    'input'            => 'text',
    'type'             => 'int',
    'label'            => 'Number of columns in dropdown menu',
    'note'             => 'must differ of 0',
    'default'          => 4,
    'visible'          => true,
    'required'         => false,
    'visible_on_front' => true,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order'       => 40
));

//$code = 'wp_no_items';
//$this->addAttribute($entityType, $code, array(
//    'group'            => $group,
//    'input'            => 'text',
//    'type'             => 'text',
//    'label'            => 'Number of items in columns',
//    'note'             => 'must differ of 0',
//    'default'          => 2,
//    'visible'          => true,
//    'required'         => true,
//    'visible_on_front' => true,
//    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
//    'sort_order'       => 50
//));
//$this->removeAttribute($entityType, $code);

$installer->endSetup();
