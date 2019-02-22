<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


class Amasty_SeoRichData_Block_Product_Richdata extends Mage_Catalog_Block_Product_Abstract
{
    public function getResult()
    {
        /** @var Amasty_SeoRichData_Helper_Data $helper */
        $helper = Mage::helper('amseorichdata');

        $product = Mage::registry('current_product') ? Mage::registry('current_product') : Mage::registry('product');

        $offers[] = array();

        $showAvailability = false;
        $showCondition = false;

        if (Mage::getStoreConfig('amseorichdata/product/show_availability')) {
            $showAvailability = true;
        }

        if (Mage::getStoreConfig('amseorichdata/product/show_condition')) {
            $showCondition = true;
        }

        if ($product->getTypeId() == 'configurable' && Mage::getStoreConfig('amseorichdata/product/show_configurable_list')) {
            $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
            //array to keep the price differences for each attribute value
            $pricesByAttributeValues = array();
            //base price of the configurable product
            $basePrice = $product->getFinalPrice();
            //loop through the attributes and get the price adjustments specified in the configurable product admin page
            foreach ($attributes as $attribute) {
                $prices = $attribute->getPrices();
                foreach ($prices as $price) {
                    if ($price['is_percent']) { //if the price is specified in percents
                        $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'] * $basePrice / 100;
                    } else { //if the price is absolute value
                        $pricesByAttributeValues[$price['value_index']] = (float)$price['pricing_value'];
                    }
                }
            }

            //get all simple products
            $simple = $product->getTypeInstance()->getUsedProductCollection();
            $simple->addAttributeToSelect('*');
            //loop through the products
            foreach ($simple as $sProduct) {
                if ($this->_useSimplePrice()) {
                    $price = $helper->getProductPrice($sProduct, $sProduct->getFinalPrice());
                } else {
                    $totalPrice = $basePrice;
                    //loop through the configurable attributes
                    foreach ($attributes as $attribute) {
                        //get the value for a specific attribute for a simple product
                        $value = $sProduct->getData($attribute->getProductAttribute()->getAttributeCode());
                        //add the price adjustment to the total price of the simple product
                        if (isset($pricesByAttributeValues[$value])) {
                            $totalPrice += $pricesByAttributeValues[$value];
                        }
                    }
                    $price = $helper->getProductPrice($sProduct, $totalPrice);
                }
                $offers[] =
                    array(
                        '@type' => 'Offer',
                        'priceCurrency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                        'price' => $price,
                        'availability' => $sProduct->isAvailable() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock',
                        'itemOffered' => array(
                            '@type' => 'Product',
                            'name' => $sProduct->getName(),
                            'sku' => $sProduct->getSku(),
                            'itemCondition' => 'http://schema.org/NewCondition'
                        ),
                        'seller' => array(
                            '@type' => 'Organization',
                            'name' => Mage::getStoreConfig('design/header/logo_alt')
                        )
                    );

            }
        } elseif ($product->getTypeId() == 'grouped' && Mage::getStoreConfig('amseorichdata/product/show_grouped_list')) {
            $products = $product->getTypeInstance()->getAssociatedProducts();
            $offers = $this->_prepareMassOffers($products);
        } else {
            $offers[] =
                array(
                    '@type' => 'Offer',
                    'priceCurrency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                    'price' => $helper->getProductPrice($product),
                    'availability' => $product->isAvailable() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock',
                    'itemCondition' => 'http://schema.org/NewCondition',
                    'seller' => array(
                        '@type' => 'Organization',
                        'name' => Mage::getStoreConfig('design/header/logo_alt')
                    )
                );
        }

        if (!$showAvailability) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['availability'])) {
                    unset($offers[$key]['availability']);
                }
            }
        }

        if (!$showCondition) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['itemCondition'])) {
                    unset($offers[$key]['itemCondition']);
                }
            }
        }

        $data['product'] = array(
            '@context' => 'http://schema.org',
            '@type' => 'Product',
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'description' => $helper->getProductDescription($product),
            'image' => $product->getImageUrl(),
            'url' => Mage::helper('core/url')->getCurrentUrl(),
            'offers' => $offers
        );

        if (Mage::getStoreConfig('amseorichdata/rating/enabled')) {
            if (is_object($product->getRatingSummary()) && $product->getRatingSummary()->getReviewsCount() > 0) {
                $ratingValue = $product->getRatingSummary()->getRatingSummary();
                $ratingCount = $product->getRatingSummary()->getReviewsCount();
            } else {
                $storeId = Mage::app()->getStore()->getId();
                $summaryData = Mage::getModel('review/review_summary')
                    ->setStoreId($storeId)
                    ->load($product->getId());

                $ratingValue = $summaryData->getRatingSummary();
                $ratingCount = $summaryData->getReviewsCount();
            }

            if ($ratingCount && $ratingValue) {
                $data['product']['aggregateRating'] = array(
                    '@type' => 'AggregateRating',
                    'ratingValue' => round(($ratingValue * 5) / 100, 2),
                );

                $showTotals = Mage::getStoreConfig('amseorichdata/rating/totals');

                if ($showTotals == 2 || $showTotals == 3) {
                    $data['product']['aggregateRating']['ratingCount'] = $this->_getProductVotes($product);
                }

                if ($showTotals == 1 || $showTotals == 3) {
                    $data['product']['aggregateRating']['reviewCount'] = $ratingCount;
                }
            }

            if (Mage::getStoreConfigFlag('amseorichdata/yotpo/enabled') &&
                $helper->isYotpoReviewsEnabled()) {
                $reviews = $this->helper('yotpo/richSnippets')->getRichSnippet();

                $data['product']['aggregateRating'] = array(
                    '@type' => 'AggregateRating',
                    'ratingValue' => $reviews["average_score"] ? $reviews["average_score"] : 0,
                    'reviewCount' => $reviews["reviews_count"] ? $reviews["reviews_count"] : 0
                );

            }
        }

        $result = '';

        foreach ($data as $section) {
            $json = json_encode($section);
            $result .= "<script type=\"application/ld+json\">{$json}</script>";
        }

        return $result;
    }

    protected function _useSimplePrice()
    {
        $useSimplePrice = false;

        if (Mage::helper('amseorichdata')->isAmastyConfEnabled()) {
            /** @var Amasty_Conf_Helper_Data $amconfHelper */
            $amconfHelper = Mage::helper('amconf');
            $useSimplePrice = (
                ($amconfHelper->getConfigUseSimplePrice() == 2 //2 - Yes for All Products
                    || ($amconfHelper->getConfigUseSimplePrice() == 1)//1 - Yes for Specified Products
                )
                && $this->getProduct()->getData('amconf_simple_price')
            ) ? true : false;
        }

        return $useSimplePrice;
    }


    protected function _prepareMassOffers($products)
    {
        $offers = array();

        /** @var Amasty_SeoRichData_Helper_Data $helper */
        $helper = Mage::helper('amseorichdata');

        foreach ($products as $sProduct) {
            $price = $helper->getProductPrice($sProduct);
            $offers[] =
                array(
                    '@type' => 'Offer',
                    'priceCurrency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                    'price' => $price,
                    'availability' => $sProduct->isAvailable() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock',
                    'itemOffered' => array(
                        '@type' => 'Product',
                        'name' => $sProduct->getName(),
                        'sku' => $sProduct->getSku(),
                        'itemCondition' => 'http://schema.org/NewCondition'
                    ),
                    'seller' => array(
                        '@type' => 'Organization',
                        'name' => Mage::getStoreConfig('design/header/logo_alt')
                    )
                );
        }

        return $offers;
    }

    public function _toHtml()
    {
        return parent::_toHtml() . $this->getResult();
    }

    protected function _getProductVotes($product)
    {
        $adapter = $product->getResource()->getReadConnection();
        $select = $adapter->select()->from($product->getResource()->getTable('rating/rating_vote_aggregated'), 'vote_count')
            ->where('store_id=?', Mage::app()->getStore()->getId())
            ->where('entity_pk_value=?', $product->getId())
            ->limit(1)
        ;

        return $adapter->fetchOne($select);
    }
}
