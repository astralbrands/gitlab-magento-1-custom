<?php
$installer = $this;
$setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->startSetup();
$installer->run("
        CREATE  TABLE IF NOT EXISTS `{$installer->getTable('born_onepageorder/navigation')}` (
        `navigation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `is_active` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
        `is_refinements` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
        `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
        `product_ids` text NOT NULL,
        PRIMARY KEY (`navigation_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='One Page Order Form  Navigation';
    
    INSERT INTO `{$installer->getTable('born_onepageorder/navigation')}` (`title`, `is_active`, `is_refinements`, `sort_order`, `product_ids`) VALUES('Promotions', 1, 0, 1, ''),('Skin Care', 1, 1, 2, ''),('Professional Peels / Masks', 1, 0, 3, ''),('Training', 1, 0, 4, ''),('Business Building', 1, 0, 5, '');
");
        
$data = array(
    'type' => 'int',
    'input' => 'select',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required' => false,
    'user_defined' => false,
    'searchable' => false,
    'filterable' =>  false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' =>  false,
    'used_in_product_listing' => false,
    'source' => 'born_onepageorder/source_refinements',
    'label' => 'Product Refinements'
);
$setup->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'product_refinements', $data);
$setup->addAttributeToGroup(Mage_Catalog_Model_Product::ENTITY, 12, 'General', 'product_refinements');

$installer->endSetup();

