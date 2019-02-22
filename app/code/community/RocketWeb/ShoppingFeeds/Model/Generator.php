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

/**
 * Class RocketWeb_ShoppingFeeds_Model_Generator
 *
 * @method RocketWeb_ShoppingFeeds_Model_Feed getFeed()
 * @method boolean hasFeed()
 */
class RocketWeb_ShoppingFeeds_Model_Generator extends Varien_Object
{

    const PRODUCT_TYPE_ASSOC = 'simple_associated';

    const DEFAULT_BATCH_MODE = 0;
    const DEFAULT_BATCH_LIMIT = 5000;

    protected $_lockFile;
    protected $_count_products_exported = -1;
    protected $_count_products_skipped = 0;

    protected $_collection = null;
    protected $_current_iter = 0;

    protected $_hooks = array();
    protected $_hooks_inited = false;

    protected function _construct()
    {
        if (!$this->hasFeed() || !($this->getFeed() instanceof RocketWeb_ShoppingFeeds_Model_Feed)) {
            Mage::throwException('Generator cannot be initialized without a valid Feed object.');
        }

        $this->addData(array(
            'store_id'              => $this->getFeed()->getStoreId(),
            'website_id'            => $this->getFeed()->getStore()->getWebsiteId(),
            'store_currency_code'   => $this->getFeed()->getStore()->getDefaultCurrencyCode(),
            'batch_mode'            => $this->getFeed()->getSchedule()->getBatchMode(),
            'batch_limit'           => $this->getFeed()->getSchedule()->getBatchLimit(),
            'started_at'            => time(),
            'progress_timing'       => Mage::getModel('core/date')->timestamp(time()),
        ));

        // Initialize locks, skip locks is used with PDP microdata
        if (!$this->getSkipLocks()) {
            Mage::helper('rocketshoppingfeeds')->initSavePath(dirname($this->getFeedPath()));

            $this->_lockFile = @fopen($this->getLockPath(), "w");
            if (!file_exists($this->getLockPath())) {
                Mage::throwException(sprintf('Can\'t create file %s', $this->getLockPath()));
            }

            // If the location is not writable, flock() does not work and it doesn't mean another script instance is running
            if (!is_writable($this->getLockPath())) {
                Mage::throwException(sprintf('Not enough permissions. Location [%s] must be writable', $this->getLockPath()));
            }
        }
    }

    protected function initialize()
    {
        $this->_current_iter = 0;
        $this->loadAdditionalAttributes();
        $maxProductPrice = (float)$this->getFeed()->getConfig('filters_skip_price_above');
        if ($maxProductPrice > 0) {
            $this->setMaxProductPrice($maxProductPrice);
        }
        $minProductPrice = (float)$this->getFeed()->getConfig('filters_skip_price_below');
        if ($minProductPrice > 0) {
            $this->setMinProductPrice($minProductPrice);
        }
        return $this;
    }

    /**
     * Also check in config for the current feed
     *
     * @return bool
     */
    public function getSkipLog() {
        $data = $this->getData('skip_log');
        if (!$data) {
            $data = Mage::getStoreConfig('rocketweb_shoppingfeeds/general/skip_log');
        }
        return $data;
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Batch
     */
    public function getBatch()
    {
        if ($this->inBatch() && !$this->hasData('batch')) {
            if (!$this->getScheduleId()) {
                Mage::throwException(sprintf('Invalid schedule_id %s', $this->getScheduleId()));
            }
            $this->setData('batch', Mage::getModel(
                'rocketshoppingfeeds/batch', array(
                    'generator' => $this,
                    'schedule_id' => $this->getScheduleId(),
                )
            ));
        }

        return $this->getData('batch');
    }

    /**
     * @return $this
     */
    public function run()
    {
        $time   = Mage::getModel('core/date')->timestamp(time());
        $memory = memory_get_usage(true);

        // Another instance is writing to the feed
        if (!$this->acquireLock()) {
            Mage::throwException(sprintf('Another generator instance is writing the file for [%s]. Try again later.', $this->getFeed()->getName()));
        }

        // Attempt to run a full feed when batch not finished
        if (!$this->inBatch() && $this->batchInProgress()) {
            Mage::throwException(sprintf('Batch generation is in progress. Wait for the batch to finish or force this action by removing [%s]', $this->getBatchLockPath()));
        }


        if ($completedForToday = $this->inBatch() ? $this->getBatch()->completedForToday() : false) {
            Mage::throwException(sprintf('[%s] Feed Completed for Today %s! Wait till tomorrow or remove lock file: %s', $this->getScheduleId(), date('Y-m-d'), $this->getBatchLockPath()));
        }

        $this->log('START', Zend_Log::ALERT);
        if ($this->getData('verbose')) {
            session_start(); // fix for magento 1.4 complaining abut headers. Not sure why 1.4 initiates the session
        }

        // Run any custom pre-generation processes
        $this->runHook('pre');

        $this->initialize();

        if ($this->inBatch()) {

            $this->getBatch()->setData('verbose', $this->getData('verbose'));
            $this->getBatch()->setTotalItems($this->getTotalItems());
            $batch_limit = ($this->getBatchLimit() <= $this->getTotalItems() ? $this->getBatchLimit() : $this->getTotalItems());
            $this->getBatch()->setLimit($batch_limit);

            // Lock cleanup
            $locked = $this->getBatch()->aquireLock();
            if (!$locked && !$completedForToday) {
                $this->log(sprintf('Previous batch did not complete. Clearing lock file %s', $this->getBatchLockPath()), Zend_Log::WARN);
                @unlink($this->getBatchLockPath());
                $this->getBatch()->lock();
            }

            if (!$this->getBatch()->getIsNew()) {
                $data = $this->getBatch()->readFile();
                $this->_current_iter = (int) $this->getBatch()->getOffset() - $this->getBatch()->getLimit();
                $this->_count_products_exported = (int) $data['items_added'];
                $this->_count_products_skipped = (int) $data['items_skipped'];
            }
        }

        $collection = $this->getCollection();
        if (!$this->inBatch() || ($this->inBatch() && $this->getBatch()->getIsNew())) {
            $this->writeFeed($this->getHeader(), false);
            // Clear processes every time but when batch mode and queue not completed
            $this->getTools()->clearProcess();
        }

        $product_types = $this->getFeed()->getConfig('filters_product_types');

        Mage::getSingleton('core/resource_iterator')->walk(
            $collection->getSelect(), array(array($this, 'processProductCallback')), array(
                'product_types' => $product_types,
            )
        );

        $this->closeTemporaryHandle()
            ->copyDataFromTemporaryFeedFile()
            ->setFeedFilePermissions()
            ->releaseLock();

        if ($this->getData('verbose')) {
            echo "---------------------------------------------------------------------\n";
        }
        $this->log(sprintf('Items: %d added, %d skipped | in file %s', $this->getCountProductsExported(), $this->getCountProductsSkipped(), $this->getFeedPath()), Zend_Log::ALERT);

        if (!$this->getBatch() || $this->getBatch()->completedForToday()) {
            $this->runFtpUpload();
        }

        $t = round(Mage::getModel('core/date')->timestamp(time())-$time);
        $this->log('END / MEMORY USED: ' . $this->formatMemory(memory_get_usage(true) - $memory). ', TIME SPENT: '. sprintf('%02d:%02d:%02d', ($t/3600),($t/60%60), $t%60), Zend_Log::ALERT);

        return $this;
    }

    /**
     * If we have FTP accounts configured, run the uploads
     */
    public function runFtpUpload() {
        $ftpAccounts =  Mage::getResourceModel('rocketshoppingfeeds/feed_ftp_collection')
            ->addFeedFilter($this->getFeed()->getId())
            ->load();

        $no_of_accounts = $ftpAccounts->count();
        $plural = ($no_of_accounts > 1) ? 's' : '';
        if ($no_of_accounts > 0) {

            if ($this->getSkipFtp()) {
                $this->log('Ftp upload skipped');
                return;
            }

            $this->log('Uploading feed to '.$no_of_accounts.' connection'.$plural);

            $errors = array();
            foreach ($ftpAccounts as $account) {
                $result = Mage::helper('rocketshoppingfeeds')->ftpUpload($account, true, $this->getFeedPath());
                if ($result !== true) {
                    $errors[] = $result;
                }
            }

            $hook_errors = $this->runHook('ftp', array('feed' => $this->getFeed(), 'accounts' => $ftpAccounts));

            if (empty($errors) && empty($hook_errors)) {
                $this->log('Feed was successfully uploaded to '.$no_of_accounts.' FTP account'.$plural);
            } else {
                if (is_array($errors)) foreach ($errors as $error) {
                    $this->log($error);
                }
                if (is_array($hook_errors)) foreach ($hook_errors as $error) {
                    $this->log($error);
                }
            }
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProduct($id)
    {
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getStoreId())
            ->setId($id);

        $product->getResource()->load($product, $id);

        return $product;
    }

    /**
     * Used on PDP microdata
     * @deprecated
     *
     * @param $product Mage_Catalog_Model_Product
     * @return array
     */
    public function generateProductMap($product)
    {
        $productMap = $this->getProductAdapterModel($product)
            ->setColumnsMap($this->getColumnsMap())
            ->setFeed($this->getFeed())
            ->setEmptyColumnsReplaceMap($this->getEmptyColumnsReplaceMap())
            ->initialize();

        return $productMap->map();
    }

    /**
     * @param $product
     * @param $args
     *
     * @deprecated
     * @see RocketWeb_ShoppingFeeds_Helper_Factory::getProductAdapterModel and use instead
     */
    public function getProductAdapterModel($product, $args) {
        if (isset($args['feed']) && ($args['feed'] instanceof RocketWeb_ShoppingFeeds_Model_Feed)) {
            $feed = $args['feed'];
        } else {
            $feed = $this->getFeed();
        }

        return Mage::helper('rocketshoppingfeeds/factory')
            ->getProductAdapterModel($product, $feed, $args);
    }

    /**
     * @param $args
     */
    public function processProductCallback($args)
    {
        $row = $args['row'];
        $parentType = '';

        // Skip if product type is not enabled
        if (!$this->isProductTypeEnabled($row['type_id'])) {
            return;
        }

        $productParents = $this->getProductParents($row);

        // Memorise possible duplicate items and skip current simple product
        if (!$this->getTestMode() && $this->getTools()->lockDuplicates($row, $productParents)) {
            $this->_count_products_skipped++;
        }
        else {
            // Prepare product and map object
            $product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId())->load($row['entity_id']);

            $productAdapter = Mage::helper('rocketshoppingfeeds/factory')
                ->getProductAdapterModel($product, $this->getFeed(),
                    array(
                        'parents' => $productParents,
                        'parent_type' => $parentType
                    )
                );

            $this->addProductToFeed($productAdapter);
        }

        if ($this->getData('verbose')) {
            echo $this->formatMemory(memory_get_usage(true)) . " - SKU " . $args['row']['sku'] . ", ID " . $args['row']['entity_id'] . "\n";
        }
        $this->_current_iter++;
        $this->logProgress();

        // Free up memory
        $this->getTools()->clearNestedObject($product);
        $this->getTools()->unsConfigurableAttributesAsArray($product);

        if ($this->isCloseToPhpLimit()) {
            // Automatically swicth to batch mode
            if (!$this->inBatch()) {
                $this->log('Automatic switch to batch mode.');
                $this->setBatchMode(1);

                $this->getFeed()->getSchedule()->addData(array(
                    'batch_mode' => 1,
                    'batch_limit' => $this->_current_iter)
                )->save();

                $this->getBatch()->aquireLock();
            }

            // Terminating batch early
            if ($this->inBatch()) {
                $this->getBatch()->updateLockOffset($this->_current_iter);
                $this->releaseLock();
            }
            // Exit the iterator but don't set the feed as failed
            throw new RocketWeb_ShoppingFeeds_Model_Exception('EARLY END / PHP Limits reached');
        }

        unset($product, $productAdapter, $row);
    }

    /**
     * @return $this
     */
    public function logProgress()
    {
        // Get correct magento hour
        $time = new Zend_Date(Mage::getModel('core/date')->timestamp(time()), Zend_Date::TIMESTAMP);

        if ($time->get(Zend_Date::TIMESTAMP) - $this->getProgressTiming() > 15
            || $this->_current_iter <= 1
            || $this->_current_iter == $this->getTotalItems()
            || ($this->inBatch() && ($this->_current_iter % $this->getBatch()->getLimit() == 0 || $this->isCloseToPhpLimit())))
        {
            $percent = sprintf('%d', round($this->_current_iter / $this->getTotalItems() * 100));
            if (!$this->getTestMode()) {
                $this->getFeed()->saveMessages(array(
                    'date' => $time->get(Zend_Date::ISO_8601),
                    'progress' => $percent,
                    'added' => $this->getCountProductsExported(),
                    'skipped' => $this->getCountProductsSkipped()
                ));
            }
            $this->log(sprintf("Processed %s", $percent). '%');
            $this->setProgressTiming($time->get(Zend_Date::TIMESTAMP));
        }
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    public function getCollection()
    {
        if (is_null($this->_collection)) {
            $this->_collection = clone $this->_getCollection();

            if ($this->inBatch()) {
                $this->_collection->getSelect()->limit($this->getBatch()->getLimit(), $this->getBatch()->getOffset() - $this->getBatch()->getLimit());
            }
            elseif ($this->getTestMode())
            {
                $this->log('Running feed in test mode');

                if ($this->getTestSku()) {
                    $sku = $this->getTestSku();
                    $search = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter(
                        array(
                            array('attribute' => 'sku', 'eq' => $sku),
                            array('attribute' => 'entity_id', 'eq' => $sku)
                        )
                    );
                    /** @var Mage_Catalog_Model_Product $prod */
                    if ($prod = $search->getFirstItem()) {
                        $prod->load($prod->getId());
                        if (!$prod->isVisibleInSiteVisibility()) {
                            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($prod->getId());
                            if(!$parentIds) {
                                $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($prod->getId());
                            }
                            if (count($parentIds)) {
                                $prod->load($parentIds[0]);
                                $this->setMessages(array(array('msg' => 'Product entered is not visible in the store on i\'s own. Running test against it\'s parent '. $prod->getTypeId().' ID '. $prod->getId(), 'type' => 'info')));
                            }
                        }
                        $this->_collection->addAttributeToFilter('entity_id', $prod->getId());
                    }
                }
                elseif ($this->getTestOffset() >= 0 && $this->getTestLimit() > 0) {
                    $this->_collection->getSelect()->limit(($this->getTestLimit() > 0 ? $this->getTestLimit() : 0), ($this->getTestOffset() > 0 ? $this->getTestOffset() : 0));
                } else {
                    Mage::throwException(sprintf("Invalid parameters for test mode: sku %s or offset %s and limit %s", $this->getTestSku(), $this->getTestOffset(), $this->getTestLimit()));
                }
            }
        }
        return $this->_collection;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _getCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStore($this->getFeed()->getStoreId())
            ->addStoreFilter($this->getFeed()->getStoreId());

        $this->addProductTypeToFilter($collection);

        // Filter visible / enabled products
        $collection->addAttributeToFilter('status', array('neq' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED));
        $collection->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $includeAllProducts = $this->getFeed()->getConfig('categories_include_all_products');
        $categoryMap = $this->getFeed()->getConfig('categories_provider_taxonomy_by_category');

        // include the root categoryId (a.k.a. default category) by default so that products assigned to it exclusively aren't excluded
        $excludeCategories = array();

        foreach ($categoryMap as $category) {
            if (isset($category['id']) && isset($category['d']) && (bool)$category['d'] === true) {
                $excludeCategories[] = (int)$category['id'];
            }
        }

        $collection = $this->_addCategoriesToFilter($collection, $excludeCategories, (bool)$includeAllProducts);

        $cfg = $this->getFeed()->getConfig('filters_attribute_sets');
        if (count($cfg) && empty($cfg[0])) {
            array_shift($cfg);
        }
        $attribute_sets = !empty($cfg) ? $cfg : false;
        if ($attribute_sets) {
            $collection->addAttributeToFilter('attribute_set_id', $attribute_sets);
        }

        if (!$this->getFeed()->getConfig('filters_add_out_of_stock')) {
            $collection->addPriceData(null, $this->getData('website_id'));
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        }

        if (!$this->getTestMode() && Mage::getConfig()->getNode('default/debug/sku') != "") {
            $collection->addAttributeToFilter('sku', Mage::getConfig()->getNode('default/debug/sku'));
        }

        return $collection;
    }

    /**
     * Adds category ids to collection filter, adding join to category-product table if needed
     *
     * @param $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     * @param $categoryIds int[]
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _addCategoriesToFilter($collection, $categoryIds, $includeAllProducts)
    {
        $where = array();

        if (count($categoryIds) > 0 || !$includeAllProducts) {
            $joinCond = 'cat_product.product_id=e.entity_id';
            $fromPart = $collection->getSelect()->getPart(Zend_Db_Select::FROM);

            if (isset($fromPart['cat_product'])) {
                $fromPart['cat_product']['joinCondition'] = $joinCond;
                $collection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
            } else {
                $collection->getSelect()->joinInner(
                    array('cat_product' => $collection->getTable('catalog/category_product')),
                    $joinCond, array()
                );
            }

            // Filter out category roots from other stores
            $rootId = $this->getFeed()->getStore()->getRootCategoryId();
            if ($rootId) {
                $rootpath = Mage::getModel('catalog/category')->setStoreId($this->getStoreId())->load($rootId)->getPath();
                $joinCond2 = 'cat_product.category_id=cat.entity_id';
                $collection->getSelect()->joinInner(
                    array('cat' => $collection->getTable('catalog/category')),
                    $joinCond2, array()
                );
                $where[] = $collection->getConnection()->quoteInto('cat.path LIKE ?', $rootpath.'/%');
            }
        }

        // Filter out specified category ids
        if (!$includeAllProducts) {
            $where[] = 'cat_product.category_id IS NOT NULL';
        }

        if (count($categoryIds) > 0) {
            $cond = $collection->getConnection()->quoteInto('cat_product.category_id NOT IN (' . implode(',', $categoryIds) . ')', "");
            if ($includeAllProducts) {
                $cond .= ' OR cat_product.category_id IS NULL';
            }
            $where[] = $cond;
        }

        if (count($where) > 0) {
            $where = '(' . implode(' AND ', $where) . ')';
            $collection->getSelect()->where($where);
        }

        $collection->getSelect()->group('e.entity_id');
        return $collection;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function addProductTypeToFilter($collection)
    {
        $default_product_types = array(
            Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
            Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
        );

        $product_types = $this->getFeed()->getConfig('filters_product_types');
        $not_in_product_types = array_diff($default_product_types, $product_types);
        $in_product_types = array_diff($product_types, $default_product_types);

        if (count($in_product_types)) {
            $collection->addAttributeToFilter('type_id', array('in' => $product_types));
        }

        if (count($not_in_product_types) > 0) {
            $collection->addAttributeToFilter('type_id', array('nin' => $not_in_product_types));
        }

        return $collection;
    }

    /**
     * Returns columns map in asc order.
     * @deprecated
     * @use RocketWeb_ShoppingFeeds_Model_Feed::getColumnsMap
     * @return array
     */
    public function getColumnsMap()
    {
        return $this->getFeed()->getColumnsMap();
    }

    /**
     * Returns columns map replaced by other attributes when it's value is empty for a product.
     * @deprecated
     * @use RocketWeb_ShoppingFeeds_Model_Feed::getEmptyColumnsReplaceMap
     *
     * @return array
     */
    protected function getEmptyColumnsReplaceMap()
    {
        return $this->getFeed()->getEmptyColumnsReplaceMap();
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Generator
     */
    protected function loadAdditionalAttributes()
    {
        $codes = array('status');
        foreach ($codes as $attribute_code) {
            $this->setAttribute($this->getAttribute($attribute_code));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        $columns = array_keys($this->getFeed()->getColumnsMap());
        return array_combine($columns, $columns);
    }

    /**
     * @param $fields
     * @param bool|true $add_new_line
     */
    protected function writeFeed($fields, $add_new_line = true)
    {
        $params = $this->getWriteFeedParams();
        /**
         * @var $defaultValue
         * @var $delimiter
         * @var $encloseCell
         * @var $encloseEscape
         */
        extract($params);
        $row = array();

        // google error: "Too many column delimiters"
        foreach ($this->getFeed()->getColumnsMap() as $column => $arr) {
            if (isset($fields[$column]) && $fields[$column] == "") {
                $fields[$column] = $defaultValue;
            }
            if ($encloseCell !== false) {
                $value = $fields[$column];
                $value = str_replace($encloseCell, $encloseEscape . $encloseCell, $value);
                $fields[$column] = sprintf('%s%s%s', $encloseCell, $value, $encloseCell);
            }
            $row[] = $fields[$column];
            if (isset($arr['counter']) && $arr['counter'] > 1) {
                for ($i = 1; $i < $arr['counter']; $i++) {
                    $row[] = $fields[$column];
                }
            }
        }

        fwrite($this->getTemporaryHandle(), ($add_new_line ? PHP_EOL : '') . implode($delimiter, $row));
        $this->_count_products_exported++;

        return $this;
    }

    public function getWriteFeedParams()
    {
        $feed = $this->getFeed();
        $providerData = $feed->hasProviderData() && is_array($feed->getProviderData()) ? $feed->getProviderData() : array();
        $encloseCell = isset($providerData['cell_enclose']) ? $providerData['cell_enclose'] : false;
        $params = array(
            'defaultValue' =>  isset($providerData['cell_default']) ? $providerData['cell_default'] : ' ',
            'delimiter' => isset($providerData['cell_delimiter']) ? $providerData['cell_delimiter'] : "\t",
            'encloseCell' => $encloseCell,
            'encloseEscape' => isset($providerData['cell_enclose_escape']) && $encloseCell !== false ? $providerData['cell_enclose_escape'] : ''
        );
        return $params;
    }

    /**
     * @param  RocketWeb_ShoppingFeeds_Model_Adapter_Abstract $productAdapter
     */
    protected function addProductToFeed($productAdapter)
    {
        if ($productAdapter->checkSkipSubmission()) {
            return $this;
        }

        $message = $this->checkPriceRangeSkip($productAdapter->getProduct());

        if ($message !== false) {
            $this->log($message);
            return $this;
        }

        $rows = $productAdapter->map();

        if ($productAdapter->isSkip()) {
            return $this;
        }

        foreach ($rows as $row) {
            $this->writeFeed($row);
        }
        return $this;
    }

    /**
     * Gets feed's filepath.
     *
     * @return string
     */
    public function getFeedPath()
    {
        $name = sprintf($this->getFeed()->getFeedFile());
        if ($this->getTestMode()) {
            $name = sprintf($this->getFeed()->getData('test_filename'), $this->getFeed()->getId());
        }

        $filepath = rtrim(Mage::getBaseDir(), DS) . DS . rtrim($this->getFeed()->getConfig('general_feed_dir'), DS) . DS;
        return $filepath. $name;
    }

    /**
     * Runs any existing hooks by type. Function name is {$type}Hook
     *
     * @param string $type
     * @param array $args
     * @return bool|mixed|void
     */
    public function runHook($type = 'pre', $args = array())
    {
        $this->_initHooks();

        $return_value = true;
        $feed_id = $this->getFeed()->getId();
        if (array_key_exists($feed_id, $this->_hooks)) {
            foreach ($this->_hooks[$feed_id] as $hookName => $hookModel) {
                $hookFunction = $type . 'Hook';
                if (is_callable(array($hookModel, $hookFunction))) {
                    $this->log(sprintf('Running %s of %s', $hookFunction, $hookName));

                    if (!array_key_exists('feed', $args)) $args['feed'] = $this->getFeed();
                    $return_value = call_user_func(array($hookModel, $hookFunction), $args);

                    if (is_string($return_value) && !empty($return_value)) {
                        $this->log($return_value);
                    }
                }
            }
        }

        return $return_value;
    }

    /**
     * Memoize hook models so they can share state across hook calls
     * Don't use singleton as we don't want to share state on multiple feed IDs
     */
    protected function _initHooks() {
        if ($this->_hooks_inited) return;

        $path = Mage::getModuleDir('model', 'RocketWeb_ShoppingFeeds') . DS . 'Model' . DS . 'Hook' . DS;

        try {
            $directory = new RecursiveDirectoryIterator($path,RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory,RecursiveIteratorIterator::LEAVES_ONLY);
        }
        catch (Exception $e) {
            // $path doesn't exist or not a directory
            $this->_hooks_inited = true;
            return;
        }

        foreach ($iterator as $file) {
            $hookName = str_replace('.php', '', str_replace($path, '', $file->getPathname()));
            $hookName = str_replace(DS, '_', strtolower($hookName));

            $feedCfg = $this->getFeed()->getData('default_feed_config');
            if (array_key_exists($hookName, $feedCfg)) {
                $hookModel = Mage::getModel('rocketshoppingfeeds/hook_' . $hookName);
                if (is_object($hookModel)) {
                    $this->_hooks[$this->getFeed()->getId()][$hookName] = $hookModel;
                }
            }
        }

        $this->_hooks_inited = true;
    }

    /**
     * Moves the feed file to it's final location after being generated in a temporary location.
     * return RocketWeb_ShoppingFeeds_Model_Generator
     */
    protected function moveFeedFile()
    {
        rename($this->getFeedPath() . '.tmp', $this->getFeedPath());
        return $this;
    }

    /**
     * Only transfer data from temporary feed file if in
     * batch mode and this is the last batch, or if not in batch mode.
     *
     * return RocketWeb_GoogleBaseFeedGenerator_Model_Generator
     */
    protected function copyDataFromTemporaryFeedFile()
    {
        if ($this->inBatch()) {
            // if this was the last batch
            if ($this->getBatch()->completed()) {
                $this->moveFeedFile();
            }
        } else {
            $this->moveFeedFile();
        }

        return $this;
    }

    /**
     * @return bool|null|resource
     */
    protected function getTemporaryHandle()
    {
        if (!$this->hasData('temporary_handle') || $this->getData('temporary_handle') === null) {
            $mode = "a";
            if (!$this->inBatch() || ($this->inBatch() && $this->getBatch()->getIsNew())) {
                $mode = "w";
            }

            $this->setData('temporary_handle', @fopen($this->getFeedPath() . '.tmp', $mode));
            if ($this->getData('temporary_handle') === false) {
                Mage::throwException(sprintf('Not enough permissions to write to file %s.', $this->getFeedPath()));
            }
        }

        return $this->getData('temporary_handle');
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Generator
     */
    protected function closeTemporaryHandle()
    {
        @fclose($this->getData('temporary_handle'));
        $this->unsetData('temporary_handle');
        return $this;
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Generator
     */
    protected function setFeedFilePermissions()
    {
        @chmod(rtrim(Mage::getBaseDir(), DS) . DS . rtrim($this->getFeed()->getConfig('general_feed_dir'), DS), 0755);
        @chmod($this->getFeedPath(), 0664);
        return $this;
    }

    public function getCountProductsExported()
    {
        return $this->_count_products_exported;
    }

    public function getCountProductsSkipped()
    {
        return $this->_count_products_skipped;
    }

    /**
     * Could take negative value to decrease count
     * @param $val
     * @return RocketWeb_ShoppingFeeds_Model_Generator
     */
    public function updateCountSkip($val = 1)
    {
        $this->_count_products_skipped = $this->_count_products_skipped + $val;
        return $this;
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Tools
     */
    public function getTools()
    {
        if (!$this->hasData('tools')) {
            $this->setData('tools', Mage::getModel('rocketshoppingfeeds/tools', array(
                    'feed' => $this->getFeed(),
                    'store_id' => $this->getData('store_id')
                )
            ));
        }

        return $this->getData('tools');
    }

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Log
     */
    public function getLog()
    {
        return Mage::getSingleton('rocketshoppingfeeds/log');
    }

    /**
     * @param $msg
     * @param null $level
     * @param null $writer
     * @param bool|false $extra
     * @return RocketWeb_ShoppingFeeds_Model_Generator
     */
    public function log($msg, $level = null, $writer = null)
    {
        if (is_null($level)) {
            $level = Zend_Log::INFO;
        }

        if ($this->getSkipLog() && $level > Zend_Log::ALERT) {
            return;
        }

        $options = array(
            'file' => $this->getFeed()->getLogFile(),
            'force' => true,
        );

        $this->getLog()->write($msg, $level, $writer, $options);

        if (!$this->inBatch()) {
            $this->getLog()->write($msg, $level, RocketWeb_ShoppingFeeds_Model_Log::WRITER_MEMORY);
        }

        if ($this->getData('verbose')) {
            echo $msg . "\n";
        }
        return $this;
    }

    /**
     * @param $memory
     * @return string
     */
    public function formatMemory($memory)
    {
        $memory = max(1, $memory);

        $memoryLimit = Mage::helper('rocketshoppingfeeds')->getMemoryLimit();
        $units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
        $m = @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2);
        $limit = @round($memoryLimit / pow(1024, ($j = floor(log($memoryLimit, 1024)))), 2);
        return sprintf('%4.2f %s/%4.2f %s', $m, $units[$i], $limit, $units[$j]);
    }

    /**
     * Check if we are using too much memory and the script should stop
     *
     * @return bool
     */
    public function isCloseToPhpLimit()
    {
        $timeSpent = (time() - $this->getData('started_at')) * 1.1;
        $timeMax = ini_get('max_execution_time');
        if ($timeMax > 0 && $timeSpent >= $timeMax) {
            $this->log('PHP max_execution_time reached.');
            return true;
        }

        $currentUsage = memory_get_usage(true) * 1.1; // We add 10% overhead so we terminate soon enough
        if ($currentUsage >= Mage::helper('rocketshoppingfeeds')->getMemoryLimit()) {
            $this->log('PHP allowed_memory reached.');
            return true;
        }
        return false;
    }

    /**
     * Wrapper for attribute cache in Tools object.
     *
     * @param  $code
     * @return mixed|null
     */
    public function getAttribute($attributeCode)
    {
        return $this->getTools()->getAttribute($attributeCode);
    }

    /**
     * Wrapper for set attribute cache in Tools object
     *
     * @param $attribute
     */
    public function setAttribute($attribute)
    {
        return $this->getTools()->setAttribute($attribute);
    }

    /**
     * Release the lock in case of issues
     */
    public function __destruct()
    {
        @fclose($this->_lockFile);
    }

    /**
     * @return string
     */
    public function getLockPath()
    {
        return rtrim(dirname($this->getFeedPath()), DS)
        . DS. sprintf($this->getFeed()->getData('lock_filename'), $this->getFeed()->getId());
    }

    /**
     * @return string
     */
    public function getBatchLockPath()
    {
        return rtrim(dirname($this->getFeedPath()), DS)
        . DS. sprintf($this->getFeed()->getData('batch_lock_filename'), $this->getFeed()->getId());
    }

    /**
     * Implements the lock feed generation using the file system lock mechanism.
     * @return bool
     */
    public function acquireLock()
    {
        // Test feed writes to a separate file so no need to lock
        if ($this->getTestMode()) {
            return true;
        }

        // Acquire an exclusive lock on file without blocking the script
        if (empty($this->_lockFile) || !flock($this->_lockFile, LOCK_EX | LOCK_NB)) {
            $this->log(sprintf('Can\'t acquire feed lock for [%s]', $this->getFeed()->getName()) . ($this->hasScheduleId() ? sprintf('script [%s]', $this->getScheduleId()) : ''), Zend_Log::ERR);
            $this->log(sprintf('Ensure write proper write permissions to [%s]', $this->getLockPath()));
            return false;
        }

        ftruncate($this->_lockFile, 0); // truncate file
        fwrite($this->_lockFile, date('Y-m-d H:i:s'));
        fflush($this->_lockFile); // flush output before releasing the lock
        return true;
    }

    /**
     * Release the file lock.
     * Will also be done automatically when php runtime ends.
     *
     * @return RocketWeb_ShoppingFeeds_Model_Generator
     */
    public function releaseLock()
    {
        if ($this->inBatch()) {
            $this->getBatch()->releaseLock();
        }

        flock($this->_lockFile, LOCK_UN);
        return $this;
    }

    /**
     * @return bool
     */
    public function batchInProgress()
    {
        if ($mixed = @file_get_contents($this->getBatchLockPath())) {
            $mixed = @unserialize($mixed);
            if (is_array($mixed) && (int)$mixed['offset'] < (int)$mixed['total'] - (int)$mixed['limit']) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $type
     * @return bool
     */
    public function isProductTypeEnabled($type)
    {
        return in_array($type, $this->getFeed()->getConfig('filters_product_types'));
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        if (!$this->hasData('total_items')) {
            $count_coll = clone $this->_getCollection();
            $count_coll->getSelect()->reset(Zend_Db_Select::GROUP);
            $this->setData('total_items', $count_coll->getSize());
        }
        return $this->getData('total_items');
    }

    /**
     * @return bool
     */
    public function inBatch()
    {
        return $this->getBatchMode() && !$this->getTestMode();
    }

    /**
     * Check if product price is not in allowed range
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return boolean | string
     */
    public function checkPriceRangeSkip($product, $additionalText = '')
    {
        $message = false;

        if ($product->hasPrice()) {
            if ($this->hasMaxProductPrice() && ($this->getMaxProductPrice() < $product->getPrice())) {
                $message = 'above';
            } else if ($this->hasMinProductPrice() && ($product->getPrice() < $this->getMinProductPrice())) {
                $message = 'below';
            }
        }
        if ($message) {
            $message = (sprintf('"product id %d sku %s, skipped - product price is %s limit%s"', $product->getId(),
                $product->getSku(), $message, $additionalText));
        }
        return $message;
    }

    /**
     * Build information about parent products into associated array by product type
     *
     * @param $row
     * @return array
     */
    public function getProductParents($row)
    {
        $parents = array('configurable' => false, 'grouped' => false, 'bundle' => false);

        $parents['configurable'] = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, $this->getStoreId());
        if (!$parents['configurable']) {
            $parents['configurable'] = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Configurable_Subscription::PRODUCT_TYPE, $this->getStoreId());
        }

        $parents['grouped'] = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], Mage_Catalog_Model_Product_Type::TYPE_GROUPED, $this->getStoreId());
        if (!$parents['grouped']) {
            $parents['grouped'] = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Grouped_Subscription::PRODUCT_TYPE, $this->getStoreId());
        }
        $parents['bundle'] = $this->getTools()->isChildOfProductType($row['type_id'], $row['sku'], Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, $this->getStoreId());

        return $parents;
    }
}
