<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */

class Born_StoreLocator_Block_View extends Mage_Core_Block_Template
{
    protected $_stores;	

    public function getStorelocations()
    {
        return $this->_stores;
    }

    public function setStorelocations($stores)
    {
        $this->_stores = $stores;
    }

}