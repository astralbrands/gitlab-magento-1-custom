<?php 

// ========== Init setup ========== //
require_once( 'app/Mage.php' );
Mage::app();

define('DEFAULT_STORE_ID', Mage_Core_Model_App::DISTRO_STORE_ID);
define('ADMIN_STORE_ID', Mage_Core_Model_App::ADMIN_STORE_ID);

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->startSetup();

define('CATEGORY_ENTITY_TYPE_ID', $setup->getEntityTypeId('catalog_category'));
define('CATEGORY_DEFAULT_ATTRIBUTE_SET_ID', $setup->getAttributeSetId(CATEGORY_ENTITY_TYPE_ID, 'Default'));

// ========== Client specific logic ========== //

//Enterprise 1.11.1.0
$categoryGeneralInfoGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'General Information');
$categoryDisplaySettingsGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'General Information');

//------------------------------------
$attrCode = 'category_group';
$setup->removeAttribute(CATEGORY_ENTITY_TYPE_ID,$attrCode);
$attrSetting = array(
'type' => 'text',
'label' => 'Category Group',
'input' => 'select',
'source' => 'born_package/adminhtml_catalog_category_source_groupoption',
'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
'required' => 0,
'user_defined' => 1,
'sort_order' => 0
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$setup->endSetup();

?>