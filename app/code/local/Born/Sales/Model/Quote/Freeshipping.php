<?php
class Born_Sales_Model_Quote_Freeshipping extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function __construct()
    {
        $this->setCode('custom_freeshipping');
    }


    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if (Mage::app()->getStore()->isAdmin() && Mage::app()->getRequest()->getPost('free_shipping')) {
            // Find if our shipping has been included.
            $rates = $address->collectShippingRates()
                ->getGroupedAllShippingRates();

            foreach ($rates as $carrier) {
                foreach ($carrier as $rate) {
                    $rate->setPrice(0);
                    $rate->save();

                }
            }

            $address->setCollectShippingRates(false);
            $address->save();
        }

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }
}
?>