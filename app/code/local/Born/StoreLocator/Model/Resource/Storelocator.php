<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Model_Resource_Storelocator 
	extends Mage_Core_Model_Resource_Db_Abstract {
	protected $_dataCleaned = false;
	protected function _construct()
	{
		$this->_init('storelocator/storelocator', 'id');
	}
        
        public function uploadAndImport(Varien_Object $object, $storeCode)
        {
            if (empty($_FILES['groups']['tmp_name']['storelocator']['fields']['location_import']['value'])) {
                return $this;
            }
            
            $csvFile = $_FILES['groups']['tmp_name']['storelocator']['fields']['location_import']['value'];
            
            $this->_importErrors        = array();
            $this->_importedRows        = 0;
            
            $io     = new Varien_Io_File();
            $info   = pathinfo($csvFile);
            $io->open(array('path' => $info['dirname']));
            $io->streamOpen($info['basename'], 'r');
            
            $headers = $io->streamReadCsv();
            $headers[] = 'store_code';
            $storeIdKey = array_search('store_id',$headers);
            if($storeIdKey === FALSE){
                $io->streamClose();
                Mage::throwException(Mage::helper('storelocator')->__('Required column "store_id" does not exists in uploaded csv file.'));
            }
            if ($headers === false) {
                $io->streamClose();
                Mage::throwException(Mage::helper('storelocator')->__('Invalid Store location File Format'));
            }
            
            $adapter = $this->_getWriteAdapter();
            $adapter->beginTransaction();
            
            try {
                $rowNumber  = 1;
                $importData = array();
                while (false !== ($csvLine = $io->streamReadCsv())) {
                    $rowNumber ++;
                    if (empty($csvLine)) {
                        continue;
                    }
                   $row = $this->_getImportRow($csvLine, $rowNumber, $headers);
                   if ($row !== false) {
                        $row['store_code'] = $storeCode;
                        $importData[] = $row;
                   }
                   if (count($importData) == 500) {
                       if(!$this->_dataCleaned){
                        $flag = $this->removeOldLocations($storeCode);
                         if($flag){
                             $this->_dataCleaned = true;
                         }
                       }
                       $this->_saveImportData($importData, $headers);
                       $importData = array();
                   }    
                }
                $this->_importedRows += $rowNumber;
                if(!$this->_dataCleaned){
                    $this->removeOldLocations($storeCode);
                }
                $this->_saveImportData($importData, $headers);
                $io->streamClose();
            }catch (Mage_Core_Exception $e) {
                $adapter->rollback();
                $io->streamClose();
                Mage::throwException($e->getMessage());
            } catch (Exception $e) {
                $adapter->rollback();
                $io->streamClose();
                Mage::logException($e);
                Mage::throwException(Mage::helper('storelocator')->__('An error occurred while import store locations.'));
            }

            $adapter->commit();

            if ($this->_importErrors) {
                $error = Mage::helper('storelocator')->__('File has not been imported. See the following list of errors: %s', implode(" \n", $this->_importErrors));
                Mage::throwException($error);
            }
            
            return $this;
        }
        
        protected function _getImportRow($row, $rowNumber = 0, $header = array())
        {
            $returnRow = array();
            $storeIdKey = array_search('store_id',$header);
            if(!isset($row[$storeIdKey])){
                $this->_importErrors[] = Mage::helper('storelocator')->__('Empty column "%s" value in the Row #%s.', $header[$storeIdKey], $rowNumber);
                return false;
            }
            foreach($header as $key=>$column)
            {
                $returnRow[$column] = $row[$key];
            }
            return $returnRow;
        }
        
        protected function _saveImportData(array $data, $columns = array())
        {
            if (!empty($data)) {
                $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            }

            return $this;
        }
        
        public function getIdbyLocation($storeId, $storeCode)
        {
            $readAdapter = $this->_getReadAdapter();
            
            $query = "SELECT `id` FROM `{$this->getMainTable()}` WHERE `store_id`='".$storeId."' AND `store_code`='".$storeCode."'";
            $result = $readAdapter->fetchOne($query);
            return $result;
        }
        
        public function removeOldLocations($storeCode)
        {
            $writeAdapter = $this->_getWriteAdapter();
            $query = "DELETE FROM `{$this->getMainTable()}` WHERE `store_code`='".$storeCode."'";
            try{
                $result = $writeAdapter->query($query);
            }catch(Exception $e){
                $result = false;
            }
            return $result;
        }
}