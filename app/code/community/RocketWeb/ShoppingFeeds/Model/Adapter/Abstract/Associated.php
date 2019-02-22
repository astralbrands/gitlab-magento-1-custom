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

class RocketWeb_ShoppingFeeds_Model_Adapter_Abstract_Associated
    extends RocketWeb_ShoppingFeeds_Model_Adapter_Abstract
{
    /**
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->setData('is_scp_associated', false);
        return $this;
    }

    /**
     * Do not run options for associated products as options are associated products
     *
     * @return $this
     */
    public function _beforeMap()
    {
        return $this;
    }

    /**
     * Computes prices for given or current product.
     * It returns an array of 4 prices: price and special_price, both including and excluding tax
     *
     * @return mixed
     */
    public function getPrices()
    {
        /** @var Mage_Catalog_Model_Product $product */
        if ($this->getParentMap()->getProduct()->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && !$this->getData('is_scp_associated')) {
            $product = $this->getParentMap()->getProduct();
            $this->setProduct($product);
        }

        return parent::getPrices();
    }
}
