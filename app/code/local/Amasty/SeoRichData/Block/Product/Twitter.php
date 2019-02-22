<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Block_Product_Twitter extends Mage_Core_Block_Template
{
    public function getProduct()
    {
        return Mage::registry('current_product') ? Mage::registry('current_product') : Mage::registry('product');
    }

    public function getResizedImage()
    {
        return $this->helper('catalog/image')->init($this->getProduct(), 'thumbnail')->resize(
            (int)Mage::getStoreConfig('amseorichdata/twitter/image_height'),
            (int)Mage::getStoreConfig('amseorichdata/twitter/image_height'));
    }

    public function getDescription()
    {
        /** @var Amasty_SeoRichData_Helper_Data $helper */
        $helper = Mage::helper('amseorichdata');

        $shortDescription = $helper->getProductDescription($this->getProduct());
        $shortDescription = preg_replace('|[\s\r\n]+|s', ' ', $shortDescription);
        $shortDescription = trim(strip_tags($shortDescription));
        $shortDescription = substr(
            $shortDescription,
            0,
            (int)Mage::getStoreConfig('amseorichdata/twitter/max_description_length')
        );

        return $shortDescription;
    }
}
