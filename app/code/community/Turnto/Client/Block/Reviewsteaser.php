<?php

class Turnto_Client_Block_Reviewsteaser extends Mage_Core_Block_Template
{
    const TURNTO_REVIEW_TEASERS_CACHE_TAG = 'TURNTO_REVIEW_TEASERS_CACHE_TAG';
    const TURNTO_REVIEW_TEASERS_CACHE_KEY = 'TURNTO_REVIEW_TEASERS_CACHE_KEY';

    public function getCacheKey()
    {
        return $this::TURNTO_REVIEW_TEASERS_CACHE_KEY;
    }

    public function getCacheLifetime()
    {
        return Mage::getStoreConfig('turnto_admin/general/teaser_cache_time') ? intval(Mage::getStoreConfig('turnto_admin/general/teaser_cache_time')) : 900;
    }

    public function getCacheTags()
    {
        return array(
            $this::TURNTO_REVIEW_TEASERS_CACHE_TAG,
            Mage_Catalog_Model_Product::CACHE_TAG,
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template')
        );
    } 
}
