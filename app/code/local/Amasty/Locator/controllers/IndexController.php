<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (Mage::getStoreConfig('amlocator/locator/use') != 1) {
            $this->_forward('noRoute');
        } else {
            $this->loadLayout(
                array('default',
                      Mage::getStoreConfig('amlocator/locator/resp') == 1
                          ? 'amlocator_index_index_resp'
                          : 'amlocator_index_index_noresp')
            );
            $this->getLayout()->getBlock('head')->setTitle(Mage::getStoreConfig('amlocator/locator/title'));
            $this->renderLayout();
        }
    }

    public function ajaxAction()
    {
        $locationCollection = Mage::getModel('amlocator/location')
            ->getCollection();
        $locationCollection->applyDefaultFilters();

        $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam("product"));

        $locationCollection->addProductCategoryFilter(
            $this->getRequest()->getParam("product"),
            $product->getCategoryIds()
        );

        $locationCollection->load();

        $this->loadLayout();
        $left = $this->getLayout()->getBlock('root')->toHtml();

        $res = array_merge_recursive(
            $locationCollection->toArray(), array('block' => $left)
        );

        $json = json_encode($res);
        $this->getResponse()->setBody($json);
    }

}