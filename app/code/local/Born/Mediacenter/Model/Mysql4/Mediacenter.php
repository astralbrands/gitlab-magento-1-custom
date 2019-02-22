<?php

/**
 * Class Born_Mediacenter_Model_Mysql4_Mediacenter
 */
class Born_Mediacenter_Model_Mysql4_Mediacenter extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("mediacenter/mediacenter", "entity_id");
    }
}