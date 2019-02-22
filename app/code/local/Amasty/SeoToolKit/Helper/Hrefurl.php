<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoToolKit
 */


class Amasty_SeoToolKit_Helper_Hrefurl extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SCOPE = 'amseotoolkit/hreflang/scope';
    const XML_PATH_ENABLED_FOR = 'amseotoolkit/hreflang/enabled_for';
    const XML_PATH_LANGUAGE = 'amseotoolkit/hreflang/language';
    const XML_PATH_COUNTRY = 'amseotoolkit/hreflang/country';
    const XML_PATH_CMS_RELATION = 'amseotoolkit/hreflang/cms_relation';
    const XML_PATH_X_DEFAULT = 'amseotoolkit/hreflang/x_default';

    const LANGUAGE_DEFAULT = '1';
    const COUNTRY_DEFAULT = '1';
    const COUNTRY_DONT_ADD = '0';

    const SCOPE_GLOBAL = '0';
    const SCOPE_WEBSITE = '1';

    const CMS_ID = 'page_id';
    const CMS_UUID = 'amseo-uuid';
    const CMS_URLKEY = 'identifier';

    const TYPE_CATEGORY = 'category';
    const TYPE_PRODUCT = 'product';
    const TYPE_CMS = 'cms';

    const X_DEFAULT = 'x-default';

    /**
     * @var array|null
     */
    protected $_storeIds;

    /**
     * @var array
     */
    protected $_storeUrls;

    /**
     * @var bool
     */
    protected $eeRewritesEnabled;

    /**
     * @var array|null
     */
    protected $homepages;

    /**
     * @return string
     */
    public function getCmsRelationField()
    {
        return Mage::getStoreConfig(static::XML_PATH_CMS_RELATION);
    }

    /**
     * @return array
     */
    public function getHreflangCodes()
    {
        $hreflangCodes = array();
        $countries = $this->getHreflangCountries();
        $languages = $this->getHreflangLanguages();

        foreach ($languages as $storeId => $code) {
            $xdefaultStoreId = $this->getXdefaultStoreId($storeId);
            if ($storeId == $xdefaultStoreId) {
                $code = static::X_DEFAULT;
            } elseif (isset($countries[$storeId])) {
                $code .= '-' . $countries[$storeId];
            }

            $hreflangCodes[$storeId] = $code;
        }

        return $hreflangCodes;
    }

    /**
     * @param $storeId
     * @return mixed
     */
    protected function getXdefaultStoreId($storeId)
    {
        if (Mage::getStoreConfig(static::XML_PATH_SCOPE) == static::SCOPE_GLOBAL) {
            $storeId = 0;
        }

        return Mage::getStoreConfig(static::XML_PATH_X_DEFAULT, $storeId);
    }

    /**
     * @return array
     */
    protected function getHreflangCountries()
    {
        $countryCodes = array();
        $storeIds = $this->getStoreIds();
        foreach ($storeIds as $storeId) {
            $code = $this->getCountryCodeByStoreId($storeId);
            if ($code) {
                $countryCodes[$storeId] = $code;
            }
        }

        return $countryCodes;
    }

    /**
     * @return array
     */
    protected function getHreflangLanguages()
    {
        $languageCodes = array();
        $storeIds = $this->getStoreIds();
        foreach ($storeIds as $storeId) {
            $languageCodes[$storeId] = $this->getLanguageCodeByStoreId($storeId);
        }

        return $languageCodes;
    }

    /**
     * @return array
     */
    protected function getStoreIds()
    {
        if ($this->_storeIds === null) {
            $this->_storeIds = array();
            $stores = $this->getStores();
            foreach ($stores as $storeId => $store) {
                /** @var Mage_Core_Model_Store $store */
                if ($store->getIsActive()) {
                    $this->_storeIds[] = $storeId;
                }
            }
        }

        return $this->_storeIds;
    }

    /**
     * @return array
     */
    protected function getStores()
    {
        $container = Mage::app();
        if (
            Mage::getStoreConfig(static::XML_PATH_SCOPE) == static::SCOPE_WEBSITE
            && !Mage::registry('amseotoolkit_hreflang_preview')) {
            $container = Mage::app()->getWebsite();
        }

        return $container->getStores();
    }

    /**
     * @param $storeId
     * @return string
     */
    protected function getCountryCodeByStoreId($storeId)
    {
        $countryCode = Mage::getStoreConfig(static::XML_PATH_COUNTRY, $storeId);
        if ($countryCode == static::COUNTRY_DONT_ADD) {
            $countryCode = '';
        } elseif ($countryCode == static::COUNTRY_DEFAULT) {
            $countryCode = Mage::getStoreConfig('general/country/default', $storeId);
        }

        return $countryCode;
    }

    /**
     * @param $storeId
     * @return string
     */
    protected function getLanguageCodeByStoreId($storeId)
    {
        $languageCode = Mage::getStoreConfig(static::XML_PATH_LANGUAGE, $storeId);
        if ($languageCode == static::LANGUAGE_DEFAULT) {
            list($languageCode) = explode('_', Mage::getStoreConfig('general/locale/code', $storeId));
        }

        return $languageCode;
    }

    /**
     * @param array $categoryIds
     * @param string $currentStoreId
     * @return array
     */
    public function getCategoriesHreflangUrls($categoryIds)
    {
        return $this->getHreflangUrls(static::TYPE_CATEGORY, $categoryIds);
    }

    /**
     * @param array $productIds
     * @return array
     */
    public function getProductsHreflangUrls($productIds)
    {
        return $this->getHreflangUrls(static::TYPE_PRODUCT, $productIds);
    }

    /**
     * @param array $relationFieldValues
     * @return array
     */
    public function getCmsHreflangUrls($relationFieldValues)
    {
        return $this->getHreflangUrls(static::TYPE_CMS, $relationFieldValues);
    }

    /**
     * @param $type
     * @param $entityIds
     * @param $additionalId
     * @return array
     */
    protected function getHreflangUrls($type, $entityIds)
    {
        $entityIds = array_filter($entityIds);
        if (empty($entityIds)) {
            return array();
        }

        $hreflangCodes = $this->getHreflangCodes();
        $origStoreIds = array_keys($hreflangCodes);
        $storeIds = $origStoreIds;
        if (($type == static::TYPE_PRODUCT && $this->isEeRewritesEnabled()) || static::TYPE_CMS == $type) {
            $storeIds[] = 0;
        }

        $result = array();
        if (empty($storeIds)) {
            return $result;
        }

        $hreflandsData = $this->getHreflangsData($type, $entityIds, $storeIds);

        $baseStoreUrls = $this->getBaseStoreUrls();

        foreach ($hreflandsData as $hreflangData) {
            if (!isset($result[$hreflangData['entity_id']])) {
                $result[$hreflangData['entity_id']] = array();
            }

            if ($hreflangData['store_id'] == 0) {
                foreach ($origStoreIds as $storeId) {
                    $language = $hreflangCodes[$storeId];
                    $suffix = $this->getAdditionalSuffix($type, $storeId);
                    $result[$hreflangData['entity_id']][$language] =
                        $baseStoreUrls[$storeId] . $hreflangData['request_path'] . $suffix;
                }
            } else {
                $storeId = $hreflangData['store_id'];
                $language = $hreflangCodes[$storeId];
                $suffix = $this->getAdditionalSuffix($type, $storeId);
                $result[$hreflangData['entity_id']][$language] =
                    $baseStoreUrls[$storeId] . $hreflangData['request_path'] . $suffix;
            }
        }

        if ($type == static::TYPE_CMS) {
            $result = $this->hreflangsDataProcessHomePage($result);
        }
        return $result;
    }

    /**
     * @param $type
     * @param $storeId
     * @return string
     */
    protected function getAdditionalSuffix($type, $storeId)
    {
        $suffix = '';
        if ($this->isEeRewritesEnabled()) {
            if ($type == static::TYPE_PRODUCT) {
                $suffix = Mage::getSingleton('catalog/url')
                    ->getProductUrlSuffix($storeId);
            } elseif ($type == static::TYPE_CATEGORY) {
                $suffix = Mage::getSingleton('catalog/url')
                    ->getCategoryUrlSuffix($storeId);
            }
        }

        return $suffix;
    }


    /**
     * @param $type
     * @param $entityIds
     * @param $storeIds
     * @param string|null $additionalId
     * @return Varien_Db_Select
     */
    protected function getHreflangsData($type, $entityIds, $storeIds)
    {
        if ($type == static::TYPE_CMS) {
            $selectModel = Mage::getSingleton('amseotoolkit/resource_hreflang_page');
        } elseif ($type == static::TYPE_CATEGORY) {
            if ($this->isEeRewritesEnabled()) {
                $selectModel = Mage::getSingleton('amseotoolkit/resource_hreflang_ee_category');
            } else {
                $selectModel = Mage::getSingleton('amseotoolkit/resource_hreflang_ce_category');
            }
        } else { // $type == static::TYPE_PRODUCT
            if ($this->isEeRewritesEnabled()) {
                $selectModel = Mage::getSingleton('amseotoolkit/resource_hreflang_ee_product');
            } else {
                $selectModel = Mage::getSingleton('amseotoolkit/resource_hreflang_ce_product');
            }
        }

        $select = $selectModel->getSelect($entityIds, $storeIds);
        $data = $select->query()->fetchAll();

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function hreflangsDataProcessHomePage($data)
    {
        $hreflangCodes = $this->getHreflangCodes();
        $stores = array_keys($hreflangCodes);

        if ($this->homepages === null) {
            foreach ($stores as $storeId) {
                $homePageIdentifier = Mage::getStoreConfig('web/default/cms_home_page', $storeId);
                $homePage = Mage::getModel('cms/page')->load($homePageIdentifier, 'identifier');
                $this->homepages[$storeId] = $homePage->getId();

            }
        }

        $baseStoreUrls = $this->getBaseStoreUrls();
        foreach ($this->homepages as $storeId => $id) {
            if (isset($data[$id]) && isset($data[$id][$hreflangCodes[$storeId]])) {
                $data[$id][$hreflangCodes[$storeId]] = $baseStoreUrls[$storeId];
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    protected function isEeRewritesEnabled()
    {
        if ($this->eeRewritesEnabled === null) {
            $this->eeRewritesEnabled =  Mage::helper('core')->isModuleEnabled('Enterprise_UrlRewrite');
        }

        return $this->eeRewritesEnabled;
    }

    /**
     * @return array
     */
    protected function getBaseStoreUrls()
    {
        if ($this->_storeUrls === null) {
            $this->_storeUrls = array();
            foreach (Mage::app()->getStores() as $storeId => $store) {
                $this->_storeUrls[$storeId] = $store->getBaseUrl();
            }
        }

        return $this->_storeUrls;
    }
}
