<?php

class Born_Borncmshooks_Model_Mysql4_Rows_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('borncmshooks/rows');
    }
    
    public function addStoreFilter($store, $withAdmin = true){

    if ($store instanceof Mage_Core_Model_Store) {
        $store = array($store->getId());
    }

    if (!is_array($store)) {
        $store = array($store);
    }

    $this->addFilter('store_id', array('in' => $store));

    return $this;
}
}