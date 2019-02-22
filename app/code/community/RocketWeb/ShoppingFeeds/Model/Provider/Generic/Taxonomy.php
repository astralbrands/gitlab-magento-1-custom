<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */

/**
 * Class RocketWeb_ShoppingFeeds_Model_Feed_Taxonomy
 */
class RocketWeb_ShoppingFeeds_Model_Provider_Generic_Taxonomy extends Varien_Object
{
    /** @var array $_taxonomy The taxonomy file */
    protected $_taxonomy = array();

    /** const CACHE_PATH Folder in which the taxonomy files are saved */
    const CACHE_PATH = 'taxonomy';

    /** cosnt CACHE_FILE_LIFETIME Age in seconds before taxonomy file is replaced */
    const CACHE_FILE_LIFETIME = 2592000; // 30 days

    /** @var string $_taxonomyCachePath Absolute path to the taxonomy cache folder */
    protected $_taxonomyCachePath = null;

    /**
     * Checks if file needs to be replaced (CACHE_FILE_LIFETIME)
     * and fetches a new one.
     *
     * @return $this
     */
    public function prepareTaxonomyFiles()
    {
        if (count($this->_taxonomy) <= 1) {
            $feed = $this->getFeed();
            if (!$feed->hasCategoryLocale()) {
                $locale = $feed->getConfig('categories_locale');
                $feed->setCategoryLocale($locale);
            }
            if (!array_key_exists($feed->getType(), $this->_getSupportedFeedTypes())) {
                return $this;
            }

            try {
                $this->_refreshCache($feed);
            } catch (RocketWeb_ShoppingFeeds_Model_Exception_Taxonomy $e) {
                Mage::getSingleton('adminhtml/session')->addWarning($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * Refresh the cache if needed, falling back on US categories
     * if locale categories are not found
     *
     * @param $feed
     * @throws RocketWeb_ShoppingFeeds_Model_Exception_Taxonomy
     */
    protected function _refreshCache($feed) {
        $locale = $feed->getCategoryLocale();
        $type = $feed->getType();

        $file = $this->_getCacheFile($type, $locale);
        $url = $this->_getTaxonomyUrl($type, $locale);
        if ($locale !== 'en-US') {
            $US_url = $this->_getTaxonomyUrl($type, 'en-US');
        }

        if (!$this->_isCacheFileValid($file)) {
            try {
                $this->_createCacheFile($url, $file);
            }
            catch (Exception $e) {
                //fallback on US categories
                if (isset($US_url)) {
                    $this->_createCacheFile($US_url, $file);
                }
                else {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param $name
     * @param $locale
     * @throws RocketWeb_ShoppingFeeds_Model_Exception_Taxonomy
     * @return string
     */
    protected function _getCacheFile($name, $locale) {
        return $this->_getCacheDir()  . DS . $name . '-' . $locale . '.txt';
    }

    /**
     * Tries to create the cache folder (if it doesn't exists)
     * and returns the full path of the cache file.
     *
     * @param RocketWeb_ShoppingFeeds_Model_Feed $feed
     * @return string
     * @throws RocketWeb_ShoppingFeeds_Model_Exception_Taxonomy
     */
    protected function _getCacheDir()
    {
        if (is_null($this->_taxonomyCachePath)) {
            $taxonomyCachePath = Mage::getBaseDir('cache') . DS . self::CACHE_PATH;
            try {
                Mage::helper('rocketshoppingfeeds')->initSavePath($taxonomyCachePath);
            } catch (Exception $e) {
                $this->_throwException(sprintf(
                    "Can't create Taxonomy cache folder %s", $taxonomyCachePath
                ));
            }
            $this->_taxonomyCachePath = $taxonomyCachePath;
        }
        return $this->_taxonomyCachePath;
    }

    /**
     * Checks if cache file is older then CACHE_FILE_LIFETIME
     * Sets the content of $_taxonomy if file is still valid
     *
     * @param string $cacheFile
     * @return bool
     */
    protected function _isCacheFileValid($cacheFile)
    {
        if (file_exists($cacheFile)) {
            $fileContentString = file_get_contents($cacheFile);
            $fileContent = explode("\n", $fileContentString);
            if (count($fileContent) > 1 && is_numeric($fileContent[0])) {
                if ($fileContent[0] + self::CACHE_FILE_LIFETIME > time()) {
                    unset($fileContent[0]);
                    $this->_taxonomy = array_values($fileContent);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Fetches the latest taxonomy file from provider (using cURL)
     * and sets the content into $_taxonomy variable
     *
     * @param string $cacheFile
     * @param RocketWeb_ShoppingFeeds_Model_Feed $feed
     * @return $this
     * @throws RocketWeb_ShoppingFeeds_Model_Exception_Taxonomy
     */
    protected function _createCacheFile($url, $cacheFile)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'header' => false,
            'timeout' => 15    //Timeout in no of seconds
        ));
        $curl->write(Zend_Http_Client::GET, $url, '1.0');
        $data = $curl->read();
        $response_code = $curl->getInfo(CURLINFO_HTTP_CODE);
        if ($response_code !== 200 || $curl->getErrno() > 0 || $data === false) {
            $this->_throwException(sprintf(
                    'Taxonomy list not loaded for %s: %s (errno %s, response %s)',
                    $url, $curl->getError(), $curl->getErrno(), $response_code)
            );
        }
        $curl->close();

        $fileContent = explode("\n", $data);
        if (strpos($fileContent[0], '#') !== false) {
            // We remove the comment from the file
            unset($fileContent[0]);
        }
        $fileContent= $this->_taxonomy = array_values(array_filter($fileContent));

        array_unshift($fileContent, time());
        file_put_contents($cacheFile, implode("\n", $fileContent));
    }

    /**
     * Returns a list of possible taxonomy lines based on given string
     *
     * @return array
     */
    public function getTaxonomyList()
    {
        return $this->_taxonomy;
    }

    /**
     * Checks if the given feed type support taxonomy autocomplete
     *
     * @return bool
     */
    public function isTaxonomyEnabled()
    {
        $supportedTypes = $this->_getSupportedFeedTypes();
        return array_key_exists($this->getFeed()->getType(), $supportedTypes);
    }

    /**
     * Returns list of feed types that supported for taxonomy autocomplete
     *
     * @return array
     */
    protected function _getSupportedFeedTypes()
    {
        return RocketWeb_ShoppingFeeds_Model_Feed_Type::getTaxonomyFeedTypes();
    }

    /**
     * Throws Taxonomy Exception
     *
     * @param string $msg
     * @throws RocketWeb_ShoppingFeeds_Model_Exception_Taxonomy
     */
    protected function _throwException($msg = '')
    {
        throw new RocketWeb_ShoppingFeeds_Model_Exception_Taxonomy($msg);
    }

    /**
     * Returns the Url of the taxonomy file provider (Google, ...)
     * for given feed
     *
     * @return string
     */
    protected function _getTaxonomyUrl($type, $locale)
    {
        $urls = RocketWeb_ShoppingFeeds_Model_Feed_Type::getTaxonomyFeedUrl();
        return array_key_exists($type, $urls) ? sprintf($urls[$type], $locale) : false;
    }
}