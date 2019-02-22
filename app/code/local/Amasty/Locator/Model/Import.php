<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Model_Import extends Mage_Core_Model_Abstract
{
    protected static $_sessionKey = 'am_locator_import_process_%key%';

    protected $_locatorRequiredFiles = array(
        'location' => 'Amlocator-locations.csv'
    );

    protected $_modelsCols = array(
        'location' => array(
            'name', 'country', 'state' , 'city', 'description', 'zip', 'address',
            'phone' , 'email' , 'website' , 'status',
            'position' , 'lat', 'lng'
        )
    );

    protected $_isCacheEnabled;

    public function getRequiredFiles()
    {
        return $this->_locatorRequiredFiles;
    }

    public function filesAvailable()
    {
        $ret = TRUE;

        $varDir = Mage::getBaseDir('var');
        $dir = $varDir . DS . 'amasty' . DS . 'amlocator';

        foreach ($this->_locatorRequiredFiles as $file) {
            if (!file_exists($dir . DS . $file)) {
                $ret = FALSE;
                break;
            }
        }

        return $ret;
    }

    public function isFileExist($filePath)
    {
        if (file_exists($filePath)) {
            return true;
        }
        return false;
    }

    public function getFilePath($type, $action)
    {
        $varDir = Mage::getBaseDir('var');
        $file = $varDir . DS . 'amasty' . DS . 'amlocator' . DS . $this->_locatorRequiredFiles['location'];
        return $file;
    }


    function startProcess($table, $filePath, $ignoredLines = 0)
    {
        $ret = array();

        $importProcess = array(
            'position'    => 0,
            'tmp_table'   => NULL,
            'rows_count'  => $this->_getRowsCount($filePath) - $ignoredLines,
            'current_row' => 0
        );

        if (($handle = fopen($filePath, "r")) !== false) {
            $tableName = $this->_prepareImport($table);

            while ($ignoredLines > 0 && ($data = fgetcsv($handle, 0, ",")) !== false) {
                $ignoredLines--;
            }

            $importProcess['position'] = ftell($handle);
            $importProcess['tmp_table'] = $tableName;
            $ret = $importProcess;
        }

        $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
        if ($sessionSaveMethod == 'files') {
            Mage::getSingleton('core/session')->setData(self::getSessionKey($table), $importProcess);
        } else {
            $this->_saveInDb($table, $importProcess);
        }

        return $ret;
    }

    function doProcess($table, $filePath, $storeId)
    {
        $ret = array();
        $importProcess = false;
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
            if ($sessionSaveMethod == 'files') {
                $importProcess = Mage::getSingleton('core/session')->getData(self::getSessionKey($table));
            } else {
                $importProcess = $this->_getFromDb($table);
            }

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            if ($importProcess) {
                $tmpTableName = $importProcess['tmp_table'];

                try {
                    $position = $importProcess['position'];

                    fseek($handle, $position);

                    $transactionIterator = 0;

                    $write->beginTransaction();


                    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                        if (!$data[array_search('lat', $this->_modelsCols['location'])]
                            || !$data[array_search('lng', $this->_modelsCols['location'])]) {

                            $address = $this->getAddressFields($data);
                            $location = $this->getCoordinates($address);
                            $data[array_search('lat', $this->_modelsCols['location'])] = $location['lat'];
                            $data[array_search('lng', $this->_modelsCols['location'])] = $location['lng'];
                        }
                        $this->_importItem($table, $tmpTableName, $data, $storeId);

                        $transactionIterator++;

                    }

                    $write->commit();

                    $importProcess['current_row'] += $transactionIterator;

                    $importProcess['position'] = ftell($handle);

                    $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
                    if ($sessionSaveMethod == 'files') {
                        Mage::getSingleton('core/session')->setData(self::getSessionKey($table), $importProcess);
                    } else {
                        $this->_saveInDb($table, $importProcess);
                    }

                    $ret = $importProcess;

                } catch (Exception $e) {
                    $write->rollback();

                    throw new Exception($e->getMessage());
                }
            } else
                throw new Exception('run start before');
        }

        return $ret;
    }

    function commitProcess($table, $isDownload = false)
    {
        $ret = false;
        $importProcess = false;
        $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
        if ($sessionSaveMethod == 'files') {
            $importProcess = Mage::getSingleton('core/session')->getData(self::getSessionKey($table));
        } else {
            $importProcess = $this->_getFromDb($table);
        }
        if ($importProcess) {
            try {

                Mage::app()->getConfig()
                    ->saveConfig('amlocator/import/' . $table, 1)
                    ->reinit();//clean cache

                $this->_doneImport($table);

            } catch (Exception $e) {
                $this->_destroyImport($table);

                throw new Exception($e->getMessage());
            }

            $this->_destroyImport($table);

            $ret = true;
        } else
            throw new Exception('run start before');

        return $ret;
    }

    function isDone()
    {
        return (Mage::getStoreConfig('amlocator/import/location'));
    }

    public function resetDone()
    {
        Mage::getConfig()->saveConfig('amlocator/import/location', 0);
    }

    static function getSessionKey($table)
    {
        return strtr(self::$_sessionKey, array(
            '%key%' => $table
        ));
    }

    protected function _getRowsCount($filePath)
    {
        $linecount = 0;
        $handle = fopen($filePath, "r");
        while (!feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }
        return $linecount;

        $a = sizeof(file($filePath));
        return sizeof(file($filePath));
    }

    protected function _importItem($table, $tmpTableName, &$data, $storeId)
    {

        if (count($data) != 14) {
            return true;
            //throw new Exception('Invalid count');
        }
        
        $data = array_map('trim', $data);

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $query = 'insert into `' . $tmpTableName . '`' .
            '(`' . implode('`, `', $this->_modelsCols[$table]) . '`) VALUES ' .
            '(?)';

        $query = $write->quoteInto($query, $data);

        $write->query($query);

        $id = $write->lastInsertId();

        //insert store dependency
        $storeTableName = Mage::getSingleton('core/resource')
            ->getTableName('amlocator/table_location_store');
        $query = 'insert into `' . $storeTableName . '`' .
            '(`location_id`,`store_id`) VALUES ' .
            '(?)';

        $query = $write->quoteInto($query, array($id, $storeId));

        $write->query($query);

    }

    protected function _prepareImport($table)
    {
        $targetTable = Mage::getSingleton('core/resource')
            ->getTableName('amlocator/table_' . $table);

        return $targetTable;
    }

    protected function _doneImport($table)
    {

    }

    protected function _saveInDb($table, $importProcess)
    {
        if ($this->_isCacheEnabled()) {
            Mage::app()->getCacheInstance()->cleanType('config');
        }
        Mage::getModel('core/config')->saveConfig('amgeoip/import/position' . $table, $importProcess['position']);
        Mage::getModel('core/config')->saveConfig('amgeoip/import/tmp_table' . $table, $importProcess['tmp_table']);
        Mage::getModel('core/config')->saveConfig('amgeoip/import/rows_count' . $table, $importProcess['rows_count']);
        Mage::getModel('core/config')->saveConfig('amgeoip/import/current_row' . $table, $importProcess['current_row']);
    }

    protected function _getFromDb($table)
    {
        if ($this->_isCacheEnabled()) {
            Mage::app()->getCacheInstance()->cleanType('config');
        }
        $importProcess = NULL;
        $importProcess['position'] = Mage::getStoreConfig('amgeoip/import/position' . $table);
        $importProcess['tmp_table'] = Mage::getStoreConfig('amgeoip/import/tmp_table' . $table);
        $importProcess['rows_count'] = Mage::getStoreConfig('amgeoip/import/rows_count' . $table);
        $importProcess['current_row'] = Mage::getStoreConfig('amgeoip/import/current_row' . $table);
        return $importProcess;
    }

    protected function _isCacheEnabled()
    {
        if (empty($this->_isCacheEnabled)) {
            $this->_isCacheEnabled = Mage::app()->useCache('config');
        }

        return $this->_isCacheEnabled;
    }

    protected function _clearDb()
    {
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/position/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/position/block');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/tmp_table/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/tmp_table/block');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/rows_count/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/rows_count/block');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/current_row/location');
        Mage::getModel('core/config')->deleteConfig('amgeoip/import/current_row/block');
    }

    protected function _destroyImport($table)
    {
        $sessionSaveMethod = (string)Mage::getSingleton('core/session')->getSessionSaveMethod();
        if ($sessionSaveMethod == 'files') {
            Mage::getSingleton('core/session')->setData(self::getSessionKey($table), NULL);
        }
    }

    public function getCoordinates($address)
    {
        $address = implode(' ', $address);
        $query = array(
            'sensor' => 'false',
            'address'=> $address,
            'key'    => Mage::getStoreConfig('amlocator/locator/api')
        );

        $url = "https://maps.google.com/maps/api/geocode/json?".http_build_query($query);
        $response = file_get_contents($url);

        $json = json_decode($response, TRUE); //generate array object from the response from the web
        $location = array('lat' => 0, 'lng' => 0);
        if ($json) {
            if ($json['status'] == 'OK' && isset($json['results'][0]['geometry']['location'])) {
                $location = array(
                    'lat' => $json['results'][0]['geometry']['location']['lat'],
                    'lng' => $json['results'][0]['geometry']['location']['lng']
                )  ;
            }
        }
        return $location;
    }

    public function getAddressFields($data)
    {
        $addressData = array();
        $addressCols = array('country', 'state', 'city', 'zip', 'address');
        foreach ($addressCols as $column) {
            $addressData[] = $data[array_search($column, $this->_modelsCols['location'])];
        }

        return $addressData;
    }

}