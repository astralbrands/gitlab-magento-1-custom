<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


class Amasty_Xnotif_Model_Htmlobserver extends Mage_Core_Helper_Abstract
{
    public function handleBlockAlert($observer)
    {
        /** @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();

        if ($block instanceof Mage_Productalert_Block_Product_View) {
            switch ($block->getNameInLayout()) {
                case 'productalert.stock':
                    $block = $this->_getHelper()->getStockAlertBlock($block);
                    break;
                case 'productalert.price':
                    $block = $this->_getHelper()->getPriceAlertBlock($block);
                    break;
            }
            $observer->setBlock($block);
        }
    }

    public function handleBlockAlertOnCategory($observer)
    {
        /** @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        if (
            $block instanceof Mage_Catalog_Block_Product_List &&
            Mage::getStoreConfig('amxnotif/stock/on_category')
        ) {
            $html = $observer->getTransport()->getHtml();
            $subscribeBlock = $this->_getHelper()->getStockAlertBlockCategory();
            preg_match_all('/price[a-z\-]*?([0-9]*?)"/', $html, $productsId);
            if (!isset($productsId[1])) {
                return;
            }
            $ids = array_unique($productsId[1]);

            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addFieldToFilter('entity_id', array('in' => $ids));
            $collection->addStoreFilter(Mage::app()->getStore()->getId())
                ->addAttributeToSelect('*');

            foreach ($collection as $_product) {
                $html = $this->_processProduct($html, $_product, $subscribeBlock);
            }

            $observer->getTransport()->setHtml($html);
        }
    }

    /**
     * Retrieve helper instance
     *
     * @return Amasty_Xnotif_Helper_Data|null
     */
    protected function _getHelper()
    {
        return Mage::helper('amxnotif');
    }

    /**
     * @param $html
     * @param $product
     * @param $subscribeBlock
     * @return string
     */
    protected function _processProduct($html, $product, $subscribeBlock)
    {
        $productId = $product->getId();
        $template = '@(product.*?-price-' . $productId . '">(.*?)div>)@s';
        preg_match_all($template, $html, $res);
        if (!isset($res[0]) || !$res[0]) {
            $template = '@(price-including-tax-' . $productId . '">(.*?)div>)@s';
            preg_match_all($template, $html, $res);
            if (!$res[0]) {
                $template = '@(price-excluding-tax-' . $productId . '">(.*?)div>)@s';
                preg_match_all($template, $html, $res);
            }
        }

        if (isset($res[0]) && $res[0]) {
            $subscribeBlock->setData('product', $product);
            $subscribeHtml = $subscribeBlock->toHtml();
            if ($subscribeHtml && isset($res[1][0]) && isset($res[0][0])) {
                $replace = $res[1][0] . $subscribeHtml;
                $html = str_replace($res[0][0], $replace, $html);
            }
        }

        return $html;
    }
}
