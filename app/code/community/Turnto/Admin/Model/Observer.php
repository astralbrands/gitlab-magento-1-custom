<?php

class Turnto_Admin_Model_Observer
{
 
    public function getProductsRatingsFeed()
    {
        
        if(Mage::helper('adminhelper1')->isFeedActivated())
            	  Mage::helper('adminhelper1')->loadRatings();

        //return $this;
    }


    public function pushHistoricalOrdersFeed() {
        $helper = Mage::helper('adminhelper1');
        $stores = $helper->enabledHistoricalOrderFeedPushStores();

        if (sizeof($stores) > 0) {
            foreach ($stores as $store) {
                $helper->pushHistoricalOrdersFeed($store);
            }
        }
    }

    public function pushCatalogFeed() {
        $logFile = 'turnto_catalog_feed_job.log';
        //Mage::log('Started catalog feed push job', null, $logFile);

        $helper = Mage::helper('adminhelper1');
        $stores = $helper->enabledCatalogFeedPushStores();

        if (sizeof($stores) > 0) {
            foreach ($stores as $store) {
                $helper->pushCatalogFeed($store);
            }
        }
    }
}