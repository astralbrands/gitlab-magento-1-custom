<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */
 
class Born_StoreLocator_Block_Search extends Mage_Core_Block_Template {
	
    /**
     * For the Find Location search page
     * @return Born_StoreLocator_Model_Resource_Storelocator_Collection
     */
    public function getSearchStorelocationsCollection()  {
        if (is_null($this->_storeCollection)) {
            $lat = Mage::app()->getRequest()->getParam('lat');
            $lng = Mage::app()->getRequest()->getParam('lng');
            $distance = Mage::app()->getRequest()->getParam('distance');
            if (!!$lat && !!$lng) {
                $stores = Mage::getModel('storelocator/storelocator')->getStoresByGeoCode($lat, $lng, array('distance'=>$distance));
                $this->_storeCollection=$stores;
            }
        }
        return $this->_storeCollection;
    }

    protected function _toHtml() {
        if ($this->getSearchStorelocationsCollection() instanceof Born_StoreLocator_Model_Resource_Storelocator_Collection)
            return json_encode($this->getSearchStorelocationsCollection()->getData());
        else
            return json_encode(null);
    }

}
