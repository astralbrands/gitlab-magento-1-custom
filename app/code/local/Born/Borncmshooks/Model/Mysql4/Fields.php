<?php

class Born_Borncmshooks_Model_Mysql4_Fields extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the field_id refers to the key field in your database table.
        $this->_init('borncmshooks/fields', 'field_id');
    }
}