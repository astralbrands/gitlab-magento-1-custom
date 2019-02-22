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
 * Class DynamicYield_Integration_Model_Config_Source_Integrationtype
 */
class DynamicYield_Integration_Model_Config_Source_Integrationtype {

    const CDN_DISABLED = 'No';
    const CDN_ENABLED = 'Yes';
    const CDN_EUROPEAN = 'European';


    /**
     * Integration Type Options
     *
     * @return array
     */
    protected function getOptions() {
        return array(static::CDN_DISABLED => static::CDN_DISABLED, static::CDN_ENABLED => static::CDN_ENABLED, static::CDN_EUROPEAN => static::CDN_EUROPEAN);
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