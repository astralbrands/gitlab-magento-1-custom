<?php
class Born_StoreLocator_Model_System_Config_Backend_Import extends Mage_Core_Model_Config_Data
{
    public function _afterSave()
    {
        $storeCode   = $this->getStoreCode();
    
        Mage::getResourceModel('storelocator/storelocator')->uploadAndImport($this, $storeCode);
    }
}

