<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */

require_once(Mage::getModuleDir('controllers','Mage_ProductAlert').DS.'UnsubscribeController.php');

class Amasty_Xnotif_UnsubscribeController extends Mage_ProductAlert_UnsubscribeController
{
    protected $_isGuest = false;
    public function preDispatch()
    {
        $customerEmail = $this->getRequest()->getParam('customer_email');
        $hash = $this->getRequest()->getParam('hash');
        if ($customerEmail && $hash) {
            $salt = Amasty_Xnotif_Helper_Data::SALT;
            $currentHash = md5($customerEmail . $salt);
            if ($currentHash == $hash) {
                $this->_isGuest = true;
                return Mage_Core_Controller_Front_Action::preDispatch();
            }
        }

        return parent::preDispatch();
    }

    public function stockAction()
    {
        $productId  = (int) $this->getRequest()->getParam('product');
        $customerEmail  = $this->getRequest()->getParam('customer_email');

        if (!$productId) {
            $this->_redirect('');
            return;
        }

        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        $product = Mage::getModel('catalog/product')->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            Mage::getSingleton('customer/session')->addError($this->__('The product was not found.'));
            $this->_redirect('customer/account/');
            return ;
        }

        try {
            if ($customerEmail) {
                $collection =  Mage::getModel('productalert/stock')->getCollection();
                $collection->addFieldToFilter('email', $customerEmail)
                    ->addFieldToFilter('product_id', $product->getId())
                    ->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId());

                $model = $collection->getFirstItem();
            } else {
                $model  = Mage::getModel('productalert/stock')
                    ->setProductId($product->getId())
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $model->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
                $model->loadByParam();
            }

            if ($model->getId()) {
                $model->delete();
                $session->addSuccess($this->__('You will no longer receive stock alerts for this product.'));
            } else {
                $session->addNotice($this->__('Subscription item was not found.'));
            }
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }

        if ($this->_isGuest) {
            $this->_redirect("/");
        } else {
            $this->_redirect('customer/account/');
        }
    }

    public function stockAllAction()
    {
        $session = Mage::getSingleton('customer/session');
        $customerEmail  = $this->getRequest()->getParam('customer_email');
        /* @var $session Mage_Customer_Model_Session */

        try {
            if ($customerEmail) {
                $collection =  Mage::getModel('productalert/stock')->getCollection();
                $collection->addFieldToFilter('email', $customerEmail)
                    ->addFieldToFilter('website_id', Mage::app()->getStore()->getWebsiteId());
                foreach ($collection as $item) {
                    $item->delete();
                }
            } else {
                Mage::getModel('productalert/stock')->deleteCustomer(
                    $session->getCustomerId(),
                    Mage::app()->getStore()->getWebsiteId()
                );
            }
            $session->addSuccess($this->__('You will no longer receive stock alerts.'));
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }

        if ($this->_isGuest) {
            $this->_redirect("/");
        } else {
            $this->_redirect('customer/account/');
        }
    }
}