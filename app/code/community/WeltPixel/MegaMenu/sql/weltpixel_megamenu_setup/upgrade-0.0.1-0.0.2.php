<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$entityType = 'catalog_category';
$group = 'WeltPixel Menu';

$code = 'wp_static_blocks';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'select',
    'type'              => 'int',
    'label'             => 'CMS Block',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'Static block content is managed under CMS / Static Blocks',
    'source'            => 'catalog/category_attribute_source_page',
    'sort_order'        => 60
));

$code = 'wp_title_color';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'text',
    'type'              => 'varchar',
    'label'             => 'Title Color:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'This color will be used for titles',
    'sort_order'        => 70
));

$code = 'wp_header_bg_color';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'text',
    'type'              => 'varchar',
    'label'             => 'Header Background Color:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'This color will be used for Header Background',
    'sort_order'        => 80
));

$code = 'wp_content_bg_image';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'image',
    'type'              => 'varchar',
    'label'             => 'Content Background Image:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'This image will be used for Content Background',
    'backend'            => 'catalog/category_attribute_backend_image',
    'sort_order'        => 90
));

$code = 'wp_content_bg_img_dm';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'select',
    'type'              => 'int',
    'label'             => 'Content Background Image Display mode:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'source'            => 'weltpixel_megamenu/category_attribute_source_display',
    'sort_order'        => 100
));

$code = 'wp_content_bg_color';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'text',
    'type'              => 'varchar',
    'label'             => 'Content Background Color:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'This color will be used for Content Background',
    'sort_order'        => 110
));

$installer->endSetup();
