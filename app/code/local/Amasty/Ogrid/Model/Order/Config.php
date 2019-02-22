<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */
class Amasty_Ogrid_Model_Order_Config
{
    function toOptionArray() {
        $ret = array();
        $statuses =  Mage::getSingleton('sales/order_config')->getStatuses();
        foreach($statuses as $value => $label)
            $ret[] = array(
                'value' => $value,
                'label' => $label
            );
        return $ret;
    }
}