<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Adminhtml_FetchlatlngController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Fetch lattitude & longitude from google
	 */
	public function indexAction() {		
		$nongeodatas = Mage::getModel('storelocator/storelocator')->getNonGeodatas();
		if($nongeodatas != 0):
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__(sprintf("%s records updated", $nongeodatas)));
		else:
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('All the records are up to date'));
		endif;
		$this->_redirectReferer();  		
	}
        
        public function exportAction()
        {
            $storeCode = $this->getRequest()->getParam('store_code');
            if($storeCode == 'pur_store'){
                $fileName = 'store_locator.csv';
                $headerColumns = array("store_id","products","company","city","street","street2","state","postal_code","country","lat","lng","update_geo","phone","website","status", "sort_order");
            }else{
                $fileName = 'spa_locator.csv';
                $headerColumns = array("store_id","products","company","city","street","street2","state","postal_code","country","lat","lng","update_geo","phone","website","status", "sort_order","is_elite");
            }
            
            $collection = Mage::getModel('storelocator/storelocator')->getCollection()
                                    ->addFieldToFilter('store_code', array('eq'=>$storeCode));
            foreach($headerColumns as $column)
            {
                $collection->addFieldToSelect($column);
            }
            
            $io = new Varien_Io_File();
            
            $path = Mage::getBaseDir('var') . DS . 'export' . DS;
            $name = md5(microtime());
            $file = $path . DS . $name . '.csv';

            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $path));
            $io->streamOpen($file, 'w+');
            $io->streamLock(true);
            $io->streamWriteCsv($headerColumns);

            //$this->_exportIterateCollection('_exportCsvItem', array($io));

            $originalCollection = $collection;
        $count = null;
        $page  = 1;
        $lPage = null;
        $break = false;

        while ($break !== true) {
            $collection = clone $originalCollection;
            $collection->setPageSize($this->_exportPageSize);
            $collection->setCurPage($page);
            $collection->load();
            if (is_null($count)) {
                $count = $collection->getSize();
                $lPage = $collection->getLastPageNumber();
            }
            if ($lPage == $page) {
                $break = true;
            }
            $page ++;

            foreach ($collection as $item) {
                //call_user_func_array(array($this, $callback), array_merge(array($item), $args));
                $row = array();
                foreach ($headerColumns as $column) {
                //if (!$column->getIsSystem()) {
                    
                    $row[] = $item->getData($column);
                //}
                }
                $io->streamWriteCsv($row);
            }
        }
            
            /*if ($this->getCountTotals()) {
                $totals = $collection->count();
                $row    = array();
                foreach ($this->_columns as $column) {
                    //if (!$column->getIsSystem()) {
                        $row[] = $column->getRowFieldExport($totals);
                    //}
                }   
                $io->streamWriteCsv($row);
            }*/

            $io->streamUnlock();
            $io->streamClose();

            $content =  array(
                'type'  => 'filename',
                'value' => $file,
                'rm'    => true // can delete file after use
            );
            
            $this->_prepareDownloadResponse($fileName, $content);
        }
        
}


