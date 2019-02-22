<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/23/13
 * Time   : 1:29 PM
 * File   : Transaction.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_Resource_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_sagepaymentspro/transaction', 'id');
    }
    public function loadByOrderId(Ebizmarts_SagePaymentsPro_Model_Transaction $obj,$orderId,$storeId) 
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.'.'order_id =?', $orderId)
            ->where($this->getMainTable().'.'.'store_id =?', $storeId);
        $transactionId = $this->_getReadAdapter()->fetchOne($select);
        if ($transactionId) {
            $this->load($obj, $transactionId);
        }
        return $this;
    }

}