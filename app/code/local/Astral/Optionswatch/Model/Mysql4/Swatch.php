<?php
class Astral_Optionswatch_Model_Mysql4_Swatch extends Mage_Core_Model_Mysql4_Abstract
{
	
    protected function _construct()
    {
        $this->_init('optionswatch/swatch', 'id'); //mark
    }
    
    public function loadByOptionId($optionId,$storeId = null, $productSku = null)
    {
    	$read = $this->_getReadAdapter();
		$result = null;
    	$select = $read->select()->from($this->getMainTable()) ->where('option_id = ?', $optionId);
    	
    	    if(!!$storeId)
    	    {
    		$select->where('store_id = ?', $storeId);
    	    }
		    if(!!$productSku)
		    {
			$select->where('product_sku = ?', $productSku);
		    }
    	
    	$result = $read->fetchRow($select);

    	    if(!$result && $productSku)
    	    {
    		$result = $this->loadByOptionId($optionId, $storeId);
    	    }elseif(!$result && $productSku)
            {
			return array();
		    }
    	return $result;    
    }
}