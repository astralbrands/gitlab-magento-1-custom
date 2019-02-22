<?php

class Astral_Optionswatch_Model_Adminhtml_Catalog_Category_Source_Groupoption extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions()
    {
        $_helper = Mage::helper('astral_optionswatch/catalog_category_data');
        $_configs = $_helper->getCategoryGroups();
        $options = array();
            foreach ($_configs as $_config) {
            $_valueKey = 'group_code';
            $options[] = $_config[$_valueKey];
            }
        array_unshift($options , '--- select group ---');
        return $options;
    }
}