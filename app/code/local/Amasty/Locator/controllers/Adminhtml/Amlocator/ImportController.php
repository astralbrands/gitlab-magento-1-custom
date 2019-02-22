<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Adminhtml_Amlocator_ImportController extends Mage_Adminhtml_Controller_Action
{
    protected $_locatorFiles = array(
        'location' => 'Amlocator-locations.csv'
    );

    protected $_locatorIgnoredLines = array(
        'location' => 0
    );

    public function startAction()
    {
        $result = array();
        try {
            $type = $this->getRequest()->getParam('type');
            $action = $this->getRequest()->getParam('action');


            /* @var $geoIpModel Amasty_locator_Model_Import */
            $locationModel = Mage::getSingleton('amlocator/import');
            $locationModel->resetDone();
            $filePath = $locationModel->getFilePath($type, $action);
            $ret = $locationModel->startProcess($type, $filePath, $this->_locatorIgnoredLines[$type]);
            $result['position'] = ceil($ret['current_row'] / $ret['rows_count'] * 100);
            $result['status'] = 'started';
            $result['file'] = $this->_locatorFiles[$type];

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function processAction()
    {
        $result = array();
        try {
            $type = $this->getRequest()->getParam('type');
            $action = $this->getRequest()->getParam('action');
            $storeId = $this->getRequest()->getParam('storeid');
            $import = Mage::getSingleton('amlocator/import');
            $filePath = $import->getFilePath($type, $action);
            $ret = Mage::getModel('amlocator/import')->doProcess($type, $filePath, $storeId);
            $result['type'] = $type;
            $result['status'] = 'processing';
            $result['position'] = ceil($ret['current_row'] / $ret['rows_count'] * 100);

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function commitAction()
    {
        $result = array();

        try {
            /* @var $geoIpModel Amasty_Locator_Model_Import */
            $geoIpModel = Mage::getModel('amlocator/import');
            $type = $this->getRequest()->getParam('type');
            $isDownload = Mage::app()->getRequest()->getParam('is_download');
            $geoIpModel->commitProcess($type, $isDownload);
            $result['status'] = 'done';
            $result['full_import_done'] = $geoIpModel->isDone() ? "1" : "0";
            $result['message'] = Mage::helper('amlocator')->__('The stores have been added. Please see admin > cms > store locator');
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            'amlocator/import'
        );
    }

}
