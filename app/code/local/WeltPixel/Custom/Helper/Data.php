<?php

class WeltPixel_Custom_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Load product attribute
     *
     * @param $attributeCode
     * @return mixed
     */
    public function getAttributeDetails($attributeCode)
    {
        return $attr = Mage::getModel('catalog/resource_eav_attribute')
            ->loadByCode('catalog_product', $attributeCode);
    }

    /**
     * Load product by SKU
     *
     * @param $sku
     * @return bool
     */
    public function getDirectionsSku($sku)
    {
        if ($sku) {
            $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

            if ($_product) {
                return $_product;
            }
        }

        return false;
    }

    /**
     * @param $_product
     * @param $imageAttr
     * @param bool $keepRatio
     * @param bool $keepFrame
     * @param int $imgWidth
     * @param bool $returnPlaceholder
     * @return bool|string
     */
    public function getProductImage($_product, $imageAttr, $keepRatio = true, $keepFrame = true, $imgWidth = 500, $returnPlaceholder = false)
    {
        $skin_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        $media_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        if ($imageAttr != 'image' && $_product->getData($imageAttr) != NULL && $_product->getData($imageAttr) != 'no_selection') {
            $img = (string) $imgSrc = Mage::helper('catalog/image')
                ->init($_product, $imageAttr)
                ->keepAspectRatio($keepRatio)
                ->keepFrame($keepFrame)
                ->resize($imgWidth);
            if ($img) {
                return $img;
            }
            return $this->getPlaceholderImage($imageAttr);
        }elseif ($imageAttr == 'image') {
            $image = $_product->getImage();
                if(!isset($image)||empty($image)){
                $image = $_product->getSmallImage();
                }
            return $media_url."catalog/product/".$image;

        }elseif ($returnPlaceholder) {
            return $this->getPlaceholderImage($imageAttr);
        }
    return false;
    }

    public function getPlaceholderImage($imageAttr = 'image', $storeId = false)
    {
        $catalogPlaceholderUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product/placeholder/';
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if ($img = Mage::getStoreConfig('catalog/placeholder/' . $imageAttr . '_placeholder', $storeId)) {
            return $catalogPlaceholderUrl . $img;
        }

        return $catalogPlaceholderUrl . Mage::getStoreConfig('catalog/placeholder/image_placeholder', $storeId);
    }


    /**
     * @param $_product
     * @param bool $smartBoxAttr
     * @return array
     */
    public function getSmartBoxes($_product, $smartBoxAttr)
    {
        $smartBoxCms = array();
        $martBoxes = $_product->getAttributeText($smartBoxAttr);
        if (is_array($martBoxes)) {
            foreach ($martBoxes as $smartBox) {
                $smartBlockIdentifier = str_replace(array(' ', '-'), '_', strtolower(trim($smartBox))) . '_smart_block';
                if ($smartBlockContent = $this->_getSmartBlockContent($smartBlockIdentifier)) {
                    $smartBoxCms[] = array(
                        'identifier' => $smartBlockIdentifier,
                        'cms' => $smartBlockContent
                    );
                }
            }
        } else {
            $smartBlockIdentifier = str_replace(array(' ', '-'), '_', strtolower(trim($martBoxes))) . '_smart_block';
            if ($smartBlockContent = $this->_getSmartBlockContent($smartBlockIdentifier)) {
                $smartBoxCms[] = array(
                    'identifier' => $smartBlockIdentifier,
                    'cms' => $smartBlockContent
                );
            }
        }

        if (count($smartBoxCms)) {
            return $smartBoxCms;
        }

        return false;
    }

    protected function _getSmartBlockContent($identifier)
    {
        $smartBlockContent = $this->getLayout()->createBlock('cms/block')->setBlockId($identifier)->toHtml();

        if ($smartBlockContent != '') {
            return $smartBlockContent;
        }

        return false;
    }

    public function getRoutineProducts($currentProduct)
    {
        $products = array();
        $currentProductPosition = (int) $currentProduct->getResource()->getAttribute('current_product_position')->getFrontend()->getValue($currentProduct);
        $currentProductPosition = $currentProductPosition ? $currentProductPosition : 0;

        if ($routineSkus = $currentProduct->getRoutineSkus()) {
            if ($currentProductPosition) {
                $products[$currentProductPosition] = $currentProduct;
            }

            $productSkuPosition = $this->_getProductPosition($currentProductPosition);
            $productSkusArr = array_map('trim', explode(',', $routineSkus));

            $productSkusCount = 1;
            foreach ($productSkusArr as $sku) {
                $_product = $currentProduct->loadByAttribute('sku', $sku);
                if ($_product) {
                    $products[$productSkuPosition[$productSkusCount]] = $_product;
                }
                $productSkusCount++;
            }
        }

        ksort($products);

        return count($products) ? $products : null;
    }

    private function _getProductPosition($currentProductPosition)
    {
        switch ($currentProductPosition) {
            case '1':
                $first = $currentProductPosition + 1;
                $second = $currentProductPosition + $first;
                break;
            case '2':
                $first = $currentProductPosition - 1;
                $second = $currentProductPosition + 1;
                break;
            case '3':
                $first = $currentProductPosition - 2;
                $second = $currentProductPosition - 1;
                break;
            default:
                $first = $currentProductPosition + 1;
                $second = $first + 1;
                break;
        }

        $productPositions = array(
            1 => $first,
            2 => $second
        );

        return $productPositions;
    }

    public function getRoutineClass($num)
    {
        switch ($num) {
            case '1':
                return 'col-sm-push-4';
                break;
            case '2':
                return 'col-sm-push-2';
                break;
            default:
                return '';
                break;
        }
    }

    /**
     * This is copied from WeltPixel_QuickView module
     * @param $product
     * @param array $additional
     * @return mixed
     */
    public function getQuickViewUrl($product, $additional = array()) {
        return Mage::helper('weltpixel_quickview')->getProductUrl($product, $additional);
    }

    public function getRemainingAmount()
    {
        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        $subtotal = $totals['subtotal']->getValue();
        if(isset($totals['discount']) && $totals['discount']->getValue()) {
            $discount = $totals['discount']->getValue();
        } else {
            $discount = 0;
        }
        $total = $subtotal + $discount;
        $min = Mage::getStoreConfig("carriers/freeshipping/free_shipping_subtotal");
        $val = $min-$total;
        $formattedPrice = Mage::helper('core')->currency($val, true, false);
        if ($val < 0) {
            $cms = $this->getLayout()->createBlock('cms/block')->setBlockId('free_shipping_calculator')->toHtml();
            return $this->__($cms);
        } else {
            $cms = $this->getLayout()->createBlock('cms/block')->setBlockId('away_free_shipping_calculator')->toHtml();
            return $this->__('%s ' . $cms, $formattedPrice);
        }
    }
}