<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
class Amasty_Xnotif_Adminhtml_AmstockController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('report/amxnotif_stock');
        if (!Mage::helper('ambase')->isVersionLessThan(1, 4)) {
            $this
                ->_title($this->__('Reports'))
                ->_title($this->__('Alerts'))
                ->_title($this->__('Stock Alerts'));
        }
        $this->_addBreadcrumb($this->__('Alerts'), $this->__('Stock Alerts'));
        $this->_addContent($this->getLayout()->createBlock('amxnotif/adminhtml_stock'));
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $alertId = (int)$this->getRequest()->getParam('alert_stock_id');

        if (!$alertId) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__('An error occurred while deleting the item from Subscriptions.')
            );
        } else {
            $alert = Mage::getModel('productalert/stock')->load($alertId);
            if ($alert && $alert->getId()) {
                try {
                    $text = $alert->getEmail();
                    if (!$text) {
                        $customer = Mage::getModel('customer/customer')->load($alert->getCustomerId());
                        if ($customer) {
                            $text = 'Customer #' . $alert->getCustomerId();
                        }
                    }
                    $alert->delete();
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('%s has been unsubscribed.', $text)
                    );
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        $this->__('An error occurred while deleting the item from Subscriptions.')
                    );
                }
            }
        }

        $this->_redirectReferer();
    }

    public function addByEmailAction()
    {
        $session = Mage::getSingleton('adminhtml/session');

        $productId = $this->getRequest()->getParam('product_id');
        if (!$productId) {
            $session->addError($this->__('Invalid product ID.'));
            $this->_redirectReferer();
            return;
        }

        $email = trim($this->getRequest()->getParam('subscription_email'));
        if (!Zend_Validate::is($email, 'EmailAddress')) {
            $session->addError(
                $this->__('Please provide a valid email.')
            );
            $this->_redirectReferer();
            return;
        }


        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product) {
            $session->addError(
                $this->__('Product with ID #%d doesn not exist.', $productId)
            );
            $this->_redirectReferer();
            return;
        }

        $storeId = $this->getRequest()->getParam('store_id');
        if (!$storeId) {
            //get store and website from product
            $productWebsiteIds = $product->getWebsiteIds();
            if (!$productWebsiteIds) {
                $session->addError(
                    $this->__(
                        'Website ID is not found. Please make sure the product ID %d has a website associated',
                        $productId
                    )
                );
                $this->_redirectReferer();
                return;
            }
            $websiteId = $productWebsiteIds[0];
            $storeId = Mage::getModel('core/website')
                ->load($websiteId)
                ->getDefaultGroup()
                ->getDefaultStoreId();
        } else {
            $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        }

        $customerCollection = Mage::getModel("customer/customer")
            ->getCollection()
            ->addAttributeToFilter('email', $email)
            ->addAttributeToFilter('website_id', $websiteId)
            ->setCurPage(1)
            ->setPageSize(1);
        try {
            //we assume that a cases of guest subscribtion negate the benefits of $collection->load()
            if ($customerCollection->getSize()) {
                $customer = $customerCollection->getFirstItem();
                $this->_subscribeCustomer($customer, $productId);
            } else {
                //subscribe guest
                $alertCollection = Mage::getModel('productalert/stock')
                    ->getCollection()
                    ->addFieldToFilter('website_id', $websiteId)
                    ->addFieldToFilter('status', 0)
                    ->addFieldToFilter('email', $email);
                if ($alertCollection->getSize()) {
                    $session->addNotice($this->__('%s is already subscribed.', $email));
                } else {
                    $alert = Mage::getModel('productalert/stock');
                    $alert->setData(
                        array(
                            'product_id' => $productId,
                            'website_id' => $websiteId,
                            'email' => $email,
                            'store_id' => $storeId,
                        )
                    );
                    $alert->save();
                    $session->addSuccess(
                        $this->__('%s has been subscribed.', $email)
                    );
                }
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError(
                $this->__('An error occurred while creating subscription item: %s', $e->getMessage())
            );
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(
                $this->__('An error occurred while creating subscription item.')
            );
        }
        $this->_redirectReferer();
    }

    protected function _subscribeCustomer($customer, $productId)
    {
        $websiteId = $customer->getWebsiteId();
        $alert = Mage::getModel('productalert/stock');
        $alert->setData(
            array(
                'customer_id' => $customer->getId(),
                'product_id' => $productId,
                'website_id' => $websiteId,
            )
        );
        $alert->save();
        $id = $customer->getId();
        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('Customer #%d has been subscribed.', $id)
        );
        return $this;
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_alerts_stock')->toHtml()
        );
    }

    /**
     * Export alerts report grid to CSV format
     */
    public function exportAlertsCsvAction()
    {
        $fileName = 'alerts.csv';
        $grid = $this->getLayout()->createBlock('amxnotif/adminhtml_report_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export alerts report grid to Excel XML format
     */
    public function exportAlertsXmlAction()
    {
        $fileName = 'alerts.xml';
        $grid = $this->getLayout()->createBlock('amxnotif/adminhtml_report_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     * Report action init operation
     */
    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new Varien_Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/amxnotif_stock');
    }
}