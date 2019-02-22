<?php
class Astral_Optionswatch_Model_Catalog_Attribute_Data extends Varien_Object
{
    protected function _getConnection($resource='core/resource',$name='core_write') {
        return  Mage::getSingleton($resource)->getConnection($name);

    }

    public function getSubtitleByEntityId($entityId)
    {
        $_tableName = 'catalog_product_entity_varchar';
        return $this->getProductAttributeValue($entityId, $this->getSubtitleAttributeId());
    }

    public function getUsageValueByEntityId($entityId)
    {        
        $_tableName = 'catalog_product_entity_varchar';
        return $this->getProductAttributeValue($entityId, $this->getUsageAttributeId());
    }

    public function getProductAttributeValue($entityId, $attributeId)
    {
        $_tableName = 'catalog_product_entity_varchar';
        return $this->getAttributeValue($entityId, $attributeId, $_tableName);
    }

    public function getProductAttributeInt($entityId, $attributeId)
    {
        $_tableName = 'catalog_product_entity_int';
        return $this->getAttributeValue($entityId, $attributeId, $_tableName);
    }

    public function getAttributeValue($entityId, $attributeId, $_tableName = null)
    {
        if (!$entityId) {
            return;
        }

            if (!is_numeric($attributeId) || !is_numeric($entityId))
            {
            return;
            }
        $storeIds = array(Mage::app()->getStore()->getStoreId(), '0');
            foreach ($storeIds as $storeId)
            {
                try {
                $_query = "SELECT  * FROM {$_tableName} WHERE attribute_id='{$attributeId}' AND store_id='{$storeId}' AND entity_id='{$entityId}';";
                $attributes = $this->_getConnection()->fetchAll($_query);
                } catch (Exception $e) {
                Mage::logException($e);
                return;
                }
            $attributes = array_shift($attributes);
                if ($attributes['value'])
                {
                return $attributes['value'];
                }
            }
        return;
    }

    protected function getSubtitleAttributeId()
    {
        $_storeId = Mage::app()->getStore()->getStoreId();
        $path= 'catalog/category_product_subtext/subtext_attribute_id';
        $config = Mage::getStoreConfig($path, $_storeId);
        return $config;
    }


    protected function getUsageAttributeId()
    {
        $_storeId = Mage::app()->getStore()->getStoreId();
        $path= 'catalog/miscellaneous/category_product_usage';
        $config = Mage::getStoreConfig($path, $_storeId);
            if (is_numeric($config))
            {
            return $config;
            }
        return;
    }
}