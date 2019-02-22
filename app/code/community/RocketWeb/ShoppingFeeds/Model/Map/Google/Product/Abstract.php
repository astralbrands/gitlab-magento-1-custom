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
 */
class RocketWeb_ShoppingFeeds_Model_Map_Google_Product_Abstract
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * @param array $params
     * @return string
     */
    public function mapDirectivePriceBuckets($params = array())
    {
        $values = array();
        $buckets = $this->getAdapter()->getFeed()->getConfig('filters_adwords_price_buckets');

        if ($buckets) {
            $prices = $this->getAdapter()->getPrices();
            $price = $this->getAdapter()->hasSpecialPrice() ? $prices['sp_excl_tax'] : $prices['p_excl_tax'];
            foreach ($buckets as $bucket) {
                if (floatval($bucket['price_from']) <= floatval($price) && floatval($price) < floatval($bucket['price_to'])) {
                    array_push($values, $bucket['bucket_name']);
                }
            }
        }

        $cell = implode(',', $values);
        $this->getAdapter()->findAndReplace($cell, $params['map']['column']);
        return $cell;
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapDirectiveIdentifier($params = array())
    {
        $val = '';
        $product = $this->getAdapter()->getProduct();
        $attr_code = $params['map']['param'];

        if (!empty($attr_code) && $product->hasData($attr_code)) {
            $attribute = $this->getAdapter()->getGenerator()->getAttribute($attr_code);
            $val = $this->getAdapter()->cleanField($this->getAdapter()->getAttributeValue($product, $attribute), $params);
        }

        return $val;
    }

    /**
     *
     * @param array $params
     * @return string
     */
    public function mapDirectiveIdentifierExists($params = array())
    {
        $identifiers = array_key_exists('param', $params['map']) ? explode(',', $params['map']['param']) : array('brand', 'gtin');
        $cacheMapValues = $this->getAdapter()->getCacheMapValues();
        foreach ($identifiers as $column) {
            if (!array_key_exists($column, $cacheMapValues) && array_key_exists($column, $this->getAdapter()->getColumnsMap())) {
                $this->getAdapter()->mapColumn($column);
            }
        }
        $cacheMapValues = $this->getAdapter()->getCacheMapValues();


        $identifiers = array_unique(array_merge($identifiers, array('brand', 'gtin', 'mpn')));

        // Special case for Google spec: gtin and mpn exclude each other
        if (array_key_exists('gtin', $cacheMapValues) && $cacheMapValues['gtin'] != '') {
            // if we've got a GTIN we do not require MPN
            $identifiers = array_diff($identifiers, array('mpn'));
        }
        else {
            // if we don't have a GTIN, we'll need an MPN so let's not require gtin any more
            $identifiers = array_diff($identifiers, array('gtin'));
        }



        $score = 0;
        foreach ($identifiers as $column) {
            if (array_key_exists($column, $cacheMapValues) && $cacheMapValues[$column] != '') {
                $score++;
            }
        }

        return ($score == count($identifiers)) ? "" : "FALSE";
    }

    public function mapDirectivePromotionIds($params = array())
    {
        $promotionModel = Mage::getSingleton('rocketshoppingfeeds/provider_google_promotions');
        $promotionModel->setFeed($this->getAdapter()->getFeed());
        $promotionIds = $promotionModel->getPromotionIds($this->getAdapter()->getProduct());

        return implode(',', $promotionIds);
    }
}