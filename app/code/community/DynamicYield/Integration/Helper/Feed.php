<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield Ltd <support@dynamicyield.com.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 **/

/**
 * Class DynamicYield_Integration_Helper_Feed
 */
class DynamicYield_Integration_Helper_Feed extends Mage_Core_Helper_Abstract
{
    const CONF_ACCESS_KEY = 'dyi_config/general/access_key_id';
    const CONF_ACCESS_KEY_SECRET = 'dyi_config/general/access_key_secret';
    const BUCKET = "com.dynamicyield.feeds";
    const EU_BUCKET = "dy-datafeeds-eu";
    const DYI_PRODUCTS_LOG_FILE = 'dyi_skipped_products.log';
    const DYI_DEBUG_LOG_FILE = 'DynamicyieldIntegration.log';


    /**
     * Returns S3 Access Key
     *
     * @return mixed
     */
    public function getAccessKey() {
        return Mage::getStoreConfig(static::CONF_ACCESS_KEY);
    }

    /**
     * Returns S3 Access Key Secret
     *
     * @return mixed
     */
    public function getAccessKeySecret() {
        return Mage::helper('core')->decrypt(Mage::getStoreConfig(static::CONF_ACCESS_KEY_SECRET));
    }

    /**
     * Returns the S3 bucket
     *
     * @return mixed
     */
    public function getBucket() {

        $_helper = Mage::helper('dynamicyield_integration');
        if($_helper->isEuropeAccount() || $_helper->isEuropeCDNIntegration()) {
            $bucket = static::EU_BUCKET;
        } else {
            $bucket = static::BUCKET;
        }

        return $bucket.DS.$_helper->getSectionId(true);
    }

    /**
     * Returns absolute path to the export feed folder
     *
     * @return string
     */
    public function getExportPath() {
        return Mage::getBaseDir('var') . "/dyi_export/";
    }

    /**
     * Returns absolute path to the export feed file
     *
     * @return string
     */
    public function getExportFile() {
        return $this->getExportPath() . $this->getExportFilename();
    }

    /**
     * Returns the name of the export filename and also the name which will be used for AWS S3 bucket
     *
     * @return string
     */
    public function getExportFilename() {
        return "productfeed.csv";
    }

    /**
     * Returns the name of the feed export products log file
     *
     * @return string
     */
    public function getFeedProductLogFile() {
        return static::DYI_PRODUCTS_LOG_FILE;
    }

    /**
     * Returns the name of the debug log file
     *
     * @return string
     */
    public function getDebugLogFile() {
        return static::DYI_DEBUG_LOG_FILE;
    }
}
