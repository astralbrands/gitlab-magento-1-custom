<?php

class Born_Handling_Block_Adminhtml_Sales_Order_Create_Totals_Fee extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default {

    /**
     * Use your own template if necessary
     * See "sales/order/create/totals/default.phtml" for model
     */
    //protected $_template = 'born/sales/order/create/totals/fee.phtml';
    public function getSource() {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals() {
        if ((float) $this->getSource()->getFeeAmount() == 0) {
            return $this;
        }
        $total = new Varien_Object(array(
            'code' => 'handling_fee',
            'field' => 'fee_amount',
            'value' => $this->getSource()->getFeeAmount(),
            'label' => $this->__('Handling Fee')
        ));
        $this->getParentBlock()->addTotalBefore($total, 'shipping');
        return $this;
    }

}
