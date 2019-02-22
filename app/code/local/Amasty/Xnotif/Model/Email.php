<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
class Amasty_Xnotif_Model_Email extends Mage_ProductAlert_Model_Email
{
    /**
     * Retrieve stock block
     *
     * @return Mage_ProductAlert_Block_Email_Stock
     */
    protected function _getStockBlock()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = Mage::helper('productalert')
                ->createBlock('productalert/email_stock');
        }

        $this->_stockBlock->setCustomer($this->_customer);
        return $this->_stockBlock;
    }
}