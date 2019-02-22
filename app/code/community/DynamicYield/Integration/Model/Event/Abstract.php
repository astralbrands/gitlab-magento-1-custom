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
 * Class DynamicYield_Integration_Model_Event_Abstract
 */
abstract class DynamicYield_Integration_Model_Event_Abstract extends Varien_Object
{
    /**
     * @return mixed
     */
    abstract function getName();

    /**
     * @return mixed
     */
    abstract function getType();

    /**
     * @return mixed
     */
    abstract function getDefaultProperties();

    abstract function generateProperties();

    /**
     * @return $this
     */
    public function build() {
        $properties = array_replace((array)$this->getDefaultProperties(), (array)$this->generateProperties());
        $properties['dyType'] = $this->getType();
        $properties['uniqueRequestId'] = $this->generateUniqueId();

        $this->setData(array('name' => $this->getName(), 'properties' => $properties = get_class($this) === "DynamicYield_Integration_Model_Event_Emptycart" ? array_values($properties) : $properties));

        return $this;
    }

    /**
     * @param $array
     * @return bool
     */
    public function is_multi($array) {
        return (count($array) != count($array, 1));
    }

    /**
     * @return int
     */
    public function generateUniqueId() {
        $eventId = intval(str_pad(mt_rand(0, 999999999999), 10, '0', STR_PAD_LEFT));
        return $eventId;
    }
}
