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
 * Class DynamicYield_Integration_Model_Queue_Collection
 */
class DynamicYield_Integration_Model_Queue_Collection
{
    protected $items = array();

    /**
     * @param Array $event
     * @return $this
     */
    public function addItem($event) {
        $this->items[$event['properties']['uniqueRequestId']] = $event;

        return $this;
    }

    /**
     * @param $eventType
     * @return $this
     */
    public function removeItem($eventType) {
        unset($this->items[$eventType]);

        return $this;
    }

    /**
     * @return array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @return $this
     */
    public function clear() {
        $this->items = array();

        return $this;
    }
}
