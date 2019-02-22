<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */   
class Amasty_Xnotif_Helper_Data extends Mage_Core_Helper_Url
{
    const SALT = 'AmastyUnsubscribeSalt';

    public function getStockAlertBlock($block)
    {
        $product = $this->getProduct();
        if (method_exists(Mage::helper('productalert'), 'isStockAlertAllowed')) {
            $isStockAlertAllowed = Mage::helper('productalert')->isStockAlertAllowed();
        } else {
            $isStockAlertAllowed = Mage::getStoreConfigFlag('catalog/productalert/allow_stock');
        }
        
        /* checking product and module settings*/
        if ($product->getData('amxnotif_hide_alert') == '1'
            || !$this->enableForCustomerGroup('stock')
            || (!$isStockAlertAllowed || !$product || $product->isAvailable())
        ) {
            $block->setTemplate('');
            return $block;
        }

        if (!Mage::helper('customer')->isLoggedIn()) {
            $block->setTemplate('amasty/amxnotif/product/view_email.phtml');
        } else {
            $block->setTemplate('amasty/amxnotif/product/view.phtml');
        }

        return $block;
    }

    public function enableForCustomerGroup($type)
    {
        $setting =  Mage::getStoreConfig('amxnotif/' . $type . '/allow_for');
        $setting = explode(',', $setting);
        if (in_array('-1', $setting)) {//for all groups
            return true;
        }

        if (in_array(Mage::getSingleton('customer/session')->getCustomerGroupId(), $setting)) {
            return true;
        }

        return false;
    }

    public function getPriceAlertBlock($block)
    {
        $product = $this->getProduct();

        if (!$this->enableForCustomerGroup('price')
            || (!Mage::helper('productalert')->isPriceAlertAllowed() || false === $product->getCanShowPrice())
        ) {
            $block->setTemplate('');
            return $block;
        }

        if (!Mage::helper('customer')->isLoggedIn()) {
            $block->setTemplate('amasty/amxnotif/product/price/view_email_simple.phtml');
        }

        return $block;
    }

    
    public function getStockAlert($product, $parentProductId = null)
    {
        $html = '';
        $tempCurrentProduct = Mage::registry('current_product');
        Mage::unregister('current_product');
        Mage::register('current_product', $product);

        $alertBlock = Mage::app()->getLayout()->createBlock(
            'productalert/product_view',
            'productalert.stock.' . $product->getId()
        );
        if ($alertBlock) {
            $alertBlock->setProduct($product);
            $alertBlock->setParentProductId($parentProductId);
            $alertBlock->setTemplate('amasty/amxnotif/product/view.phtml');
            $alertBlock->prepareStockAlertData();
            $alertBlock->setHtmlClass('alert-stock link-stock-alert');
            $alertBlock->setSignupLabel($this->__('Sign up to get notified when this configuration is back in stock'));

            $alertBlock = $this->getStockAlertBlock($alertBlock);
            $html = $alertBlock->toHtml();
        }

        Mage::unregister('current_product');
        Mage::register('current_product', $tempCurrentProduct);
            
        return $html;
    }
    
    public function getProduct()
    {
        return Mage::registry('current_product');
    }
    
    public function getSignupUrl($type, $productId = null, $parentId = null)
    {
        if (!$productId) {
            $productId = $this->getProduct()->getId();
        }
        $url = $this->_getUrl(
            'xnotif/email/' . $type,
            array(
                'product_id'    => $productId,
                'parent_id'     => $parentId,
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            )
        );

        return $url;
    }

    public function getEmailUrl($type)
    {
         return $this->_getUrl('xnotif/email/' . $type);
    }

    public function getStockAlertBlockCategory()
    {
        $block = Mage::app()->getLayout()->createBlock('amxnotif/category_subscribe');
        $block = $this->configureTemplate($block);

        return $block;
    }

    public function configureTemplate($block)
    {
        $template = 'amasty/amxnotif/product/view.phtml';
        if (!Mage::helper('customer')->isLoggedIn()) {
            if (Mage::getStoreConfig('amxnotif/stock/with_popup')) {
                $block->setData('popup', true);
            }
            $template = 'amasty/amxnotif/product/view_email.phtml';
        }
        $block->setTemplate($template);

        return $block;
    }

    /**
     * @return bool
     */
    public function isGdprEnabled() {
        return Mage::getStoreConfig('amxnotif/gdpr/enabled');
    }

    /**
     * @return string
     */
    public function getGdprText() {
        return Mage::getStoreConfig('amxnotif/gdpr/text');
    }
}
