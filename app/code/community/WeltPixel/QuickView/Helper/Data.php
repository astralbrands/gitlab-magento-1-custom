<?php

class WeltPixel_QuickView_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isMageEnterprise()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')
            && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws')
            && Mage::getConfig()->getModuleConfig('Enterprise_Checkout')
            && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }

    public function getProductUrl($product, $additional)
    {
        $additional += array('id' => $product->getId());

        return Mage::getUrl('weltpixel_quickview/ajax_catalog_product/view', $additional);
    }
}
