<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


class Amasty_Ogrid_Block_Adminhtml_Sales_Order_Grid_Renderer_Tax extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_defaultWidth = 100;

    public function render(Varien_Object $row)
    {
        $data = $row->getData($this->getColumn()->getIndex());
        $currencyCode = $this->_getCurrencyCode($row);
        $rate = $this->_getRate($row);
        if (!$currencyCode) {
            return $data;
        }

        $taxString = array();
        $taxArray = explode(',', $data);
        if (count($taxArray) >= 2) {
            foreach ($taxArray as $key => $value) {
                $value = floatval($value) * $rate;
                $value = sprintf("%F", $value);
                $taxString[] = Mage::app()->getLocale()->currency($currencyCode)->toCurrency($value);
            }
            $data = implode(', ', $taxString);
        } else {
            $data = floatval($data) * $rate;
            $data = sprintf("%F", $data);
            $data = Mage::app()->getLocale()->currency($currencyCode)->toCurrency($data);
        }

        return $data;
    }

    /**
     * Returns currency code for the row, false on error
     *
     * @param Varien_Object $row
     * @return string|bool
     */
    protected function _getCurrencyCode($row)
    {
        if ($code = $this->getColumn()->getCurrencyCode() or $code = $row->getData($this->getColumn()->getCurrency())) {
            return $code;
        }

        return false;
    }

    /**
     * Returns rate for the row, 1 by default
     *
     * @param Varien_Object $row
     * @return float|int
     */
    protected function _getRate($row)
    {
        if ($rate = $this->getColumn()->getRate() or $rate = $row->getData($this->getColumn()->getRateField())) {
            return floatval($rate);
        }

        return 1;
    }
}