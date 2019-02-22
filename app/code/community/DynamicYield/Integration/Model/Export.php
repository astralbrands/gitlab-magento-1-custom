<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

use \Aws\S3\S3Client;

/**
 * Class DynamicYield_Integration_Model_Export
 */
class DynamicYield_Integration_Model_Export
{
    protected $baseAttributes = array('name', 'url', 'sku', 'group_id', 'price', 'in_stock', 'categories', 'image_url','keywords');

    protected $globalAttributes = array('sku', 'group_id', 'in_stock', 'image_url','has_options','required_options','created_at','updated_at');

    protected $loadAttributes = array('image','url_path');

    protected $backendTypes = array("catalog/product_attribute_backend_boolean", "catalog/product_attribute_backend_msrp", "int");

    protected $customAttributes = array('categories','url','keywords',DynamicYield_Integration_Helper_Data::FINAL_PRICE);

    protected $skipAttributes = array('category_ids');
    /**
     * @var
     */
    protected $additionalAttributes;

    /**
     * @var Mage_Core_Model_Store[]
     */
    protected $stores = array();

    /**
     * @var array
     */
    protected $uniqueStores = array();

    /**
     * @var array
     */
    protected $storeLocaleMapping = array();


    /**
     * @var array
     */
    protected $excludedCategories = array();

    /**
     * @var DynamicYield_Integration_Helper_Data
     */
    protected $dataHelper;

    /**
     * @var DynamicYield_Integration_Helper_Feed
     */
    protected $feedHelper;

    /**
     * @var array
     */
    protected $header;

    /**
     * @var
     */
    protected $defaultStore;

    /**
     * @var Mage_Catalog_Model_Resource_Product|object
     */
    protected $productSingleton;

    /**
     * @var
     */
    protected $readConnection;

    /**
     * DynamicYield_Integration_Model_Export constructor
     */
    public function __construct()
    {
        $this->dataHelper = Mage::helper('dynamicyield_integration/data');
        $this->feedHelper = Mage::helper('dynamicyield_integration/feed');
        $this->productSingleton = Mage::getResourceSingleton('catalog/product');
        $this->readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * @return array
     */
    public function loadStores()
    {
        /**
         * @var $stores Mage_Core_Model_Store[]
         */
        $_stores = Mage::app()->getStores();

        $locales = array();

        foreach ($_stores as $store) {
            $locales[$store->getId()] =
                                    Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_LOCALE,$store->getId()) ?
                                        (Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_SELECT,$store->getId()) ?
                                            Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE_CUSTOM,$store->getId()) :
                                                Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE,$store->getId())) :
                                                    Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE,$store->getId());
                
            $this->stores[$store->getId()] = $store;
        }

        $locales = array_unique($locales);

        $this->defaultStore = $this->getDefaultWebsite();

        foreach ($locales as $key => $value)
        {
            $this->uniqueStores[] = $key;
        }

        return $locales;
    }

    /**
     * Export product feed.
     */
    public function export()
    {
        $this->loadStores();

        $this->excludedCategories = $this->dataHelper->getExcludedCategories();

        $path = $this->feedHelper->getExportPath();

        if (!is_dir($path)) {
            mkdir($path);
        }

        $handle = fopen($path . 'productfeed.csv', 'w+');

        $additionalAttributes = array();
        $translatableAttributes = array();

        foreach ($this->dataHelper->getExportableAttributes() as $attribute) {
            if ((int)$attribute->getIsGlobal() === Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE) {
                $translatableAttributes[] = $attribute->getAttributeCode();
            }

            $additionalAttributes[] = $attribute->getAttributeCode();
        }

        $delimiter = ",";

        // Write header
        $header = array_unique(array_merge($this->baseAttributes, $additionalAttributes));
        $load = array_unique(array_merge($header,$this->loadAttributes));
        if($this->dataHelper->isFinalPriceSelected()) {
            $header[] = DynamicYield_Integration_Helper_Data::FINAL_PRICE;
        }

        if($this->dataHelper->isMultiLanguage()) {
            // Add multilingual fields
            foreach ($header as $code) {
                if ((!in_array($code, $this->globalAttributes) and in_array($code, $translatableAttributes)) || in_array($code,$this->customAttributes)) {
                    foreach ($this->stores as $store) {
                        if(in_array($store->getStoreId(),$this->uniqueStores))
                        $header[] = $this->getLngKey(
                            Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_LOCALE,$store->getId()) ?
                                (Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_SELECT,$store->getId()) ?
                                    Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE_CUSTOM,$store->getId()) :
                                        Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE,$store->getId())) :
                                            Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE,$store->getId()), $code);
                    }
                }
            }
        }
        $this->header = $header;

        fputcsv($handle, $header, $delimiter);
        $offset = 0;
        $limit = $selected = $this->dataHelper->getChunkSize();

        Mage::register('use_product_eav', true);
        while ($limit === $selected) {
            $result = $this->chunkProductExport($handle, $limit, $offset, $load);
            $selected = $result['count'];
            $offset = $result['last'];
        }
        Mage::unregister('use_product_eav');
        $this->upload();
    }

    /**
     * Uploads genereated product feed to S3 Bucket
     */
    public function upload()
    {
        $s3 = S3Client::factory(array('credentials' => array('key' => $this->feedHelper->getAccessKey(), 'secret' => $this->feedHelper->getAccessKeySecret(),)));
        try {
            $s3->upload($this->feedHelper->getBucket(), $this->feedHelper->getExportFilename(), fopen($this->feedHelper->getExportFile(), 'r'));
        } catch (\Aws\S3\Exception\S3Exception $e) {
            Mage::log('DYI: ' .$e->getMessage(), Zend_Log::CRIT);
        }
    }

    /**
     * @param resource $handle
     * @param int $limit
     * @param int $offset
     * @param array $additionalAttributes
     *
     * @return mixed
     */
    public function chunkProductExport($handle, $limit = 100, $offset = 0, $additionalAttributes = array())
    {
        if($this->dataHelper->isDebugMode()) {
            $time_start = microtime(true);
        }
        /**
         * @var $collection Mage_Catalog_Model_Resource_Product_Collection
         */
        Mage::getModel("dynamicyield_integration/product")->getCollection()->setStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $collection = Mage::getModel("dynamicyield_integration/product")->getCollection()
            ->addAttributeToSelect($additionalAttributes)
            ->addUrlRewrite();
        $collection->setStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $collection->setFlag('require_stock_items',true);
        $collection->addFieldToFilter("entity_id",array("gt" => $offset));
        $collection->getSelect()->limit($limit, 0);
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('type_id', ['nin' => [
            Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
        ]]);
        $collection->getSelect()->joinLeft(array('super' => $this->readConnection->getTableName('catalog_product_super_link')),'`e`.`entity_id` = `super`.`product_id`',array('parent_id'));
        $collection->getSelect()->group('e.entity_id');
        $collection->load();

        $storeCollection = array();

        if($this->dataHelper->isMultiLanguage()) {
            $ids = array();

            /**
             * Collect selected product IDs
             */
            foreach ($collection as $product) {
                $ids[] = $product->getId();
            }

            foreach ($this->uniqueStores as $store) {
                Mage::getModel("dynamicyield_integration/product")->getCollection()->setStore($store);
                $storeCollection[$store] = Mage::getModel("dynamicyield_integration/product")->getCollection()
                    ->addAttributeToSelect($additionalAttributes)
                    ->addUrlRewrite();
                $storeCollection[$store]->setStore($store);
                $storeCollection[$store]->addFieldToFilter("entity_id", ["in" => $ids]);
                $storeCollection[$store]->getSelect()->limit($limit, 0);
                $storeCollection[$store]->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                $collection->addAttributeToFilter('type_id', ['nin' => [
                    Mage_Catalog_Model_Product_Type::TYPE_GROUPED, Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                ]]);
                $storeCollection[$store]->getSelect()->joinLeft(array('super' => $this->readConnection->getTableName('catalog_product_super_link')),'`e`.`entity_id` = `super`.`product_id`',array('parent_id'));
                $storeCollection[$store]->getSelect()->group('e.entity_id');
                $storeCollection[$store]->load();
            }
        }

        $parentIds = array();
        /**
         * Collect selected parent IDs
         */
        foreach ($collection as $product) {
            $parentIds[] = $product->getParentId();
        }

        $parentProductCollection = Mage::getModel("dynamicyield_integration/product")->getCollection()
            ->addAttributeToSelect($additionalAttributes)
            ->addUrlRewrite();
        $parentProductCollection->addFieldToFilter("entity_id", ["in" => $parentIds]);
        $parentProductCollection->getSelect()->limit($limit, 0);

        foreach ($collection as $product) {
            $line = $this->readLine($product,$parentProductCollection,$storeCollection);
            if($line) fputcsv($handle, $this->fillLine($line));
        }

        if($this->dataHelper->isDebugMode()){
            $memory = memory_get_usage();
            Mage::log('MEMORY USED '.$memory.'. Chunk execution time in seconds: '.(microtime(true) - $time_start), null, $this->feedHelper->getDebugLogFile());
        }

        return array(
            'count' => $collection->count(),
            'last' => $collection->getLastItem()->getEntityId()
        );
    }

    /**
     * Get final price of variation
     * 
     * @param $simpleProduct
     * @param $parentCollection
     * @return mixed
     */
    public function getFinalPrice($simpleProduct,$parentCollection)
    {
        foreach ($parentCollection as $parent) {
            if ($parent->getId() == $simpleProduct->getParentId()) {
                $values = array();
                $attributeCodes = array();

                /**
                 * Custom query to fetch configurable attributes
                 */
                $query =
                    "SELECT eav.attribute_code, eav.attribute_id FROM "
                    . $this->readConnection->getTableName('eav_attribute') .
                    " as eav LEFT JOIN "
                    . $this->readConnection->getTableName('catalog_product_super_attribute') .
                    " as super ON eav.attribute_id = super.attribute_id WHERE (product_id = " . $parent->getId() . ");";

                $result = $this->readConnection->query($query);

                while ($row = $result->fetch()) {
                    $attributeCodes[$row['attribute_id']] = $row['attribute_code'];
                };

                foreach ($attributeCodes as $id => $code) {
                    $values[$id] = $simpleProduct->getData($code);
                }
                $parent->addCustomOption('attributes', serialize($values));
                $price = $parent->getPriceModel()->getFinalPrice(null, $parent);
                return $price ?: $simpleProduct->getPrice();
            }
        }

        return $simpleProduct->getPrice();
    }

    /**
     * Get Product Image Url
     * Fallback to parent product image
     *
     * @param $product
     * @return mixed
     */
    public function getProductImageUrl($product) {
        $productMediaConfig = Mage::getModel('catalog/product_media_config');
        if(!in_array($product->getImage(),array('no_selection',''))) {
            $image = $product->getImage();
        } else{
            $image = $this->productSingleton->getAttributeRawValue($product->getParentId(), 'image', $product->getStore()->getId());
        }

        return $image ? $productMediaConfig->getMediaUrl($image) : '';
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param array $storeCollections
     *
     * @return array
     */
    public function readLine(Mage_Catalog_Model_Product $product, $parentCollection = null, $storeCollections = array())
    {
        try {
            Mage::app()->setCurrentStore($this->defaultStore->getDefaultStore()->getId());
        } catch(Exception $exception) {
            Mage::logException($exception);
        }

        $rowData = array(
            'name'  => $product->getName(),
            'url'   => $this->getProductUrl($product),
            'sku' => $product->getData('sku'),
            'group_id' => $this->getGroupId($product),
            'price' => $product->getParentId() ? $this->getFinalPrice($product,$parentCollection) : $product->getPrice(),
            'in_stock' => $product->getStockItem()->getIsInStock() ? "true" : "false",
            'categories' => $productCategories = $this->buildCategories($product),
            'image_url' => $this->getProductImageUrl($product),
        );

        if(count($rowData) !=  count(array_diff($rowData,array('')))) {
            Mage::log('MANDATORY ATTRIBUTE MISSING: '. json_encode($rowData), null, $this->feedHelper->getFeedProductLogFile());
            return false;
        }

        $rowData['keywords'] = $this->buildCategories($product,true);
        $rowData[DynamicYield_Integration_Helper_Data::FINAL_PRICE] = $product->getFinalPrice();

        $storeIds = $product->getStoreIds();
        $currentStore = $product->getStore();
        $attributes = $product->getAttributes();

        foreach ($attributes as $code => $attribute) {
            $attributeData = $product->getData($code);
            if(in_array($code,$this->baseAttributes) || in_array($code,$this->skipAttributes)) continue;
            if(!is_array($attributeData)) {
                if(in_array($attribute->getData('backend_model'),$this->backendTypes)){
                    $rowData[$code] = $product->getAttributeText($code);
                }else{
                    if(!$attributeData || $attributeData == '') {
                        $attributeData = $this->productSingleton->getAttributeRawValue($product->getParentId(), $code, $product->getStore()->getId());
                    }
                    $rowData[$code] = $attributeData;
                }
            }else{
                $rowData[$code] = json_encode($attributeData);
            }
        }

        if(!empty($storeCollections)) {
            foreach ($storeIds as $storeId) {
                if (($store = $this->stores[$storeId]) && in_array($storeId, $this->uniqueStores)) {
                    Mage::app()->setCurrentStore($store);

                    $langCode = Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_LOCALE,$storeId) ?
                                    (Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_SELECT,$storeId) ?
                                        Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE_CUSTOM,$storeId) :
                                            Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_CUSTOM_LOCALE,$storeId)) :
                                                Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE,$storeId);

                    foreach ($storeCollections[$storeId] as $loadedProduct) {
                        if ($product->getId() == $loadedProduct->getId()) {
                            $productStore = clone $loadedProduct;
                            break;
                        }
                    }

                    if (!$productStore) continue;

                    /**
                     * Translate non-standard attributes
                     */
                    $rowData[$this->getLngKey($langCode, 'categories')] = $this->buildCategories($productStore);
                    $rowData[$this->getLngKey($langCode, 'keywords')] = $this->buildCategories($productStore,true);
                    $rowData[$this->getLngKey($langCode, 'url')] = $this->getProductUrl($productStore);
                    $rowData[$this->getLngKey($langCode,DynamicYield_Integration_Helper_Data::FINAL_PRICE)] = $productStore->getFinalPrice();

                    foreach ($attributes as $code => $attribute) {
                        $attributeData = $productStore->getData($code);
                        if ((int)$attribute->getIsGlobal() === Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE) {
                            $field = $this->getLngKey($langCode, $code);
                            if (!is_array($attributeData)) {
                                if (in_array($attribute->getData('backend_model'), $this->backendTypes)) {
                                    $rowData[$field] = $productStore->getAttributeText($code);
                                } else {
                                    if(!$attributeData || $attributeData == '') {
                                        $attributeData = $this->productSingleton->getAttributeRawValue($productStore->getParentId(), $code, $storeId);
                                    }
                                    $rowData[$field] = $attributeData;
                                }
                            } else {
                                $rowData[$field] = json_encode($attributeData);
                            }
                        }
                    }
                }
            }
        }

        Mage::app()->setCurrentStore($currentStore);

        return $rowData;
    }

    /**
     * Define empty values for a row
     *
     * @param $line
     *
     * @return array
     */
    protected function fillLine($line)
    {
        $out = array();

        foreach ($this->header as $key) {
            if (isset($line[$key])) {
                $out[$key] = $line[$key];
            } else {
                $out[$key] = NULL;
            }
        }

        return $out;
    }

    /**
     * Formats the language key
     *
     * @param $langCode
     * @param $code
     *
     * @return string
     */
    protected function getLngKey($langCode, $code)
    {
        return sprintf('lng:%s:%s', $langCode, $code);
    }

    /**
     * Get product url
     * Return parent url if child product is not visible
     * @param $product
     *
     * @return string
     */
    protected function getProductUrl($product)
    {
        $path = null;
        if($product->getUrlPath()) {
            $path = $product->isVisibleInSiteVisibility() ? $product->getUrlPath() : ($product->getParentId() ? $this->productSingleton->getAttributeRawValue($product->getParentId(), 'url_path',$product->getStore()->getId()) : $product->getUrlPath());
        } else {
            $productId = $product->isVisibleInSiteVisibility() ? $product->getEntityId() : $product->getParentId();
            if($productId) {
                if(Mage::helper('core')->isModuleEnabled('Enterprise_Catalog')) {
                    $rewrite = Mage::getResourceSingleton('enterprise_catalog/product')->getRewriteByProductId($productId, $product->getStoreId());
                    $path = $rewrite['request_path'];
                } else {
                    $path = $this->dataHelper->getProductUrl($productId,$product->getStoreId());
                }
            }
        }
        if(!$path) {
            return $product->getProductUrl($product);
        }
        return Mage::app()->getStore($product->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true ) . $path;
    }

    /**
     * Return Group Id
     *
     * @param $product
     * @return array|bool|string
     */
    protected function getGroupId($product)
    {
        return $product->getParentId() ? $this->productSingleton->getAttributeRawValue($product->getParentId(), 'sku', Mage_Core_Model_App::ADMIN_STORE_ID) : $product->getSku();
    }

    /**
     * Builds the category tree for categories or keywords
     *
     * Note: Does not support products with multiple category trees
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $keywords
     *
     * @return string
     */
    protected function buildCategories(Mage_Catalog_Model_Product $product, $keywords = false)
    {
        if(!empty($product->getCategoryCollection()->addNameToResult()->getItems())) {
            $categories = $product->getCategoryCollection()
                ->addNameToResult();
                if($keywords) {
                    $categories->addFieldToFilter('entity_id', array('in' => $this->excludedCategories));
                } else {
                    $categories->addFieldToFilter('entity_id', array('nin' => $this->excludedCategories));
                }
                $categories->getItems();
        } else {
            $categories = $this->dataHelper->getCategoryCollection($product->getParentId(),$keywords);
            if(!$categories) return null;

            $categories->addNameToResult()->getItems();
        }
        return $this->buildCategoryStructure($categories);
    }

    /**
     * Get Default Website
     *
     * @return mixed
     */
    private function getDefaultWebsite()
    {
        $websites = Mage::getModel('core/website')->getCollection()->addFieldToFilter('is_default', 1);
        $website = $websites->getFirstItem();

        return $website;
    }

    /**
     * Builds the structure
     *
     * @param $items
     * @return string
     */
    protected function buildCategoryStructure($items)
    {
        return join('|', $this->map(function ($item) {
            return $item->getName();
        },$items));
    }

    /**
     * Map collection
     *
     * @param callable $fn
     * @param $items
     * @return array
     */
    protected function map(callable $fn, $items)
    {
        $result = array();
        foreach ($items as $item) {
            $result[] = $fn($item);
        }
        return $result;
    }

    /**
     * Get configurable products first child price
     * Return 0 if not found
     *
     * @param $product
     * @return int
     */
    public function getChildPrice($product)
    {
        if($product->getTypeId() == "configurable"){
            $configurable = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
            if(!$configurable) return 0;
            $simpleCollection = $configurable->getUsedProductCollection()->addAttributeToSelect('price')->addFilterByRequiredOptions();

            foreach($simpleCollection as $simple){
                return $simple->getPrice();
            }
        }

        return 0;
    }
}
