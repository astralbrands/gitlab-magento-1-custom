<?php

class Born_Borncmshooks_Model_Forms extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('borncmshooks/forms');
    }
}