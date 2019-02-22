<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getVisibilities()
    {
        return array(
            '0' => $this->__('Disabled'),
            '1' => $this->__('Enabled'),
        );
    }

    public function getUrl()
    {
        return Mage::getUrl(
            Mage::getStoreConfig('amlocator/locator/url'),
            array('_secure' => Mage::app()->getStore()->isCurrentlySecure())
        );
    }

    public function getApiKey()
    {
        return "&key=" . Mage::getStoreConfig('amlocator/locator/api');
    }
}
