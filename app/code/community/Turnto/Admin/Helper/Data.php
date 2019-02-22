<?php

class Turnto_Admin_Helper_Data extends Mage_Core_Helper_Data
{

    protected $_activated = null;
    protected $_feed = null;

    public function isFeedActivated()
    {
        $feed_url = $this->getFeedUrl();
        return $this->checkFeed($feed_url);
    }

    public function enabledHistoricalOrderFeedPushStores() {
        $enabledStores = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if (Mage::getStoreConfig('turnto_admin/historicalfeedconfig/enabled', $store) == 1) {
                        $enabledStores[] = $store;
                    }
                }
            }
        }
        return $enabledStores;
    }

    /**
     * A list of the stores that have the catalog push turned on
     *
     * @return array
     */
    public function enabledCatalogFeedPushStores() {
        $enabledStores = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if (Mage::getStoreConfig('turnto_admin/catalogfeedconfig/enabled', $store) == 1) {
                        $enabledStores[] = $store;
                    }
                }
            }
        }
        return $enabledStores;
    }

    public function checkFeed($url = null)
    {

        if (!$url)
            return null;

        if (!$xml = simplexml_load_file($url)) {

            $active = false;
        } else {
            $this->_feed = simplexml_load_file($url);
            $active = true;
        }

        return $active;
    }

    public function getFeedUrl()
    {

        $url = Mage::getStoreConfig('turnto_admin/feedconfig/url');
        $site_key = Mage::getStoreConfig('turnto_admin/general/site_key');
        $site_auth = Mage::getStoreConfig('turnto_admin/general/site_auth');

        if (!$url || !$site_key || !$site_auth)
            return null;

        $feed_url = rtrim($url, '/') . '/' . $site_key . '/' . $site_auth . '/turnto-skuaveragerating.xml';

        return $feed_url;
    }


    public function loadRatings($url = null)
    {

        if (!$this->_feed) {

            if (!$url)
                $url = $this->getFeedUrl();

            if (!$this->checkFeed($url))
                return null;
        }


        $key_field = 'sku';

        foreach ($this->_feed->products->product as $prod_rating) {

            if (!$prod_rating[$key_field]) {
                //Mage::log('Product sku (id) is missing in the feed. Feed product record is invalid...', null, 'turnto_product_ratings.log');
                continue;
            }


            $rating = Mage::getModel('turnto_admin/rating')
                ->getCollection()
                //    ->addFieldToFilter('product_id', $prod_rating[$key_field])
                ->addFieldToFilter($key_field, $prod_rating[$key_field])
                ->getFirstItem();

            try {


                # udpate bv_average_rating attribute

                $bvProductExternalId = $prod_rating[$key_field];

                $productAverageRating = (string)$prod_rating;
                //     echo $productAverageRating; echo '<br>';
                $productReviewCount = $prod_rating['review_count'];
                $productRatingRange = 5;

                // $product = Bazaarvoice_Helper_Data::getProductFromProductExternalId($bvProductExternalId);
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $bvProductExternalId);

                if ($product) {

                    $productAverageRating = (int)round($productAverageRating);

                    $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute("bv_average_rating");

                    if ($attribute) {

                        $productAverageRating = $attribute->getSource()->getOptionId($productAverageRating);
                        $product->setBvAverageRating($productAverageRating);
                        $product->setBvReviewCount($productReviewCount);
                        $product->setBvRatingRange($productRatingRange);
                        $product->getResource()->saveAttribute($product, 'bv_average_rating');
                        $product->getResource()->saveAttribute($product, 'bv_review_count');
                        $product->getResource()->saveAttribute($product, 'bv_rating_range');
                    }

                    # save rating for tne product
                    if (!$rating->getCreatedAt())
                        $rating->setCreatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));

                    $rating->setSku($prod_rating[$key_field])
                        ->setProductId($product->getId())
                        ->setReviewCount($prod_rating['review_count'])
                        ->setUpdatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'))
                        ->setRating((string)$prod_rating)
                        ->save();


                } else {
                    //Mage::log('Could not find Magento product for xml file product id:' . $bvProductExternalId, null, 'turnto_product_ratings.log');
                }

            } catch (Exception $e) {
                //Mage::log('Error updating product rating for product id:' . $prod_rating[$key_field] . '. Error details:' . $e->getMessage(), null, 'turnto_product_ratings.log');

            }

        }

        #cleanup turnto_product_ratings table
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');

        $table_name = Mage::getConfig()->getTablePrefix() . 'turnto_products_ratings';

        $sql = "DELETE FROM " . $table_name . " WHERE sku = ''";

        try {
            $result = $write->query($sql);

        } catch (Exception $e) {
            //Mage::log('Error:' . $e->getMessage(), null, 'turnto_product_ratings.log');
        }

    }

    public function getLastUpdated()
    {

        $updated = null;

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

        $query = "SELECT MAX(updated_at) as updated FROM " . Mage::getSingleton('core/resource')->getTableName('turnto_admin/rating');

        $result = $read->fetchAll($query);

        if ($result[0]['updated'])
            $updated = $result[0]['updated'];

        // var_dump($query); var_dump($result); exit;

        return $updated;
    }

    public function generateCatalogFeed($websiteId, $storeId, $fileName) {
        try {
            $logFile = 'turnto_catalog_feed_job.log';

            if (!isset($storeId)) {
                $storeId = 1;
            }

            if (isset($websiteId)) {
                $websiteId = 1;
            }

            $path = Mage::getBaseDir('media') . DS . 'turnto/';
            if (!file_exists($path)) {
                //Mage::log('Attempting to create turnto directory', null, $logFile);
                mkdir($path, 0766);
            }

            //Mage::log('Opening catalog file for writing', null, $logFile);
            $fh = fopen($path . $fileName, 'w');

            if (!$fh) {
                //Mage::log('Failed to open catalog file' . $path . $fileName, null, $logFile);
                Mage::throwException($this->__('Could not create historical feed file in directory ' . $path));
            }

            //Mage::log('Writing Header', null, $logFile);
            fwrite($fh, "SKU\tIMAGEURL\tTITLE\tPRICE\tCURRENCY\tACTIVE\tITEMURL\tCATEGORY\tKEYWORDS\tREPLACEMENTSKU\tINSTOCK\tVIRTUALPARENTCODE\tCATEGORYPATHJSON\tISCATEGORY\tBRAND\tUPC\tMPN\tISBN\tEAN\tJAN\tASIN");
            fwrite($fh, "\n");

            //Mage::log('Getting simple product count', null, $logFile);
            $pageSize = 100;
            $count = Mage::getModel('catalog/product')
                ->getCollection()
                ->addStoreFilter($storeId)
                ->addWebsiteFilter($websiteId)
                ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                ->getSize();
            //Mage::log('Simple product count: ' . $count, null, $logFile);

            // other products
            $otherProductsCount = Mage::getModel('catalog/product')
                ->getCollection()
                ->addStoreFilter($storeId)
                ->addWebsiteFilter($websiteId)
                ->addAttributeToFilter('type_id', array('neq' => 'simple'))
                ->getSize();
            //Mage::log('Non-simple product count' . $otherProductsCount, null, $logFile);

            $page = 1;
            $pages = ceil($count / $pageSize);
            $upcCode = Mage::getStoreConfig('turnto_admin/general/upc_attribute');
            $mpnCode = Mage::getStoreConfig('turnto_admin/general/mpn_attribute');
            $isbnCode = Mage::getStoreConfig('turnto_admin/general/isbn_attribute');
            $eanCode = Mage::getStoreConfig('turnto_admin/general/ean_attribute');
            $janCode = Mage::getStoreConfig('turnto_admin/general/jan_attribute');
            $asinCode = Mage::getStoreConfig('turnto_admin/general/asin_attribute');
            $brandCode = Mage::getStoreConfig('turnto_admin/general/brand_attribute');

            //Mage::log('Starting simple product output', null, $logFile);
            do {
                //Mage::log('Generating Page ' . $page, null, $logFile);
                $collection = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addWebsiteFilter($websiteId)
                    ->addStoreFilter($storeId)
                    ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                    ->setPageSize($pageSize)
                    ->setCurPage($page);

                foreach ($collection as $product) {
                    $parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                    if (isset($parents[0])) {
                        // skip
                    } else {
                        self::outputProduct($fh, $product, $storeId, $upcCode, $mpnCode, $isbnCode, $eanCode, $janCode, $asinCode, $brandCode);
                    }
                }
                $page++;
                $collection->clear();
            } while ($page <= $pages);

            //Mage::log('Done with simple product output', null, $logFile);

            $page = 1;
            $pages = ceil($otherProductsCount / $pageSize);

            //Mage::log('Starting non-simple product output', null, $logFile);
            do {
                //Mage::log('Generating Page ' . $page, null, $logFile);
                $collection = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addWebsiteFilter($websiteId)
                    ->addStoreFilter($storeId)
                    ->addAttributeToFilter('type_id', array('neq' => 'simple'))
                    ->setPageSize($pageSize)
                    ->setCurPage($page);

                foreach ($collection as $product) {
                    self::outputProduct($fh, $product, $storeId, $upcCode, $mpnCode, $isbnCode, $eanCode, $janCode, $asinCode, $brandCode);
                }
                $page++;
                $collection->clear();
            } while ($page <= $pages);

            //Mage::log('Starting category output', null, $logFile);
            $categories = Mage::getModel('catalog/category')->setStoreId($storeId)->getCollection()->addAttributeToSelect('*');
            if ($categories) {
                foreach ($categories as $category) {
                    $category->setStoreId($storeId);
                    fwrite($fh, 'mag_category_'.$category->getId());
                    fwrite($fh, "\t");
                    //IMAGEURL
                    fwrite($fh, "\t");
                    //TITLE
                    fwrite($fh, $category->getName());
                    fwrite($fh, "\t");
                    //PRICE
                    fwrite($fh, "\t");
                    //CURRENCY
                    fwrite($fh, "\t");
                    //ACTIVE
                    fwrite($fh, "Y");
                    fwrite($fh, "\t");
                    //ITEMURL
                    fwrite($fh, $category->getUrl());
                    fwrite($fh, "\t");
                    //CATEGORY
                    fwrite($fh, $category->getParentCategory()->getId() ? 'mag_category_'.$category->getParentCategory()->getId() : '');
                    fwrite($fh, "\t");
                    //KEYWORDS
                    fwrite($fh, "\t");
                    //REPLACEMENTSKU
                    fwrite($fh, "\t");
                    //VIRTUALPARENTCODE
                    fwrite($fh, "\t");
                    //INSTOCK
                    fwrite($fh, "\t");
                    //CATEGORYPATHJSON
                    fwrite($fh, "\t");
                    //ISCATEGORY
                    fwrite($fh, "Y");
                    fwrite($fh, "\t");
                    //BRAND
                    fwrite($fh, "\t");
                    //UPC
                    fwrite($fh, "\t");
                    //MPN
                    fwrite($fh, "\t");
                    //ISBN
                    fwrite($fh, "\t");
                    //EAN
                    fwrite($fh, "\t");
                    //JAN
                    fwrite($fh, "\t");
                    //ASIN
                    fwrite($fh, "\n");
                }
            }
            //Mage::log('Done', null, $logFile);
            fclose($fh);
        } catch (Exception $e) {
            //Mage::log($e->getMessage(), null, $logFile);
        }

        return;
    }

    private function getGTINsCommaSeparated($gtins) {
        return join(',', $gtins);
    }

    private function getProductAttributeValue($product, $code, $storeId) {
        if ($code != null && $code != '') {
            $attributeText = $product->getAttributeText($code);

            if ($attributeText == null) {
                $attributeText = $product->getData($code);
            }

            if ($attributeText == null) {
                $attributeText = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product->getId(), $code, $storeId);
            }

            if ($attributeText != null || strcasecmp($attributeText, 'NULL') != 0) {
                return $attributeText;
            }
        }

        return '';
    }

    private function outputProduct($fh, $product, $storeId, $upcCode, $mpnCode, $isbnCode, $eanCode, $janCode, $asinCode, $brandCode) {
        $product->setStoreId($storeId);

        //SKU
        fwrite($fh, $product->getSku());
        fwrite($fh, "\t");

        $childProducts = null;
        if ($product->isConfigurable()) {
            $childProducts = Mage::getModel('catalog/product_type_configurable')
                ->getUsedProducts(null, $product);
        }

        //echo Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );
        // IMAGEURL
        $imageUrl = null;
        if ($product->getImage() != null && $product->getImage() != "no_selection") {
            $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
        } else if ($product->getSmallImage() != null && $product->getSmallImage() != "no_selection") {
            $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getSmallImage());
        } else if ($product->getThumbnail() != null && $product->getThumbnail() != "no_selection") {
            $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail());
        }
        if (!$imageUrl) {
            fwrite($fh, $product->getImageUrl());
        } else {
            fwrite($fh, $imageUrl);
        }

        fwrite($fh, "\t");
        //TITLE
        fwrite($fh, $product->getName());
        fwrite($fh, "\t");
        //PRICE
        fwrite($fh, $product->getPrice());
        fwrite($fh, "\t");
        //CURRENCY
        fwrite($fh, "\t");
        //ACTIVE
        fwrite($fh, 'Y');
        fwrite($fh, "\t");
        //ITEMURL
        fwrite($fh, $product->getProductUrl());
        fwrite($fh, "\t");
        //CATEGORY
        $ids = $product->getCategoryIds();
        fwrite($fh,(isset($ids[0]) ? 'mag_category_'.$ids[0] : ''));
        fwrite($fh, "\t");
        // KEYWORDS
        fwrite($fh, "\t");
        // REPLACEMENTSKU
        fwrite($fh, "\t");
        //VIRTUALPARENTCODE
        fwrite($fh, "\t");
        //INSTOCK
        fwrite($fh, "\t");
        //CATEGORYPATHJSON
        fwrite($fh, "\t");
        //ISCATEGORY
        fwrite($fh, "n");
        fwrite($fh, "\t");
        //Brand
        fwrite($fh, self::getProductAttributeValue($product, $brandCode, $storeId));
        fwrite($fh, "\t");
        $productId = $product->getId();
        if ($product->isConfigurable()) {
            // this product is a parent for another product.  roll-up the GTINs
            // UPCs rolled up
            $upcs = array();
            foreach ($childProducts as $child) {
                self::pushValueIfNotNull($upcs, self::getProductAttributeValue($child, $upcCode, $storeId));
            }
            fwrite($fh, self::getGTINsCommaSeparated($upcs));
            fwrite($fh, "\t");
            // MPNs rolled up
            $mpns = array();
            foreach ($childProducts as $child) {
                self::pushValueIfNotNull($mpns, self::getProductAttributeValue($child, $mpnCode, $storeId));
            }
            fwrite($fh, self::getGTINsCommaSeparated($mpns));
            fwrite($fh, "\t");
            // ISBNs rolled up
            $isbns = array();
            foreach ($childProducts as $child) {
                self::pushValueIfNotNull($isbns, self::getProductAttributeValue($child, $isbnCode, $storeId));
            }
            fwrite($fh, self::getGTINsCommaSeparated($isbns));
            fwrite($fh, "\t");
            // EANs rolled up
            $eans = array();
            foreach ($childProducts as $child) {
                self::pushValueIfNotNull($eans, self::getProductAttributeValue($child, $eanCode, $storeId));
            }
            fwrite($fh, self::getGTINsCommaSeparated($eans));
            fwrite($fh, "\t");
            // JANs rolled up
            $jans = array();
            foreach ($childProducts as $child) {
                self::pushValueIfNotNull($jans, self::getProductAttributeValue($child, $janCode, $storeId));
            }
            fwrite($fh, self::getGTINsCommaSeparated($jans));
            fwrite($fh, "\t");
            // ASINs rolled up
            $asins = array();
            foreach ($childProducts as $child) {
                self::pushValueIfNotNull($asins, self::getProductAttributeValue($child, $asinCode, $storeId));
            }
            fwrite($fh, self::getGTINsCommaSeparated($asins));
        } else {
            // this is a simple product just output the single GTINs
            //UPC
            fwrite($fh, self::getProductAttributeValue($product, $upcCode, $storeId));
            fwrite($fh, "\t");
            //MPN
            fwrite($fh, self::getProductAttributeValue($product, $mpnCode, $storeId));
            fwrite($fh, "\t");
            //ISBN
            fwrite($fh, self::getProductAttributeValue($product, $isbnCode, $storeId));
            fwrite($fh, "\t");
            //EAN
            fwrite($fh, self::getProductAttributeValue($product, $eanCode, $storeId));
            fwrite($fh, "\t");
            //JAN
            fwrite($fh, self::getProductAttributeValue($product, $janCode, $storeId));
            fwrite($fh, "\t");
            //ASIN
            fwrite($fh, self::getProductAttributeValue($product, $asinCode, $storeId));
        }

        fwrite($fh, "\n");
    }

    private function pushValueIfNotNull(&$arr, $val) {
        if ($val != null && $val != '') {
            array_push($arr, $val);
        }
    }

    public function generateHistoricalOrdersFeed($startDate, $storeId, $fileName) {
        $path = Mage::getBaseDir('media') . DS . 'turnto/';
        if (!file_exists($path)) {
            mkdir($path, 0766);
        }

        $handle = fopen($path . $fileName, 'w');

        if (!$handle) {
            Mage::throwException($this->__('Could not create historical feed file in directory ' . $path));
        }

        fwrite($handle, "ORDERID\tORDERDATE\tEMAIL\tITEMTITLE\tITEMURL\tITEMLINEID\tZIP\tFIRSTNAME\tLASTNAME\tSKU\tPRICE\tITEMIMAGEURL\tDELIVERYDATE");
        fwrite($handle, "\n");

        $fromDate = date('Y-m-d H:i:s', strtotime($startDate));
        Mage::app();
        $orders = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToFilter('created_at', array('from'=>$fromDate))
            ->addAttributeToSort('entity_id', 'ASC')
            ->setPageSize(100);

        $pages = $orders->getLastPageNumber();

        for ($curPage = 1; $curPage <= $pages; $curPage++) {
            $orders->setCurPage($curPage);
            $orders->load();
            foreach ($orders as $order) {
                $itemlineid = 0;
                foreach ($order->getAllVisibleItems() as $item) {
                    $parent = null;
                    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                        ->getParentIdsByChild($item->getProduct()->getId());
                    if (isset($parentIds[0])) {
                        $parent = Mage::getModel('catalog/product')->load($parentIds[0]);
                    }
                    if ($parent) {
                        $product = $parent;
                    } else {
                        $product = $item->getProduct();
                    }
                    //ORDERID
                    fwrite($handle, $order->getId());
                    fwrite($handle, "\t");
                    //ORDERDATE
                    fwrite($handle, $order->getCreatedAtDate()->toString('Y-MM-d'));
                    fwrite($handle, "\t");
                    //EMAIL
                    fwrite($handle, $order->getCustomerEmail());
                    fwrite($handle, "\t");
                    //ITEMTITLE
                    fwrite($handle, $product->getName());
                    fwrite($handle, "\t");
                    //ITEMURL
                    fwrite($handle, $product->getProductUrl());
                    fwrite($handle, "\t");
                    //ITEMLINEID
                    fwrite($handle, $itemlineid++);
                    fwrite($handle, "\t");
                    //ZIP
                    $shippingAddress = $order->getShippingAddress();
                    if ($shippingAddress){
                        fwrite($handle, $shippingAddress->getPostcode());
                    }
                    fwrite($handle, "\t");
                    //FIRSTNAME
                    $name = explode(' ', $order->getCustomerName());
                    fwrite($handle, $name[0]);
                    fwrite($handle, "\t");
                    //LASTNAME
                    if (isset($name[1])) {
                        fwrite($handle, $name[1]);
                    }
                    fwrite($handle, "\t");
                    //SKU
                    fwrite($handle, $product->getSku());
                    fwrite($handle, "\t");
                    //PRICE
                    fwrite($handle, $item->getOriginalPrice());
                    fwrite($handle, "\t");
                    //ITEMIMAGEURL
                    if ($product->getImage() != null && $product->getImage() != "no_selection") {
                        fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()));
                    } else if ($product->getSmallImage() != null && $product->getSmallImage() != "no_selection") {
                        fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getSmallImage()));
                    } else if ($product->getThumbnail() != null && $product->getThumbnail() != "no_selection") {
                        fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail()));
                    }
                    fwrite($handle, "\t");

                    //DELIVERYDATE
                    $shipDate = $this->getDateOfShipmentContainingItem($order, $item);
                    if ($shipDate != null) {
                        fwrite($handle, $shipDate->toString('Y-MM-d'));
                    }

                    fwrite($handle, "\n");
                }
            }
            $orders->clear();
        }

        fclose($handle);
    }

    private function getDateOfShipmentContainingItem($order, $item) {
        // get the shipments for this order
        $shipments = $order->getShipmentsCollection();
        foreach ($shipments as $shipment) {
            // get the items in this shipment
            $items = $shipment->getItemsCollection();
            foreach ($items as $it) {
                // check if this shipment contains the item that was passed in
                if ($item->getId() == $it->getOrderItemId()) {
                    return $shipment->getCreatedAtDate();
                }
            }
        }

        return null;
    }

    public function pushHistoricalOrdersFeed($store) {
        $path = Mage::getBaseDir('media') . DS . 'turnto/';
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }

        $logFile = 'turnto_historical_feed_job.log';

        //Mage::log('Started pushHistoricalOrdersFeed', null, $logFile);

        try {
            // delete the old files
            $path = Mage::getBaseDir('media') . DS . 'turnto/';
            array_map('unlink', glob($path . '/magento_auto_histfeed-*.tsv'));

            $fileName = 'magento_auto_histfeed-'.microtime(true).'-'.$store->getId().'.tsv';
            $storeId = $store ? $store->getId() : 1;
            $this->generateHistoricalOrdersFeed("-2 days", $storeId, $fileName);

            $file = $path . $fileName;
            $siteKey = Mage::getStoreConfig('turnto_admin/general/site_key', $store);
            $authKey = Mage::getStoreConfig('turnto_admin/general/site_auth', $store);
            $baseUrl = Mage::getStoreConfig('turnto_admin/general/url', $store);
            if (!$baseUrl) {
                $baseUrl = "http://www.turnto.com";
            }
            $url = $baseUrl . "/feedUpload/postfile";
            $feedStyle = "tab-style.1";

            //Mage::log('Filename: "' . $fileName . '"', null, $logFile);
            //Mage::log('Store Id: "' . $storeId . '"', null, $logFile);
            //Mage::log('siteKey: "' . $siteKey . '"', null, $logFile);
            //Mage::log('authKey: "' . $authKey . '"', null, $logFile);

            if (!$siteKey || !$authKey) {
                //Mage::log('No siteKey or authKey found in configuration', null, $logFile);
                return;
            }

            if (!file_exists($file)) {
                //Mage::log('Could not find the newly created historical feed file. Are the write permission correct on /media/turnto?', null, $logFile);
                return;
            }

            //Mage::log("File size: " . filesize($file) . ' bytes', null, $logFile);

            $curlFile = '@' . $file;
            if (function_exists('curl_file_create')) {
                // required for php 5.5+
                $curlFile = curl_file_create($file);
            }
            $fields = array('siteKey' => $siteKey, 'authKey' => $authKey, 'feedStyle' => $feedStyle, 'file' => $curlFile);

            //Mage::log('Attempting to post file to ' . $url, null, $logFile);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

            $response = curl_exec($ch);
            $errNo = curl_error($ch);
            //Mage::log('Response from server (error: ' . $errNo . '): ' . $response, null, $logFile);
            curl_close($ch);

            //Mage::log('Ended pushHistoricalOrdersFeed', null, $logFile);
        } catch (Exception $e) {
            //Mage::log('Exception caught: ' . $e->getMessage(), null, $logFile);
        }
    }


    public function pushCatalogFeed($store) {
        $path = Mage::getBaseDir('media') . DS . 'turnto/';
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }

        $logFile = 'turnto_catalog_feed_job.log';

        //Mage::log('Started pushCatalogFeed for store ' . $store->getId(), null, $logFile);

        try {
            // delete the old files
            $path = Mage::getBaseDir('media') . DS . 'turnto/';
            array_map('unlink', glob($path . '/magento_auto_catalog_feed-*'.$store->getId().'.tsv'));

            $fileName = 'magento_auto_catalog_feed-'.microtime(true).'-'.$store->getId().'.tsv';
            $storeId = $store ? $store->getId() : 1;
            $websiteId = $store ? $store->getWebsiteId() : 1;
            //Mage::log('Generating catalog...', null, $logFile);
            $this->generateCatalogFeed($websiteId, $storeId, $fileName);
            //Mage::log('Done generating catalog', null, $logFile);

            $file = $path . $fileName;
            $siteKey = Mage::getStoreConfig('turnto_admin/general/site_key', $store);
            $authKey = Mage::getStoreConfig('turnto_admin/general/site_auth', $store);
            $baseUrl = Mage::getStoreConfig('turnto_admin/general/url', $store);
            if (!$baseUrl) {
                $baseUrl = "http://www.turnto.com";
            }
            $url = $baseUrl . "/feedUpload/postfile";
            $feedStyle = "tab-style.1";

            //Mage::log('Filename: "' . $fileName . '"', null, $logFile);
            //Mage::log('Store Id: "' . $storeId . '"', null, $logFile);
            //Mage::log('siteKey: "' . $siteKey . '"', null, $logFile);
            //Mage::log('authKey: "' . $authKey . '"', null, $logFile);

            if (!$siteKey || !$authKey) {
                //Mage::log('No siteKey or authKey found in configuration', null, $logFile);
                return;
            }

            if (!file_exists($file)) {
                //Mage::log('Could not find the newly created catalog feed file. Are the write permission correct on /media/turnto?', null, $logFile);
                return;
            }

            //Mage::log("File size: " . filesize($file) . ' bytes', null, $logFile);

            $curlFile = '@' . $file;
            if (function_exists('curl_file_create')) {
                // required for php 5.5+
                $curlFile = curl_file_create($file);
            }
            $fields = array('siteKey' => $siteKey, 'authKey' => $authKey, 'feedStyle' => $feedStyle, 'file' => $curlFile);

            //Mage::log('Attempting to post file to ' . $url, null, $logFile);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

            $response = curl_exec($ch);
            $errNo = curl_error($ch);
            $curlInfo = curl_getinfo($ch);
            //Mage::log('Response from server (error: ' . $errNo . '): ' . $response, null, $logFile);
            //Mage::log('Additional response info: ' . print_r($curlInfo, true), null, $logFile);
            curl_close($ch);

            //Mage::log('Ended pushCatalogFeed', null, $logFile);
        } catch (Exception $e) {
            //Mage::log('Exception caught: ' . $e->getMessage(), null, $logFile);
        }
    }
}
