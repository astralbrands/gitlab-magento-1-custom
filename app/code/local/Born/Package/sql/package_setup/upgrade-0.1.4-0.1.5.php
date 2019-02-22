<?php
$installer = new Mage_Customer_Model_Entity_Setup;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$attrCode = 'account_disable';
$setup->removeAttribute($entityTypeId, $attrCode);
$installer->addAttribute("customer", $attrCode,  array(
    "type"     => "int",
    "backend"  => "",
    "label"    => "Disable Account",
    "input"    => "select",
    "source"   => "eav/entity_attribute_source_boolean",
    "visible"  => true,
    "required" => false,
    "default"  => 0,
    "frontend" => "",
    "unique"   => false,
    "note"     => ""
));
$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", $attrCode);


$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    $attrCode,
    '10'  //sort_order
);

$used_in_forms=array();

//$used_in_forms[]="adminhtml_customer";
//$used_in_forms[]="checkout_register";
//$used_in_forms[]="customer_account_create";
//$used_in_forms[]="customer_account_edit";
$used_in_forms[]="adminhtml_customer";
$attribute->setData("used_in_forms", $used_in_forms)
    ->setData("is_used_for_customer_segment", false)
    ->setData("is_system", 0)
    ->setData("is_user_defined", 1)
    ->setData("is_visible", 1)
    ->setData("sort_order", 10)
;
$attribute->save();

$installer->endSetup();

