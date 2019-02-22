<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/29/13
 * Time   : 5:34 PM
 * File   : EventObserver.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_EventObserver
{
    public function saveConfig(Varien_Event_Observer $o)
    {
        $groups = Mage::app()->getRequest()->getParams();
        $groups = $groups['groups'];
        $sageValues = $groups['sagepaymentspro']['fields'];
        if (isset($sageValues['active']['value'])) {
            $active = $sageValues['active']['value'];
        }
        $website = $o->getEvent()->getWebsite()===null ? Mage::app()->getWebsite(true): $o->getEvent()->getWebsite();
        $store  = $o->getEvent()->getStore()===null ?
            Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId():
            $o->getEvent()->getStore();
        if ((isset($website) && is_object($website) && $website->getIsDefault())) {
            $scope = 'default';
            $store = 0;
        } elseif ($store==1) {
            $scope = 'websites';
            $store = Mage::app()->getWebsite($website)->getId();
        } else {
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $oneStore) {
                if ($oneStore->getCode()==$store) {
                    $store = $oneStore->getId();
                    break;
                }
            }
            $scope = 'stores';
        }
        $this->_saveConfigValues($scope, $store, $sageValues, $active);
    }
    protected function _saveConfigValues($scope, $store, $sageValues, $active)
    {
        if (isset($sageValues['type_integration']['value'])) {
            $integration = $sageValues['type_integration']['value'];
        } else {
            $integration = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_INTEGRATION, $store);
        }
        if (isset($sageValues['active']['inherit'])) {
            $config =  Mage::getModel('core/config');
            $config->deleteConfig(Ebizmarts_SagePaymentsPro_Model_Config::DIRECT_ENABLE, $scope, $store);
            $config->deleteConfig(Ebizmarts_SagePaymentsPro_Model_Config::SERVER_ENABLE, $scope, $store);
            Mage::getConfig()->cleanCache();
        } elseif (!$active) {
            $config =  Mage::getModel('core/config');
            $config->saveConfig(Ebizmarts_SagePaymentsPro_Model_Config::DIRECT_ENABLE, 0, $scope, $store);
            $config->saveConfig(Ebizmarts_SagePaymentsPro_Model_Config::SERVER_ENABLE, 0, $scope, $store);
            Mage::getConfig()->cleanCache();
        }
        if ($integration == Ebizmarts_SagePaymentsPro_Model_Config::INTEGRATION_DIRECT) {
            $config = Mage::getModel('core/config');
            if (isset($sageValues['active']['value'])&&$sageValues['active']['value']) {
                $config->saveConfig(Ebizmarts_SagePaymentsPro_Model_Config::DIRECT_ENABLE, 1, $scope, $store);
                $config->saveConfig(Ebizmarts_SagePaymentsPro_Model_Config::SERVER_ENABLE, 0, $scope, $store);
            }
            $path = 'payment/sagepaymentsprodirect/';
            foreach ($sageValues as $key => $value) {
                if (isset($value['inherit'])) {
                    $config->deleteConfig($path.$key, $scope, $store);
                } elseif (isset($value['value'])) {
                    if (is_array($value['value'])) {
                        $values = implode(',', $value['value']);
                    } else {
                        $values = $value['value'];
                    }
                    $config->saveConfig($path.$key, $values, $scope, $store);
                }
            }
            Mage::getConfig()->cleanCache();
        } elseif ($integration == Ebizmarts_SagePaymentsPro_Model_Config::INTEGRATION_SERVER) {
            $config = Mage::getModel('core/config');
            if (isset($sageValues['active']['value'])&&$sageValues['active']['value']) {
                $config->saveConfig(Ebizmarts_SagePaymentsPro_Model_Config::DIRECT_ENABLE, 0, $scope, $store);
                $config->saveConfig(Ebizmarts_SagePaymentsPro_Model_Config::SERVER_ENABLE, 1, $scope, $store);
            }
            $path = 'payment/sagepaymentsproserver/';
            foreach ($sageValues as $key => $value) {
                if (isset($value['inherit'])) {
                    $config->deleteConfig($path.$key, $scope, $store);
                } elseif (isset($value['value'])) {
                    if (is_array($value['value'])) {
                        $values = implode(',', $value['value']);
                    } else {
                        $values = $value['value'];
                    }
                    $config->saveConfig($path.$key, $values, $scope, $store);
                }
            }
            Mage::getConfig()->cleanCache();
        }

    }
    public function newOrder(Varien_Event_Observer $o)
    {
        $order = $o->getEvent()->getOrder();
        $transaction = Mage::getModel('ebizmarts_sagepaymentspro/transaction')
            ->loadByOrderId($order->getIncrementId(), $order->getStoreId());
        if ($transaction && $transaction->getOrderId()==$order->getIncrementId()) {
            $transaction->setOrderId($order->getEntityId())->save();
        }
    }
}