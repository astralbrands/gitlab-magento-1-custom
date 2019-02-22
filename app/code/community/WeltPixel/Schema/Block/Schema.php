<?php

class WeltPixel_Schema_Block_Schema
    extends Mage_Core_Block_Template
{
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    public function getNonCachedImage($product)
    {
        $_image = Mage::getBaseUrl('media') . 'catalog/product' . $product->getImage();
        ;
        return $_image;
    }

    public function getProductPrice()
    {
        $price = $this->getProduct()->getFinalPrice();
        return $price;
    }

    public function getProductDescription($_product)
    {
        if (Mage::getStoreConfig('weltpixel_schema/rich_snippets/desc')) {
            return nl2br($_product->getDescription());
        } else {
            return nl2br($_product->getShortDescription());
        }
    }

    public function getReviewSummary()
    {
        $storeId = Mage::app()->getStore()->getId();

        $summaryData = Mage::getModel('review/review_summary')
            ->setStoreId($storeId)
            ->load($this->getProduct()->getId());
        return $summaryData;
    }

    public function getCurrencyCode()
    {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

}