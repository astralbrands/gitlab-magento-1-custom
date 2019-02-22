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
 * Class DynamicYield_Integration_Model_Queue
 */
class DynamicYield_Integration_Model_Queue
{
    protected $collection;

    const COLLECTION_ID = 'dyi_queue';

    /**
     * @var Mage_Core_Model_Session
     */
    protected $session;

    public function __construct() {
        $this->session = Mage::getSingleton('core/session');
        $this->collection = $this->getCollection();
    }

    /**
     * @return array
     */
    public function getItems() {
        return $this->getCollection()->getItems();
    }

    /**
     * @return DynamicYield_Integration_Model_Queue_Collection
     */
    public function getCollection() {

        if (!$this->session->getData(static::COLLECTION_ID)) {
            $this->session->setData(static::COLLECTION_ID, Mage::getModel('dynamicyield_integration/queue_collection'));
        }

        return $this->session->getData(static::COLLECTION_ID);
    }

    /**
     * @param Array $event
     */
    public function addToQueue($event) {
        $items = $this->getCollection();
        $items->addItem($event);

        Mage::log('Added to queue: ' . $event['name']);
    }

    /**
     * @return $this
     */
    public function clearQueue() {
        $queue = $this->getCollection();
        $queue->clear();

        return $this;
    }
}
