<?php

class DynamicYield_Integration_Model_Config_Backend_Accounttype extends Mage_Core_Model_Config_Data {

    const EUROPE_ACCOUNT = 'europe_account';
    const CDN_INTEGRATION = 'cdn_integration';


    /**
     * Update Integration Type
     * CDN and Europe Account can not be enabled at the same time
     *
     * @throws Exception
     */
    protected function _afterSave()
    {
        $value = $this->getValue();

        if($value) {
            try {
                if($this->getField() == static::EUROPE_ACCOUNT) {
                    Mage::getConfig()->saveConfig('dev/dyi/cdn_integration', DynamicYield_Integration_Model_Config_Source_Integrationtype::CDN_DISABLED, $this->getScope(), $this->getScopeId());
                } else {
                    Mage::getConfig()->saveConfig('dev/dyi/europe_account', '0', $this->getScope(), $this->getScopeId());
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
}