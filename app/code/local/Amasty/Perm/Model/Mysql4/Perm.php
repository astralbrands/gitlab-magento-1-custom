<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Model_Mysql4_Perm extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amperm/perm', 'perm_id');
    }

    public function getCustomerIds($userId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('amperm/perm'), 'cid')
            ->where('uid = ?', $userId);

        return $db->fetchCol($sql);

    }
    
    public function assignCustomers($userId, $customerIds)
    {
        $db = $this->_getWriteAdapter();

        $userId = intVal($userId);
        $db->delete($this->getTable('amperm/perm'), "uid=$userId");

        if (!$customerIds)
            return;

        $db->delete($this->getTable('amperm/perm'), "cid IN (".join(',', $customerIds).")");

        $sql = 'INSERT INTO `' . $this->getTable('amperm/perm') . '` (`uid`, `cid`) VALUES ';
        foreach ($customerIds as $id) {
            $id  = intVal($id);
            $sql .= "($userId , $id),";
        }

        $sql = substr($sql, 0, -1);
        $db->raw_query($sql);

        // make sure old orders now assigned to the right user
        //$sql = 'UPDATE `' . $this->getTable('sales/order_grid') . '` AS o, `'.$this->getTable('amperm/perm').'` AS p SET o.uid=p.uid WHERE o.customer_id=p.cid';
        //$db->raw_query($sql);


        return true;
    }

    public function assignOneCustomer($userId, $customerId)
    {
        $db = $this->_getWriteAdapter();

        $userId     = intVal($userId);
        $customerId = intVal($customerId);

        $db->delete($this->getTable('amperm/perm'), "cid=$customerId");

        $db->insert($this->getTable('amperm/perm'), array('uid'=>$userId, 'cid'=>$customerId));

        return true;
    }

    public function getOrderIds($userId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('amperm/order'), 'oid')
            ->where('uid = ?', $userId);

        return $db->fetchCol($sql);
    }

    public function assignOneOrder($userId, $orderId)
    {
        $db = $this->_getWriteAdapter();

        $userId  = intVal($userId);
        $orderId = intVal($orderId);

        $db->delete($this->getTable('amperm/order'), "oid=$orderId");

        $db->insert($this->getTable('amperm/order'), array('uid'=>$userId, 'oid'=>$orderId));

        return true;
    }

    public function getUserByCustomer($customerId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('amperm/perm'), 'uid')
            ->where('cid = ?', $customerId)
            ->limit(1);

        return $db->fetchOne($sql);
    }

    public function getUserByOrder($orderId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('amperm/order'), 'uid')
            ->where('oid = ?', $orderId)
            ->limit(1);

        return $db->fetchOne($sql);
    }

    public function removeOneCustomer($customerId)
    {
        $db = $this->_getWriteAdapter();

        $customerId = intVal($customerId);

        $db->delete($this->getTable('amperm/perm'), "cid=$customerId");

        return true;
    }

    public function massAssignCustomers($userId, $customerIds)
    {
        $db = $this->_getWriteAdapter();

        $userId = intVal($userId);
        if (!$customerIds)
            return;

        $db->delete($this->getTable('amperm/perm'), "cid IN (".join(',', $customerIds).")");

        $sql = 'INSERT INTO `' . $this->getTable('amperm/perm') . '` (`uid`, `cid`) VALUES ';
        foreach ($customerIds as $id) {
            $id  = intVal($id);
            $sql .= "($userId , $id),";
        }

        $sql = substr($sql, 0, -1);
        $db->raw_query($sql);

        return true;
    }
}