<?php 

// ========== Init setup ========== //
require_once( 'app/Mage.php' );
Mage::app();

define('DEFAULT_STORE_ID', Mage_Core_Model_App::DISTRO_STORE_ID);
define('ADMIN_STORE_ID', Mage_Core_Model_App::ADMIN_STORE_ID);

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
define('CATEGORY_ENTITY_TYPE_ID', $setup->getEntityTypeId('catalog_category'));
define('CATEGORY_DEFAULT_ATTRIBUTE_SET_ID', $setup->getAttributeSetId(CATEGORY_ENTITY_TYPE_ID, 'Default'));


//Remove category group, it will no longer be used.
$_categoryGroupCode = 'category_group';
$setup->removeAttribute(CATEGORY_ENTITY_TYPE_ID,$_categoryGroupCode);


// ========== Client specific logic ========== //

//Enterprise 1.11.1.0
$categoryGeneralInfoGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'General Information');
$categoryDisplaySettingsGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'Display Settings');

$attrCode = 'group_by_category_id';
$setup->removeAttribute(CATEGORY_ENTITY_TYPE_ID,$attrCode);
$attrSetting = array(
	'label' => 'Group Products By Categories',
	'comment' => 'All the products will be grouped by the child categories of the category entered here.',
	'type' => 'text',
	'input' => 'text',
	'required'=> 0,
	'user_defined' => 1,
	'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'sort_order' => 1000
	);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, $attrCode);
$setup->endSetup();

?>