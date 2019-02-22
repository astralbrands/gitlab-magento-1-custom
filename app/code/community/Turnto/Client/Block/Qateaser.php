<?php

class Turnto_Client_Block_Qateaser extends Mage_Core_Block_Template
{
    const TURNTO_QA_TEASERS_CACHE_TAG = 'TURNTO_QA_TEASERS_CACHE_TAG';
    const TURNTO_QA_TEASERS_CACHE_KEY = 'TURNTO_QA_TEASERS_CACHE_KEY';

    public function getCacheKey(){
        return $this::TURNTO_QA_TEASERS_CACHE_KEY;
    }

    public function getCacheKeyInfo()
    {
        $helper = Mage::helper('turnto_client_helper/data');
        $info = parent::getCacheKeyInfo();
        if ($helper->getProduct())
        {
            $info['product_id'] = $helper->getProduct()->getId();
        }
        return $info;
    }

    public function getCacheLifetime()
    {
        return Mage::getStoreConfig('turnto_admin/general/teaser_cache_time') ? intval(Mage::getStoreConfig('turnto_admin/general/teaser_cache_time')) : 900;
    }

    public function getCacheTags()
    {
        return array(
            $this::TURNTO_QA_TEASERS_CACHE_TAG,
            Mage_Catalog_Model_Product::CACHE_TAG,
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template')
        );
    }
}
