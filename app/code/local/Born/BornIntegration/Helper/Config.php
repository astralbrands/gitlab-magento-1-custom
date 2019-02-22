<?php

class Born_BornIntegration_Helper_Config extends Born_BornIntegration_Helper_Data {    
    const CONFIG_ENABLE = 'bornintegration/connection/enable';
    const CONFIG_HOST_TYPE = 'bornintegration/connection/type';
    const CONFIG_HOST = 'bornintegration/connection/host';
    const CONFIG_USERNAME = 'bornintegration/connection/username';
    const CONFIG_PASSWORD = 'bornintegration/connection/password';
    const CONFIG_RELATIVEPATH = 'bornintegration/connection/relative_path';
    const CONFIG_PROCCESSED_DIRECTORY = 'bornintegration/connection/processed';
    const CONFIG_SAGE_CODES = 'bornintegration/sage/sage_codes';
    const CONFIG_SAGE_STATUS_MESSAGE = 'bornintegration/sage/x3_status_message';

    const EXPORTED_PROCESSING = 'exported';

    public function isEnabled() {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Helper_Config_'.date('Ymd').'.log');
        return Mage::getStoreConfig(self::CONFIG_ENABLE, Mage::app()->getStore());
    }

    public function getHostType() {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Helper_Config_'.date('Ymd').'.log');
        return Mage::getStoreConfig(self::CONFIG_HOST_TYPE, Mage::app()->getStore());
    }

    public function getSageCodes() {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Helper_Config_'.date('Ymd').'.log');
        $codes = unserialize(Mage::getStoreConfig(self::CONFIG_SAGE_CODES, Mage::app()->getStore()));
        $data = array();
        foreach($codes as $code) {
            $data[$code['store_code']] = array(
                'company_code' => $code['company_code'],
                'order_type' => $code['order_type']
            );
        }
        return $data;
    }
    

    public function getConnectionInfo() {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Helper_Config_'.date('Ymd').'.log');
        switch($this->getHostType()) {
            case 'sftp': 
                return array(
                    'host'      => Mage::getStoreConfig(self::CONFIG_HOST, Mage::app()->getStore()),
                    'username'  => Mage::getStoreConfig(self::CONFIG_USERNAME, Mage::app()->getStore()),
                    'password'  => Mage::getStoreConfig(self::CONFIG_PASSWORD, Mage::app()->getStore()),
                    'timeout'   => '10'
                ); break;
            case 'ftp':
                return array(
                    'host'      => Mage::getStoreConfig(self::CONFIG_HOST, Mage::app()->getStore()),
                    'user'  => Mage::getStoreConfig(self::CONFIG_USERNAME, Mage::app()->getStore()),
                    'password'  => Mage::getStoreConfig(self::CONFIG_PASSWORD, Mage::app()->getStore()),
                    'timeout'   => '10'
                );
            default:
                $this->error('no host type specified.');
                return null;
        }
    }
}