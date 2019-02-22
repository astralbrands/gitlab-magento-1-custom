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
class RocketWeb_ShoppingFeeds_Model_Adapter_Configurable_Scp
    extends RocketWeb_ShoppingFeeds_Model_Adapter_Configurable
{
    /**
     * Computes prices for given or current product.
     * It returns an array of 4 prices: price and special_price, both including and excluding tax
     *
     * @return mixed
     */
    public function getPrices()
    {
        $minPrice = PHP_INT_MAX;
        $prices = array();
        if (count($this->getAssocAdapters())) {
            foreach ($this->getAssocAdapters() as $assocAdapter) {
                $tmpPrices = $assocAdapter->getPrices();
                $price = min($tmpPrices['p_excl_tax'], $tmpPrices['sp_excl_tax']);
                if ($price < $minPrice) {
                    $minPrice = $price;
                    $prices = $tmpPrices;
                }
            }
        }

        // Fallback in case there isn't any associated products
        if (count($prices) == 0) {
            $prices = parent::getPrices();
        }

        return $prices;
    }

    /**
     * SCP configurable takes the promo price from the min price assoc.
     *
     * @param bool|true $processRules
     * @param null $product
     * @return bool
     */
    public function hasSpecialPrice($processRules = true, $product = null)
    {
        $has = false;
        $minAssoc = null;
        $minPrice = PHP_INT_MAX;

        foreach ($this->getAssocAdapters() as $assocAdapter) {
            $prices = $assocAdapter->getPrices();
            $price = min($prices['sp_excl_tax'], $prices['sp_excl_tax']);
            if ($price < $minPrice) {
                $minPrice = $price;
                $minAssoc = $assocAdapter;
            }
        }

        if (!is_null($minAssoc)) {
            $has = $minAssoc->hasSpecialPrice($processRules, $product);
        }

        return $has;
    }
}
