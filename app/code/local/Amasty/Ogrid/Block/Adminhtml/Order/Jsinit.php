<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


class Amasty_Ogrid_Block_Adminhtml_Order_Jsinit extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('amogrid/js.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
}
