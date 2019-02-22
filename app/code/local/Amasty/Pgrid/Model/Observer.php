<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function checkoutAllSubmitAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getInventoryProcessed()) {
            $this->reindexQuoteInventory($observer);
        }
        return $this;
    }

    /**
     * Refresh qty sold index for specific stock items after succesful order placement
     * @param $observer
     */
    public function reindexQuoteInventory($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        return $this->reindexItems($quote->getAllItems());
    }

    /**
     * Refresh qty sold index for specific stock items after order refund
     * @param $observer
     */
    public function refundCreditmemoInventory($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        return $this->reindexItems($creditmemo->getAllItems());
    }

    /**
     * reindex qty sold
     * @param $items
     * @return $this
     */
    protected function reindexItems($items)
    {
        $productIds = array();

        foreach ($items as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children   = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }

        if (count($productIds)) {
            Mage::getResourceSingleton('ampgrid/indexer_qty')->reindexProducts($productIds);
        }

        return $this;
    }

    public function catalogProductPrepareSave(Varien_Event_Observer $observer) {
        if($observer->getRequest()->getModuleName() == 'ampgrid') {
            $date = $observer->getProduct()->getData('created_at');
            $observer->getProduct()->setData('created_at', strtotime($date));
        }
    }

    public function adminUserSaveAfter($observer)
    {
        $adminId = $observer->getEvent()->getObject()->getUserId();
        $columns = Mage::getModel('ampgrid/column')->getCollection();

        $currentGroup = Mage::getModel('ampgrid/group');
        $currentGroup->setData('title', 'Default');
        $currentGroup->setData('user_id', $adminId);
        $currentGroup->save();

        Mage::getConfig()->saveConfig('ampgrid/attributes/ongrid' . $adminId, $currentGroup->getId());
        Mage::getModel('core/config')->cleanCache();

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
}