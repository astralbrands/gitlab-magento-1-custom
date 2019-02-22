<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Born_Sales_Model_Order extends Mage_Sales_Model_Order {

    /**
     * Order state setter.
     * If status is specified, will add order status history with specified comment
     * the setData() cannot be overriden because of compatibility issues with resource model
     *
     * @param string $state
     * @param string|bool $status
     * @param string $comment
     * @param bool $isCustomerNotified
     * @return Mage_Sales_Model_Order
     */
    public function setState($state, $status = false, $comment = '', $isCustomerNotified = null)
    {
        // ASBI-1166 : Do not change order status when generate invoice or shipment
        $currentStatus = $this->getStatus();
        $checkStatues = array('sage_exported');
        if (in_array($currentStatus, $checkStatues) && $state == Mage_Sales_Model_Order::STATE_PROCESSING) {
            $status = $currentStatus;
        }
        return $this->_setState($state, $status, $comment, $isCustomerNotified, true);
    }

}
