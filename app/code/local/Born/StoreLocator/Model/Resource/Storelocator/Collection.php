<?php 
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Model_Resource_Storelocator_Collection 
	extends Mage_Core_Model_Resource_Db_Collection_Abstract {
	
    protected function _construct()
    {
            $this->_init('storelocator/storelocator');
    }
}