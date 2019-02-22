<?php
class Born_Sagelog_Model_Mysql4_Logging extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("sagelog/logging", "entity_id");
    }
}