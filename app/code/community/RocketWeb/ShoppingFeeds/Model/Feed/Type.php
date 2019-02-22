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
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 * @copyright  Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_ShoppingFeeds_Model_Feed_Type extends Varien_Object
{
    /**
     * Naming convention:
     * first word from type code delimited by "_" corresponds to the map class prefix under Model/Map
     */
    const TYPE_GENERIC          = 'generic';
    const TYPE_GOOGLE_SHOPPING  = 'google_shopping';
    const TYPE_GOOGLE_INVENTORY = 'google_inventory';
    const TYPE_GOOGLE_PROMO     = 'google_promo';
    const TYPE_BING_CATALOG     = 'bing_catalog';
    const TYPE_SHAREASALE       = 'shareasale';
    const TYPE_AMAZON           = 'amazon';
    const TYPE_EBAY             = 'ebay';
    const TYPE_JET              = 'jet';
    const TYPE_SHOPPING         = 'shopping';
    const TYPE_GETPRICE         = 'getprice';
    const TYPE_NETXTAG          = 'nextag';
    const TYPE_THEFIND          = 'thefind';
    const TYPE_SHOPMANIA        = 'shopmania';
    const TYPE_SHOPZILLA        = 'shopzilla';
    const TYPE_SEARS            = 'sears';
    const TYPE_ALIEXPRESS       = 'aliexpress';

    static public function getOptionArray()
    {
        $options = array(
            self::TYPE_GOOGLE_SHOPPING  => Mage::helper('rocketshoppingfeeds')->__('Google Shopping Feed'),
            self::TYPE_GOOGLE_INVENTORY => Mage::helper('rocketshoppingfeeds')->__('Google Inventory'),
            self::TYPE_GOOGLE_PROMO     => Mage::helper('rocketshoppingfeeds')->__('Google Promotions'),
            self::TYPE_BING_CATALOG     => Mage::helper('rocketshoppingfeeds')->__('Bing Catalog'),
            self::TYPE_SHAREASALE       => Mage::helper('rocketshoppingfeeds')->__('ShareASale'),
            self::TYPE_AMAZON           => Mage::helper('rocketshoppingfeeds')->__('Amazon'),
            self::TYPE_EBAY             => Mage::helper('rocketshoppingfeeds')->__('Ebay EEAN'),
            self::TYPE_JET              => Mage::helper('rocketshoppingfeeds')->__('Jet.com'),
            self::TYPE_SHOPPING         => Mage::helper('rocketshoppingfeeds')->__('Shopping.com'),
            self::TYPE_GETPRICE         => Mage::helper('rocketshoppingfeeds')->__('Getprice.com'),
            self::TYPE_NETXTAG          => Mage::helper('rocketshoppingfeeds')->__('Nextag.com'),
            self::TYPE_THEFIND          => Mage::helper('rocketshoppingfeeds')->__('Thefind.com'),
            self::TYPE_SHOPMANIA        => Mage::helper('rocketshoppingfeeds')->__('Shopmania.com'),
            self::TYPE_SHOPZILLA        => Mage::helper('rocketshoppingfeeds')->__('Shopzilla.com'),
            self::TYPE_SEARS            => Mage::helper('rocketshoppingfeeds')->__('Sears.com'),
            self::TYPE_ALIEXPRESS       => Mage::helper('rocketshoppingfeeds')->__('Aliexpress.com'),
            self::TYPE_GENERIC          => Mage::helper('rocketshoppingfeeds')->__('Generic'),
        );
        foreach ($options as $type => $label) {
            $file = Mage::getModuleDir('etc', 'RocketWeb_ShoppingFeeds'). DS. 'feeds'. DS. $type. '.xml';
            if (!is_readable($file)) {
                unset($options[$type]);
            }
        }
        return $options;
    }

    static public function getTaxonomyFeedTypes()
    {
        return array(
            self::TYPE_GOOGLE_SHOPPING  => Mage::helper('rocketshoppingfeeds')->__('Google Shopping'),
            self::TYPE_BING_CATALOG     => Mage::helper('rocketshoppingfeeds')->__('Bing Catalog'),
            self::TYPE_SHAREASALE         => Mage::helper('rocketshoppingfeeds')->__('ShareASale'),
        );
    }

    static public function getTaxonomyFeedUrl()
    {
        return array(
            self::TYPE_GOOGLE_SHOPPING    => 'https://www.google.com/basepages/producttype/taxonomy.%s.txt',
            // could not find localized taxonomies for Bing
            self::TYPE_BING_CATALOG       => 'http://fp.advertising.microsoft.com/en-us/WWDocs/user/search/en-us/Bing_Category_Taxonomy.txt'
        );
    }

    static public function getLabel($type)
    {
        $options = self::getOptionArray();
        return array_key_exists($type, $options) ? $options[$type] : ucwords(str_replace('_', '', $options[$type]));
    }
}
