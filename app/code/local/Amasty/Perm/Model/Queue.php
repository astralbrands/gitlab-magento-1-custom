<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Model_Queue extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amperm/queue');
    }
}