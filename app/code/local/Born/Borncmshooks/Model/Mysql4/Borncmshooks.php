<?php

class Born_Borncmshooks_Model_Mysql4_Borncmshooks extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the borncmshooks_id refers to the key field in your database table.
        $this->_init('borncmshooks/borncmshooks', 'hook_id');
    }
}