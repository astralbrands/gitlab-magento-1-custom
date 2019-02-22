<?php 

class Born_Package_Helper_Config extends Mage_Core_Helper_Abstract
{
    public function getConfig($path)
    {
        if (!$path) {
            return;
        }

        $_storeId = Mage::app()->getStore()->getStoreId();
        $config = Mage::getStoreConfig($path, $_storeId);

        return $config;
    }
}

?>