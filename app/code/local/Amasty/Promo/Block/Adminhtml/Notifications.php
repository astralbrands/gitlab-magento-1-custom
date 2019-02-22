<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return mixed
     */
    public function getSettingsUrl()
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/catalog_product_attribute/index", array());
    }

    protected function _toHtml()
    {
        if (!Mage::helper('core')->isModuleEnabled('Amasty_Rules')) {
            return parent::_toHtml();
        }

        return '';
    }
}
