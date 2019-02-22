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
class RocketWeb_ShoppingFeeds_Helper_Map extends Mage_Core_Helper_Abstract
{
    /**
     * Check if mapping class exists using raw file verification
     *
     * @param string $key
     * @return bool
     */
    public function mapExists($key)
    {
        $pieces = explode('_', 'Model_Map_'. $key);
        return $this->classExists(implode(DS, $pieces));
    }

    /**
     * Check if adapter class exists using raw file verification
     *
     * @param string $key
     * @return bool
     */
    public function adapterExists($key)
    {
        $pieces = explode('_', 'Model_'. $key);
        return $this->classExists(implode(DS, $pieces));
    }

    /**
     * Check if provider class exists using raw file verification
     *
     * @param $key
     * @return bool
     */
    public function providerExists($key)
    {
        $pieces = explode('_', 'Model_Provider_'. $key);
        return $this->classExists(implode(DS, $pieces));
    }

    /**
     * Check if class exists using raw file verification
     * Cannot use class_exists() as magento registers autoload during bootstrap and fails
     *
     * @param string $relativePath
     * @return bool
     */
    protected function classExists($relativePath)
    {
        $file_path = Mage::getModuleDir('model', 'RocketWeb_ShoppingFeeds'). DS . $relativePath . '.php';
        return file_exists($file_path);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSimplePricingEnabled(Mage_Catalog_Model_Product $product)
    {
        $config = Mage::getStoreConfig('rocketweb_shoppingfeeds/general/simple_pricing');
        if ($config == RocketWeb_ShoppingFeeds_Model_Source_Yesnoauto::NO) {
            return false;
        }
        elseif ($config == RocketWeb_ShoppingFeeds_Model_Source_Yesnoauto::YES) {
            return true;
        }
        elseif ($config == RocketWeb_ShoppingFeeds_Model_Source_Yesnoauto::AUTO) {
            $storeId = $product->getStoreId();

            $scp = Mage::helper('rocketshoppingfeeds')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts');

            $aya = Mage::helper('rocketshoppingfeeds')->isModuleEnabled('Ayasoftware_SimpleProductPricing')
                && Mage::getStoreConfig('spp/setting/enableModule', $storeId);

            $amc = Mage::helper('rocketshoppingfeeds')->isModuleEnabled('Amasty_Conf')
                && (Mage::getStoreConfig('amconf/general/use_simple_price', $storeId) == 2
                    || (Mage::getStoreConfig('amconf/general/use_simple_price', $storeId) == 1 && $product->getData('amconf_simple_price')));

            $cpsp = Mage::helper('rocketshoppingfeeds')->isModuleEnabled('Best4Mage_ConfigurableProductsSimplePrices')
                && Mage::getStoreConfig('cpsp/settings/enable', $storeId);

            $result = $scp || $aya || $amc || $cpsp;
            return $result;
        }
    }

    /**
     * Sort taxonomy grid array by criteria value $by (p = priority /l = level).
     * When provided $field, it will not consider any empty values for that field when sorting.
     * $map[$k]['d'] reffers to disabled 0/1
     *
     * @param $map
     * @param string $by
     * @param string $field (one of the array key of taxonomy widget: tx = taxonomy /ty = type)
     * @return array
     */
    public function sortMap($map, $field = null, $by = 'p')
    {
        $ret = array();
        if (empty($map)) {
            return array();
        }

        $order = array();
        $tt = array();
        foreach ($map as $k => $value) {
            // Ignore disabled categories
            if (isset($value['d']) && !((bool) $value['d'])) {
                // Ignore empty string values when $feed provided
                if (is_null($field) || (isset($value[$field]) && trim($value[$field]) != "")) {
                    // build the sort by array
                    if (isset($value[$by]) && $value[$by] != "") {
                        $order[$k] = $value[$by];
                    } else {
                        $tt[$k] = "";
                    }
                }
            }
        }
        asort($order);

        foreach ($order as $k => $v) {
            $ret[$k] = $map[$k];
            if (!is_null($field) && isset($ret[$k][$field])) {
                $ret[$k][$field] = trim($ret[$k][$field]);
            }
        }
        foreach ($tt as $k => $v) {
            $ret[$k] = $map[$k];
        }

        return $ret;
    }

    public function getAllowedStockStatuses()
    {
        return array('in stock', 'out of stock', 'available for order', 'preorder');
    }

    public function getInStockStatus()
    {
        return 'in stock';
    }

    public function getOutOfStockStatus()
    {
        return 'out of stock';
    }
}
