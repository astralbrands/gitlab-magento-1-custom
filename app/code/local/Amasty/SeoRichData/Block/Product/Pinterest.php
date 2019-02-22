<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Block_Product_Pinterest extends Mage_Core_Block_Template
{
    public function getProduct()
    {
        return Mage::registry('current_product') ? Mage::registry('current_product') : Mage::registry('product');
    }

    public function getPrice()
    {
        /** @var Amasty_SeoRichData_Helper_Data $helper */
        $helper = Mage::helper('amseorichdata');
        $price = $helper->getProductPrice($this->getProduct());

        return $price;
    }
}
