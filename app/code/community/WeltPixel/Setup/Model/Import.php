<?php

class WeltPixel_Setup_Model_Import extends Varien_Object {

    protected $_entity = null;
    protected $_storeId = null;
    protected $_importFile = null;
    protected $_headerColumns = null;

    public function setImportFile($path) {
        $this->_importFile = $path;
    }

    protected function _importEntity($data, $forceCreation) {

        if (is_array($this->_storeId)) {
            foreach ($this->_storeId as $storeId) {
                $this->_importEntityForStore($data, $forceCreation, $storeId);
            }
        } else {
                $this->_importEntityForStore($data, $forceCreation, $this->_storeId);
        }        
    }
    
    protected function _importEntityForStore($data, $forceCreation, $storeId) {

        $storeLoadId = $storeId;
        if ($storeId == 0) {
            $storeLoadId = 'admin';
        }
        $entity = Mage::getModel($this->_entity)
                ->setStoreId($storeLoadId)
                ->setIgnoreActivationFlag(true)
                ->load($data['identifier']);
        
        if ((null === $entity->getId()) || (!in_array($storeId, $entity->getData('store_id')))) {
            $entity = Mage::getModel($this->_entity);
        }

        if ($forceCreation || $entity->getId() === null) {
            $data['stores'] = array($storeId);
            $entity->addData($data);
            $entity->save();
        }
    }

    /**
     * @$forceCreation true overwrites existing entities with the new values
     */
    public function import($forceCreation = false) {
        if (is_null($this->_entity)) {
            throw Mage::exception('Please specify a valid entity.');
        }

        if (!file_exists($this->_importFile)) {
            throw Mage::exception('Please specify a valid csv file.');
        }
        
         if (is_null($this->_storeId)) {
            throw Mage::exception('Please specify a valid store.');
        }

        $io = new Varien_Io_File();
        $io->streamOpen($this->_importFile, 'r');
        $io->streamLock(true);
        $firstLine = true;

        while (false !== ($line = $io->streamReadCsv())) {
            if ($firstLine) {
                $firstLine = false;
                $this->_headerColumns = $line;
                continue;
            }

            $data = array();
            foreach ($this->_headerColumns as $key => $val) {
                $data[$val] = $line[$key];
            }

            $this->_importEntity($data, $forceCreation);
        }
    }
    
}