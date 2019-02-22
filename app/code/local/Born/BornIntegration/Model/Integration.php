<?php

abstract class Born_BornIntegration_Model_Integration {
    public $helper;
    private $remote;

    function __construct() {
        $this->helper = Mage::helper('bornintegration/config');
    }

    public function connect() {
        if($this->helper->isEnabled()) {
            if($this->remote) {
                return $this->remote;
            }

            switch($this->helper->getHostType()) {
                case 'sftp': 
                    $remote = new Varien_Io_Sftp();
                    break;
                case 'ftp':
                    $remote = new Varien_Io_Ftp();
                    break;
                default:
                    $this->helper->error('no host type specified.');
                    return null;
            }   

            try {
                $remote->open($this->helper->getConnectionInfo());
            } catch(Exception $e) {
                $this->helper->error($e->getMessage());
            }

            return $this->remote = $remote;
        }
    }

    public function disconnect() {
        if($this->remote) {
            $this->remote->close();
        }
    }

    public function getSagePostCodeResultById($orderId)
    {
        $_fieldName = 'post_code_result';
        $result = $this->getSageValue($orderId,$_fieldName);
        if ($result && $result[$_fieldName]) {
            return $result[$_fieldName];
        }
        return;
    }

    public function getSageTokenByOrderId($orderId)
    {
        $_fieldName = 'token';
        $result = $this->getSageValue($orderId,$_fieldName);

        if ($result && $result[$_fieldName]) {
            return $result[$_fieldName];
        }

        return;
    }

    public function getSageValue($orderId, $fieldName, $tableName = 'sagepaymentspro_transaction')
    {  
        if (!$orderId || !$fieldName) {
            return;
        }

        try {
            $_query = "SELECT order_id,{$fieldName} FROM {$tableName} WHERE order_id='{$orderId}';";
            $_results = $this->_getConnection()->fetchAll($_query);

            if (is_array($_results) && count($_results)) {
                $_results = array_shift($_results);
            }

            return $_results;

        } catch (Exception $e) {
            Mage::logException($e);
            return;
        }

        return;
    }

    private function _getConnection($resource='core/resource',$name='core_write') {
        return  Mage::getSingleton($resource)->getConnection($name);
    }
}