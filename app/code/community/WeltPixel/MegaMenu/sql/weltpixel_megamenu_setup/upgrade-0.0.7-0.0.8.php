<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$entityType = 'catalog_category';
$group = 'WeltPixel Menu';


$code = 'wp_header_bg_hover_color';
$this->addAttribute($entityType, $code, array(
    'group'             => $group,
    'input'             => 'text',
    'type'              => 'varchar',
    'label'             => 'Header Background Hover Color:',
    'visible'           => true,
    'required'          => false,
    'visible_on_front'  => true,
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'note'              => 'This color will be used for Header Background Hover',
    'sort_order'        => 85
));

$installer->endSetup();
