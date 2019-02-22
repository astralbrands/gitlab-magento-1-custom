<?php
$installer = new Mage_Customer_Model_Entity_Setup;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute("customer", "credit_terms",  array(
    "type"     => "varchar",
    "backend"  => "",
    "label"    => "Enable Credit Terms",
    "input"    => "select",
    "source"   => "eav/entity_attribute_source_boolean",
    "visible"  => true,
    "required" => false,
    "default"  => "",
    "frontend" => "",
    "unique"   => false,
    "note"     => "Enable Credit Terms"
 ));
$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "credit_terms");


$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'credit_terms',
    '999'  //sort_order
);


$used_in_forms[]="adminhtml_customer";
$attribute->setData("used_in_forms", $used_in_forms)
->setData("is_used_for_customer_segment", false)
->setData("is_system", 0)
->setData("is_user_defined", 1)
->setData("is_visible", 1)
->setData("sort_order", 100);
$attribute->save();


$installer->endSetup();