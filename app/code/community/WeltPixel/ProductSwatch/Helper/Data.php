<?php

class WeltPixel_ProductSwatch_Helper_Data extends Mage_Core_Helper_Data {

    public function isColorSwatchEnabledOnLayeredNavigation() {
        $moduleName = 'WeltPixel_LayeredNavigation';
        if (Mage::getConfig()->getModuleConfig($moduleName)->is('active', 'true')) {
            return true;
        } else {
            return false;
        }
    }

    public function getBorderRadius($store = 0) {
        return Mage::getStoreConfig('weltpixel_productswatch/general/border_radius', $store);
    }

    public function getSwatchResize($store = 0) {
        return Mage::getStoreConfig('weltpixel_productswatch/general/colors_images_resize', $store);
    }

    public function getSwatchHeight($store = 0) {
        return Mage::getStoreConfig('weltpixel_productswatch/general/colors_images_height', $store);
    }

    public function getSwatchWidth($store = 0) {
        return Mage::getStoreConfig('weltpixel_productswatch/general/colors_images_width', $store);
    }

}
