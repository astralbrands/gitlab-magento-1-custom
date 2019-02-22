<?php

class WeltPixel_Setup_Model_Export extends Varien_Object {

    protected $_items = null;
    protected $_requiredColumns = null;
    protected $_entity = null;

    public function setItems($collection) {
        $this->_items = $collection;
    }

    public function getItems() {
        return $this->_items;
    }

    protected function _getExportData($data) {
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->_requiredColumns)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    protected function _getCsvHeaders($items) {
        $item = current($items);
        $headers = array_intersect(array_keys($item->getData()), $this->_requiredColumns);

        return $headers;
    }

    public function export() {
        if (!is_null($this->_items)) {
            $items = $this->_items->getItems();
            if (count($items) > 0) {
                $io = new Varien_Io_File();
                $path = Mage::getBaseDir('var') . DS . 'export'  . DS .  $this->_entity;
                $name = md5(microtime());
                $file = $path . DS . $name . '.csv';
                $io->setAllowCreateFolders(true);
                $io->open(array('path' => $path));
                $io->streamOpen($file, 'w+');
                $io->streamLock(true);

                $io->streamWriteCsv($this->_getCsvHeaders($items));
                foreach ($items as $item) {
                    $io->streamWriteCsv($this->_getExportData($item->getData()));
                }

                return array(
                    'type' => 'filename',
                    'value' => $file,
                    'rm' => true // remove or keep the exported file on filesystem as well
                );
            }
        }
    }

}