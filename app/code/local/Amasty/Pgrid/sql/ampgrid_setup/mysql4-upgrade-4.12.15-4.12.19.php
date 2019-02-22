<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * @var Magento_Db_Adapter_Pdo_Mysql $this
 */
$admins = Mage::getModel('admin/user')->getCollection();
$columns = Mage::getModel('ampgrid/column')->getCollection();

foreach ($admins as $admin) {
    $currentGroup = Mage::getModel('ampgrid/group');
    $currentGroup->setData('title', 'Default');
    $currentGroup->setData('user_id', $admin->getId());
    $currentGroup->save();

    Mage::getConfig()->saveConfig('ampgrid/attributes/ongrid' . $admin->getId(), $currentGroup->getId());

    foreach ($columns as $columnData) {
        $columnModel = Mage::getModel('ampgrid/groupcolumn');
        $columnModel->setData('column_id', $columnData['entity_id']);
        $columnModel->setData('group_id', $currentGroup->getId());
        $columnModel->setData('is_editable', $columnData['editable']);
        $columnModel->setData('is_visible', $columnData['visible']);
        $columnModel->setData('custom_title', $columnData['title']);
        $columnModel->save();
    }
}
