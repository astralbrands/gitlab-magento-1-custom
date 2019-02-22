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
 * Class DynamicYield_Integration_Model_Config_Backend_Updaterate
 */
class DynamicYield_Integration_Model_Config_Backend_Updaterate extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH = 'crontab/jobs/dyi_export_product_feed/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/jobs/dyi_export_product_feed/run/model';
    const CRON_VALIDATOR_PATH = 'crontab/jobs/dyi_cron_validator/schedule/cron_expr';
    const CRON_VALIDATOR_SCHEDULE = '*/5 * * * *';
    const CRON_TPL = 'min h d m w';

    protected $units = array(
        'min' => array(0, 59, 1),
        'h' => array(0, 23, 60),
        'd' => array(1, 31, 1440),
        'w' => array(0, 6, 10080),
        'm' => array(1, 12, 43200)
    );

    protected function _beforeSave() {
        $value = (array)$this->getValue();

        if (sizeof($value) === 1 || !Zend_Validate::is($value[0], 'Digits')) {
            Mage::throwException(Mage::helper('core')->__('Update rate should be an integer.'));
        }

        $match = NULL;

        foreach ($this->units as $unit) {
            if ($unit[2] == $value[1]) {
                $match = $unit;
            }
        }

        if (!$match) {
            Mage::throwException(Mage::helper('core')->__('Invalid update rate selected.'));
        }

        list($min, $max, $unit) = $match;
        $input = $value[0] + 0;

        if ($min === 0) {
            $min = 1;
            $max += 1;
        }

        if ($min > $input || $input > $max) {
            Mage::throwException(Mage::helper('core')->__('The update rate must be between %u and %u.', $min, $max));
        }

        return parent::_beforeSave();
    }

    /**
     * Update the cron job for feed
     *
     * @throws Exception
     */
    protected function _afterSave() {
        $value = explode(',', $this->getValue());

        $minutes = array_reduce($value, function ($carry, $value) {
            return $carry * $value;
        }, 1);

        $cronExpr = array('min' => '*', 'h' => '*', 'd' => '*', 'w' => '*', 'y' => '*');

        $units = $this->units;

        arsort($units, SORT_NUMERIC);

        foreach ($units as $k => $value) {
            list($min, $max, $division) = $value;

            $number = $minutes / $division;

            if ($minutes >= $division && $number < $max && $number >= $min) {
                $cronExpr[$k] = '*/' . $number;
            } else {
                $cronExpr[$k] = $min > 0 ? '*/' . $min : ($minutes > $division ? '0' : '*');
            }
        }

        $cronExprString = str_replace(array_keys($cronExpr), $cronExpr, static::CRON_TPL);

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_VALIDATOR_PATH, 'path')
                ->setValue(self::CRON_VALIDATOR_SCHEDULE)
                ->setPath(self::CRON_VALIDATOR_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string)Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }
}
