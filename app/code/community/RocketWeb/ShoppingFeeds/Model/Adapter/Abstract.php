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
 * @method Mage_Catalog_Model_Product getProduct() Current product or null
 * @method RocketWeb_ShoppingFeeds_Model_Feed getFeed() Current feed
 * @method array getCacheMapValues()
 * @method array getColumnsMap()
 * @method RocketWeb_ShoppingFeeds_Model_Map_Option getOptionProcessor()
 * @method array getAssocColumnsInherit()
 */
class RocketWeb_ShoppingFeeds_Model_Adapter_Abstract extends Varien_Object
{
    /**
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function initialize()
    {
        if(!$this->hasData('feed')) {
            throw new Mage_Core_Exception('Cannot initialize Adapter model without a Feed been set.');
        }
        $store = $this->getStore();
        $this->setData('store_currency_code', $store->getData('current_currency')->getCode());
        $this->setData('images_url_prefix', $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false) . 'catalog/product');
        $this->setData('images_path_prefix', Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath());
        $this->setOptionProcessor(Mage::getModel('rocketshoppingfeeds/map_option', array('map' => $this, 'store_currency_code' => $store->getData('current_currency')->getCode())));
        $this->setCacheMapValues(array());
        $this->setData('assoc_columns_inherit', array('name' => 'title', 'description' => 'description', 'image' => 'image_link', 'url' => 'link'));

        return $this;
    }

    public function clearInstance()
    {
        $this->unsAssociated();
        $this->unsAssociatedIds();
        $this->unsColumnsMap();
    }

    /******************************************/
    /*************** MAPPING METHODS **********/
    /**
     * @return array
     */
    public function map()
    {
        $this->_beforeMap();
        $rows = $this->_map();
        $ret = $this->_afterMap($rows);
        return $ret;
    }

    /**
     * Implement product options on top of complex product variants.
     *
     * @return $this
     */
    public function _beforeMap()
    {
        $this->_setAssocAdapters();
        return $this;
    }

    /**
     * @param $rows
     * @return array
     */
    public function _afterMap($rows)
    {
        reset($rows);
        $this->_checkEmptyColumns(current($rows));
        $this->setCacheMapValues(array());
        return $rows;
    }

    /**
     * Forms product's data row. [column] => [value]
     * @return array
     */
    protected function _map()
    {
        $rows = array();

        // Map current product
        $fields = array();
        foreach ($this->getColumnsMap() as $column => $arr) {
            $fields[$column] = $this->mapColumn($column);
        }
        $rows[] = $fields;


        // Map product options
        if ($this->_isAllowProductOptions()) {
            $rows = $this->getOptionProcessor()->process($rows);
        }

        return $rows;
    }

    /**
     * Maps one column from a row
     *
     * @param  string $column
     * @return string
     */
    public function mapColumn($column)
    {
        if (empty($column) || !array_key_exists($column, $this->getColumnsMap())) {
            return "";
        }

        // Return from cache as there are few columns like price, sale_price and shipping who would share the same info.
        $cacheMapValues = is_array($this->getCacheMapValues()) ? $this->getCacheMapValues() : array();
        if (array_key_exists($column, $cacheMapValues) && !empty($cacheMapValues[$column])) {
            return $cacheMapValues[$column];
        }

        $columnsMap = $this->getColumnsMap();
        $arr = $columnsMap[$column];
        $args = array('map' => $arr);

        /*
           Column methods are required in a few cases.
           e.g. When child needs to get value from parent first. Further if value is empty takes value from it's own mapColumn* method.
           Can loop infinitely if misused.
        */
        $method = 'mapColumn' . $this->_camelize($column);
        $model = $this->_getMapModel($method);
        if ($model !== false) {
            $value = $model->$method($args);
            $model->popAdapter();
        } else {
            $value = $this->getCellValue($args);
        }

        // Run replace empty rules if no value so far
        if ($value == "") {
            $value = $this->mapEmptyValues($args);
        }

        $cacheMapValues[$column] = $value;
        $this->setCacheMapValues($cacheMapValues);
        return $value;
    }

    /**
     * Gets value either from directive method or attribute method.
     *
     * @param  array $args
     * @return mixed
     */
    public function getCellValue($args = array())
    {
        $arr = $args['map'];

        if ($this->getFeed()->isAllowedDirective($arr['attribute']) && !isset($args['skip_directive'])) {
            $method = 'mapDirective' . $this->_camelize(str_replace('rw_gbase_directive', '', $arr['attribute']));
            $model = $this->_getMapModel($method);
            if ($model !== false) {
                $value = $model->$method($args);
                $model->popAdapter();
            } else {
                $value = "";
            }
        } else {
            $method = 'mapAttribute' . $this->_camelize($arr['attribute']);
            $model = $this->_getMapModel($method);
            if ($model !== false) {
                $value = $model->$method($args);
                $model->popAdapter();
            } else {
                $value = $this->mapAttribute($args);
            }
        }

        return $value;
    }

    /**
     * Process any other attribute.
     *
     * @param  array $params
     * @return string
     */
    protected function mapAttribute($params = array())
    {
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getProduct();

        $map = $params['map'];

        // Get attribute value
        $attribute = $this->getGenerator()->getAttribute($map['attribute']);
        if ($attribute === false) {
            Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
        }

        $value = $this->getAttributeValue($product, $attribute);
        return $this->cleanField($value, $params);
    }

    /**
     * @param $args
     * @return mixed|string
     */
    public function mapEmptyValues($args)
    {
        $value = '';
        $column = $args['map']['column'];
        $columnsMap = $this->getColumnsMap();

        // Avoid infinite loop, and not process if already replaced
        if ( isset($columnsMap[$column]) && array_key_exists('empty_replaced', $columnsMap[$column])) {
            return $value;
        }

        if (count($this->getEmptyColumnsReplaceMap())) {

            // Go through replacement rules and pick the one matching current column.
            foreach ($this->getEmptyColumnsReplaceMap() as $arr) {
                if ($column == $arr['column']) {

                    $columnsMap[$column]['empty_replaced'] = true;

                    if (!empty($arr['static']) && (!$arr['attribute'] || $arr['attribute'] == 'rw_gbase_directive_static_value')) {
                        $value = $arr['static'];
                    } else {
                        // Map it again but this time against the new attribute / directive
                        $method = 'mapColumn' . $this->_camelize($column);
                        if (method_exists($this, $method)) {
                            $value = $this->$method(array('map' => $arr));
                        } else {
                            $value = $this->getCellValue(array('map' => $arr));
                        }
                    }
                }

            }
        }
        $this->setColumnsMap($columnsMap);

        return $value;
    }

    /**
     * Implements the inheritance parent/associate
     * $params['skip_directive'] is set when concatenate directive is used
     *
     * @param  $type
     * @param  array $params
     * @return mixed|string
     */
    public function mapColumnByProductType($type, $params = array())
    {
        $column = $params['map']['column'];
        $parentMap = $this->getParentMap();

        switch ($type) {
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Associated::FROM_PARENT:
                $value = '';
                if ($parentMap) {
                    $value = isset($params['skip_directive']) ? $parentMap->getCellValue($params) : $parentMap->mapColumn($column);
                }
                break;
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Associated::FROM_ASSOCIATED:
                $value = $this->getCellValue($params);
                break;
            case RocketWeb_ShoppingFeeds_Model_Source_Product_Associated::FROM_PARENT_ASSOCIATED:
                $value = '';
                if ($parentMap) {
                    $value = isset($params['skip_directive']) ? $parentMap->getCellValue($params) : $parentMap->mapColumn($column);
                }
                if ($value == '') {
                    $value = $this->getCellValue($params);
                }
                break;

            case RocketWeb_ShoppingFeeds_Model_Source_Product_Associated::FROM_ASSOCIATED_PARENT:
            default:
                $value = $this->getCellValue($params);
                // Run replace empty rules if no value so far
                if ($value == "") {
                    $value = $this->mapEmptyValues($params);
                }
                if ($value == '' && $parentMap) {
                    $value = isset($params['skip_directive']) ? $parentMap->getCellValue($params) : $parentMap->mapColumn($column);
                }
                break;
        }

        return $value;
    }

    protected function _getMapModel($method)
    {
        $list = $this->getMapList();
        foreach ($list as $map) {
            $className = 'RocketWeb_ShoppingFeeds_Model_Map_' . $map;
            $callback = array($className, $method);
            if (is_callable($callback)) {
                // Not a Varien_Object!
                $model = Mage::getSingleton('rocketshoppingfeeds/map_' . $map);
                $model->addAdapter($this);
                return $model;
            }
        }
        return false;
    }

    /******************************************/
    /*************** PRODUCT METHODS **********/
    /**
     * Computes prices for given or current product.
     * It returns an array of 4 prices: price and special_price, both including and excluding tax
     *
     * @return mixed
     */
    public function getPrices()
    {
        if ($this->hasData('price_array')) {
            return $this->getData('price_array');
        }

        /** @var Mage_Weee_Helper_Data $weeeHelper */
        $weeeHelper = $this->getHelper('weee');
        /** @var RocketWeb_ShoppingFeeds_Helper_Tax $taxHelper */
        $taxHelper = $this->getHelper('rocketshoppingfeeds/tax');
        $helper = $this->getHelper();
        $store = $this->getStore();
        $algorithm = $taxHelper->getConfig()->getAlgorithm($store);
        $isVersion1702OrLess = version_compare(Mage::getVersion(), '1.7.0.2', '<=');

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getProduct();
        if ($helper->isModuleEnabled('Aitoc_Aitcbp')) {
            $product = $product->load($product->getid());
        }

        $qtyIncrements = $helper->getQuantityIcrements($product);

        // Compute Weee tax
        $weeeExcludingTax = $weeeHelper->getAmountForDisplay($product);
        $weeeIncludingTax = $weeeExcludingTax;
        if ($weeeHelper->isTaxable()) {
            $weeeIncludingTax = $weeeHelper->getAmountInclTaxes($weeeHelper->getProductWeeeAttributesForRenderer($product, null, null, null, true));
        }

        $prices = array();
        // Compute equivalent to default/template/catalog/product/price.phtml
        $price = $product->getPrice();
        $convertedPrice = $this->convertPrice($price);
        $prices['p_excl_tax'] = $taxHelper->getPrice($product, $convertedPrice);
        $prices['p_incl_tax'] = $taxHelper->getPrice($product, $convertedPrice, true);

        $catalogRulesPrice = $this->getPriceByCatalogRules();
        $finalPrice = $catalogRulesPrice ? min($catalogRulesPrice, $product->getFinalPrice()) : $product->getFinalPrice();
        $convertedFinalPrice = $this->convertPrice($finalPrice);

        $prices['sp_excl_tax'] = $taxHelper->getPrice($product, $convertedFinalPrice);
        $prices['sp_incl_tax'] = $taxHelper->getPrice($product, $convertedFinalPrice, true);

        if ($algorithm !== Mage_Tax_Model_Calculation::CALC_UNIT_BASE && $qtyIncrements > 1.0) {
            // We need to multiply base before calculating tax for whole ((itemPrice * qty) + vat = total)
            $prices['p_excl_tax'] *= $qtyIncrements;
            $prices['p_incl_tax'] = $taxHelper->getPrice($product, $prices['p_excl_tax'], true);

            $prices['sp_excl_tax'] *= $qtyIncrements;
            $prices['sp_incl_tax'] = $taxHelper->getPrice($product, $prices['sp_excl_tax'], true);
        } else if ($qtyIncrements > 1.0) {
            // We just need to multiply incl_tax/excl_tax prices
            foreach ($prices as $code => $price) {
                $prices[$code] = $price * $qtyIncrements;
            }
        }

        foreach ($prices as $code => $price) {
            if (strpos($code, '_incl_') !== false) {
                $price = $price + $weeeIncludingTax;
            } else {
                $price = $price + $weeeExcludingTax;
            }
            /**
             * Version <= 1.7.0.2 doesn't use roundPrice()
             * so we need to change it
             */
            if (!$isVersion1702OrLess) {
                $price = $store->roundPrice($price);
            }
            $prices[$code] = $price;
        }

        $this->setData('price_array', $prices);
        return $this->getData('price_array');
    }

    /**
     * @param bool|true $processRules
     * @param null $product
     * @return bool
     * @throws Zend_Date_Exception
     */
    public function hasSpecialPrice($processRules = true, $product = null)
    {
        $has = false;
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if ($processRules && $this->hasPriceByCatalogRules()) {
            $has = true;
        }
        elseif ($this->getHelper()->hasMsrp($product))
        {
            $has = true;
        }
        else
        {
            $specialPrice = $this->getAttributeValue($product, $this->getGenerator()->getAttribute('special_price'));
            if ($specialPrice > 0) {
                $cDate = Mage::app()->getLocale()->date(null, null, Mage::app()->getLocale()->getDefaultLocale());
                $timezone = $this->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);

                // From Date
                $fromDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
                if ($timezone) {
                    $fromDate->setTimezone($timezone);
                }

                $specialFromDate = $product->getSpecialFromDate();
                if (is_empty_date($specialFromDate)) {
                    $specialFromDate = $cDate->toString('yyyy-MM-dd HH:mm:ss');
                }

                $fromDate->setDate(substr($specialFromDate, 0, 10), 'yyyy-MM-dd');
                $fromDate->setTime(substr($specialFromDate, 11, 8), 'HH:mm:ss');

                // To Date
                $toDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
                if ($timezone) {
                    $toDate->setTimezone($timezone);
                }

                $specialToDate = $product->getSpecialToDate();
                if (is_empty_date($specialToDate)) {
                    $specialToDate = $cDate->toString('yyyy-MM-dd HH:mm:ss');
                }
                if (is_empty_date($specialToDate)) {
                    $toDate->add(365, Zend_Date::DAY);
                }

                $toDate->setDate(substr($specialToDate, 0, 10), 'yyyy-MM-dd');
                $toDate->setTime(substr($specialToDate, 11, 8), 'HH:mm:ss');

                if (($fromDate->compare($cDate) == -1 || $fromDate->compare($cDate) == 0) && ($toDate->compare($cDate) == 1 || $toDate->compare($cDate) == 0)) {
                    $has = true;
                }
            }
        }

        return $has;
    }

    /**
     * @param null $product
     * @return bool
     */
    public function hasPriceByCatalogRules()
    {
        $has = false;
        $product = $this->getProduct();

        if ($this->getFeed()->getConfig('general_apply_catalog_price_rules')) {
            $rulesPrice = $this->getPriceByCatalogRules();

            if (round($product->getPrice(), 2) != round($rulesPrice, 2)) {
                $specialPrice = $product->getSpecialPrice();
                $hasSpecialPrice = $this->hasSpecialPrice(false);

                if ($hasSpecialPrice && $specialPrice > 0 && floatval($specialPrice) < floatval($rulesPrice)) {
                    $has = false;
                } else {
                    $has = true;
                }
            }
        }

        return $has;
    }

    /**
     * When computing the special price, we send the $price parameter from associated items
     * @return mixed
     */
    public function getPriceByCatalogRules($price = null)
    {
        if (is_null($price)) {
            $price = $this->getProduct()->getPrice();
        }

        return Mage_Catalog_Model_Product_Type_Price::calculatePrice(
            $price,
            false, false, false, false,
            $this->getStore()->getWebsiteId(),
            Mage_Customer_Model_Group::NOT_LOGGED_IN_ID,
            $this->getProduct()->getId()
        );
    }

    /**
     * Retrieves the start and end date for the catalog rule that applies to the product.
     * If there's no rule, or if the rule doesn't have dates, it defaults 365 days
     *
     * @see self::hasPriceByCatalogRules() - you should first check if the product has catalog rules
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  null|string|Zend_Date $date day to use when looking up prices and start of interval, defaults to today
     * @return false|Zend_Date[] 'start','end'
     */
    protected function _getCatalogRuleEffectiveDates($product, $date = null)
    {
        $read = $this->getTools()->getConnRead();

        if ($date == null) {
            $date = Mage::app()->getLocale()->storeTimeStamp($this->getData('store_id'));
        }
        $date = new Zend_Date($date);

        $select = $read->select()
            ->from(
                Mage::getResourceModel('catalogrule/rule')->getTable('catalogrule/rule_product_price'),
                array('latest_start_date', 'earliest_end_date')
            )
            ->where('rule_date=?', Varien_Date::formatDate($date, false))
            ->where('website_id=?', Mage::app()->getStore($this->getStore()->getId())->getWebsiteId())
            ->where('product_id=?', $product->getId())
            ->where('rule_price=?', $this->getPriceByCatalogRules())
            ->where('customer_group_id=?', Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        $rule = $read->fetchRow($select);

        $dates = array();

        if ($rule['latest_start_date']) {
            $dates['start'] = new Zend_Date($rule['latest_start_date'], 'yyyy-MM-dd');
        } else {
            $dates['start'] = clone $date;
            $dates['start']->setTime('00:00:00', 'HH:mm:ss');
        }

        if ($rule['earliest_end_date']) {
            $dates['end'] = new Zend_Date($rule['earliest_end_date'], 'yyyy-MM-dd');
        } else {
            $dates['end'] = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
            $dates['end']->setDate($date->toString('yyyy-MM-dd'), 'yyyy-MM-dd');
            $dates['end']->setTime('23:59:59', 'HH:mm:ss');
            $dates['end']->add(365, Zend_Date::DAY);
        }

        $timezone = $this->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);

        if ($timezone) {
            $dates['start']->setTimezone($timezone);
            $dates['end']->setTimezone($timezone);
        }

        return $dates;
    }

    /******************************************/
    /*********** MAPPER HELPER METHODS ********/

    /**
     * @param Zend_Date[] $dates ('start', 'end')
     * @return string
     */
    public function formatDateInterval($dates)
    {
        if (is_array($dates) && array_key_exists('start', $dates) && array_key_exists('end', $dates)) {
            return $dates['start']->toString(Zend_Date::ISO_8601) . "/" . $dates['end']->toString(Zend_Date::ISO_8601);
        } else {
            return '';
        }
    }

    /**
     * Cleans field by Google Shopping specs.
     *
     * @param  string $field
     * @return string
     */
    public function cleanField($field, $params = null)
    {
        // Find and Replace
        if (!is_null($params) && array_key_exists('map', $params) && array_key_exists('column', $params['map'])) {
            $this->findAndReplace($field, $params['map']['column']);
        }
        if (is_array($field) && empty($field)) {
            $field = '';
        }
        if (extension_loaded('mbstring')) {
            mb_convert_encoding($field, mb_detect_encoding($field, mb_detect_order(), true), "UTF-8");
        }

        $field = strtr(
            $field, array(
                "\"" => "&quot;",
                "'" => "&#39;",
                "’" => "&rsquo;",
                "’" => "&#8217;",
                "‘" => "&#8216;",
                "\t" => " ",
                "\n" => " ",
                "\r" => " ",
            )
        );

        $field = strip_tags($field, '>');
        if (extension_loaded('mbstring')) {
            $field = preg_replace_callback("/(&#?[a-z0-9]{2,8};)/i", array($this->getHelper(), 'htmlEntitiesToUtf8Callback'), $field);
        }
        $field = preg_replace('/\s\s+/', ' ', $field);
        $field = str_replace(PHP_EOL, "", $field);
        $field = trim($field);

        return $field;
    }

    /**
     * Find a replace logic
     *
     * @param $string
     * @param $column
     */
    public function findAndReplace(&$string, $column)
    {
        if (!$this->getGenerator()->hasData('cache_find_and_replace')) {

            $def = array('find' => array(), 'replace' => array());
            $find_and_replace = array('-all-' => $def);

            $current_img = $this->getFeed()->getConfig('filters_find_and_replace');
            if (!empty($current_img) && !is_array($current_img)) {
                $current_img = unserialize($current_img);
            }

            if (is_array($current_img) && count($current_img)) {
                foreach ($current_img as $item) {
                    if (empty($item['columns'])) {
                        array_push($find_and_replace['-all-']['find'], $item['find']);
                        array_push($find_and_replace['-all-']['replace'], $item['replace']);
                    } else {
                        if (!array_key_exists($item['columns'], $find_and_replace)) {
                            $find_and_replace[$item['columns']] = $def;
                        }
                        array_push($find_and_replace[$item['columns']]['find'], $item['find']);
                        array_push($find_and_replace[$item['columns']]['replace'], $item['replace']);
                    }
                }
            }
            $this->getGenerator()->setData('cache_find_and_replace', $find_and_replace);

        } elseif ($this->getGenerator()->hasData('cache_find_and_replace')) {
            $find_and_replace = $this->getGenerator()->getData('cache_find_and_replace');
        }

        // Find and replace
        if (array_key_exists((string)$column, $find_and_replace)) {
            $string = str_replace($find_and_replace[$column]['find'], $find_and_replace[$column]['replace'], $string);
        }
        if (count($find_and_replace['-all-']['find'])) {
            $string = str_replace($find_and_replace['-all-']['find'], $find_and_replace['-all-']['replace'], $string);
        }
    }

    /**
     * Y-m-d H:i:s to timestamp
     *
     * @param int $date
     */
    public function dateToTime($date)
    {

        return mktime(
            substr($date, 11, 2),
            substr($date, 14, 2),
            substr($date, 17, 2),
            substr($date, 5, 2),
            substr($date, 8, 2),
            substr($date, 0, 4)
        );
    }

    /**
     * @param $msg
     * @param null $level
     * @return mixed
     */
    public function log($msg, $level = null)
    {
        return $this->getGenerator()->log($msg, $level);
    }
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return boolean
     */

    public function checkSkipSubmission($type = 'configurable')
    {
        // if it's not, check if we should skip it via skip attr
        if (!$this->isSkip() && $this->getProduct()->getData('rw_google_base_skip_submi') == 1) {
            $this->setSkip(sprintf(
                "product id %d sku %s, ".$type." associated, skipped - product has 'Skip from Being Submitted' = 'Yes'.",
                $this->getProduct()->getId(), $this->getProduct()->getSku()
            ));
        }

        return $this->isSkip();
    }

    /******************************************/
    /*************** OTHER METHODS **********/

    /**
     * Implements mapAttribute but it truncates the result with max_len
     *
     * @param  $params
     * @param  int $max_len
     * @return string
     */
    public function getTruncatedAttribute($params, $max_len = 0)
    {
        $map = $params['map'];
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getProduct();
        $attribute = $this->getGenerator()->getAttribute($map['attribute']);

        if ($attribute === false) {
            Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
        }

        $value = $this->getAttributeValue($product, $attribute);
        $value = $this->cleanField($value, $params);

        if ($max_len > 0) {
            $ref = '';
            $value = Mage::helper('core/string')->truncate($value, $max_len, '', $ref, false);
        }

        return $value;
    }

    /**
     * Fetch associated products ids of configurable product.
     * Filtered by current store_id (website_id) and status (enabled).
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  string $store_id
     * @return array | false
     */
    public function loadAssocIds($product, $store_id)
    {
        $as = false;
        $assoc_ids = array();

        if ($product->isConfigurable() || $product->getTypeId() == RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Configurable_Subscription::PRODUCT_TYPE) {
            $as = $this->getTools()->getConfigurableChildsIds($product->getId());
        } elseif ($product->isGrouped() || $product->getTypeId() == RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Grouped_Subscription::PRODUCT_TYPE) {
            $as = $this->getTools()->getGroupedChildsIds($product->getId());
        }

        if ($as === false) {
            return $assoc_ids;
        }

        $as = $this->getTools()->getProductInStoresIds($as);

        foreach ($as as $assocId => $s) {
            $attribute = $this->getGenerator()->getAttribute('status');
            $status = $this->getTools()->getProductAttributeValueBySql($attribute, $attribute->getBackendType(), $assocId, $store_id);

            if ($status != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                continue;
            }
            if (is_array($s) && array_search($store_id, $s) !== false) {
                $assoc_ids[] = $assocId;
            }
        }

        return $assoc_ids;
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @param $attribute
     * @return string
     */
    public function getAttributeValue($product, $attribute)
    {
        if ($attribute->getSourceModel() == 'eav/entity_attribute_source_boolean') {
            $value = $product->getData($attribute->getAttributeCode()) ? 'Yes' : 'No';
        }
        elseif ($attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "multiselect") {
            $value = $this->getAttributeSelectValue($product, $attribute, $this->getFeed()->getStoreId());
        } else {
            $value = $product->getData($attribute->getAttributeCode());
        }

        return $value;
    }

    /**
     * Gets option text value from product for attributes with frontend_type select.
     * Multiselect values are by default imploded with comma.
     * By default gets option text from admin store (recommended - english values in feed).
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getAttributeSelectValue($product, $attribute, $store_id = null)
    {
        if (is_null($store_id)) {
            $store_id = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        // Try to get the value from the custom source model
        if ($attribute->hasData('source_model') && $attribute->getData('source_model')
            && strpos($attribute->getData('source_model'), 'eav/entity_attribute_source') === false
        ) {
            $source_model = Mage::getModel($attribute->getData('source_model'));
            if (!$source_model) {
                $this->log(sprintf('Invalid source model in attribute "%s" > "%s"', $attribute->getData('attribute_code'), $attribute->getData('source_model')));
            } else {
                // TODO: if #source_model is RocketWeb_ShoppingFeeds_Model_Source_Category, than pass the $this->getFeed() to the $source_model.
                $options = $source_model->getAllOptions();
                $productAttributeData = $product->getData($attribute->getData('attribute_code'));

                // If attribute has a custom source_model which returns an array
                if (is_array($productAttributeData)) {
                    $attributeOptions = array();
                    foreach ($productAttributeData as $attributeValue) {
                        foreach ($options as $option) {
                            if ($attributeValue == $option['value']) {
                                $attributeOptions[] = $option['label'];
                            }
                        }
                    }
                    return implode(', ', $attributeOptions);
                }

                foreach ($options as $option) {
                    if ($productAttributeData == $option['value']) {
                        return $option['label'];
                    }
                }
            }
        }

        // Get the value from the mage eav/entity_attribute_source
        $attributeValueId = $this->getTools()->getProductAttributeValueBySql($attribute, $attribute->getBackendType(), $product->getId(), $store_id);
        $ret = $this->getTools()->getProductAttributeSelectValue($attribute, $attributeValueId, $store_id);
        return (strcasecmp($ret, "No") == 0 ? '' : $ret);
    }

    /**
     * @param $mapByCategory
     * @param $categoryIds
     * @param string $field
     * @return string
     */
    public function matchByCategory($mapByCategory, $categoryIds, $field = 'tx')
    {
        $value = '';
        if (!empty($categoryIds) && !empty($mapByCategory)) {
            // match logic
            foreach ($mapByCategory as $arr) {
                if (array_key_exists($field, $arr) && !empty($arr[$field])
                    && array_search($arr['id'], $categoryIds) !== false) {
                    $value = $arr[$field];
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @param $product
     * @param $codes
     * @param $typeId
     * @return string
     */
    public function addUrlUniqueParams($value, $product)
    {
        if (!$this->hasParentMap()) {
            return $value;
        }
        $parentProduct = $this->getParentMap()->getProduct();
        switch ($parentProduct->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                $codes = $this->getTools()->getOptionCodes($parentProduct->getId());
                foreach ($codes as $attributeId => $attributeCode) {
                    $data = $product->getData($attributeCode);
                    if (empty($data)) {
                        $this->setSkip(sprintf("product id %d product sku %s, can't fetch data from attribute: '%s' ('%s') to make create url.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $attributeCode, $data));
                        return $value;
                    }
                    $params[$attributeId] = $data;
                }
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE :
                $codes = $this->getTools()->getOptionCodes($parentProduct->getId());
                $params = $codes;
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                $params = array('prod_id' => $product->getId());
                break;
            default:
                $params = array();
        }
        $params['aid'] = $this->getProduct()->getId();

        $urlInfo = parse_url($value);
        if ($urlInfo !== false) {
            if (isset($urlInfo['query'])) {
                $urlInfo['query'] .= '&' . http_build_query($params);
            } else {
                $urlInfo['query'] = http_build_query($params);
            }
            $new = "";
            foreach ($urlInfo as $k => $v) {
                if ($k == 'scheme') {
                    $new .= $v . '://';
                } elseif ($k == 'port') {
                    $new .= ':' . $v;
                } elseif ($k == 'query') {
                    $new .= '?' . $v;
                } elseif ($k == 'fragment') {
                    $new .= '#' . $v;
                } else {
                    $new .= $v;
                }
            }
            if (parse_url($new) === false) {
                $this->setSkip(sprintf("product id %d product sku %s, failed to form new url: %s from old url %s.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $new, $value));
            } else {
                $value = $new;
            }
        }

        return $value;
    }

    public function getChildrenCount() {
        return 1;
    }

    /**
     * @return $this
     */
    public function setSkip($skipMessage)
    {
        if ($this->getFeed()->getData('auto_skip')) {
            $this->setData('skip', true);
            $this->getGenerator()->updateCountSkip($this->getChildrenCount());
            $this->log($skipMessage);
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSkip()
    {
        return is_bool($this->getData('skip')) ? $this->getData('skip') : false;
    }

    /**
     * @return $this
     */
    public function unSkip()
    {
        $this->setData('skip', false);
        return $this;
    }

    /**
     * @param $parentRow
     * @param $rows
     */
    protected function mergeVariantValuesToParent(&$parentRow, $rows)
    {
        $inheritColumns = array('size' => array(), 'color' => array(), 'gender' => array(),
            'age_group' => array(), 'material' => array(), 'pattern' => array());

        // When isAllowConfigurableAssociatedMode() is off, need to map associated apparel columns before merge
        if (empty($rows)) {
            $adapters = $this->getAssocAdapters();
            if (!count($adapters) && $this->hasVariants()) {
                foreach ($this->getVariants() as $assoc) {
                    $adapters[] = Mage::helper('rocketshoppingfeeds/factory')
                        ->getChildAdapterModel($assoc, $this, $this->getFeed());
                }
            }
            foreach ($adapters as $assocAdapter) {
                $row = array();
                foreach (array_keys($inheritColumns) as $column) {
                    if (array_key_exists($column, $parentRow)) {
                        $row[$column] = $assocAdapter->mapColumn($column);
                    }
                }
                array_push($rows, $row);
            }
        }

        foreach ($rows as $row) {
            foreach ($inheritColumns as $column => $v) {
                if (!array_key_exists($column, $row) || empty($row[$column])) {
                    continue;
                }
                
                if (!in_array($row[$column], $inheritColumns[$column])) {
                    array_push($inheritColumns[$column], $row[$column]);
                }
            }
        }

        foreach ($inheritColumns as $column => $v) {
            if (!array_key_exists($column, $parentRow)) {
                continue;
            }

            if (is_array($parentRow[$column])) {
                foreach ($parentRow[$column] as $value) {
                    if ((array_search($value, $v) === false) && !empty($value)) {
                        $v[] = $value;
                    }
                }
            } else {
                if ((array_search($parentRow[$column], $v) === false) && !empty($parentRow[$column])) {
                    $v[] = $parentRow[$column];
                }
            }

            if (count($v)) {
                $v = array_filter($v);
                $parentRow[$column] = implode(', ', $v);
            }
        }
    }

    /**
     * @return bool
     */
    public function isDuplicate()
    {
        $process = Mage::getModel('rocketshoppingfeeds/process')->load($this->getProduct()->getEntityId(), 'item_id');
        $process->setParentItemId($this->hasParentMap() ? $this->getParentMap()->getProduct()->getEntityId() : $process->getParentItemId());

        if ($process->getId()) {
            if ($process->getStatus() == RocketWeb_ShoppingFeeds_Model_Process::STATUS_PROCESSED && intval($process->getParentItemId()) > 0) {
                $this->setSkip(sprintf('Product SKU %s, ID %d was omitted - it was already processed as part of product ID %d', $this->getProduct()->getSku(), $this->getProduct()->getEntityId(), $process->getParentItemId()));
                return true;
            } else {
                $process->process();
            }
        } else {
            $process->addData(array('feed_id' => $this->getFeed()->getId(), 'item_id' => $this->getProduct()->getEntityId()))
                ->process();
        }

        return false;
    }

    /**
     * Product Options are implemented for simple products for now.
     * @return bool
     */
    protected function _isAllowProductOptions()
    {
        if ($this->getTools()->isAllowProductOptions()) {
            $categs = $this->getFeed()->getConfig('options_vary_categories');
            $match = $categs ? array_intersect($categs, $this->getProduct()->getCategoryIds()) : array();

            if (!$categs || ($categs && $match)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Builds the associated maps from assocs array
     *
     * @return $this
     */
    protected function _setAssocAdapters()
    {
        $assocAdapterArr = array();
        if (count($this->getAssociated())) {
            foreach ($this->getAssociated() as $assoc) {
                $assocAdapter = Mage::helper('rocketshoppingfeeds/factory')
                    ->getChildAdapterModel($assoc, $this, $this->getFeed());
                if ($assocAdapter->checkSkipSubmission()) {
                    continue;
                }
                if (!$this->hasSkipDuplicateCheck() && $assocAdapter->isDuplicate()) {
                    continue;
                }
                $assocAdapterArr[$assoc->getId()] = $assocAdapter;
            }
        }
        $this->setAssocAdapters($assocAdapterArr);
        return $this;
    }

    /**
     * @param $rows
     * @return $this
     */
    protected function _checkEmptyColumns($row)
    {
        $skipEmptyColumn = $this->getFeed()->getConfig('filters_skip_column_empty');

        foreach ($skipEmptyColumn as $column) {
            if (isset($row[$column]) && $row[$column] == "") {
                $this->setSkip(sprintf("product id %d product sku %s, skipped - by product skip rule, has %s empty.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $column));
                break;
            }
        }

        return $this;
    }

    /**
     * Used in all complex products to iterate through all children products
     *
     * @deprecated
     * @use RocketWeb_ShoppingFeeds_Helper_Factory::getChildAdapterModel
     */
    protected function _getAssocAdapterModel($product)
    {
        return Mage::helper('rocketshoppingfeeds/factory')->getChildAdapterModel(
            $product, $this, $this->getFeed()
        );
    }

    /******************************************/
    /*************** GETTERS  *****************/

    /**
     * @return RocketWeb_ShoppingFeeds_Model_Tools
     */
    public function getTools()
    {
        return $this->getGenerator()->getTools();
    }

    /**
     * Singleton by $storeId of generator class
     *
     * @return RocketWeb_ShoppingFeeds_Model_Generator
     */
    public function getGenerator()
    {
        $registryKey = '_singleton/rocketshoppingfeeds/generator_feed_' . $this->getFeed()->getId();

        if (!Mage::registry($registryKey)) {
            Mage::register($registryKey, Mage::getModel('rocketshoppingfeeds/generator', $this->getData()));
        }

        return Mage::registry($registryKey);
    }

    /**
     * @param string $name
     * @return RocketWeb_ShoppingFeeds_Helper_Data
     */
    public function getHelper($name = 'rocketshoppingfeeds')
    {
        return Mage::helper($name);
    }

    /**
     * Returns store instance with feed currency
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (!$this->hasData('store')) {
            $this->setData('store', $this->getFeed()->getStore());
        }
        return $this->getData('store');
    }

    /**
     * Catch any undefined currency rate
     *
     * @param $price
     * @return float
     */
    public function convertPrice($price)
    {
        try {
            // Convert the price according to store's currency
            return $this->getStore()->convertPrice($price);
        } catch (Exception $e) {
            // Set currency back to original
            $this->setData('store_currency_code', $this->getStore()->getCurrentCurrencyCode());
            return $price;
        }
    }

    /**
     * @return float|int
     */
    public function getInventoryCount()
    {
        $v = 0;
        $stockQty = floatval(
            Mage::getModel('cataloginventory/stock_item')
                ->setStoreId($this->getFeed()->getStoreId())
                ->loadByProduct($this->getProduct())
                ->getQty()
        );
        if ($stockQty > 0) {
            $v = $stockQty;
        }
        return $v;
    }

    /**
     * Get an array of sale price effective dates from catalog rules or product's special price
     *
     * @return false|Zend_Date[]
     */
    public function getSalePriceEffectiveDates()
    {
        $product = $this->getProduct();

        if ($this->hasPriceByCatalogRules($product)) {
            return $this->_getCatalogRuleEffectiveDates($product);
        } else if ($this->hasSpecialPrice(false)) {
            return $this->_getSpecialPriceEffectiveDates($product);
        }

        return false;
    }

    /**
     * Retrieves the start and end date for the product's special price, if they exist.
     *
     * @see self::hasSpecialPrice() - you should check to see if the product is using a special price
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return false|Zend_Date[] 'start','end'
     */
    protected function _getSpecialPriceEffectiveDates($product)
    {
        $special_from_date = $product->getSpecialFromDate();
        $special_to_date = $product->getSpecialToDate();

        if ((empty($special_from_date) && empty($special_to_date))) {
            return false;
        }

        $cDate = Mage::app()->getLocale()->date(null, null, Mage::app()->getLocale()->getDefaultLocale());
        $timezone = $this->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);

        // From Date
        if (is_empty_date($special_from_date)) {
            $special_from_date = $cDate->toString('yyyy-MM-dd HH:mm:ss');
        }

        $fromDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());

        if ($timezone) {
            $fromDate->setTimezone($timezone);
        }

        $fromDate->setDate(substr($special_from_date, 0, 10), 'yyyy-MM-dd');
        $fromDate->setTime(substr($special_from_date, 11, 8), 'HH:mm:ss');

        // To Date
        if (is_empty_date($product->getSpecialToDate())) {
            $special_to_date = $cDate->toString('yyyy-MM-dd HH:mm:ss');
        }

        $toDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());

        if ($timezone) {
            $toDate->setTimezone($timezone);
        }

        $toDate->setDate(substr($special_to_date, 0, 10), 'yyyy-MM-dd');
        $toDate->setTime('23:59:59', 'HH:mm:ss');

        if (is_empty_date($product->getSpecialToDate())) {
            $toDate->add(365, Zend_Date::DAY);
        }

        return array(
                'start' => $fromDate,
                'end' => $toDate
        );
    }

    /**
     * Computes the taxoomy category array and sorted by priority and deepth according to the sorting mode
     *
     * @return mixed
     */
    public function getSortedTaxonomyMap()
    {
        if (!$this->hasData('taxonomy_map')) {
            $map = $this->getFeed()->getConfig('categories_provider_taxonomy_by_category');

            // Load categories to the the level path for each one
            $categories = Mage::helper('rocketshoppingfeeds')->getAllCategories($this->getFeed());
            $sort = array();
            foreach ($map as $k => $v) {
                // Build a sort array by category level and priority
                $sort['level'][$k] = (array_key_exists($k, $categories)) ? $categories[$k]['level'] : 0;
                $sort['priority'][$k] = $v['p'];
            }

            // Perform the sort
            if (!empty($map)) {
                if ($this->getFeed()->getConfig('categories_sort_mode') == RocketWeb_ShoppingFeeds_Model_Source_Category_Mode::PRIORITY_AFTER_LEVEL) {
                    array_multisort($sort['level'], SORT_DESC, $sort['priority'], SORT_ASC, $map);
                } else {
                    array_multisort($sort['priority'], SORT_ASC, $map);
                }
            }

            $this->setData('taxonomy_map', $map);
        }

        return $this->getData('taxonomy_map');
    }
}
