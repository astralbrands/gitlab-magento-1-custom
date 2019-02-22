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
 * Class DynamicYield_Integration_Model_Config_Source_Store
 */
class DynamicYield_Integration_Model_Config_Source_Store
{
    /**
     * @return array
     */
    public function toOptionArray() {
        return Mage::getModel('adminhtml/system_store')->getStoreValuesForForm(false, true);
    }
}
