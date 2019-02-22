<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Model_Storelocator extends Mage_Core_Model_Abstract
{
	const DISTANCE = 100; // Mi
		
	protected function _construct()
	{
		$this->_init('storelocator/storelocator');
	}
	
	private function _getStoreLocationTableName(){
        return Mage::getSingleton('core/resource')->getTableName('store_locations');
    }
    

    public function getStoresByGeoCode($lat, $lng, $filter=array()) {
        $storeCode = Mage::app()->getStore()->getCode();
        if ($lat && $lng) {
        	//get only non-updated rows
            $stores = Mage::getModel('storelocator/storelocator')->getCollection()
				->addFieldToFilter('update_geo', array('eq'=>'1'))
                                ->addFieldToFilter('store_code',array('eq'=>$storeCode));
			//set distance to 100 if not in filter param
			$distance = self::DISTANCE;
            if (array_key_exists('distance', $filter)) {
            	if(!empty($filter['distance']))
                	$distance = $filter['distance'];
            }
            $dist = '( 3959 * acos( cos( radians('.$lat.') ) * cos( radians( main_table.lat ) ) * cos( radians( main_table.lng ) - radians('.$lng.') ) + sin( radians('.$lat.') ) * sin( radians( main_table.lat ) ) ) ) AS distance';
            $stores->getSelect()->columns($dist);
            $stores->getSelect()->having('distance < '.$distance);
            $stores->getSelect()
                ->order('distance')
                ->order('postal_code')
                ->order('city')
                ->order('lat')
                ->order('lng')
                ->order('company');

            $stores->getSelect()->limit(10);

            // resort collection based on sort_order
            $stores2 = Mage::getModel('storelocator/storelocator')->getCollection();
            $stores2->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->reset(Zend_Db_Select::FROM)
                ->from($stores->getSelect())
                ->order('t.sort_order DESC')
                ->order('t.distance ASC')
            ;
            return $stores2;
        }
        return;
    }
	
	/**
	 * @return updated row count
	 */
	public function getNonGeodatas() {
		$fetchLimit = '400'; //default value.
		$delay = 100000; //Google Map API throttle delay in micro seconds
		$storeUpdatedCount = 0;

		$storeTableName = $this->_getStoreLocationTableName();
		$stores = $this->_getConnection()->fetchAll("SELECT * FROM {$storeTableName} WHERE update_geo = '0' OR update_geo = '';");
		if($stores)
        {
            $index = 0;
            foreach ($stores as $_store) {
                if($index++ < $fetchLimit){
                    if ($_store['update_geo'] == 0) {
                        if($geoResult = $this->_getCoordinates($_store))
                        {
                            if($geoResult['status'] =='OK'){
                                $_store['lat']= $geoResult['results'][0]['geometry']['location']['lat'];
                                $_store['lng']= $geoResult['results'][0]['geometry']['location']['lng'];
								$_store['update_time'] = date('Y-m-d H:i:s');
                                $_store['update_geo'] = 1;
                                $storeUpdatedCount++;
                            }
                            elseif($geoResult['status'] =='OVER_QUERY_LIMIT'){
                                @sleep(2);
                            }
                            elseif($geoResult['status'] == 'ZERO_RESULTS'){
                                //if address is not valid, will not run geocode on that store again. 
                                $_store['update_geo'] = 0;
								$_store['update_time'] = date('Y-m-d H:i:s');
                            }
                            else{
                                @sleep(2);
                            }

                        }
                        $this->_updateQuery($_store, $_store['id']);
                        @usleep($delay);
                        }else{
                            break;
                        }    
                    }
                }
                return $storeUpdatedCount;
        }
        else
        {
             return 0;
        }
	}

	/**
    * get coordinates for empty coordinates
    * 
    */
    protected function _getCoordinates($shopData){

        if($shopData['update_geo'] == 0 || $shopData['update_geo'] == '') {                
            $addressStr = $shopData['street'] . ', ' . $shopData['city'] . ', ' . $shopData['postal_code'] . ', ' . $shopData['country'];
			
			$language = Mage::helper('storelocator')->getLanguage();
            $result = Mage::getSingleton('storelocator/geocoder')->getLocation($addressStr,$language);
        }
        return $result;
    }

	/**
    * @return Varien_Db_Adapter_Interface
    * 
    * @param mixed $resource
     * 
    * @param mixed $name
    */
    private function _getConnection($resource='core/resource',$name='core_write') {
        return  Mage::getSingleton($resource)->getConnection($name);

    }

	protected function _updateQuery($shopData=array(), $id){
        $this->_getConnection()->update($this->_getStoreLocationTableName(),$shopData,"id='{$id}'");
    }
    
}
