<?php
class Born_Package_Model_Customer_Observer
{
    public function beforeBlockToHtml(Varien_Event_Observer $observer)
    {
        $grid = $observer->getBlock();

        /**
         * Mage_Adminhtml_Block_Customer_Grid
         */
        if ($grid instanceof Mage_Adminhtml_Block_Customer_Grid) {
            $grid->addColumnAfter(
                'increment_id',
                array(
                    'header' => Mage::helper('born_package')->__('Increment ID'),
                    'index'  => 'increment_id'
                ),
                'entity_id'
            );
        }
    }

    public function checkIfAccountDisabled(Varien_Event_Observer $observer)
    {

        $customerModel = $observer->getEvent()->getModel();
        if ($customerModel && $customerModel->getData('account_disable')) {
            throw Mage::exception('Mage_Core', Mage::helper('born_package')->__('This account is disabled.')
            );
        }

        return true;
    }

}