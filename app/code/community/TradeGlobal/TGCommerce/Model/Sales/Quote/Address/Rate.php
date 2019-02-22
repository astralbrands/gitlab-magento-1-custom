<?php
/**
 *  Overwrites the "importShippingRate" method as we need to pass back our custom TGCommerce values
 *
 * @author      Paul Snell (paulsnell@singpost.com)
 * @category    TradeGobal
 * @package     TradeGlobal_TGCommerce
 * @copyright   Copyright (c) 2017 TradeGlobal
 */



class TradeGlobal_TGCommerce_Model_Sales_Quote_Address_Rate extends Mage_Sales_Model_Quote_Address_Rate
{

    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {
        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
            $this
                ->setCode($rate->getCarrier().'_error')
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setErrorMessage($rate->getErrorMessage())
            ;
        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this
                ->setCode($rate->getCarrier().'_'.$rate->getMethod())
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setMethod($rate->getMethod())
                ->setMethodTitle($rate->getMethodTitle())
                ->setMethodDescription($rate->getMethodDescription())
                ->setPrice($rate->getPrice());
            if ($rate->getExtendedDetail()) {  // Map fields to match database as this rate gets saved to 'sales_flat_quote_shipping_rate'
               $this
                  ->setExtendedDetail($rate->getExtendedDetail())
                  ->setExtQuoteId($rate->getQuoteId())
                  ->setExtCogs($rate->getCogs())
                  ->setCost($rate->getCost())
                  ->setExtDeliveryEstimate($rate->getDeliveryTime())
                  ->setExtCustomFee($rate->getCustomFee())
                  ->setExtCustomDiscount($rate->getCustomDiscount())
                  ->setExtCustomString($rate->getCustomFeeString())
                  ->setExtImportFee($rate->getImportFee())
                  ->setExtImportDiscount($rate->getImportDiscount())
                  ->setExtImportString($rate->getImportFeeString())
                  ->setExtServiceFee($rate->getServiceFee())
                  ->setExtServiceDiscount($rate->getServiceDiscount())
                  ->setExtServiceString($rate->getServiceFeeString())
                  ->setExtShippingFee($rate->getShippingFee())
                  ->setExtShippingDiscount($rate->getShippingDiscount())
                  ->setExtShippingString($rate->getShippingFeeString())
                  ->setNonShipFee($rate->getNonShipFee())
                  ->setTotalFee($rate->getTotalFee())
                  ->setCustomImportFee($rate->getCustomImportFee())
                  ->setShipServiceFee($rate->getShipServiceFee())
               ;
            }
        }
        return $this;
    }
}
