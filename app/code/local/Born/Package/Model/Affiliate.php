<?php 
class Born_Package_Model_Affiliate
{

	private $varien_csv = NULL ;
	private $file_name = 'var/products.csv';
	private $cj_file_name = 'var/cj_ products.csv';
	private $sas_file_name = 'var/products.csv';
	private $csv_data = array();
	private $fh = NULL;
	protected $_delimiter = ',';
    protected $_enclosure = '"';
	public function getProducts(){
		$products=Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*');
		return $products;
	}
	
	
	/*
	*  @param array $data
	*  writes line to file specified in config
	*
	*/
	private function writeLine($data){
		
		$this->csv_data[]=$data;
		return $this->csv_data;
		/*
		$filename=$this->file_name;
		if(!$this->varien_csv ){
			$this->varien_csv = new Varien_File_Csv();
		}
		
		return $this->varien_csv->saveData($filename,array($data));
		*/
	}
	
	private function writeData(){
		$filename=$this->file_name;
		$data=$this->csv_data;
	
		if(!$this->varien_csv ){
			$this->varien_csv = new Varien_File_Csv();
		}
		
		return $this->varien_csv->saveData($filename,$data);
	}
	
	public function  exportShareASale(){
		
		
			$this->writeline($this->getCsvHeader());
			$this->_exportShareASale();
			$this->writeData();
	}
	
	
	private function _exportShareASale(){
		$attributes=Mage::getStoreConfig('born_affiliate/shareasale_settings/columns');
		$attributes=unserialize($attributes);
		$products=Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
		

		foreach ($products as $product){
		$data=array();
		$url=Mage::getStoreConfig('web/unsecure/base_url');
			foreach($attributes as $key => $value){
				
			
			
				if(!empty($value['product_attribute'])){
				
					switch ($value['product_attribute']){
						
						case 'thumbnail':
							$temp=$url.'media'.DS.$product->getData($value['product_attribute']);
							$data[$value['sort_order']]=$temp;
							//Mage::log($url.'media'.$product->getData($value['product_attribute']),7,'cddsvexport.log');
						 break;
						case 'image':
							//Mage::log($url.'media'.DS.$product->getData($value['product_attribute']),7,'cddsvexport.log');
							$temp=$url.'media'.$product->getData($value['product_attribute']);
							$data[$value['sort_order']]=$temp;
						 break;
						case 'url_key':
							//Mage::log($url.$product->getData($value['product_attribute']),7,'csvexport.log');
							$temp=$url.$product->getData($value['product_attribute']);
							$data[$value['sort_order']]=$temp;
						
						 break;
						default:
						$data[$value['sort_order']]=$product->getData($value['product_attribute']);
					}
					
				}
				else{
					if($value['sort_order']){
						$data[$value['sort_order']]='';
					}
				}
				
			}
			//Mage::log(print_r($data,true),7,'csvdata.log');
			$this->writeLine($data);
				unset($data);
				

		}
	}
	
	private function getCsvHeader(){
			$attributes=Mage::getStoreConfig('born_affiliate/shareasale_settings/columns');
			$attributes=unserialize($attributes);
			$data=array();
			foreach($attributes as $attribute){
				$data[$attribute['sort_order']]=$attribute['csv_column'];
			}
			return $data;
	}
	
	
	private function _exportCJFeed(){
		$products=Mage::getModel('catalog/product')
						->getCollection()
						->addAttributeToSelect('*')
						->addFinalPrice();
		$attributes=Mage::getStoreConfig('born_affiliate/cj_tracking/export_file');
		$attributes=unserialize($attributes);
		$data=array();
		
		foreach ($products as $product){
		
		$url=Mage::getStoreConfig('web/unsecure/base_url');
			foreach($attributes as $key => $value){
			
				if(!empty($value['product_attribute'])){
				
					switch ($value['product_attribute']){
						
						case 'thumbnail':
							$temp=$url.'media'.DS.$product->getData($value['product_attribute']);
							$data[$value['sort_order']]=$temp;
						
						 break;
						case 'image':
							$temp=$url.'media'.$product->getData($value['product_attribute']);
							$data[$value['sort_order']]=$temp;
						 break;
						case 'url_key':
							$temp=$url.$product->getData($value['product_attribute']);
							$data[$value['sort_order']]=$temp;
						
						 break;
						default:
						$data[$value['sort_order']]=$product->getData($value['product_attribute']);
					}
					
				}
				else{
					if($value['sort_order']){
						$data[$value['sort_order']]='';
					}
				}
				
			}
				//Mage::log(print_r($data,true),7,'csv_cj_data.log');
				$this->writeLineCSV($data);
				unset($data);
				

		}
			
			
		
	}
	
	public function exportCJFeed(){
		$this->getHandle($this->cj_file_name);
		$this->writeCJheader();
		$this->_exportCJFeed();
		$this->closeFile();
		
	}
	
	public function writeCJheader(){
		
		
			

		
			$cid=Mage::getStoreConfig('born_affiliate/cj_tracking/export_cid');
				fwrite($this->fh,'&CID='.$cid.PHP_EOL);
			$subid=Mage::getStoreConfig('born_affiliate/cj_tracking/export_subid');
				fwrite($this->fh,'&SUBID='.$subid.PHP_EOL);
			$datefmt=Mage::getStoreConfig('born_affiliate/cj_tracking/export_aid');
				fwrite($this->fh,'&DATEFMT='.$datefmt.PHP_EOL);
			$aid=Mage::getStoreConfig('born_affiliate/cj_tracking/export_dateformat');
				fwrite($this->fh,'&AID='.$aid.PHP_EOL);
			//$encoding='UTF-8';
				//fwrite($this->fh,'&encoding='.$aid.PHP_EOL);
			$processtype=Mage::getStoreConfig('born_affiliate/cj_tracking/export_processtype');
				fwrite($this->fh,'&PROCESSTYPE='.$processtype.PHP_EOL);
			
			$attributes=Mage::getStoreConfig('born_affiliate/cj_tracking/export_file');
			$attributes=unserialize($attributes);
			$data=array();
			foreach($attributes as $attribute){
				$data[$attribute['sort_order']]=$attribute['csv_column'];
			}
			$parameters=implode('|',$data);
			fwrite($this->fh,'&PARAMETERS='.$parameters.PHP_EOL);
	}
	private function writeLineCSV($data){
			$varien=$this->getVarienCSV();
            $varien->fputcsv($this->fh, $data, $this->_delimiter, $this->_enclosure);
        
	}
		
	private function closeFile(){
		return fclose($this->fh);
	}
	
	
	private function getHandle($file_name){
		if(!$this->fh){
				$this->fh = fopen($file_name, 'a');
			}
		return $this->fh;
	}
	public function getVarienCSV(){
		if(!$this->varien_csv ){
			$this->varien_csv = new Varien_File_Csv();
			}
			return $this->varien_csv;
	}
}
