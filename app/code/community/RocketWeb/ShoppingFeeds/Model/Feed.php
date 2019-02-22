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
 * @category   RocketWeb
 * @package    RocketWeb_ShoppingFeeds
 * @copyright  Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

/**
 * Class RocketWeb_ShoppingFeeds_Model_Feed
 */
class RocketWeb_ShoppingFeeds_Model_Feed extends Mage_Core_Model_Abstract
{
    /* models */
    protected $_status;
    protected $_store;
    protected $_tools;

    protected $_columns_map = null;
    protected $_empty_columns_replace_map = null;

    protected function _construct()
    {
        $this->_init('rocketshoppingfeeds/feed');
        $this->_status = Mage::getModel('rocketshoppingfeeds/feed_status');
        $this->_tools = Mage::getSingleton('rocketshoppingfeeds/tools', array(
            'feed' => $this,
            'store_id' => $this->getData('store_id')
        ));
    }

    /**
     * Load the extra objects: status and config_data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        // Fill in default feed settings from config.xml
        $feed_data = Mage::getConfig()->getNode('default/feed');
        foreach ($feed_data->asArray() as $key => $value) {
            if (!$this->hasData($key)) {
                if (in_array($key, array('exclude_attributes'))) {
                    $value = explode(',', $value);
                }
                $this->setData($key, $value);
            }
        }

        // Load default configs from feed_type.xml
        $file = Mage::getModuleDir('etc', 'RocketWeb_ShoppingFeeds'). DS. 'feeds'. DS. $this->getType(). '.xml';
        if (!is_readable($file)) { // fallback on generic columns
            $file = Mage::getModuleDir('etc', 'RocketWeb_ShoppingFeeds'). DS. 'feeds'. DS. 'generic.xml';
        }
        if (is_readable($file)) {
            $conf = new RocketWeb_ShoppingFeeds_Model_Core_Config();
            $conf->loadFile($file);
            $extras = $this->hasData('type_extend') ? $this->getData('type_extend') : array();
            foreach ($extras as $key => $elm) {
                if ($this->getType() == $key) {
                    $file = Mage::getModuleDir('etc', 'RocketWeb_ShoppingFeeds'). DS. 'feeds'. DS. $elm. '.xml';
                    if (is_readable($file)) {
                        $confExt = new RocketWeb_ShoppingFeeds_Model_Core_Config();
                        $confExt->loadFile($file);
                        $conf->extend($confExt);
                    }
                }
            }

            foreach ($conf->getNode()->asArray() as $node => $key) {
                $this->setData($node, $key);
            }
        }
        if ($this->hasData('default_feed_config') && is_array($this->getData('default_feed_config'))) {
            $defaultConfig = $this->getData('default_feed_config');
            if (isset($defaultConfig['schedule']) && is_array($defaultConfig['schedule']) ){
                if (isset($defaultConfig['schedule']['batch_limit']) && empty($defaultConfig['schedule']['batch_limit'])) {
                    $defaultConfig['schedule']['batch_limit'] = RocketWeb_ShoppingFeeds_Model_Generator::DEFAULT_BATCH_LIMIT;
                }
                if (isset($defaultConfig['schedule']['batch_mode']) && empty($defaultConfig['schedule']['batch_mode'])) {
                    $defaultConfig['schedule']['batch_mode'] = RocketWeb_ShoppingFeeds_Model_Generator::DEFAULT_BATCH_MODE;
                }

                $this->setData('default_feed_config', $defaultConfig);
            }
        }
        // Fill in default store_id
        if (!$this->hasData('store_id')) {
            $this->setData('store_id', Mage::helper('rocketshoppingfeeds')->getDefaultStoreId());
        }

        // Fill in the messages displayed in grid
        $messages = unserialize($this->getMessages());
        $this->setMessages(array_merge(
            array('date' => date("Y-m-d H:i:s"), 'progress' => 0, 'added' => 0, 'skipped' => 0),
            is_array($messages) ? $messages : array()
        ));
        return parent::_afterLoad();
    }

    /**
     * Prepare the messages
     */
    protected function _beforeSave()
    {
        if(is_array($this->getMessages())) {
            $this->setMessages(serialize($this->getMessages()));
        }

        $this->_ensureUniqueMicrodata();
    }

    protected function _ensureUniqueMicrodata() {
        if ($this->getData('use_for_microdata') === $this->getOrigData('use_for_microdata')
            || $this->getData('use_for_microdata') == '0') {
            return true;
        }

        $feeds = $this->getCollection()
            ->addStoreFilter($this->getStoreId())
            ->addFieldToFilter('use_for_microdata', 1)
            ->addFieldToFilter('id', array('neq' => $this->getId()))
            ->addFieldToFilter('status', array('neq' => RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_DISABLED));

        if ($feeds->getSize() > 0) {
            $this->getResourceCollection()->disableMicrodataOnCollection($feeds);
        }
    }

    /**
     * Save the config values attached to the feed object
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        $config_groups = array_keys($this->getData('default_feed_config'));
        $shippingSettingsChanged = false;

        foreach ($this->getConfig() as $key => $value) {
            $tab = substr($key, 0, strpos($key, '_'));
            if (in_array($tab, $config_groups) || in_array($key, $config_groups)) {
                $lookup = Mage::getModel('rocketshoppingfeeds/config')->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('feed_id', $this->getId())
                    ->addFieldToFilter('path', $key)
                    ->load();
                $model = count($lookup) == 0 ? Mage::getModel('rocketshoppingfeeds/config') : $lookup->getFirstItem();

                // check if any of the shipping settings have changed
                if (!$shippingSettingsChanged && $model->getId() && self::_isShippingConfig($key)) {
                    if ($model->getValue() != $value) {
                        $shippingSettingsChanged = true;
                        self::clearShippingCache($this->getId());
                    }
                }

                $model->addData(array(
                    'feed' => $this,
                    'feed_id' => $this->getId(),
                    'path' => $key,
                    'value' => $value));
                $model->save();
            }
        }

        $yesterday = new Zend_Date();
        $yesterday->subDay(1);

        $schedule = $this->getSchedule();
        if ($schedule && is_array($schedule)) {
            $processedSchedules = array();
            $skipped = 0;
            foreach ($schedule as $scheduleId => $data) {

                // this should be to true at the end of the Feed_Import::importSource method
                if ($this->isObjectNew()) {
                    $model = Mage::getModel('rocketshoppingfeeds/feed_schedule');
                    $model->setProcessedAt($yesterday->get(Zend_Date::ISO_8601));
                } else {
                    $model = Mage::getModel('rocketshoppingfeeds/feed_schedule')->load($scheduleId);

                    if (!$model->getId()) {
                        $model->setProcessedAt($yesterday->get(Zend_Date::ISO_8601));
                    }

                    if ($model->getId() && isset($data['delete']) && $data['delete']) {
                        $model->delete();
                        continue ;
                    }
                }

                $batchMode = $data['batch_mode'];
                if ($batchMode && isset($data['batch_limit']) && $data['batch_limit']) {
                    $batchLimit = $data['batch_limit'];
                } else {
                    $batchLimit = RocketWeb_ShoppingFeeds_Model_Generator::DEFAULT_BATCH_LIMIT;
                }
                $startAt = $data['start_at'];
                if (array_key_exists($startAt, $processedSchedules) &&
                    ($startAt != 0 || ($startAt == 0 && !$this->getIsClone()))) {
                    // skip duplicated hour and throw warning exception at the end
                    $skipped++;
                    continue;
                }
                $processedSchedules[$startAt] = $startAt;
                $model->setStartAt($startAt)
                    ->setBatchLimit($batchLimit)
                    ->setBatchMode($batchMode)
                    ->setFeedId($this->getId())
                    ->save();
            }
            if ($skipped && !$this->hasData('ignore_warnings')) {
                throw new RocketWeb_ShoppingFeeds_Model_Exception('Check the feed schedule, you cannot have multiple schedules on the same hour, duplicates where not saved.');
            }
        }

        $ftpAccounts = $this->getFtp();
        if ($ftpAccounts && is_array($ftpAccounts)) {
            foreach ($ftpAccounts as $id => $accountData) {

                // this should be to true at the end of the Feed_Import::importSource method
                if ($this->isObjectNew()) {
                    $model = Mage::getModel('rocketshoppingfeeds/feed_ftp');
                } else {
                    $model = Mage::getModel('rocketshoppingfeeds/feed_ftp')->load($id);
                    $delete = (isset($accountData['delete']) && $accountData['delete']);

                    if ($model->getId() && $delete) {
                        $model->delete();
                        continue ;
                    }
                }

                foreach ($accountData as $key => $value) {
                    $model->setData($key, $value);
                }

                $model->setFeedId($this->getId())->save();
            }
        }

        return parent::_afterSave();
    }


    /**
     * Clear the shipping entries associated with this feed from the rw_gfeed_shipping table.
     *
     * @param $feedId int
     */
    public static function clearShippingCache($feedId = false)
    {
        $collection = Mage::getModel('rocketshoppingfeeds/shipping')->getCollection()
            ->addFieldToSelect('id');

        if ($feedId !== false) {
            $collection->addFieldToFilter('feed_id', $feedId);
        }

        $collection->load();

        /** @var RocketWeb_GoogleBaseFeedGenerator_Model_Shipping $cachedShippingValue */
        foreach($collection as $cachedShippingValue) {
            //TODO: this foreach should be improved with a single DELETE query
            try {
                $cachedShippingValue->delete();
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::throwException('Error removing shipping cache entry: ' . $e->getMessage());
            }
        }
    }

    /**
     * Test if a config path is part of the Shipping tab.
     *
     * @param string $configPath
     * @return bool
     */
    protected static function _isShippingConfig($configPath)
    {
        $configKeys = array(
            'shipping_methods',
            'shipping_country',
            'shipping_only_minimum',
            'shipping_only_free_shipping',
            'shipping_add_tax_to_price'
        );

        return in_array($configPath, $configKeys);
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        if (!$this->_status->hasData()) {
            $this->_status->load($this);
        }
        return $this->_status;
    }

    /**
     * Use resource to save the feed so that config is not saved.
     *
     * @param $value
     * @return $this
     */
    public function saveStatus($value)
    {
        $this->setStatus($value);
        $this->_beforeSave();
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getMessage($key)
    {
        $data = $this->getMessages();
        return array_key_exists($key, $data) ? $data[$key] : '';
    }

    /**
     * Use resource to save the feed so that config is not saved.
     *
     * @param $value
     * @return $this
     */
    public function saveMessages($value)
    {
        $this->setMessages($value);
        $this->_beforeSave();
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        $code = $this->getStatus()->getCode();
        return $code == RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_DISABLED;
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        $code = $this->getStatus()->getCode();

        return !in_array($code, array(
            RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_DISABLED,
//            RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_PENDING,
//            RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_PROCESSING
        ));
    }


    /**
     * Checks if directive is allowed for this feed
     * @param string $code
     * @return bool $code
     */
    public function isAllowedDirective($code)
    {
        $directives = $this->getData('directives');
        if (array_key_exists($code, $directives)) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param $section
     * @param $key
     * @return bool
     */
    public function isAllowedConfig($section, $key)
    {
        $map = $this->getData('default_feed_config');
        return array_key_exists($section, $map) && array_key_exists($key, $map[$section]);
    }

    /**
     * * Get the configuration array of the feed, loads data from feed_config
     * and fills in default values from config.xml
     *
     * If $key is specified, it will pull the value from it
     *
     * @param null $key
     * @param boolean $defaultData
     * @return mixed|null
     */
    public function getConfig($key = null, $defaultData = true)
    {
        if (!is_null($key)) {
            $cfg = $this->getConfig(null, $defaultData);
            return array_key_exists($key, $cfg) ? $cfg[$key] : null;
        }

        if (!$this->hasData('config') || !$defaultData) {
            $data = array();

            // Load saved config data
            $config_collection = Mage::getModel('rocketshoppingfeeds/config')->getCollection()
                ->addFieldToFilter('feed_id', $this->getId());

            foreach($config_collection as $item) {
                $data[$item->getPath()] = $item->getValue();
            }

            if ($defaultData) {
                // Fill in missing configuration keys with default data from config.xml
                $config = Mage::getModel('rocketshoppingfeeds/config')->setFeed($this);

                foreach ($this->getData('default_feed_config') as $section => $node) {
                    foreach ($node as $key => $value) {
                        $path = $section. '_'. $key;
                        if (!array_key_exists($path, $data)) {
                            // force data backend processing
                            $config->addData(array('path' => $path, 'value' => $value));
                            $data[$config->getPath()] = $config->getValue();
                        }
                    }
                }
            } else {
                return $data;
            }

            $this->setData('config', $data);
        }

        return $this->getData('config');
    }

    /**
     * Used as a callback in RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_Grid
     *
     * @return string
     */
    public function getFeedUrl()
    {
        $url = '';
        $feed_dir = $this->getConfig('general_feed_dir');

        if (!empty($feed_dir)) {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $feed_dir. DS. $this->getFeedFile();
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getFeedFile()
    {
        return sprintf($this->getData('feed_filename'), $this->getId());
    }

    /**
     * @return string
     */
    public function getPromoFeedFile()
    {
        $cfg = $this->getConfig('google_promotions');
        if (is_array($cfg) && array_key_exists('mode', $cfg) && $cfg['mode'] == '1') {
            return sprintf($this->getData('promotion_filename'), $this->getId());
        }
        return '';
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return sprintf($this->getData('log_filename'), $this->getId());
    }

    /**
     * Convert object attributes to XML
     *
     * @param  array $arrAttributes array of required attributes
     * @param string $rootName name of the root element
     * @return string
     */
    protected function __toXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag=false, $addCdata=true)
    {
        $xml = '';
        if ($addOpenTag) {
            $xml.= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        }
        if (!empty($rootName)) {
            $xml.= '<'.$rootName.'>'."\n";
        }
        $xmlModel = new Varien_Simplexml_Element('<node></node>');
        $arrData = $this->toArray($arrAttributes);
        unset($arrData['id']);
        foreach ($arrData as $fieldName => $fieldValue) {
            $fieldValue = is_array($fieldValue) ? serialize($fieldValue) : $fieldValue;
            $fieldValue = $addCdata ? "<![CDATA[$fieldValue]]>" : $xmlModel->xmlentities($fieldValue);
            $xml.= "\t<$fieldName>$fieldValue</$fieldName>\n";
        }

        $schedule = Mage::getModel('rocketshoppingfeeds/feed_schedule')->getCollection()
            ->addFieldToFilter('feed_id', $this->getId());
        $schedule->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('start_at', 'batch_limit', 'batch_mode'));
        foreach ($schedule->getItems() as $item) {
            $xml .= Mage::helper('rocketshoppingfeeds')->arrayToXml($item->getData(), 'schedule', $addCdata);
        }

        $ftpAccounts = $schedule = Mage::getModel('rocketshoppingfeeds/feed_ftp')->getCollection()
            ->addFieldToFilter('feed_id', $this->getId());
        $ftpAccounts->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('username', 'password', 'host', 'port', 'path', 'mode'));
        foreach ($ftpAccounts->getItems() as $item) {
            $xml .= Mage::helper('rocketshoppingfeeds')->arrayToXml($item->getData(), 'ftp_accounts', $addCdata);
        }

        // Load default values that make up sample config, also usefull for debuging.
        $this->load($this->getId());
        $xml .= Mage::helper('rocketshoppingfeeds')->arrayToXml($this->getConfig(null, false), 'config', $addCdata);

        if (!empty($rootName)) {
            $xml.= '</'.$rootName.'>'."\n";
        }
        return $xml;
    }

    /**
     * Loads schedule for the current feed.
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getSchedule()
    {
        if (!$this->hasData('schedule')) {
            $schedule = Mage::getModel('rocketshoppingfeeds/feed_schedule')->getCollection()
                ->addFieldToFilter('feed_id', $this->getId())
                ->getFirstItem();
            $this->setData('schedule', $schedule);
        }
        return $this->getData('schedule');
    }

    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->_store = Mage::app()->getStore($this->getStoreId());
            $currency = Mage::getModel('directory/currency')->load($this->getConfig('general_currency'));
            $this->_store->setData('current_currency', $currency);
        }
        return $this->_store;
    }

    /**
     * Returns columns map in asc order.
     * Skips columns with attributes that doesn't exist.
     * Caches eav attributes model used.
     *
     *  [column] =>
     *            [column]
     *            [attribute code or directive code]
     *            [default_value]
     *            [order]
     *
     * @return array
     */
    public function getColumnsMap()
    {
        if (!is_null($this->_columns_map)) {
            return $this->_columns_map;
        }

        $tmp = $cfg_map = $this->getConfig('columns_map_product_columns');

        foreach ($tmp as $k => $arr) {
            if (!$this->isAllowedDirective($arr['attribute'])) {
                $attribute = $this->_tools->getAttribute($arr['attribute']);
                if ($attribute == false) {
                    $this->log(sprintf("Column '%s' ignored, can't find attribute with code '%s'.", $arr['column'], $arr['attribute']), Zend_Log::WARN);
                    unset($cfg_map[$k]);
                    continue;
                }
                $attribute->setStoreId($this->getData('store_id'));
                $this->_tools->setAttribute($attribute);
            }
        }
        $this->_columns_map = array();
        $output_columns = $this->getOutputColumns();
        foreach ($cfg_map as $arr) {
            if (empty($output_columns) || in_array($arr['column'], $output_columns)) {
                if (isset($this->_columns_map[$arr['column']])) {
                    $this->_columns_map[$arr['column']]['counter']++;
                } else {
                    $this->_columns_map[$arr['column']] = $arr;
                    $this->_columns_map[$arr['column']]['counter'] = 1;
                }
            }
        }

        // Check attribute assigned to availability column (stock status).
        if (!$this->getConfig('general_use_default_stock') && isset($this->_columns_map['availability']) && $this->getConfig('general_stock_attribute_code') !== "") {
            $attribute = $this->_tools->getAttribute($this->getConfig('general_stock_attribute_code'));
            if ($attribute !== false) {
                $attribute->setStoreId($this->getData('store_id'));
                $this->_tools->setAttribute($attribute);
            } else {
                $this->log(sprintf("Column '%s' ignored, can't find attribute with code '%s'.", $this->_columns_map['availability']['column'], $this->getConfig('general_stock_attribute_code')), Zend_Log::WARN);
                unset($this->_columns_map['availability']);
            }
        }

        $s = array();
        foreach ($this->_columns_map as $column => $arr) {
            $s[$column] = $arr['order'];
        }
        array_multisort($s, $this->_columns_map);

        return $this->_columns_map;
    }

    /**
     * Returns columns map replaced by other attributes when it's value is empty for a product.
     * Sorts result asc by rule order.
     * Caches eav attributes model used.
     * Skips rules with attributes that doesn't exist.
     *
     * @return array
     */
    public function getEmptyColumnsReplaceMap()
    {
        if (!is_null($this->_empty_columns_replace_map)) {
            return $this->_empty_columns_replace_map;
        }

        $_columns_map = $this->getColumnsMap();
        $tmp = $cfg_map = $this->getConfig('filters_map_replace_empty_columns');

        if (empty($cfg_map)) {
            $tmp = $cfg_map = array();
        }

        foreach ($tmp as $k => $arr) {

            if (!isset($_columns_map[$arr['column']])) {
                unset($cfg_map[$k]);
                continue;
            }

            if (strpos($arr['attribute'], 'rw_gbase_directive_') === false) {
                $attribute = $this->_tools->getAttribute($arr['attribute']);
                if ($attribute == false && empty($arr['static'])) {
                    $this->log(sprintf("Rule ('%s', '%s', '%d') is ignored, can't find attribute with code '%s'.", $arr['column'], $arr['attribute'], @$arr['order'], $arr['attribute']), Zend_Log::WARN);
                    unset($cfg_map[$k]);
                    continue;
                } elseif ($attribute) {
                    $attribute->setStoreId($this->getData('store_id'));
                    $this->_tools->setAttribute($attribute);
                }
            }
        }

        $this->_empty_columns_replace_map = $cfg_map;

        // Move rules without order to the bottom.
        $s = array();
        foreach ($this->_empty_columns_replace_map as $k => $arr) {
            if (!isset($arr['order']) || (isset($arr['order']) && $arr['order'] == "")) {
                $this->_empty_columns_replace_map[$k]['order'] = 99999;
            }

            $s[$k] = $arr['order'];
        }
        array_multisort($s, $this->_empty_columns_replace_map);

        return $this->_empty_columns_replace_map;
    }

    public function log($msg) {
        Mage::getSingleton('rocketshoppingfeeds/log')->write($msg, null, null, array(
            'file' => $this->getLogFile()
        ));
    }
}
