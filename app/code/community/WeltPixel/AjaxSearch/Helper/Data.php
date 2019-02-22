<?php

class WeltPixel_AjaxSearch_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getMinimalCharacters() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/minimum_characters');
    }

    public function getNoSearchResultText() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/no_search_result_text');
    }

    public function showImageThumbnail($store = 0) {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/show_image', $store);
    }

    public function getImageWidth() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/image_width');
    }

    public function getImageHeight() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/image_height');
    }

    public function getMaxNrOfResults() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/max_result_nr');
    }

    public function showShortDescription() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/show_short_description');
    }

    public function getShortDescriptionLimit() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/limit_short_description');
    }

    public function showPrice() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/show_price');
    }

    public function getMoreResultMessage() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/more_result_message');
    }

    public function getHeaderText() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/header_text');
    }

    public function getFooterText() {
        return Mage::getStoreConfig('weltpixel_ajaxsearch/general/footer_text');
    }
}