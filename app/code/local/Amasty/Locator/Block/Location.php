<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Location extends Mage_Core_Block_Template
{

    public function getLocationCollection($productId = null, $categoryId = null)
    {
        if (!Mage::registry('amlocator_location')) {
            $locationCollection = Mage::getModel('amlocator/location')
                ->getCollection();

            if (!$productId) {
                $productId = $this->getRequest()->getParam('product');
            }

            $product = Mage::getModel('catalog/product')->load($productId);

            $locationCollection->addProductCategoryFilter(
                $productId, $product->getCategoryIds()
            );

            $locationCollection->applyDefaultFilters();

            Mage::register('amlocator_location', $locationCollection);
        }
        return Mage::registry('amlocator_location');
    }

    public function getBaloonTemplate()
    {
        $baloon = Mage::getStoreConfig('amlocator/locator/template');
        $store_url = Mage::helper('amlocator/image')->getStoreUrl();
        $baloon = str_replace(
            '{{photo}}', '<img src="' . $store_url . '{{photo}}">', $baloon
        );
        return Mage::helper('core')->jsonEncode(array("baloon" => $baloon));

    }

    public function getGeoUse()
    {
        return Mage::getStoreConfig('amlocator/geoip/usebrowserip');
    }

    public function  getJsonLocations()
    {
        $locations = $this->getLocationCollection();
        $locationArray = $locations->toArray();
        $locationArray['totalRecords'] = count($locationArray['items']);
        return Mage::helper('core')->jsonEncode($locationArray);
    }

    public function getDistanceConfig()
    {

        return Mage::getStoreConfig('amlocator/locator/distance') == "choose"
            ? true : false;
    }

    public function issetLocation($productId, $categoryId)
    {
        $locationCollection = Mage::getResourceModel('amlocator/location');
        $locationCollection->issetLocation($productId, $categoryId);
    }

    public function getLinkToMap($productId)
    {
        return Mage::getUrl(
            Mage::getStoreConfig('amlocator/locator/url'),
            array('_query' => array("product"  => $productId))
        );
    }

    public function getQueryString(){
        if ($this->getRequest()->getParam('product')){
            return '?product='.$this->getRequest()->getParam('product');
        }
        return '';
    }

    public function getTarget()
    {
        $target = '';
        if (Mage::getStoreConfig('amlocator/locator/new_page')) {
            $target = 'target="_blank"';
        }
        return $target;
    }
}