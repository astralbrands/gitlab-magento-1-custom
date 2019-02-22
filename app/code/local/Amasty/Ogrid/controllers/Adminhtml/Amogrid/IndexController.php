<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */
class Amasty_Ogrid_Adminhtml_Amogrid_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function viewAction()
    {
        $order_id = $this->getRequest()->getParam("order_id");
        
        $block = $this->getLayout()->createBlock('amogrid/adminhtml_order_view');
        
        $block->setData('order_id', $order_id);

        $this->getResponse()->setBody($block->toHtml());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/ogrid_adminsetting');
    }
}