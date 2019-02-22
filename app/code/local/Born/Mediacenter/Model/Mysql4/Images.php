<?php

/**
 * Class Born_Mediacenter_Model_Mysql4_Images
 */
class Born_Mediacenter_Model_Mysql4_Images extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("mediacenter/images", "id");
    }
}