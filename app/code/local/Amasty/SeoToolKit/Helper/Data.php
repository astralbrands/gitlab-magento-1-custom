<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check if SeoRichData exists
     *
     * @return bool
     */
    public static function isSeoRichDataExists()
    {
        return Mage::helper('core')->isModuleEnabled('Amasty_SeoRichData');
    }

    /**
     * Check if SeoRichData exists
     *
     * @return bool
     */
    public function isSeoMetaExists()
    {
        return Mage::helper('core')->isModuleEnabled('Amasty_Meta');
    }

    /**
     * Check if SeoRichData exists
     *
     * @return bool
     */
    public function isSeoUrlExists()
    {
        return Mage::helper('core')->isModuleEnabled('Amasty_SeoSingleUrl');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    public function getProductUrl(Mage_Catalog_Model_Product $product)
    {
        $productPath = $product->getRequestPath();
        if ($this->isSeoUrlExists()) {
            $url = Mage::helper('amseourl/product_url_rewrite')->getProductPath($product);
            if ($url) {
                $productPath = $url;
            }
        } elseif ($product->getUrlPath()) {
            $productPath = $product->getUrlPath();
        }

        return rtrim(Mage::getUrl('', array('_direct' => $productPath)), '/');
    }
}
