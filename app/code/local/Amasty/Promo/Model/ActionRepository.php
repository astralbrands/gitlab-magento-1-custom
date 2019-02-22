<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_ActionRepository
{
    /**
     * @var Mage_SalesRule_Model_Rule
     */
    protected $_rules = array();

    /**
     * @var string
     */
    protected $_ampromoPrefix = 'ampromo_';

    /**
     * @param $simpleAction
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getActionBySimpleAction($simpleAction)
    {
        if (strpos($simpleAction, $this->_ampromoPrefix) === 0) {
            $action = str_replace($this->_ampromoPrefix, '', $simpleAction);
            $instance = Mage::getSingleton('ampromo/rule_action_' . $action);

            return ($instance && $instance->getCode() == $simpleAction) ? $instance : false;
        }

        return false;
    }
}