<?php

class Born_Package_Model_Adminhtml_Catalog_Category_Source_Option extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions()
    {
    	$options = array('white', 'black');

        array_unshift($options , '--- please select ---');

        return $options;
    }
}