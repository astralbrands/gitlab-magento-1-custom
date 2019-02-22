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
class RocketWeb_ShoppingFeeds_Model_Observer
{
    public function processScheduleCron($observer) {
        if (Mage::helper('rocketshoppingfeeds')->isCronEnabled()) {
            $this->processSchedule($observer);
        }
    }

    public function processQueueCron($observer) {
        if (Mage::helper('rocketshoppingfeeds')->isCronEnabled()) {
            $this->processQueue($observer);
        }
    }

    /**
     * Process the schedule data and adds messages to queue
     */
    public function processSchedule($schedule)
    {
        // Always work with time in UTC, just like the DB
        $time = new Zend_Date();
        $hour = $time->get(Zend_Date::HOUR_SHORT);
        $date = $time->get(Zend_Date::ISO_8601);

        /** @var RocketWeb_ShoppingFeeds_Model_Mysql4_Feed_Schedule_Collection $collection */
        $collection = Mage::getResourceModel('rocketshoppingfeeds/feed_schedule_collection');
        $collection->getSelect()->where('DATE(`processed_at`) < DATE(\'' . $date . '\') AND start_at = ' . $hour);

        $scheduled = array();
        foreach ($collection as $item) {
            $feed = Mage::getModel('rocketshoppingfeeds/feed')->load($item->getFeedId());
            if ($feed->getId() && $feed->isAllowed()) {
                $scheduled[] = $feed->getId();
                Mage::getModel('rocketshoppingfeeds/queue')->send($feed, 'schedule', $item);
                $item->setData('processed_at', $date)
                    ->save();
                $feed->saveStatus(RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_PENDING);
            }
        }

        if ($schedule->getVerbose()) {
            if (!empty($scheduled)) {
                echo '[scheduler] Hour '.$hour.' UTC: scheduled feed IDs '.implode(',', $scheduled). PHP_EOL;
            } else {
                echo '[scheduler] Hour '.$hour.' UTC: did not find anything to schedule.'. PHP_EOL;
            }
        }
    }

    /**
     * Processes the queue and runs the generator
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @throws Exception for Magento errors
     */
    public function processQueue($schedule)
    {
        /** @var RocketWeb_ShoppingFeeds_Model_Queue $queue */
        $queue = Mage::getModel('rocketshoppingfeeds/queue')->read();
        if ($schedule->getVerbose()) {
            echo $queue->getId() ? 
                '[queue] Processing queue ID: '. $queue->getId(). PHP_EOL : '[queue] Nothing in the queue to process.'. PHP_EOL;
        }
        if (!$queue->getId()) {
            return;
        }
        // lock the queue so that another cron does not process it
        $queue->lock();

        /** @var RocketWeb_ShoppingFeeds_Model_Feed $feed */
        $feed = Mage::getModel('rocketshoppingfeeds/feed')->load($queue->getFeedId());
        $messages = $feed->getMessages();
        if ($messages['progress'] == '100') {
            $feed->setMessages(array('date' => date("Y-m-d H:i:s"), 'progress' => 0, 'added' => 0, 'skipped' => 0));
        }
        $feed->saveStatus(RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_PROCESSING);

        $feedSchedule = Mage::getModel('rocketshoppingfeeds/feed_schedule')->load($queue->getScheduleId());
        if (!$feedSchedule->getId()) {
            $feedSchedule = $feed->getSchedule();
        }
        $feed->setSchedule($feedSchedule);

        try {
            $generator = Mage::helper('rocketshoppingfeeds')->getGenerator($feed)
            ->addData(array(
                'schedule_id'   => $schedule->getScheduleId(),
                'verbose'       => $schedule->getVerbose())
            );
            $generator->run();
        }
        catch (RocketWeb_ShoppingFeeds_Model_Exception $e) {
            // Ending batch earlier due memory limit. Do not release the queue.
            $generator->log($e->getMessage());
        }
        catch (Exception $e) {
            $queue->delete();
            $feed->saveStatus(RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_ERROR);
            $log = Mage::getSingleton('rocketshoppingfeeds/log');
            $log->write($e->getMessage(), Zend_Log::ERR, null, array('file' => $feed->getLogFile(), 'force' => true));
            throw new Exception($e);
        }
        
        $batchMode = $feedSchedule->getBatchMode();

        // Unlock the queue message so that it ca process next batch
        if ($batchMode && !$generator->getBatch()->completedForToday()) {
            $queue->unlock();
        }

        // Set the feed as completed
        if (!$batchMode || ($batchMode && $generator->getBatch()->completedForToday())) {
            $queue->delete();
            $feed->saveStatus(RocketWeb_ShoppingFeeds_Model_Feed_Status::STATUS_COMPLETED);
        }
    }

    /**
     * Listen for the admin_system_config_changed_section_carriers event.
     *
     * @param Varien_Event_Observer  $observer
     * @return RocketWeb_ShoppingFeeds_Model_Observer
     */
    public function systemConfigCarriersAfterSave($observer)
    {
        return $this->_clearShippingCache();
    }


    /**
     * Listen for the controller_action_postdispatch_admin_systemCurrency_saveRates event.
     *
     * @param Varien_Event_Observer  $observer
     * @return RocketWeb_ShoppingFeeds_Model_Observer
     */
    public function adminCurrencySaveRatesActionAfter($observer)
    {
        return $this->_clearShippingCache();
    }


    /**
     * Clear shipping cache table rw_gfeed_shipping.
     *
     * @return RocketWeb_ShoppingFeeds_Model_Observer
     */
    protected function _clearShippingCache()
    {
        // don't do anything if $scheduled import is enabled because we won't even use the cache if this is enabled
        if (Mage::helper('rocketshoppingfeeds')->isScheduledCurrencyRateUpdateEnabled()) {
            return $this;
        }

        $feedModel = Mage::getSingleton('rocketshoppingfeeds/feed');
        $feedModel->clearShippingCache();
        return $this;
    }
}
