<?php

class WeltPixel_LayeredNavigation_Helper_Data extends Mage_Core_Helper_Data {

    public function isColorSwatchEnabledOnProduct() {
        $moduleName = 'WeltPixel_ProductSwatch';
        if (Mage::getConfig()->getModuleConfig($moduleName)->is('active', 'true')) {
            return true;
        } else {
            return false;
        }
    }

    public function getNavigationPosition() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/display/position');
    }

    public function showCurrentlyShoppingBy() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/display/show_shop_by');
    }
    
    public function isMobileOrTablet() {
        $_helper = Mage::helper('weltpixel_mobiledetect');
        $mobileDeviceWasDetected = $_helper->isMobile() || $_helper->isTablet();
        return $mobileDeviceWasDetected;
    }

    public function moveNavigationToTop() {
        $_helper = Mage::helper('weltpixel_mobiledetect');
        $mobileDeviceWasDetected = $_helper->isMobile() || $_helper->isTablet();
        return (boolean) ($mobileDeviceWasDetected);
    }

    public function isLayeredNavigationOnTop() {
        return ( $this->getNavigationPosition() == 1 ) || ($this->moveNavigationToTop());
    }  

    public function isAjaxEnabled() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/ajax/enable_ajax');
    }

    public function disableMouseClicks() {
        return (boolean) Mage::getStoreConfig('weltpixel_layerednavigation/ajax/disable_mouseclicks');
    }

    public function showClearAll() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/show_clear_all');
    }

    public function defaultFilters() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/show_other_filters_as');
    }

    /**
     * 0 - Default
     * 1 - Checkboxes
     * 2 - Dropdown
     * 3 - ColorSwatches
     */
    public function getAttributeDisplay($attribute) {
        //when store specific options will be set, logic here
        //check if colorswatch was enabled
        $attrObject = Mage::getSingleton("eav/config")->getAttribute("catalog_product", $attribute);
        $isSwatchEnabled = $attrObject->getData('enable_layered_swatch');
        $customOption = $attrObject->getData('layered_filter_option');

        if ($isSwatchEnabled) {
            return 3;
        }
        
        /**
         * Option was set from the attribute page, overwriting default settings
         */
        if (!is_null($customOption) && $customOption != -1) :
            return $customOption;
        endif;

        return $this->defaultFilters();
    }

    public function showProductCountOnCategories() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/show_categories_product_count');
    }
    
     public function showProductCountOnAttributes() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/show_attributes_product_count');
    }

    public function getBorderRadius($store = 0) {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/border_radius', $store);
    }

    public function getSwatchResize($store = 0) {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/colors_images_resize', $store);
    }

    public function getSwatchHeight($store = 0) {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/colors_images_height', $store);
    }

    public function getSwatchWidth($store = 0) {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/colors_images_width', $store);
    }

    public function getCategoryDisplay() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/show_categories_as');
    }
    
    public function getPriceDisplay() {
        // for mobile devices use the from - to slider
        $_helper = Mage::helper('weltpixel_layerednavigation');
        if ($_helper->isMobileOrTablet()) {
            return 4;
        }
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/show_price_filter_as');
    }

    public function getItemsDisplay() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/display_all_items_as');
    }
    
    public function getItemLimit() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/show_item_limit');
    }    
    
    public function getAttributeSortByOption() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/sort_items_by');
    }
    
    public function getAttributeOrderByOption() {
        return Mage::getStoreConfig('weltpixel_layerednavigation/default_display/order_items_by');
    }

    public function orderAttributes(&$data) {
        $sortByOptionType = $this->getAttributeSortByOption();
        $orderByOption = 'count';

        switch ($sortByOptionType) {
            case 1:
                $orderByOption = 'label';
                break;
            case 2:
                $orderByOption = 'count';
                break;

            default:
                return;
                break;
        }

        $count = array();
        foreach ($data as $key => $row) {
            $count[$key] = $row[$orderByOption];
        }

        /*
         * Also sort by ASC / DESC could be changed from backend
         */
        $orderBy = SORT_ASC;
        switch ($this->getAttributeOrderByOption()) {
            case 1:
                $orderBy = SORT_ASC;
                break;
            case 2:
                $orderBy = SORT_DESC;
                break;
            default:
                $orderBy = SORT_ASC;
                break;
        }
        array_multisort($count, $orderBy, $data);

        //return $data;
    }
    
    public function getCategoryImage() {
        $uploadedFile = Mage::getStoreConfig('weltpixel_layerednavigation/default_display/category_mobile_image');
        if (strlen(trim($uploadedFile))) :
            return Mage::getBaseUrl('media'). WeltPixel_LayeredNavigation_Model_Adminhtml_System_Config_Backend_Navigation_Category_Image::UPLOAD_DIR . DS . $uploadedFile;
        else:
            return false;
        endif;
    }

}
