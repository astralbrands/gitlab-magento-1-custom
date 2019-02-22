<?php

/**
 * Product:       Xtento_GridActions (1.8.5)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2013-09-13T15:45:14+02:00
 * File:          app/code/local/Xtento/GridActions/Helper/Data.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Helper_Data extends Mage_Core_Helper_Abstract
{
    const EDITION = '%!version!%';
    protected $carriers = array();

    public function getModuleEnabled()
    {
        if (!Mage::getStoreConfigFlag(Xtento_GridActions_Model_Observer::MODULE_ENABLED)) {
            return 0;
        }
        $moduleEnabled = Mage::getModel('core/config_data')->load('gridactions/general/' . str_rot13('frevny'), 'path')->getValue();
        if (empty($moduleEnabled) || !$moduleEnabled || (0x28 !== strlen(trim($moduleEnabled)))) {
            return 0;
        }
        if (!Mage::registry('moduleString')) {
            Mage::register('moduleString', 'false');
        }
        return true;
    }

    public function determineCarrierTitle($carrierCode, $shippingMethod = '')
    {
        if ($carrierCode == 'custom') {
            return (empty($shippingMethod)) ? $this->__('Custom') : $shippingMethod;
        } else {
            if (!isset($this->carriers[$carrierCode])) {
                $this->carriers[$carrierCode] = Mage::getStoreConfig('carriers/' . $carrierCode . '/title');
                if (empty($this->carriers[$carrierCode])) {
                    $this->carriers[$carrierCode] = Mage::getStoreConfig('customtrackers/' . $carrierCode . '/title');
                }
            }
            return $this->carriers[$carrierCode];
        }
    }
}