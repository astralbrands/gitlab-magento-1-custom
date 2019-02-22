<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Helper_Data
 */
class DynamicYield_Integration_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_CDN = 'cdn.dynamicyield.com';
    const EUROPE_CDN = 'cdn-eu.dynamicyield.com';

    const FINAL_PRICE = 'final_price';

    const CONF_SECTION_ID = 'dyi_config/general/section_id';
    const CONF_LOAD_JQUERY = 'dev/dyi/load_jQuery';
    const CONF_CUSTOM_SELECTORS = 'dev/dyi';
    const CONF_USE_CUSTOM_SELECTORS = 'dev/dyi/custom_selectors';
    const CONF_UPDATE_RATE = 'dyi_config/product_feed/update_rate';
    const CONF_DEBUG_MODE = 'dev/dyi/debug_mode';
    const CONF_CHUNK_SIZE = 'dev/dyi/feed_chunk_size';
    const CONF_CUSTOM_LOCALE = 'dev/dyi/custome_locale';
    const CONF_CUSTOM_LOCALE_CUSTOM = 'dev/dyi/custome_locale_custom';
    const CONF_ENABLE_CUSTOM_LOCALE = 'dev/dyi/custome_locale_enabled';
    const CONF_ENABLE_CUSTOM_SELECT = 'dev/dyi/custom_locale_select';
    const CONF_ENABLE_EUROPE_ACCOUNT = 'dev/dyi/europe_account';
    const CONF_ENABLE_CDN_INTEGRATION = 'dev/dyi/cdn_integration';
    const CONF_CUSTOM_CDN = 'dev/dyi/cdn_url';
    const CONF_EXCLUDED_CATEGORIES = 'dyi_config/product_feed/excluded_categories';
    const CONF_DEFAULT_STORE = 'dev/dyi/default_store';

    protected $_count;

    /**
     * Is tracking enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->getSectionId() != '';
    }

    /**
     * Get section Id
     *
     * @param bool $default
     * @return mixed
     */
    public function getSectionId($default = false) {
        return $default ? Mage::getStoreConfig(static::CONF_SECTION_ID,Mage_Core_Model_App::ADMIN_STORE_ID) : Mage::getStoreConfig(static::CONF_SECTION_ID);
    }

    /**
     * Get default CDN
     *
     * @return string
     */
    public function getDefaultCDN()
    {
        return static::DEFAULT_CDN;
    }

    /**
     * Get Europe CDN
     *
     * @return string
     */
    public function getEuropeCDN()
    {
        return static::EUROPE_CDN;
    }

    /**
     * Get custom CDN
     *
     * @return mixed
     */
    public function getCustomCDN()
    {
        return Mage::getStoreConfig(static::CONF_CUSTOM_CDN);
    }

    /**
     * Is EU account enabled
     *
     * @return mixed
     */
    public function isEuropeAccount()
    {
        return Mage::getStoreConfig(static::CONF_ENABLE_EUROPE_ACCOUNT);
    }

    /**
     * Is CDN integration enabled
     *
     * @return mixed
     */
    public function isCDNIntegration()
    {
        return Mage::getStoreConfig(static::CONF_ENABLE_CDN_INTEGRATION) != DynamicYield_Integration_Model_Config_Source_Integrationtype::CDN_DISABLED;
    }

    /**
     * Is European CDN Integration enabled
     *
     * @return bool
     */
    public function isEuropeCDNIntegration()
    {
        return Mage::getStoreConfig(static::CONF_ENABLE_CDN_INTEGRATION) == DynamicYield_Integration_Model_Config_Source_Integrationtype::CDN_EUROPEAN;
    }

    /**
     * Get CDN url
     *
     * @return mixed|string
     */
    public function getCDN()
    {
        if($this->isEuropeAccount()) {
            return $this->getEuropeCDN();
        } elseif($this->isCDNIntegration()) {
            return $this->getCustomCDN();
        }

        return $this->getDefaultCDN();
    }

    /**
     * @return mixed
     */
    public function getLoadJquery()
    {
        return Mage::getStoreConfig(static::CONF_LOAD_JQUERY);
    }

    /**
     * Return custom tracking selectors from config
     *
     * @return mixed
     */
    public function getTrackingSelectors()
    {
        return [
            "category_page_filters" => Mage::getStoreConfig("dev/dyi/category_page_filters") ?: "{}",
            "category_page_sort_options" => Mage::getStoreConfig("dev/dyi/category_page_sort_options") ?: "{}",
            "category_page_sort_order" => Mage::getStoreConfig("dev/dyi/category_page_sort_order") ?: "{}",
            "product_page_buttons_swatch" => Mage::getStoreConfig("dev/dyi/product_page_buttons_swatch") ?: "{}",
            "product_page_dropdowns" => Mage::getStoreConfig("dev/dyi/product_page_dropdowns") ?: "{}",
        ];
    }

    /**
     * Adds prefix to methods to allow duplicate keys in array
     *
     * @param $element
     * @return array|mixed
     */
    public function prepareStructure($element)
    {
        $this->_count = 0;
        $preparedElement = $element ? preg_replace_callback('/,"/', array($this, '_addPrefix'), $element) : array();
        return $preparedElement;
    }

    /**
     * Callback to prepare elements by adding a prefix
     *
     * @param $matches
     * @return string
     */
    private function _addPrefix($matches)
    {
           return ',"' . $this->_count++;
    }

    /**
     * Return custom tracking structure from config
     *
     * @return mixed
     */
    public function getTrackingStructure()
    {
        $output = array();
        $structureData = array(
            "product_page_attribute_type",
            "product_page_attribute_value",
            "product_page_swatch_type",
            "product_page_swatch_value",
            "category_page_sort_order_by",
            "category_page_sort_order_direction",
            "category_page_sort_order_action",
            "category_page_sort_order_switcher",
            "category_page_filters_type",
            "category_page_filters_price_value",
            "category_page_filters_swatch_value",
            "category_page_filters_swatch_image_value",
            "category_page_filters_regular_value"
        );

        foreach ($structureData as $element)
        {
            $output[$element] = $this->prepareStructure(Mage::getStoreConfig(static::CONF_CUSTOM_SELECTORS."/".$element));
        }

        return $output;
    }

    /**
     * Is custom selectors enabled
     *
     * @return boolean
     */
    public function getUseCustomSelectors()
    {
        return Mage::getStoreConfig(static::CONF_USE_CUSTOM_SELECTORS);
    }

    /**
     * Is final price exported
     */
    public function isFinalPriceSelected()
    {
        $attributeIds = explode(',', Mage::getStoreConfig('dyi_config/product_feed/additional_attributes'));
        if($attributeIds && in_array(static::FINAL_PRICE,$attributeIds)) {
            return true;
        }
        return false;
    }

    /**
     * @return Mage_Catalog_Model_Entity_Attribute[]
     */
    public function getExportableAttributes()
    {
        $attributeIds = explode(',', Mage::getStoreConfig('dyi_config/product_feed/additional_attributes'));
        /**
         * @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection
         */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');

        if (sizeof($attributeIds) > 0) {
            if (($key = array_search(static::FINAL_PRICE, $attributeIds)) !== false) {
                unset($attributeIds[$key]);
            }
            $collection->addFieldToFilter('main_table.attribute_id', array('in' => $attributeIds));
            return $collection->getItems();
        }

        return array();
    }

    /**
     * Return sync rate
     *
     * @return mixed
     */
    public function getUpdateRate()
    {
        return Mage::getStoreConfig(static::CONF_UPDATE_RATE);
    }

    /**
     * Return custom locale
     *
     * @return mixed
     */
    public function getCustomLocale()
    {
        return Mage::getStoreConfig(static::CONF_CUSTOM_LOCALE_CUSTOM) ? Mage::getStoreConfig(static::CONF_CUSTOM_LOCALE_CUSTOM) : (Mage::getStoreConfig(static::CONF_ENABLE_CUSTOM_LOCALE) ? Mage::getStoreConfig(static::CONF_CUSTOM_LOCALE) : false);
    }

    /**
     * Get last cron job execution time
     *
     * @param $jobCode
     * @return bool
     */
    public function getLastExecutionTime($jobCode)
    {

        /* @var $schedules Mage_Cron_Model_Mysql4_Schedule_Collection */
        $schedules = Mage::getModel('cron/schedule')->getCollection();
        $schedules->getSelect()->limit(1)->order('executed_at DESC');
        $schedules->addFieldToFilter(
            array('status'),
            array(
                array('eq' => 'success'),
            )
        );

        $schedules->addFieldToFilter('job_code', $jobCode);
        $schedules->load();
        if (count($schedules) == 0) {
            return false;
        }
        $executedAt = $schedules->getFirstItem()->getExecutedAt();
        $value = Mage::getModel('core/date')->date(null, $executedAt);
        return $value;
    }

    /**
     * Diff between time;
     *
     * @param $time1
     * @param $time2
     * @return int
     */
    public function dateDiff($time1, $time2 = null) {
        if (is_null($time2)) {
            $time2 = Mage::getModel('core/date')->date();
        }
        $time1 = strtotime($time1);
        $time2 = strtotime($time2);
        return $time2 - $time1;
    }

    /**
     * Get Configured Default Store View or fallback to default
     * 
     * @return mixed
     */
    public function getDefaultStoreView()
    {
        return Mage::getStoreConfig(static::CONF_DEFAULT_STORE) ?: Mage::app()->getStore();
    }

    /**
     * Return ordered collection of parent categories
     *
     * @param $category
     * @return category collection
     */
    public function getParentCategories($category) {
        $pathIds = array_reverse(explode(',', $category->getPathInStore()));

        $categories = Mage::getResourceModel('catalog/category_collection')
            ->setStore($this->getDefaultStoreView())
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', array('in' => $pathIds))
            ->addFieldToFilter('is_active', 1);

        $categories->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $pathIds).')'));

        return $categories;
    }

    /**
     * Return Category or Keyword collection
     * Keywords are categories that are excluded via configuration
     *
     * @param $productId
     * @param $keywords
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getCategoryCollection($productId,$keywords = false)
    {
        if(!$productId) return null;
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->joinField('product_id',
                'catalog/category_product',
                'product_id',
                'category_id = entity_id',
                null)
            ->addFieldToFilter('product_id', (int)$productId);
        if($keywords) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->getExcludedCategories()));
        } else {
            $collection->addFieldToFilter('entity_id', array('nin' => $this->getExcludedCategories()));
        }
        return $collection;
    }

    /**
     * Get excluded categories from configuration
     *
     * @return mixed
     */
    public function getExcludedCategories()
    {
        return explode(',',Mage::getStoreConfig(static::CONF_EXCLUDED_CATEGORIES));
    }

    /**
     * Get product request path
     *
     * @param $productId
     * @param $storeId
     * @return bool|string
     */
    public function getProductUrl($productId, $storeId)
    {
        $idPath = sprintf('product/%d', $productId);
        $rewrite = Mage::getSingleton('core/factory')->getUrlRewriteInstance();
        $rewrite->setStoreId($storeId)
            ->loadByIdPath($idPath);
        if ($rewrite->getId()) {
            return $rewrite->getRequestPath();
        }
        return false;
    }

    /**
     * Is debug mode enabled
     *
     * @return mixed
     */
    public function isDebugMode()
    {
        return Mage::getStoreConfig(static::CONF_DEBUG_MODE);
    }

    /**
     * Get chunk size configuration value
     *
     * @return mixed
     */
    public function getChunkSize()
    {
        return (int) Mage::getStoreConfig(static::CONF_CHUNK_SIZE);
    }

    /**
     * Get variation from configurable product
     *
     * @param $product
     * @return Mage_Catalog_Model_Product
     */
    public function getRandomChild($product)
    {
        if($product->getTypeId() == "configurable"){
            $configurable = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
            if(!$configurable) {
                return $product;
            }
            $simpleCollection = $configurable->getUsedProductCollection()
                ->addAttributeToSelect('sku','price')
                ->addFilterByRequiredOptions()
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

            foreach($simpleCollection as $simple){
                return $simple;
            }
        }

        return $product;
    }

    /**
     * Check if SKU is valid as per product feed requirements
     *
     * @param $product
     * @return Mage_Catalog_Model_Product
     */
    public function validateSku($product)
    {
        $variation = Mage::getModel('catalog/product')->loadByAttribute('sku',$product->getSku());
        return $variation ? true : false;
    }

    /**
     * Return sales_quote_item parent product sku
     *
     * @param $item
     * @return bool
     */
    public function getParentItemSku($item)
    {
        if($item->getParentItemId()) {
            return Mage::getResourceModel('catalog/product')->getAttributeRawValue($item->getParentItem()->getProductId(), 'sku');
        }
        return false;
    }

    /**
     * Check if website has multiple active locales
     *
     * @return bool
     */
    public function isMultiLanguage()
    {
        $locale = array();
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            if(Mage::getStoreConfig(DynamicYield_Integration_Helper_Data::CONF_ENABLE_CUSTOM_LOCALE,$store->getId())) {
                return true;
            }

            if (!$store->getIsActive()) continue;
            $locale[] = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE,$store->getId());
        }

        return count(array_unique($locale)) > 1 ? true : false;
    }
}
