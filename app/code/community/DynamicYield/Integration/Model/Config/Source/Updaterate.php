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
 * Class DynamicYield_Integration_Model_Config_Source_Updaterate
 */
class DynamicYield_Integration_Model_Config_Source_Updaterate
{
    /**
     * Simplified options
     *
     * @return array
     */
    protected function getRates() {
        return array(60 => 'Hours', 1440 => 'Days');
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $options = array();

        foreach ($this->getRates() as $key => $value) {
            $options[] = array('value' => $key, 'label' => $value);
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return $this->getRates();
    }
}
