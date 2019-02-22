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

class RocketWeb_ShoppingFeeds_Model_Map_Generic_Product_Configurable_Scp
    extends RocketWeb_ShoppingFeeds_Model_Map_Abstract
{
    /**
     * Simple Pricing is on, we need to take the dates from the same product we used to take the price
     *
     * @param array $params
     * @return string
     */
    public function mapDirectiveSalePriceEffectiveDate($params = array())
    {
        $minAssoc = null;
        $minPrice = PHP_INT_MAX;

        foreach ($this->getAdapter()->getAssocAdapters() as $assocAdapter) {
            $prices = $assocAdapter->getPrices();
            if ($prices['sp_excl_tax'] < $minPrice) {
                $minPrice = $prices['sp_excl_tax'];
                $minAssoc = $assocAdapter;
            }
        }

        if (!is_null($minAssoc)) {
            $params['force_assoc'] = true;
            return $minAssoc->getCellValue($params);
        }

        return '';
    }
}