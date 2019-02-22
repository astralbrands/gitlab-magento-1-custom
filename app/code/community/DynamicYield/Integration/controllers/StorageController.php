<?php

class DynamicYield_Integration_StorageController extends Mage_Core_Controller_Front_Action {

    /**
     * Add failed events to queue to be executed on the next page load
     *
     * @return string
     */
    public function indexAction() {
        $data = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('data', []));

        $queue = Mage::getModel('dynamicyield_integration/queue');

        $queue->addToQueue($data);

        return true;
    }
}
