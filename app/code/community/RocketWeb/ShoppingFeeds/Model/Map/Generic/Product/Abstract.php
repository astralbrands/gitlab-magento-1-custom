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
class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Abstract
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    protected $_cache_categories = array();

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveId($params = array())
    {
        $cell = $this->getAdapter()->getProduct()->getId();
        if ($params['map']['param']) {
            $cell .= preg_replace('/[^a-zA-Z0-9]/', "", $this->getAdapter()->getStore()->getCode());
        }
        return $this->getAdapter()->cleanField($cell, $params);
    }

    /**
     * Aims to group associated products of configurable into products that vary size, color, material or pattern,
     * setting the impression that there are several configurable products instead of just one.
     * For configurable products, returns the parent SKU of the product suffixed by non-variant attribute values.
     * returns empty value for other products.
     *
     * @param array $params
     * @return mixed
     */
    public function mapDirectiveItemGroupId($params = array())
    {
        if (!$this->getAdapter()->hasParentMap()) {
            return '';
        }

        $variable_columns = array();
        $attr = array_key_exists('param', $params['map']) && !empty($params['map']['param']) ? $params['map']['param'] : 'sku';
        $code = $this->getAdapter()->getParentMap()->getProduct()->getData($attr);

        // Find out what which attributes are been used in the map, and which may vary.
        $options = $this->getAdapter()->getTools()->getOptionCodes($this->getAdapter()->getParentMap()->getProduct()->getId());
        foreach ($this->getAdapter()->getColumnsMap() as $column => $map) {

            if (in_array($column, array('color', 'size', 'material', 'pattern'))) {
                if (in_array($map['attribute'], $options)) {
                    $variable_columns[$map['attribute']] = $column;
                }
                if (array_key_exists('param', $map)) {
                    if (is_array($map['param'])) {
                        foreach ($map['param'] as $val) {
                            $val = strtolower($val);
                            if (in_array($val, $options)) {
                                $variable_columns[$val] = $column;
                            }
                        }
                    } elseif (in_array($map['param'], $options)) {
                        $variable_columns[$map['param']] = $column;
                    }
                }
            }
        }

        // Suffix the Parent SKU with non-variable option values
        $suffixes = array();
        $diff = array_diff(array_values($options), array_keys($variable_columns));
        foreach ($diff as $attr_code) {
            $suffixes[] = $this->getAdapter()->getAttributeValue($this->getAdapter()->getProduct(), $this->getAdapter()->getGenerator()->getAttribute($attr_code));
        }

        //$codeAndSuffixes = $code . '-' . implode('-', $suffixes);
		/*
		* Removing simple product variant value.
		* customization request by sidecar
		*/
		$codeAndSuffixes = $code;
		
        if (count($suffixes) && strlen($codeAndSuffixes) >= 50) {
            if (strlen($code) >= 9) {
                // We hash the whole string since sha1() returns 40 chars and we hit 50 total
                $codeAndSuffixes = sha1($codeAndSuffixes);
            } else {
                // For at least a bit easier readability we hash only suffixes
                $onlySuffixes = implode('-', $suffixes);
                $codeAndSuffixes = $code . '-' . sha1($onlySuffixes);
            }
        }

        return count($suffixes) ? $codeAndSuffixes : $code;
    }

    /**
     * @param array $params
     * @return mixed
     * @todo Check if this can be moved?!?
     */
    public function mapColumnDescription($params = array())
    {
        $type = -1;

        if ($params['map']['attribute'] !== 'rw_gbase_directive_concatenate') {
            // Determine the type
            if ($this->getAdapter()->hasParentMap()) {
                switch ($this->getAdapter()->getParentMap()->getProduct()->getTypeId()) {
                    case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                        $type = $this->getAdapter()->getFeed()->getConfig('configurable_associated_products_description');
                        break;
                    case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                        $type = $this->getAdapter()->getFeed()->getConfig('grouped_associated_products_description');
                        break;
                }
            }
        }

        return $this->getAdapter()->mapColumnByProductType($type, $params);
    }
    /**
     * Does not do anything other than returns the static value
     * @param array $params
     * @return string
     */
    public function mapDirectiveStaticValue($params = array())
    {
        $value = isset($params['map']['param']) ? $params['map']['param'] : "";
        return $this->getAdapter()->cleanField($value, $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveProductReviewAverage($params = array())
    {
        // @var $product Mage_Catalog_Model_Product
        if ($parent = $this->getAdapter()->getParentMap()) {
            $product = $parent->getProduct();
        } else {
            $product = $this->getAdapter()->getProduct();
        }

        $avg = 0;
        $summaryData = Mage::getModel('review/review_summary')->setStoreId($this->getAdapter()->getData('store_id'))
            ->load($product->getId());
        if (isset($summaryData['rating_summary'])) {
            $avg = $summaryData['rating_summary'] > 0 ? $summaryData['rating_summary'] * 5 / 100 : 0;
        }

        return $this->getAdapter()->cleanField($avg, $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveProductReviewCount($params = array())
    {
        // @var $product Mage_Catalog_Model_Product
        if ($parent = $this->getAdapter()->getParentMap()) {
            $product = $parent->getProduct();
        } else {
            $product = $this->getAdapter()->getProduct();
        }

        $reviewSummary = Mage::getModel('review/review_summary')
            ->setStoreId($this->getAdapter()->getData('store_id'))
            ->load($product->getId());

        return $this->getAdapter()->cleanField($reviewSummary->getData('reviews_count'), $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveUrl($params = array())
    {
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $add_to_url = array_key_exists('param', $params['map']) ? $params['map']['param'] : '';

        // Mageworks_Megamenu fix admin store view in the URL
        Mage::app()->setCurrentStore($product->getStore());

        $url = $product->getProductUrl();
        $pieces = parse_url($product->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));

        if (strpos($url, $pieces['host']) === false) {
            $url = $pieces['scheme'] . '://' . $pieces['host'] . $url;
        } else {
            $pieces = parse_url($url);
            $url = $pieces['scheme'] . '://' . $pieces['host'] . $pieces['path'];
        }

        $cell = $url . $add_to_url;
        $mapKey = $this->getAdapter()->getGenerator()->getData('map_key');
        if (!empty($mapKey)) {
            $cell .= strpos($cell, '?') === false ? '?' : '&';
            $cell .= 'm=' . $mapKey;
        }

        $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveImageLink($params = array())
    {
        if (!isset($params['loop'])) {
            $params['loop'] = true;
            return $this->_mapDirectiveImage($params);
        }

        $url = '';
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $image_type = (array_key_exists('param', $params['map']) && !empty($params['map']['param'])) ? $params['map']['param'] : 'image';

        // try to get image from cache
        if ($this->getAdapter()->getFeed()->getConfig('general_use_image_cache')) {
            try {
                $image = Mage::helper('rocketshoppingfeeds/image');
                $url = (string)$image->setStore($this->getAdapter()->getStore())->init($product, $image_type);
            } catch (Exception $e) {
                $url = false;
            }
        }

        // try to get the direct image URL
        if (empty($url)) {
            $image = $product->getData($image_type);
            if ($image != 'no_selection' && $image != "") {
                $url = $this->getAdapter()->getData('images_url_prefix') . '/' . ltrim($image, '/');
            }
        }

        $this->getAdapter()->findAndReplace($url, $params['map']['column']);
        return $url;
    }

    /**
     * @param array $params
     * @return mixed
     */
    protected function _mapDirectiveImage($params = array())
    {
        $type = -1;

        if ($params['map']['attribute'] !== 'rw_gbase_directive_concatenate') {
            // Determine the type
            if ($this->getAdapter()->hasParentMap()) {
                switch ($this->getAdapter()->getParentMap()->getProduct()->getTypeId()) {
                    case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                        $type = $this->getAdapter()->getFeed()->getConfig('configurable_associated_products_image_link');
                        break;
                    case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                        $type = $this->getAdapter()->getFeed()->getConfig('grouped_associated_products_image_link');
                        break;
                }
            }
        }

        return $this->getAdapter()->mapColumnByProductType($type, $params);
    }


    /**
     * Implement MagicToolbox_Magic360 main image
     * @param array $params
     * @return string
     */
    public function mapDirectiveImageLink360Magic($params = array())
    {

        if (!$this->getAdapter()->getHelper()->isModuleEnabled('MagicToolbox_Magic360')) {
            return $this->mapDirectiveImageLink($params);
        }

        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $images = Mage::getModel('rocketshoppingfeeds/thirdparty_magic360')->getProductImages($product);
        $first = reset($images);
        $cell = $first['medium'];

        $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * Toybanana_Extimages implementation
     *
     * @param  array $params
     * @return string
     */
    public function mapDirectiveExternalImageLink($params = array())
    {
        if (!$this->getAdapter()->getHelper()->isModuleEnabled('Toybanana_Extimages')) {
            return $this->mapDirectiveImageLink($params);
        }

        $image = '';
        if (Mage::getStoreConfig('ExtImages/general/enabled', $this->getAdapter()->getFeed()->getStoreId()) && $this->getAdapter()->getProduct()->getData('use_external_images')) {
            $imageObj = $this->getAdapter()->getHelper('catalog/image')->init($this->getAdapter()->getProduct(), 'image');
            $image = $imageObj->getRawUrl();

            if (empty($image)) {
                if (array_key_exists('image_link', $this->_columns_map) && $this->_columns_map['image_link']['attribute'] == 'rw_gbase_directive_external_image_link') {
                    $image = $this->getAdapter()->getProduct()->getData('image_external_url');
                }
            }
        }

        $this->getAdapter()->findAndReplace($image, $params['map']['column']);
        return $image;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveCategoryImageLink($params = array())
    {
        $image = '';
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();

        foreach ($product->getCategoryIds() as $id) {
            $category = Mage::getModel('catalog/category')->setStoreId($this->getAdapter()->getFeed()->getStoreId())->load($id);
            if ($image = $category->getImageUrl()) {
                break;
            }
        }

        $this->getAdapter()->findAndReplace($image, $params['map']['column']);
        return $image;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveAdditionalImageLink($params = array())
    {
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $image_type = (array_key_exists('param', $params['map']) && !empty($params['map']['param'])) ? $params['map']['param'] : 'image';
        $img = Mage::helper('rocketshoppingfeeds/image')->setStore($this->getAdapter()->getStore());

        if (empty($base_image)) {
            $base_image = $product->getData($image_type);
        }

        $urls = array();
        $c = 0;
        $media_gal_imgs = $product->getMediaGallery('images');

        if (is_array($media_gal_imgs) || $media_gal_imgs instanceof Varien_Data_Collection) {
            foreach ($media_gal_imgs as $image) {

                // Skip base image, disabled images, and limit to 10 images
                if (++$c > 10 || $image['disabled'] || strcmp($base_image, $image['file']) == 0) {
                    continue;
                }

                // try to get image from cache
                $url = false;
                if ($this->getAdapter()->getFeed()->getConfig('general_use_image_cache')) {
                    try {
                        $url = (string)$img->init($product, 'image', $image['file']);
                    } catch (Exception $e) {
                        $url = false;
                    }
                }

                // try to get the direct image URL
                if (empty($url)) {
                    $image['file'] = str_replace(DS, '/', $image['file']);
                    $url = $this->getAdapter()->getData('images_url_prefix') . '/' . ltrim($image['file'], '/');
                }

                $urls[] = $url;
            }
        }
        $cell = implode(",", $urls);
        $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * Implement MagicToolbox_Magic360 additional images
     * @param array $params
     * @return string
     */
    public function mapDirectiveAdditionalImageLink360Magic($params = array())
    {
        if (!$this->getAdapter()->getHelper()->isModuleEnabled('MagicToolbox_Magic360')) {
            return $this->mapDirectiveAdditionalImageLink($params);
        }

        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $images = Mage::getModel('rocketshoppingfeeds/thirdparty_magic360')->getProductImages($product);

        array_shift($images);
        foreach ($images as $image) {
            $cell[] = $image['medium'];
        }
        $cell = implode(',', $cell);
        $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectivePrice($params = array())
    {
        $prices = $this->getAdapter()->getPrices();
        $helper = $this->getAdapter()->getHelper();
        $includingTax = array_key_exists('param', $params['map']) ? (boolean)$params['map']['param'] : true;
        $price = $includingTax ? $prices['p_incl_tax'] : $prices['p_excl_tax'];

        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getAdapter()->getProduct();

        // equivalent to default/template/catalog/product/msrp_price.phtml
        if ($helper->hasMsrp($product)) {
            $qtyIncrements = $helper->getQuantityIcrements($product);
            // This will probably need some fixing on 1.7.0.2 versions
            $price = $this->getAdapter()->convertPrice($product->getMsrp() * $qtyIncrements);
        }

        $cell  = ($price > 0) ? sprintf("%.2F", $price) . ' ' . $this->getAdapter()->getData('store_currency_code') : '';

        $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * @param  array $params
     * @return string
     */
    public function mapDirectiveSalePrice($params = array())
    {
        if (!$this->getAdapter()->hasSpecialPrice()) {
            return '';
        }
        $prices = $this->getAdapter()->getPrices();
        $includingTax = array_key_exists('param', $params['map']) ? (boolean)$params['map']['param'] : true;
        $price = $includingTax ? $prices['sp_incl_tax'] : $prices['sp_excl_tax'];

        return ($price > 0) ? sprintf("%.2F", $price) . ' ' . $this->getAdapter()->getData('store_currency_code') : '';
    }

    /**
     * @param  array $params
     * @return string
     */
    public function mapDirectiveSalePriceEffectiveDate($params = array())
    {
        $cell = "";
        $dates = $this->getAdapter()->getSalePriceEffectiveDates();
        if (is_array($dates)) {
            $cell = $this->getAdapter()->formatDateInterval($dates);
        }

        if (!empty($cell)) {
            $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        }

        return $cell;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveQuantity($params = array())
    {
        return $this->getAdapter()->cleanField(sprintf('%d', $this->getAdapter()->getInventoryCount()), $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveAvailability($params = array())
    {
        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $helper = Mage::helper('rocketshoppingfeeds/map');

        if ($this->getAdapter()->getFeed()->getConfig('general_use_default_stock')) {
            $cell = $helper->getOutOfStockStatus();
            $stockItem = Mage::getModel('cataloginventory/stock_item');
            $stockItem->setStoreId($this->getAdapter()->getFeed()->getStoreId());
            $stockItem->getResource()->loadByProductId($stockItem, $product->getId());
            $stockItem->setOrigData();

            if ($stockItem->getId()) {
                if ($stockItem->getBackorders() > 0 ) {
                    if ($stockItem->getQty() > 0) {
                        $cell = $helper->getInStockStatus();
                    }
                }
                else if($stockItem->getIsInStock()) {
                    $cell = $helper->getInStockStatus();
                }
            }
        } else {
            $stock_attribute = $this->getAdapter()->getGenerator()->getAttribute($this->getAdapter()->getFeed()->getConfig('general_stock_attribute_code'));
            if ($stock_attribute === false) {
                Mage::throwException(sprintf('Invalid attribute for Availability column. Please make sure proper attribute is set under the setting "Alternate Stock/Availability Attribute.". Provided attribute code \'%s\' could not be found.', $this->getAdapter()->getFeed()->getConfig('general_stock_attribute_code')));
            }

            $stock_status = trim(strtolower($this->getAdapter()->getAttributeValue($product, $stock_attribute)));
            if (array_search($stock_status, $helper->getAllowedStockStatuses()) === false) {
                $stock_status = $helper->getOutOfStockStatus();
            }

            $cell = $stock_status;
        }

        return $this->getAdapter()->cleanField($cell, $params);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function mapDirectiveExpirationDate($params = array())
    {
        $days = intval($params['map']['param']) - 1;
        $days = $days < 0 ? 0 : $days;
        $date = date('Y-m-d', Mage::getModel('core/date')->timestamp(time()) + 3600 * 24 * $days);
        return $this->getAdapter()->cleanField($date, $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapAttributeDescription($params = array())
    {
        $max_len = (($max_len = $this->getAdapter()->getFeed()->getConfig('general_max_description_length')) > 10000 ? 10000 : $max_len);
        return $this->getAdapter()->getTruncatedAttribute($params, $max_len);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapAttributeName($params = array())
    {
        $max_len = $this->getAdapter()->getFeed()->getConfig('general_max_title_length');
        return $this->getAdapter()->getTruncatedAttribute($params, $max_len);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveShippingWeight($params = array())
    {
        $map = $params['map'];
        $map['attribute'] = 'weight';
        $unit = $map['param'];

        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();

        // Get weight attribute
        $weight_attribute = $this->getAdapter()->getGenerator()->getAttribute($map['attribute']);
        if ($weight_attribute === false) {
            Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
        }

        $weight = $this->getAdapter()->getAttributeValue($product, $weight_attribute);
        if (!empty($weight)) {
            $weight = number_format((float)$weight, 2). ' '. $unit;
        }

        return $this->getAdapter()->cleanField($weight, $params);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveGoogleCategoryByCategory($params = array())
    {
        $mapByCategory = $this->getAdapter()->getSortedTaxonomyMap();
        $value = $this->getAdapter()->matchByCategory($mapByCategory, $this->getAdapter()->getProduct()->getCategoryIds(), 'tx');

        $this->getAdapter()->findAndReplace($value, $params['map']['column']);
        return html_entity_decode($value);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveProductTypeByCategory($params = array())
    {
        $mapByCategory = $this->getAdapter()->getSortedTaxonomyMap();
        $value = $this->getAdapter()->matchByCategory($mapByCategory, $this->getAdapter()->getProduct()->getCategoryIds(), 'ty');

        $this->getAdapter()->findAndReplace($value, $params['map']['column']);
        return html_entity_decode($value);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveShipping($params = array())
    {
        $cacheShipping = $this->getAdapter()->getCacheShipping();
        if (!is_null($cacheShipping)) {
            return $cacheShipping;
        }

        $allowed_countries = $this->getAdapter()->getFeed()->getConfig('shipping_country');
        if (!(is_array($allowed_countries) && count($allowed_countries) > 0)) {
            $cacheShipping = $cell = "";
            $this->getAdapter()->setCacheShipping($cacheShipping);
            return $cell;
        }

        // @var $product Mage_Catalog_Model_Product
        $product = $this->getAdapter()->getProduct();
        $feed = $this->getAdapter()->getFeed();
        $scheduledCurrencyUpdateEnabled = Mage::helper('rocketshoppingfeeds')->isScheduledCurrencyRateUpdateEnabled();
        $useShippingCache = $feed->getConfig('shipping_cache_enabled') && !$scheduledCurrencyUpdateEnabled;

        if ($useShippingCache && !$this->getAdapter()->getGenerator()->getTestMode()) {
            $Cache = Mage::getModel('rocketshoppingfeeds/shipping_cache')
                ->setStoreId($this->getAdapter()->getFeed()->getStoreId())
                ->setGenerator($this->getAdapter()->getGenerator());

            // pack arguments for $Cache->hit method into array
            $cacheData = array(
                'product_id'    => $product->getId(),
                'store_id'      => $feed->getStoreId(),
                'currency_code' => $feed->getConfig('general_currency'),
                'feed_id'       => $feed->getId()
            );

            if (($data = $Cache->hit($cacheData)) !== false) {
                $cacheShipping = $cell = $data;
                $this->getAdapter()->setCacheShipping($cacheShipping);
                return $cell;
            }
        }

        /* @var $shipping RocketWeb_ShoppingFeeds_Model_Map_Shipping */
        $shipping = Mage::getModel(
            'rocketshoppingfeeds/map_shipping',
            array('store_id' => $this->getAdapter()->getFeed()->getStoreId(),
                'website_id' => $this->getAdapter()->getStore()->getWebsiteId(),
                'feed' => $this->getAdapter()->getFeed(),
                'generator' => $this->getAdapter()->getGenerator(),
                'columns_map' => $this->getAdapter()->getGenerator()->getColumnsMap(),
                'map_product' => $this,
            )
        );
        if (count($shipping->getAllowedCarriers()) <= 0) {
            $cacheShipping = $cell = "";
            $this->getAdapter()->setCacheShipping($cacheShipping);
            return $cell;
        }
        if (is_object($this->getAdapter()->getParentMap()) && $this->getAdapter()->getParentMap()->getProduct() && $this->getAdapter()->getParentMap()->getProduct()->getId() != $product->getId()) {
            $shipping->setItem($product, $this->getAdapter()->getParentMap()->getProduct());
        } else {
            $shipping->setItem($product);
        }
        $shipping->collectRates();
        $cell = $shipping->getFormatedValue();
        $cacheShipping = $cell;
        $this->getAdapter()->setCacheShipping($cacheShipping);

        if ($useShippingCache && !$this->getAdapter()->getGenerator()->getTestMode()) {
            // pack arguments for $Cache->miss method into array
            $cacheData = array(
                'product_id'    => $product->getId(),
                'store_id'      => $feed->getStoreId(),
                'currency_code' => $feed->getConfig('general_currency'),
                'feed_id'       => $feed->getId(),
                'value'         => $cell
            );

            $Cache->miss($cacheData);
        }

        return $this->getAdapter()->cleanField($cell, $params);
    }

    /**
     * Returns magento category paths
     * e.g.: Home > Garden > Flowers > Roses
     *
     * @param  array $params
     * @return string
     */
    public function mapDirectiveProductTypeMagentoCategory($params = array())
    {
        // Return parent's value if exists.
        $value = $this->getAdapter()->hasParentMap() ? $this->getAdapter()->getParentMap()->getCellValue($params) : '';
        if (!empty($value)) {
            return $value;
        }

        $product = $this->getAdapter()->getProduct();

        $disabledCategories = array();
        $maxValues = !empty($params['map']['param']) ? $params['map']['param'] : 3;
        $mapByCategory = $this->getAdapter()->getFeed()->getConfig('categories_provider_taxonomy_by_category');
        $mapByCategory = Mage::helper('rocketshoppingfeeds/map')->sortMap($mapByCategory, 'ty');

        // Exclude disabled categories from the Categories Map tab
        foreach ($mapByCategory as $categoryId => $categoryData) {
            if (array_key_exists('d', $categoryData) && (bool)$categoryData['d']) {
                $disabledCategories[] = $categoryId;
            }
        }

        // Check if at least one of the product categories is enable din the mapping
        $categoryCollection = $product->getCategoryCollection()->addFieldToFilter('is_active', 1);
        if (count($disabledCategories)) {
            $categoryCollection->addFieldToFilter('entity_id', array('nin' => $disabledCategories));
        }
        $categories = $categoryCollection->exportToArray();

        // Return empty if no categories are enabled
        if (empty($categories)) {
            return '';
        }

        // Build category path for each of product categories
        $return = array();
        $removes = array('default', 'root');

        foreach ($categories as $cat_info) {
            $names = array();

            // Loop through each items of the path
            $pItemsPieces = explode('/', $cat_info['path']);
            foreach ($pItemsPieces AS $id) {

                if (!array_key_exists($id, $this->_cache_categories)) {
                    $category = Mage::getModel('catalog/category')->setStoreId($this->getAdapter()->getStore()->getId())->load($id);
                    $this->_cache_categories[$id] = trim($category->getName());
                }
                $category_name = $this->_cache_categories[$id];

                if (empty($category_name)) {
                    continue;
                }

                $skip_node = false;
                foreach ($removes as $value) {
                    if (strstr(strtolower($category_name), strtolower(trim($value))) !== false) {
                        $skip_node = true;
                    }
                }

                if (!$skip_node) {
                    array_push($names, $category_name);
                }
            }

            // Implode the result items
            $return[implode(' > ', $names)] = count($names);
        }

        // Limit the output
        arsort($return);
        $value = array_slice(array_keys($return), 0, $maxValues);

        return $this->getAdapter()->cleanField(implode(',', $value), $params);
    }

    /**
     * Returns true for bundle items, and false for the others.
     *
     * @param array $params
     * @return string
     */
    public function mapDirectiveIsBundle($params = array())
    {
        return 'FALSE';
    }

    /**
     * This method adds support for the AheadWorks Shop By Brand extension
     * available here: http://ecommerce.aheadworks.com/magento-extensions/shop-by-brand.html
     * @param  array $params [description]
     * @return string         Returns the Title of the Manufacturer/Brand
     */
    public function mapAttributeAwShopbybrandBrand($params = array())
    {
        $attribute_id = $this->getAdapter()->getProduct()->getData('aw_shopbybrand_brand');
        $aw_model = Mage::getModel('awshopbybrand/brand')->load($attribute_id);
        return $this->getAdapter()->cleanField($aw_model->getTitle(), $params);
    }

    /**
     * @param $params
     * @param $attributes_codes
     * @return string
     */
    public function mapDirectiveVariantAttributes($params = array())
    {
        $attributes_codes = $params['map']['param'];

        if (count($attributes_codes) == 0) {
            return '';
        }

        $cell = '';
        $map = $params['map'];
        $product = $this->getAdapter()->getProduct();

        // Try to match the proper attribute by looking at what product has loaded
        foreach ($attributes_codes as $attr_code) {
            if (!empty($attr_code) && $product->hasData($attr_code)) {
                $attribute = $this->getAdapter()->getGenerator()->getAttribute($attr_code);
                $v = $this->getAdapter()->cleanField($this->getAdapter()->getAttributeValue($product, $attribute), $params);
                if ($v != "") {
                    $cell .= empty($cell) ? $v : $this->getAdapter()->getFeed()->getConfig('configurable_attribute_merge_value_separator') . $v;
                }
            }
        }

        // Try get from parent as it may be a non super-attribute value.
        if ($cell == "" && $this->getAdapter()->hasParentMap()) {
            $cell = $this->getAdapter()->getParentMap()->mapColumn($map['column']);
        }

        // Multi-select attributes - comma replace
        return str_replace(",", " /", $cell);
    }

    /**
     * Map the product option directive, receives through params the option_id to be mapped.
     * @param array $params
     * @return string
     */
    public function mapDirectiveProductOption($params = array())
    {
        if (!array_key_exists('param', $params['map'])) {
            return '';
        }

        $values = array();
        $names = is_array($params['map']['param']) ? $params['map']['param'] : array($params['map']['param']);
        $options = $this->getAdapter()->getOptionProcessor()->getOptions(array($params['map']['column'] => $names));

        foreach ($options as $_values) {
            foreach ($_values as $val) {
                $values[] = $val->getTitle();
            }
        }


        return implode(',', $values);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveConcatenate($params = array())
    {
        $expr = $params['map']['param'];
        preg_match_all('/\{\{(.*?)\}\}/is', $expr, $attributes);

        if (!isset($attributes[1])) {
            $this->log('Invalid expression in Concatenate directive. Could not find product attributes');
            return $expr;
        }

        // Get value for each identified attribute
        $values = array();
        foreach ($attributes[1] as $k => $attrCode) {

            $assocColumnsInherit = $this->getAdapter()->getAssocColumnsInherit();
            $column = array_key_exists($attrCode, $assocColumnsInherit) ? $assocColumnsInherit[$attrCode] : $attrCode;
            $params['map']['column'] = $column;
            $params['skip_directive'] = null;

            $columnsMap = $this->getAdapter()->getColumnsMap();
            $params['map']['attribute'] = array_key_exists($column, $columnsMap) ? $columnsMap[$column]['attribute'] : $attrCode;
            $params['map']['param'] = (array_key_exists($column, $columnsMap) && array_key_exists('param', $columnsMap[$column])) ? $columnsMap[$column]['param'] : '';

            if ($params['map']['attribute'] == 'rw_gbase_directive_concatenate') {
                $params['map']['attribute'] = $attrCode;
            }

            try {
                if ($this->getAdapter()->hasParentMap() && in_array($attrCode, array_keys($assocColumnsInherit))) {

                    // Apply attribute associated product inheritance
                    $type = -1;
                    switch ($this->getAdapter()->getParentMap()->getProduct()->getTypeId()) {
                        case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                            $type = $this->getAdapter()->getFeed()->getConfig('configurable_associated_products_' . $column);
                            break;
                        case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                            $type = $this->getAdapter()->getFeed()->getConfig('grouped_associated_products_' . $column);
                            break;
                    }

                    if ($column == $params['map']['column']) {
                        $params['skip_directive'] = true;
                    }
                    $values[$k] = $this->getAdapter()->mapColumnByProductType($type, $params);

                    if (empty($values[$k])) {
                        $values[$k] = $this->getAdapter()->mapEmptyValues($params);
                    }
                } else {

                    // Regular get the attribute value
                    $values[$k] = $this->getAdapter()->getCellValue($params);

                    if (empty($values[$k])) {
                        $values[$k] = $this->getAdapter()->mapEmptyValues($params);
                    }
                    if (empty($values[$k]) && $this->getAdapter()->hasParentMap()) {
                        $values[$k] = $this->getAdapter()->getParentMap()->getCellValue($params);
                    }
                }

            } catch (Exception $e) {
                $this->getAdapter()->log(sprintf('Invalid attribute name in Concatenate directive. Could not find product attribute matching {{%s}}', $attrCode));
                $values[$k] = $attrCode;
            }

        }

        // replace expression placeholders
        $implodedValues = implode('', $values);
        if (!empty($implodedValues)) {
            foreach ($values as $k => $val) {
                $expr = str_replace($attributes[0][$k], $val, $expr);
            }
        } else {
            $expr = '';
        }

        return $this->getAdapter()->cleanField($expr, $params);
    }
}
