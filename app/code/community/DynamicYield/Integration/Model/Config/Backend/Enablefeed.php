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
 * Class DynamicYield_Integration_Model_Config_Backend_Enablefeed
 */
class DynamicYield_Integration_Model_Config_Backend_Enablefeed extends Mage_Core_Model_Config_Data {

    const CRON_STRING_PATH = 'crontab/jobs/dyi_export_product_feed/schedule/cron_expr';
    const CRON_VALIDATOR_PATH = 'crontab/jobs/dyi_cron_validator/schedule/cron_expr';
    const CRON_DISABLED_VALUE = '0';

    /**
     * Update the cron job for feed
     *
     * @throws Exception
     */
    protected function _afterSave()
    {
        $value = $this->getValue();

        if(!$value) {
            try {
                Mage::getModel('core/config_data')
                    ->load(self::CRON_STRING_PATH, 'path')
                    ->setValue(self::CRON_DISABLED_VALUE)
                    ->setPath(self::CRON_STRING_PATH)
                    ->save();
                Mage::getModel('core/config_data')
                    ->load(self::CRON_VALIDATOR_PATH, 'path')
                    ->setValue(self::CRON_DISABLED_VALUE)
                    ->setPath(self::CRON_VALIDATOR_PATH)
                    ->save();
            } catch (Exception $e) {
                throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
            }
        }
    }
}