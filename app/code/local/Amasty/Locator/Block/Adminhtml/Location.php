<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Location
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        parent::_construct();
        $helper = Mage::helper('amlocator');
        $this->_blockGroup = 'amlocator';
        $this->_controller = 'adminhtml_location';
        $this->_headerText = $helper->__('Location Management');
        $this->_addButtonLabel = $helper->__('Add Location');
    }


}