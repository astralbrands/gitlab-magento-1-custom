<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Block_Items_Item extends Mage_Catalog_Block_Product_Abstract
{
    protected $_template = 'amasty/ampromo/items/item.phtml';

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        $params = Mage::helper('ampromo')->getUrlParams();

        return $this->getUrl('ampromo/cart/update', $params);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param bool|false $displayMinimalPrice
     * @param string $idSuffix
     * @return mixed|string
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $html = '';
        if ($product->getAmpromoShowOrigPrice()) {

            if ($product->getTypeId() == 'giftcard') {
                $_amount = Mage::helper("ampromo")->getGiftcardAmounts($product);
                $_amount = array_shift($_amount);
                $product->setPrice($_amount);
            }

            $html = $this->emulateTaxHelper(
                array(get_parent_class($this), 'getPriceHtml'),
                array($product, $displayMinimalPrice, $idSuffix)
            );

            if ($product->getSpecialPrice() == 0) {
                $html = str_replace('regular-price', 'old-price', $html);
            }
            return $html;
        }

        return '';
    }

    /**
     * @param $callback
     * @param $args
     * @return mixed
     */
    public function emulateTaxHelper($callback, $args)
    {
        $originalHelper = Mage::helper('tax');
        Mage::unregister('_helper/tax');
        Mage::register('_helper/tax', Mage::helper('ampromo/calc'));

        $result = call_user_func_array($callback, $args);

        Mage::unregister('_helper/tax');
        Mage::register('_helper/tax', $originalHelper);

        return $result;
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        if ($this->getProduct()->getTypeInstance(true)->hasOptions($this->getProduct())) {
            return true;
        }
        return false;
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $magentoVersion = Mage::getVersion();
        $baseHelper = Mage::helper('ambase');
        if ((version_compare($magentoVersion, '1.9.3.0', '<') && !$baseHelper->isEnterpriseEdition())
            || (version_compare($magentoVersion, '1.14.3.2', '<') && $baseHelper->isEnterpriseEdition())
        ){
            return $this->getJsonConfigOld();
        } else {
            $config = array();
            if (!$this->hasOptions()) {
                return Mage::helper('core')->jsonEncode($config);
            }

            /* @var $product Mage_Catalog_Model_Product */
            $product = $this->getProduct();

            /** @var Mage_Catalog_Helper_Product_Type_Composite $compositeProductHelper */
            $compositeProductHelper = $this->helper('catalog/product_type_composite');
            $config = array_merge(
                $compositeProductHelper->prepareJsonGeneralConfig(),
                $compositeProductHelper->prepareJsonProductConfig($product)
            );

            $responseObject = new Varien_Object();
            Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
            if (is_array($responseObject->getAdditionalOptions())) {
                foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                    $config[$option] = $value;
                }
            }

            return Mage::helper('core')->jsonEncode($config);
        }
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfigOld()
    {
        $config = array();
        if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);
        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);
        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true,
                null, null, null, null, null, false);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, false,
                null, null, null, null, null, false);
        } else {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        }
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency($tierPrice['website_price'], false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (int)$tierPrice['website_price'], true),
                false, false);
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );
        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }
        return Mage::helper('core')->jsonEncode($config);
    }

    public function getTypeOptionsHtml()
    {
        $product = $this->getProduct();

        if (Mage::registry('current_product')) {
            Mage::unregister('current_product');
        }

        Mage::register('current_product', $product);

        switch ($product->getTypeId()) {
            case 'downloadable':
                $_blockOpt = 'downloadable/catalog_product_links';
                $_templateOpt = 'amasty/ampromo/items/downloadable.phtml';
                break;
            case 'configurable':
                $_blockOpt = 'catalog/product_view_type_configurable';
                $_templateOpt = 'amasty/ampromo/items/configurable.phtml';
                break;
            case 'bundle':
                $_blockOpt = 'ampromo/items_bundle';
                $_templateOpt = 'bundle/catalog/product/view/type/bundle/options.phtml';
                break;
            case 'amgiftcard':
                $_blockOpt = 'amgiftcard/catalog_product_view_type_giftCard';
                $_templateOpt = 'amasty/amgiftcard/catalog/product/view/type/giftcard.phtml';
                break;
            case 'virtual':
                $_blockOpt = 'catalog/product_view_type_virtual';
                break;
            case 'giftcard':
                $_blockOpt = 'enterprise_giftcard/catalog_product_view_type_giftcard';
                $_templateOpt = 'amasty/ampromo/items/giftcard.phtml';
                break;
            case 'amstcred':
                $_blockOpt = 'amstcred/catalog_product_view_type_storeCredit';
                $_templateOpt = 'amasty/amstcred/catalog/product/view/type/amstcred.phtml';
                break;
        }

        if (!empty($_blockOpt) && !empty($_templateOpt)) {
            $block = $this->getLayout()
                          ->createBlock(
                              $_blockOpt,
                              'ampromo_item_' . $product->getId(),
                              array('product' => $product)
                          )
                          ->setProduct($product)
                          ->setTemplate($_templateOpt);

            switch ($product->getTypeId()) {
                case 'giftcard':
                    $child = $this->getLayout()->createBlock(
                        'enterprise_giftcard/catalog_product_view_type_giftcard_form',
                        'product.info.giftcard.form'
                    )
                                  ->setTemplate('giftcard/catalog/product/view/type/giftcard/form.phtml');

                    $block->setChild('product.info.giftcard.form', $child);
                    break;
            }

            return $block->toHtml();
        }
    }

    public function customOptionsHtml()
    {
        return $this->getLayout()
                    ->createBlock('ampromo/items_options', '', array('product' => $this->getProduct()))
                    ->toHtml();
    }

    public function getImageUrl(Mage_Catalog_Model_Product $product, $width, $height)
    {
        /** @var Mage_Catalog_Helper_Image $helper */
        $helper = Mage::helper('catalog/image');

        $image = $helper->init($product, 'small_image')->resize($width, $height);

        return (string)$image;
    }
}
