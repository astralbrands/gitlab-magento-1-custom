<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_Rule_ActionProvider
{
    /**
     * @var Amasty_Promo_Model_Rule_Action_ActionAbstract[]
     */
    protected $_actionsList;

    /**
     * @param string $code
     *
     * @return Amasty_Promo_Model_Rule_Action_ActionAbstract|null
     */
    public function getAction($code)
    {
        $actions = $this->getList();
        if (isset($actions[$code])) {
            return $actions[$code];
        }

        return null;
    }

    public function getOptionsArray()
    {
        $options = array();
        foreach ($this->getList() as $action) {
            $options[] = array(
                'value' => $action->getCode(),
                'label' => $action->getLabel()
            );
        }

        return $options;
    }

    public function getList()
    {
        if ($this->_actionsList === null) {
            $this->_actionsList = array();
            foreach ($this->getActions() as $actionObject) {
                $this->_actionsList[$actionObject->getCode()] = $actionObject;
            }
        }

        return $this->_actionsList;
    }

    /**
     * @return Amasty_Promo_Model_Rule_Action_ActionAbstract[]
     */
    protected function getActions()
    {
        return array(
            Mage::getSingleton('ampromo/rule_action_cart'),
            Mage::getSingleton('ampromo/rule_action_items'),
            Mage::getSingleton('ampromo/rule_action_product'),
            Mage::getSingleton('ampromo/rule_action_spent'),
            Mage::getSingleton('ampromo/rule_action_eachn')
        );
    }
}
