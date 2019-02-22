<?php
$installer = $this;
$installer->startSetup();
$setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$data = array(
    'type' => 'int',
    'input' => 'select',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required' => false,
    'user_defined' => false,
    'unique' =>  false,
    'source' => 'eav/entity_attribute_source_boolean',
    'label' => 'Enable For One Page Order'
);
$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'enable_for_onepageorder', $data);
$setup->addAttributeToGroup(Mage_Catalog_Model_Category::ENTITY, 'Default', 'General Information', 'enable_for_onepageorder');

$setup->removeAttribute(Mage_Catalog_Model_Product::ENTITY, 'product_refinements');
$installer->endSetup();

