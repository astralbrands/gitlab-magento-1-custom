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
 * Class DynamicYield_Integration_Model_Config_Backend_Productattribute
 */
class DynamicYield_Integration_Model_Config_Backend_Productattribute extends Mage_Core_Model_Config_Data
{
    /**
     * Validate additional attribute count
     */
    protected function _beforeSave()
    {
        $value = (array)$this->getValue();
        if (sizeof($value) > 13) {
            Mage::throwException('You can not select more than 10 additional attributes!');
        }
        return parent::_beforeSave();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function save() {
        $source = Mage::getModel('dynamicyield_integration/config_source_productattribute');

        $values = array_unique((array)$this->getValue());

        $diff = array_diff($values, array_keys($source->toArray()));

        if (sizeof($diff) > 0) {
            Mage::throwException('Invalid product attributes selected!');
        }

        $selected = Mage::getStoreConfig('dyi_config/product_feed/additional_attributes');

        $output = array_unique(array_merge($this->getValue(), explode(",", $selected)));

        $this->setValue($output);

        return parent::save();
    }
}
