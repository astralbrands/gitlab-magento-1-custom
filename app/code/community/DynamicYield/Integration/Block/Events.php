<?php
/**
 * DynamicYield_Integration
 *
 * @category     DynamicYield
 * @package      DynamicYield_Integration
 * @author       Dynamic Yield <support@dynamicyield.com>
 * @copyright    Copyright (c) 2017 Dynamic Yield (https://www.dynamicyield.com)
 */

class DynamicYield_Integration_Block_Events extends Mage_Core_Block_Text {

    /**
     * @return mixed|string
     */
    public function _toHtml()
    {
        /** @var DynamicYield_Integration_Model_Queue $queue */
        $queue = Mage::getModel('dynamicyield_integration/queue');

        foreach ($queue->getItems() as $event) {
            $this->addEvent($event);
        }
        $queue->clearQueue();

        return parent::_toHtml();
    }

    /**
     * @param $event
     */
    public function addEvent($event) {
        if ($event instanceof DynamicYield_Integration_Model_Event_Abstract) {
            $data = $event->toArray();
        } else {
            $data = $event;
        }

        $this->addText("<script>try{DY.API('event', " . Mage::helper('core')->jsonEncode($data) . ");
        }catch(e){}</script>\n");
    }

}
