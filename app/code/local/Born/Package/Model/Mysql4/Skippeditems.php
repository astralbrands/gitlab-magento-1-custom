<?php

/**
 * Class Born_Package_Model_Mysql4_Skippeditems
 */
class Born_Package_Model_Mysql4_Skippeditems extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("born_package/skippeditems", "entity_id");
    }
}