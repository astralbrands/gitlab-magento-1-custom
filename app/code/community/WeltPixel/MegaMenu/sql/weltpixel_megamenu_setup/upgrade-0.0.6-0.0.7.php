<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$entityType = 'catalog_category';
$group = 'WeltPixel Menu';

$code = 'wp_display_mode';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'select',
    'type'              => 'int',
    'label'             => 'Display Mode:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'source'            => 'weltpixel_megamenu/category_attribute_source_display_mode',
    'sort_order'        => 120,
    'default'           => WeltPixel_MegaMenu_Model_Category_Attribute_Source_Display_Mode::FULL_WIDTH
));

$installer->endSetup();
