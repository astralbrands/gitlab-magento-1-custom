<?php 

class Born_Package_Model_Amasty_Promo_Sales_Quote_Address extends Amasty_Promo_Model_Sales_Quote_Address
{
    /**
     * Collect address totals
     * Add sales_quote_address_collect_totals_before event for Magento<=1.5
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function collectTotals()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_before', array($this->_eventObject => $this));
        foreach ($this->getTotalCollector()->getCollectors() as $name => $model) {
            $this->_currentCollector = $name;
            try {
                $model->collect($this);
            } catch (Exception $e) {
                Mage::logException('Current Collector: '. $name . $e);
            }
        }
        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
        return $this;
    }
}

?>