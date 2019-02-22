<?php

/* @var $this Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$cmsBlock = Mage::getModel('cms/block');
$cmsBlock->setData(array(
    'title'      => 'WeltPixel Mobile Block',
    'identifier' => 'weltpixel_mm_custom_mobile_block',
    'stores'     => array(0),
    'content'    => '<p>Lorem ipsum dolor sit amet</p>',
))->save();

$installer->endSetup();
