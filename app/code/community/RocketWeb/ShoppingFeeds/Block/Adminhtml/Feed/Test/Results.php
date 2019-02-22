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

class RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_Test_Results extends Mage_Adminhtml_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('rocketshoppingfeeds/system/results.phtml');
    }
    /**
     * @return string
     */
    public function renderView()
    {
        $this->runUpdate();
        return parent::renderView();
    }

    /**
     * Run the generators
     */
    protected function runUpdate()
    {
        /* @var $feed RocketWeb_ShoppingFeeds_Model_Feed */
        $feed = Mage::registry('rocketshoppingfeeds_feed');
        $sku = $this->getData('sku');
        $generator = false;

        $messages = array();
        $this->addData(array('messages' => $messages,
            'is_feed' => false,
            'log_messages' => array(),
            'script_started_at' => Mage::app()->getLocale()->date(null, null, Mage::app()->getLocale()->getDefaultLocale()),
            'script_finished_at' => Mage::app()->getLocale()->date(null, null, Mage::app()->getLocale()->getDefaultLocale())));

        try {
            /* @var $Generator RocketWeb_ShoppingFeeds_Model_Generator */
            $generator = Mage::helper('rocketshoppingfeeds')->getGenerator($feed);
            $messages[] = array('msg' => 'Feed: ' . $feed->getName(), 'type' => 'info');

            $this->setTestMode(true);
            $generator->setTestMode(true);
            if ($sku) {
                $generator->setTestSku($sku);
                $this->setTestSku($sku);
            } else {
                Mage::throwException(sprintf("Invalid parameters for test mode: sku %s", $sku));
            }

            $messages[] = array('msg' => 'Test mode.', 'type' => 'info');

            // Generate feed - costly process.
            $generator->run();
            if ($generator->getCountProductsExported() > 0) {
                $this->setData('is_feed', true);
            }
            $generatorMessages = is_array($generator->getMessages()) ? $generator->getMessages() : array();
            $messages = array_merge($messages, $generatorMessages);

        } catch (Exception $e) {
            $messages[] = array('msg' => 'Error:<br />' . $e->getMessage(), 'type' => 'error');
        }

        $count_products = 0;
        $count_skipped = 0;
        $feed_data = array();

        if ($generator) {
            $count_products = $generator->getCountProductsExported();
            $count_skipped = $generator->getCountProductsSkipped();

            if ($this->getIsFeed() && $sku != "" && $count_products > 0 && file_exists($generator->getFeedPath())) {
                /* tsv file */
                $feedParams = $generator->getWriteFeedParams();
                $enclosure = $feedParams['encloseCell'] !== false ? $feedParams['encloseCell'] : '~'; // dummy enclosure

                $csv = new Varien_File_Csv();
                $csv->setDelimiter($feedParams['delimiter']);
                $csv->setEnclosure($enclosure);
                $rows = $csv->getData($generator->getFeedPath());
                $i = 0;
                foreach ($rows as $row) {
                    if ($i == 0) {
                        $i++;
                        continue;
                    }
                    $data = array();
                    foreach ($row as $i => $value) {
                        $key = $rows[0][$i];
                        $data[] = array('key' => $key, 'value' => $value);
                    }
                    $feed_data[] = $data;
                    $i++;
                }
            }
        }

        $this->setFeedData($feed_data);
        $messages[] = array('msg' => sprintf("The feed was generated.<br />%d items were added %d products were skipped.", $count_products, $count_skipped), 'type' => 'info');
        $this->setData('messages', $messages);

        if ($generator) {
            $this->setData('log_messages', $generator->getLog()->getMemoryStorage());
        }

        $this->setData('script_finished_at', Mage::app()->getLocale()->date(null, null, Mage::app()->getLocale()->getDefaultLocale()));
    }
}