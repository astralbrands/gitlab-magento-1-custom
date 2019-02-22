<?php 

// ========== Init setup ========== //
require_once( 'app/Mage.php' );
Mage::app();

define('DEFAULT_STORE_ID', Mage_Core_Model_App::DISTRO_STORE_ID);
define('ADMIN_STORE_ID', Mage_Core_Model_App::ADMIN_STORE_ID);

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
define('CATEGORY_ENTITY_TYPE_ID', $setup->getEntityTypeId('catalog_category'));
define('CATEGORY_DEFAULT_ATTRIBUTE_SET_ID', $setup->getAttributeSetId(CATEGORY_ENTITY_TYPE_ID, 'Default'));

// ========== Client specific logic ========== //

//Enterprise 1.11.1.0
$categoryGeneralInfoGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'General Information');
$categoryDisplaySettingsGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'Display Settings');

$attrCode = 'hide_title_navigation_menu';
$setup->removeAttribute(CATEGORY_ENTITY_TYPE_ID,$attrCode);
$attrSetting = array(
	'type'     => 'int',
	'label'    => 'Hide Title on Navigation Menu',
	'input'    => 'select',
	'source'   => 'eav/entity_attribute_source_boolean',
	'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'required' => 0,
	'default'  => 0,
	'user_defined'  => 1,
	'options'     => array ('0'=>'No','1'=>'Yes'),
	'sort_order' => 200
	);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, $attrCode);

$setup->endSetup();

?>