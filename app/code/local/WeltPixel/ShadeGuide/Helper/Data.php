<?php

class WeltPixel_ShadeGuide_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get system config value
     *
     * @param $group
     * @param $field
     * @param bool $storeId
     * @return mixed
     */
    public function getConfigValue($group, $field, $storeId = false)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return Mage::getStoreConfig('shadeguide/' . $group . '/' . $field, $storeId);
    }

    /**
     * @return mixed
     */
    public function foundationFinderIsEnabled()
    {
        return $this->getConfigValue('general', 'enable');
    }

    /**
     * Get Intro CMS Block
     *
     * @return bool
     */
    public function getIntroBlock()
    {
        $identifier = $this->getConfigValue('general', 'intropage_cms');
        if ($identifier != null && $identifier != '') {
            return $this->getLayout()->createBlock('cms/block')->setBlockId($identifier);
        }

        return false;
    }

    /**
     * Get additional CMS Block
     *
     * @param $step
     * @param bool $add
     * @return bool
     */
    public function getAdditionalCmsBlock($step, $add = false)
    {
        $identifier = 'foundation_finder_' . $step['product_attribute'];
        if ($add) {
            $identifier .= '_' . $add;
        }

        // get cms block
        $cmsBlock = $this->getLayout()->createBlock('cms/block')->setBlockId($identifier);
        if ($cmsBlock->toHtml() != '') {
            return $cmsBlock->toHtml();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getBackgroundImage()
    {
        $identifier = 'foundation_finder_background_image';
        $cmsBlock = $this->getLayout()->createBlock('cms/block')->setBlockId($identifier);
        if ($cmsBlock->toHtml() != '') {
            return $cmsBlock->toHtml();
        }

        return false;
    }

    public function getPlaceholderImage($storeId = false)
    {
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }

        return Mage::getStoreConfig('catalog/placeholder/image_placeholder', $storeId);
    }

    public function convertOptionLabel($optionLabel)
    {
        $productModel = Mage::getModel('catalog/product');
        $optionLabel = str_replace('-', '_', $productModel->formatUrlKey($optionLabel));

        return $optionLabel;
    }
}