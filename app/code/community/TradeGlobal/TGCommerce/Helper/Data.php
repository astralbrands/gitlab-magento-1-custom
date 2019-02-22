<?php

/**
 *  Magento complains if this file is not present.  We aren't actually using it though.
 *
 * @author      Paul Snell (paulsnell@singpost.com)
 * @category    TradeGobal
 * @package     TradeGlobal_TGCommerce
 * @copyright   Copyright (c) 2017 TradeGlobal
 */

class TradeGlobal_TGCommerce_Helper_Data extends Mage_Core_Helper_Abstract
{

	/**
	 * @return bool
	 */
	public function enabledShowTax()
	{
		//return Mage::getStoreConfig('tgc_extension/tgcommerce/show-tax') === '1';
	}

	public function convertPrice(Mage_Directory_Model_Currency $currencyFrom, Mage_Directory_Model_Currency $currencyTo, $price)
	{
		$baseCurrency = Mage::app()->getStore()->getBaseCurrency();
		$currencyFromRate = $baseCurrency->getRate($currencyFrom);
		$value = $price / $currencyFromRate;
		$currencyToRate = $baseCurrency->getRate($currencyTo);
		$value = $value * $currencyToRate;
		return $value;
	}
}