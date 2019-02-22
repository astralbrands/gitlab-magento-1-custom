<?php

class WeltPixel_Setup_Model_Import_Setting extends WeltPixel_Setup_Model_Import {

    public function __construct($options) {
        $this->_entity = 'setting';
        $this->_storeId = $options['storeId'];
        $this->_importFile = $options['importFile'];
        $this->_configObj = Mage::getModel('core/config');
    }

    protected function _importEntityForStore($data, $forceCreation, $storeId) {

        $scope = 'stores';
        if ($storeId == 0) {
            $scope = 'default';
        }

        $this->_configObj->saveConfig($data['path'], $data['value'], $scope, $storeId);
    }
}