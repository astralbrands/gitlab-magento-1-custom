<?php

/**
 * Class Born_Mediacenter_Model_Mysql4_Subsections
 */
class Born_Mediacenter_Model_Mysql4_Subsections extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("mediacenter/subsections", "entity_id");
    }
}