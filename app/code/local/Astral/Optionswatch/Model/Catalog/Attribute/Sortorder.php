<?php 

class Astral_Optionswatch_Model_Catalog_Attribute_Sortorder extends Varien_Object 
{

    private $_tableName = 'eav_attribute_option';

    private function _getConnection($resource='core/resource',$name='core_write')
    {
        return  Mage::getSingleton($resource)->getConnection($name);
    }

    public function getAttributeByOptionId($optionIds)
    {
            if (!$optionIds)
            {
            return;
            }
        $_fields = 'option_id, sort_order';
        $attributes = $this->_getConnection()->fetchAll("SELECT {$_fields} FROM {$this->_tableName} WHERE option_id in ({$optionIds}) ORDER BY sort_order ASC;");
        return $attributes;
    }
}