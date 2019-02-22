<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Block_Adminhtml_Form_Field_Preview extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getElementHtml();
        $html .= $this->getPreviewPopup();
        return $html;
    }

    /**
     * @return string
     */
    protected function getPreviewPopup()
    {
        return Mage::app()->getLayout()
            ->createBlock('core/template')
            ->setTemplate('amasty/amseotoolkit/hreflang_preview.phtml')
            ->setHreflangData($this->getHreflangData())
            ->toHtml();
    }

    /**
     * @return array
     */
    protected function getHreflangData()
    {
        $data = array('websites' => array());
        $stores = Mage::app()->getStores();
        Mage::register('amseotoolkit_hreflang_preview', true);
        $hrefUrls = Mage::helper('amseotoolkit/hrefurl')->getHreflangCodes();
        Mage::unregister('amseotoolkit_hreflang_preview');

        foreach ($stores as $storeId => $store) {
            /**@var Mage_Core_Model_Store $store */
            if (!$store->getIsActive()) {
                continue;
            }

            $websiteId = $store->getWebsiteId();
            if (!isset($data['websites'][$websiteId])) {
                $data['websites'][$websiteId] = array('stores' => array());
            }

            $storeData = array();
            $storeData['name'] = $store->getName();
            $storeData['hreflang'] = isset($hrefUrls[$storeId]) ? $hrefUrls[$storeId] : '-';

            $data['websites'][$websiteId]['stores'][$storeId] = $storeData;
            if (!isset($data['websites'][$websiteId]['name'])) {
                $data['websites'][$websiteId]['name'] = $store->getWebsite()->getName();
            }
        }

        $product = Amasty_SeoToolKit_Helper_Hrefurl::TYPE_PRODUCT;
        $category = Amasty_SeoToolKit_Helper_Hrefurl::TYPE_CATEGORY;
        $cms = Amasty_SeoToolKit_Helper_Hrefurl::TYPE_CMS;
        $data['product_style'] = $this->getAdditionalStyle($product);
        $data['category_style'] = $this->getAdditionalStyle($category);
        $data['cms_style'] = $this->getAdditionalStyle($cms);
        return $data;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getAdditionalStyle($type)
    {
        $result = '';
        if ($this->isSeoMetaEnabled()) {
            $enabledFor = explode(',',
                Mage::getStoreConfig(Amasty_SeoToolKit_Helper_Hrefurl::XML_PATH_ENABLED_FOR)
            );
            $result = in_array($type, $enabledFor) ? 'included' : 'excluded';
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isSeoMetaEnabled()
    {
        return Mage::helper('core')->isModuleEnabled('Amasty_Meta');
    }
}
