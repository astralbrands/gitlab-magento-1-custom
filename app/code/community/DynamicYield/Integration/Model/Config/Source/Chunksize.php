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
 * Class DynamicYield_Integration_Model_Config_Source_Chunksize
 */
class DynamicYield_Integration_Model_Config_Source_Chunksize {

    /**
     * Chunk size options
     *
     * @return array
     */
    protected function getOptions() {
        return array(20 => '20', 40 => '40', 100 => '100', 200 => '200');
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $options = array();

        foreach ($this->getOptions() as $key => $value) {
            $options[] = array('value' => $key, 'label' => $value);
        }

        return $options;
    }

    /**
     * Return options as key => value
     *
     * @return array
     */
    public function toArray() {
        return $this->getOptions();
    }

}