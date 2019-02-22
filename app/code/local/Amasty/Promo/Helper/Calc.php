<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Helper_Calc extends Mage_Tax_Helper_Data
{
    /**
     * @param $quote
     * @param $rule
     * @return int
     */
    function getQuoteSubtotal($quote, $rule)
    {
        $subtotal = 0;
        $taxInSubtotal = Mage::getStoreConfig('ampromo/general/tax_in_subtotal');
        $defualtCurrency = Mage::getStoreConfig('ampromo/general/default_currency');
        
        foreach ($quote->getItemsCollection() as $item) {
            if ($rule->getActions()->validate($item) && (!$item->getIsPromo() && $item->getPrice() != 0)) {
                if ($taxInSubtotal && $defualtCurrency) {
                    $subtotal += $item->getBaseRowTotalInclTax();
                }

                if ($taxInSubtotal && !$defualtCurrency) {
                    $subtotal += $item->getRowTotalInclTax();
                }

                if (!$taxInSubtotal && $defualtCurrency) {
                    $subtotal += $item->getBaseRowTotal();
                }

                if (!$taxInSubtotal && !$defualtCurrency) {
                    $subtotal += $item->getRowTotal();
                }
            }
        }

        return $subtotal;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param float $price
     * @param null $includeTaxes
     * @param null $shippingAddress
     * @param null $billingAddress
     * @param null $ctc
     * @param null $store
     * @param null $priceIncludesTax
     * @param bool $roundPrice
     * @return float
     */
    public function getPrice($product, $price, $includeTaxes = null, $shippingAddress = null, $billingAddress = null,
                             $ctc = null, $store = null, $priceIncludesTax = null, $roundPrice = true)
    {
        $store          = Mage::app()->getStore();
        $taxConfig = Mage::getModel('tax/config');
        $catalogPriceTax = $taxConfig->priceIncludesTax($store);
        $shippingPriceTax = parent::displayCartPriceInclTax($store);
        $taxRate = $this->getProductTaxRate($product, $store);

        if ($shippingPriceTax && $catalogPriceTax) {
            return $price;
        }

        if ($catalogPriceTax) {
            $priceValue = parent::getPrice(
                $product->setTaxPercent(null),
                $price,
                false,
                null,
                null,
                null,
                $store,
                true
            );
        } else {
            $priceValue = parent::getPrice(
                $product->setTaxPercent(null),
                $price,
                false,
                null,
                null,
                null,
                $store,
                false
            );
        }

        if (!$catalogPriceTax && $shippingPriceTax) {
            $priceValue = $price * (1 + ($taxRate / 100));
        }

        return $roundPrice ? $store->roundPrice($priceValue) : $priceValue;
    }

    /**
     * @param $product
     * @param $store
     * @return int
     */
    public function getProductTaxRate($product, $store)
    {
        $product        = $product->load($product->getId());
        $taxCalculation = Mage::getModel('tax/calculation');
        $request        = $taxCalculation->getRateRequest(null, null, null, $store);
        $taxClassId     = $product->getTaxClassId();

        if ($taxClassId) {
            $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));

            return $percent;
        }

        return 0;
    }
}
