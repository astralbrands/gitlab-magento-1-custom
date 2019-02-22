<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Location_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected function _construct()
    {
        $this->_blockGroup = 'amlocator';
        $this->_controller = 'adminhtml_location';
    }

    public function getHeaderText()
    {
        $helper = Mage::helper('amlocator');
        $model = Mage::registry('current_location');

        if ($model->getId()) {
            return $helper->__(
                "Edit Location item '%s'", $this->escapeHtml($model->getName())
            );
        } else {
            return $helper->__("Add Location item");
        }
    }

}

