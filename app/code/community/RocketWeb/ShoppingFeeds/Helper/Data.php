<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_ShoppingFeeds_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_ENABLE_MAGENTO_CRON = 'rocketweb_shoppingfeeds/general/enable_mage_cron';

    protected $_store_categories = array();

    /**
     * Checks if a module is enabled or not
     * @param $module_namespace
     * @return bool
     */
    public function isModuleEnabled($module_namespace = null)
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        return isset($modulesArray[$module_namespace]) && $modulesArray[$module_namespace]->active == "true";
    }

    public function getFeedForFrontend($store_id) {

    }

    /**
     * Singleton by $feed_id of generator class
     *
     * @param RocketWeb_ShoppingFeeds_Model_Feed $feed
     * @return mixed
     */
    public function getGenerator(RocketWeb_ShoppingFeeds_Model_Feed $feed)
    {
        $registryKey = '_singleton/rocketshoppingfeeds/generator_feed_' . $feed->getId();

        if (!Mage::registry($registryKey)) {
            $generator = Mage::getModel('rocketshoppingfeeds/generator', array('feed' => $feed));
            Mage::register($registryKey, $generator);
        }

        return Mage::registry($registryKey);
    }

    /**
     * Translates the feed type into config location
     *
     * @param $type
     * @return string
     */
    public function getFeedConfigPath($type)
    {
        return Mage::getModuleDir('etc', 'RocketWeb_ShoppingFeeds'). DS. 'feeds'. DS. $type. '.xml';
    }

    /**
     * $string = preg_replace_callback('/\\\\u(\w{4})/', array(Mage::helper('rocketshoppingfeeds'), 'jsonUnescapedUnicodeCallback'), $string);
     * php 5.2 alternative to JSON_UNESCAPED_UNICODE
     *
     * @param $matches
     * @return string
     */
    public function jsonUnescapedUnicodeCallback($matches) {
        return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
    }

    /**
     * if (extension_loaded('mbstring')) {
     *    $string = preg_replace_callback("/(&#?[a-z0-9]{2,8};)/i", array(Mage::helper('rocketshoppingfeeds'), 'htmlEntitiesToUtf8Callback'), $string);
     * }
     *
     * @param $matches
     * @return string
     */
    public function htmlEntitiesToUtf8Callback($matches) {
        return mb_convert_encoding($matches[1], "UTF-8", "HTML-ENTITIES");
    }

    /**
     * @param RocketWeb_ShoppingFeeds_Model_Feed $feed
     * @return array
     */
    public function getAllCategories(RocketWeb_ShoppingFeeds_Model_Feed $feed, $onlyIds = false, $fetchAll = false)
    {
        $store_id = $feed->hasData('store_id') ? $feed->getStoreId() : 0;
        $rootId = $store_id ? Mage::app()->getStore($store_id)->getRootCategoryId() : 0;
        $cacheKey = sprintf('%s_%s', $store_id, $onlyIds ? 1 : 0);

        if (!array_key_exists($cacheKey, $this->_store_categories)) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('level')
                ->addAttributeToSort('path', 'asc')
                ->addAttributeToFilter('name', array('neq' => ''));

            // Flat catalogs do not have setStore method, store filter shoul dbe covered by $rootIf filter in category path
            if ($fetchAll === false) {
                if (method_exists($collection, 'setStore')) {
                    $collection->setStore($store_id);
                }

                if ($feed->hasData('category_depth')) {
                    $collection->addAttributeToFilter('level', array('lt' => $feed->getData('category_depth')));
                }
                if ($rootId > 0) {
                    $collection->addAttributeToFilter(array(
                        array('attribute' => 'path', 'like' => "1/$rootId/%"),
                        array('attribute' => 'path', 'eq' => "1/$rootId")
                        ));
                }
                $collection->addAttributeToFilter('is_active', 1);
            }

            $categories = $onlyIds ? $collection->getAllIds() : $collection->load()->toArray();
            $this->_store_categories[$cacheKey] = $categories;
        }

        return $this->_store_categories[$cacheKey];
    }

    /**
     * This method returns a string containing the category of a
     * product according to the path of the category starting from
     * the root category (if the product has it assigned), up to the
     * specific category assigned to the product.
     * e.g.: Home > Garden > Flowers > Roses
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     * @author RocketWeb
     */
    public function getProductCategoryTree(Mage_Catalog_Model_Product $product, RocketWeb_ShoppingFeeds_Model_Feed $feed, $allowedCategories = null)
    {
        $product = $product->load($product->getId());
        $collection = $product->getCategoryCollection()->addFieldToFilter('is_active', 1);

        if (is_array($allowedCategories)) {
            $collection->addFieldToFilter('entity_id', array('in' => $allowedCategories));
        }

        $categories = $collection->exportToArray();
        if (empty($categories)) {
            return '';
        }

        $categoriesOrdered = array_map(function($v) use ($categories) {return $categories[$v];}, $allowedCategories);
        $categories = array_filter($categoriesOrdered);
        $removes = array('default', 'root');

        /*
         * Build category path for each of product categories
         */
        $return = array();
        foreach ($categories as $cat_info) {

            $names = array();
            // Loop through each items of the path
            $pItemsPieces = explode('/', $cat_info['path']);

            foreach ($pItemsPieces as $id) {

                if (!array_key_exists($id, $this->_cache_categories)) {
                    $categories = $this->getAllCategories($feed, false, true);
                    $category = $categories[$id];
                    $this->_cache_categories[$id] = trim($category['name']);
                }
                $categoryName = $this->_cache_categories[$id];

                if (empty($categoryName)) {
                    continue;
                }

                $skip_node = false;
                foreach ($removes as $value) {
                    if (strstr(strtolower($categoryName), strtolower(trim($value))) !== false) {
                        $skip_node = true;
                    }
                }

                if (!$skip_node) {
                    array_push($names, $categoryName);
                }
            }

            // Implode the result items
            $return[implode(' > ', $names)] = count($names);
        }

        // do not sort here as they are sorted by priorities
        // arsort($return);
        return array_keys($return);
    }

    /**
     * @param RocketWeb_ShoppingFeeds_Model_Feed $feed
     * @return array
     */
    public function getCategoriesTree($feed)
    {
        $category_tree = array();
        $names = array();

        /**
         * @var Mage_Catalog_Model_Resource_Category_Tree $tree
         */
        $tree = Mage::getModel('catalog/category')->getTreeModel()->load();

        foreach ($this->getAllCategories($feed) as $categ) {

            if (array_key_exists('name', $categ)) {
                $categ['name'] = addslashes($categ['name']);
                $path = array();
                $names[$categ['entity_id']] = $categ['name'];
                $node = $tree->getNodeById($categ['entity_id']);
                if (method_exists($node, 'getPath')) {
                    foreach ($node->getPath() as $item) {
                        if ($item->getLevel() > 1 && array_key_exists($item->getId(), $names)) {
                            array_unshift($path, $names[$item->getId()]);
                        }
                    }
                }
                $category_tree[$categ['entity_id']] = implode(' > ', $path);
            }
        }

        return $category_tree;
    }

    /**
     * Rerun the most or less expensive associated product map object.
     *
     * @param $assocAdapers array of product map objects
     * @param string $direction (max/min)
     * @return mixed
     */
    public function sortAssocsByPrice($assocAdapers, $direction = 'max')
    {
        if (!in_array($direction, array('max', 'min'))) {
            throw new RocketWeb_ShoppingFeeds_Model_Exception(
                $this->__('Invalid sort $direction provided in RocketWeb_GoogleBaseFeedGenerator_Helper_Data::sortAssocsByPrice()')
            );
        }
        $assoc = null;

        if (!empty($assocAdapers)) {
            $sum =  ($direction == 'max') ? 0 : PHP_INT_MAX;
            foreach ($assocAdapers as $adapter) {
                $adapter_prices = $adapter->getPrices();
                $price = $adapter_prices['p_excl_tax'];
                if (($direction == 'max' && $price > $sum)
                    || ($direction == 'min' && $price < $sum)) {
                    $sum = $price;
                }
                if (($direction == 'max' && $price >= $sum)
                    || ($direction == 'min' && $price <= $sum)) {
                    $assoc = $adapter;
                }

            }
        }
        return $assoc;
    }

    /**
     * Gets the category root id based on feed store
     *
     * @param RocketWeb_ShoppingFeeds_Model_Feed $feed
     * @return int
     */
    public function getRootCategoryId(RocketWeb_ShoppingFeeds_Model_Feed $feed)
    {
        $storeId = $feed->hasData('store_id') ? $feed->getStoreId() : $this->getDefaultStoreId();
        $rootId = $storeId ? Mage::app()->getStore($storeId)->getRootCategoryId() : 0;
        return $rootId;
    }

    /**
     * Returns the first store_id it finds.
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        $ids = array_keys(Mage::app()->getStores());
        return count($ids) ? $ids[0] : 0;
    }

    /*
     * Create directory
     *
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function initSavePath($path)
    {
        $ioAdapter = new Varien_Io_File();
        if (!is_dir($path)) {
            $ioAdapter->mkdir($path);
            if (!is_dir($path)) {
                Mage::throwException(sprintf('Not enough permissions, can\'t create dir [%s].', $path));
            }
        }
        return $this;
    }

    /**
     * Calculate the quantity increments including minimal sale quantity
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getQuantityIcrements(Mage_Catalog_Model_Product $product)
    {
        $qtyIncrements = 1.0;
        /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
        $stockItem = $product->getStockItem();
        if ($stockItem) {
            if ($stockItem->getData('use_config_min_sale_qty') != 1 && $stockItem->getData('min_sale_qty')) {
                $qtyIncrements = $stockItem->getData('min_sale_qty');
            }

            if ($stockItem->getData('enable_qty_increments')) {
                if ($qtyIncrements > 1.0) {
                    $qtyIncrementsTmp = $stockItem->getData('qty_increments');
                    if ($qtyIncrements % $qtyIncrementsTmp != 0) {
                        $nextIncrement = ceil($qtyIncrements / $qtyIncrementsTmp);
                        $qtyIncrements = $nextIncrement * $qtyIncrementsTmp;
                    }
                } else {
                    $qtyIncrements = $stockItem->getData('qty_increments');
                }
            }
        }
        return $qtyIncrements;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasMsrp(Mage_Catalog_Model_Product $product)
    {
        $catalogHelper = Mage::helper('catalog');
        return method_exists($catalogHelper, 'canApplyMsrp')
        && $catalogHelper->canApplyMsrp($product)
        && $product->hasMsrp()
        && ($product->getPrice() < $product->getMsrp());
    }

    /**
     * Returns the memory limit in bytes ??
     * @return float
     */
    public function getMemoryLimit()
    {
        $memory = ini_get('memory_limit');
        if (is_numeric($memory) && $memory <= 0) {
            return memory_get_usage(true) * 1.5;
        }

        if (!is_numeric($memory)) {
            preg_match('/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $memory, $matches);
            $num = (float)$matches[1];
            switch (strtoupper($matches[2])) {
                case 'E':
                    $num = $num * 1024;
                case 'P':
                    $num = $num * 1024;
                case 'T':
                    $num = $num * 1024;
                case 'G':
                    $num = $num * 1024;
                case 'M':
                    $num = $num * 1024;
                case 'K':
                    $num = $num * 1024;
            }
            $memory = $num;
        }
        return $memory;
    }

    /**
     * @param $array
     * @param string $node
     * @param bool|true $addCdata
     * @return string
     */
    public function arrayToXml($array, $node = 'root', $addCdata = true)
    {
        $xmlModel = new Varien_Simplexml_Element('<node></node>');
        $xml = '<'.$node.'>'. "\n";
        foreach ($array as $fieldName => $fieldValue) {
            $fieldValue = is_array($fieldValue) ? serialize($fieldValue) : $fieldValue;
            $fieldValue = $addCdata ? "<![CDATA[$fieldValue]]>" : $xmlModel->xmlentities($fieldValue);
            $xml.= "\t<$fieldName>$fieldValue</$fieldName>\n";
        }
        $xml .= '</'.$node.'>'. "\n";
        return $xml;
    }

    /**
     * Get last execution time
     *
     * @param $jobCode
     * @return bool
     */
    public function cronHasHeartbeat($jobCode = 'rw_feeds_queue')
    {
        $schedules = Mage::getModel('cron/schedule')->getCollection(); /* @var $schedules Mage_Cron_Model_Mysql4_Schedule_Collection */
        $schedules->getSelect()->limit(1)->order('executed_at DESC');
        $schedules->addFieldToFilter('status', 'success');
        $schedules->addFieldToFilter('job_code', $jobCode);
        $schedules->load();
        if (count($schedules) == 0) {
            return false;
        }
        $executedAt = $schedules->getFirstItem()->getExecutedAt();
        $diff = time() - strtotime($executedAt);
        return (time() - strtotime($executedAt) <= 1800);
    }

    public function isCronEnabled() {
        return Mage::getStoreConfig(self::XML_PATH_ENABLE_MAGENTO_CRON);
    }

    /**
     * Do ftp upload or check connection if path is not specified
     * 
     * @param Varien_Object $account
     * @param boolean $passwordEncrypted
     * @param boolean | string $ftpPath
     * @return string | boolean
     */
    public function ftpUpload($account, $passwordEncrypted = false, $ftpPath = false)
    {
        $error = false;
        $params = array(
            'passive'   => true,
            'path'      => ($account->getPath() ? $account->getPath() : ''),
        );
        if ($account->getMode() == RocketWeb_ShoppingFeeds_Model_Source_Ftp::SFTP_MODE) {
            $params = array_merge($params, $this->_prepareSftpParams($account));
            $uploader = new RocketWeb_ShoppingFeeds_Model_Sftp();
        } else {
            $params = array_merge($params, $this->_prepareFtpParams($account));
            $uploader = new Varien_Io_Ftp();
        }
        if ($passwordEncrypted) {
            $params['password'] = Mage::helper('core')->decrypt($account->getPassword());
        } else {
            $params['password'] = $account->getPassword();
        }
        if ($account->hasTimeout()) {
            $params['timeout'] = $account->getTimeout();
        }
        try {
            $uploader->open($params);
            if ($ftpPath = $this->_gzipFeed($ftpPath, $account)) {
                $write_result = $uploader->write(basename($ftpPath), $ftpPath);
                if ($write_result === false) {
                    throw new Exception('Cannot write to FTP server, check path and permissions');
                }
            }
            $uploader->close();
        } catch (Exception $e) {
            $error = 'Cannot process ftp upload for host="' . $account->getHost(). '". An error "' .
                $e->getMessage() . '" occurred';
        }
        return $error === false ? true : $error;
    }

    /**
     * Prepares FTP specific parameters
     * 
     * @param Varien_Object $account
     * @return array
     */
    protected function _prepareFtpParams($account)
    {
        return array(
            'host'      => $account->getHost(),
            'port'      => $account->getPort(),
            'user'      => $account->getUsername(),
        );
    }

    /**
     * Prepares SFTP specific parameters
     * 
     * @param Varien_Object $account
     * @return array
     */
    protected function _prepareSftpParams($account)
    {
        return array(
            'host'      => $account->getHost() . ':' . $account->getPort(),
            'username'  => $account->getUsername(),
        );
    }

    /**
     * Gzips feed and returns path to archive if configured so
     * 
     * @param string $feedPath
     * @param Varien_Object $account
     * @return string | false
     */
    protected function _gzipFeed($feedPath, $account)
    {
        if (!$account->getGzip()) {
            return $feedPath;
        }
        $newFeedPath = $feedPath . '.gz';
        $error = false; 
        if ($gzipResource = gzopen($newFeedPath, 'wb')) { 
            if ($readResource = fopen($feedPath, 'rb')) { 
                while (!feof($readResource)) {
                    gzwrite($gzipResource, fread($readResource, 1024 * 512)); 
                }
                fclose($readResource); 
            } else {
                $error = true; 
            }
            gzclose($gzipResource); 
        } else {
            $error = true; 
        }
        if ($error) {
            Mage::throwException('Cannot gzip feed file');
        }
        return $newFeedPath;
    }

    /**
     * Test if currency rates are scheduled to update by cron.
     *
     * @return bool
     */
    public function isScheduledCurrencyRateUpdateEnabled()
    {
        return Mage::getConfig(Mage_Directory_Model_Observer::IMPORT_ENABLE);
    }
}
