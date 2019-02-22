<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


class Amasty_Promo_Model_Rule_Action_Eachn extends  Amasty_Promo_Model_Rule_Action_Items
{
    /**
     * @var string
     */
    protected $_actionName = 'ampromo_eachn';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return Mage::helper('ampromo')->__('Add gift with each N-th product in the cart');
    }
    
}
