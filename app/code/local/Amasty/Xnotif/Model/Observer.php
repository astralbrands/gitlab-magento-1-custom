<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */  
class Amasty_Xnotif_Model_Observer extends Mage_ProductAlert_Model_Observer
{
    /**
     * @param Mage_ProductAlert_Model_Email $email
     */
    protected function _processStock(Mage_ProductAlert_Model_Email $email)
    {
        if ($this->isEnabledQtyLimit()) {
            return;
        }

        $this->_foreachAlert('stock', $email);
    }

    /**
     * @param Mage_ProductAlert_Model_Email $email
     */
    protected function _processPrice(Mage_ProductAlert_Model_Email $email)
    {
        $this->_foreachAlert('price', $email);
    }

    public function notify()
    {
        if ($this->isEnabledQtyLimit()) {
            $email = Mage::getModel('productalert/email');
            /** @var $email Mage_ProductAlert_Model_Email */
            $this->_foreachAlert('stock', $email, true);
        }
        
        $this->dailyAdminNotification();
    }

    /**
     * @param string $type
     * @param Mage_ProductAlert_Model_Email $email
     * @param bool $enableLimit
     * @return $this
     */
    protected function _foreachAlert($type, Mage_ProductAlert_Model_Email $email, $enableLimit = false)
    {
        $email->setType($type);
        foreach ($this->_getWebsites() as $website) {
            /** @var $website Mage_Core_Model_Website */

            if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                continue;
            }

            if (!Mage::getStoreConfig(
                self::XML_PATH_STOCK_ALLOW,
                $website->getDefaultGroup()->getDefaultStore()->getId()
            )) {
                continue;
            }

            try {
                $collection = Mage::getModel('productalert/' . $type)
                    ->getCollection()
                    ->addWebsiteFilter($website->getId())
                    ->addFieldToFilter('status', 0);
                if ($enableLimit) {
                    $collection->setOrder('add_date', 'ASC');
                } else {
                    $collection->setCustomerOrder();
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                $this->_errors[] = $e->getMessage();
                return $this;
            }
            $previousCustomer = null;
            $email->setWebsite($website);

            $productJoinAlertData = array();
            foreach ($collection as $alert) {
                $storeId = $alert->getStoreId() ? $alert->getStoreId() : $website->getDefaultStore()->getId();
                try {
                    /** @var $product Mage_catalog_Model_Product */
                    $product = Mage::getModel('catalog/product')
                        ->setStoreId($storeId)
                        ->load($alert->getProductId());
                    if (!$product) {
                        continue;
                    }

                    if ($enableLimit) {
                        if (array_key_exists($alert->getProductId(), $productJoinAlertData)) {
                            if ($productJoinAlertData[$alert->getProductId()]['qty']
                                <= $productJoinAlertData[$alert->getProductId()]['counter']) {
                                continue;
                            } else {
                                $productJoinAlertData[$alert->getProductId()]['counter']++;
                            }
                        } else {
                            $productJoinAlertData[$alert->getProductId()] = array(
                                'qty' => $this->getQty($product),
                                'counter' => 1
                            );
                        }
                    }

                    if (!$previousCustomer
                        || ($previousCustomer->getId() != $alert->getCustomerId()
                        && $previousCustomer->getEmail() != $alert->getEmail())
                    ) {
                        if ($previousCustomer) {
                            $email->send();
                        }

                        $customer = $this->getAlertCustomer($alert, $website, $storeId);
                        if (!$customer) {
                            continue;
                        }
                        $previousCustomer = $customer;
                        $email->clean();
                        $email->setCustomer($customer);
                        if ($storeId == $website->getDefaultStore()->getId() && $customer->getStoreId()) {
                            $storeId = $customer->getStoreId();
                        }
                    } else {
                        $customer = $previousCustomer;
                    }

                    $product->setCustomerGroupId($customer->getGroupId());

                    /*
                     * check alert data by type
                     * */
                    if ('stock' == $type) {
                        if ($this->getIsInStock($product)) {
                            if ($alert->getParentId() && !$product->isConfigurable()) {
                                $parentProduct = Mage::getModel('catalog/product')
                                    ->setStoreId($storeId)
                                    ->load($alert->getParentId());
                                $product->setData('parent_id', $alert->getParentId());
                                $product->setData('url', $parentProduct->getProductUrl());
                            }

                            $email->addStockProduct($product);
                            $alert->setSendDate(Mage::getModel('core/date')->gmtDate());

                            $alert->setSendCount($alert->getSendCount() + 1);
                            $alert->setStatus(1);
                            $alert->save();
                        }
                    } else {
                        if ($alert->getPrice() > $product->getFinalPrice()) {
                            $productPrice = $product->getFinalPrice();
                            $product->setFinalPrice(Mage::helper('tax')->getPrice($product, $productPrice));
                            $product->setPrice(Mage::helper('tax')->getPrice($product, $product->getPrice()));
                            $email->addPriceProduct($product);

                            $alert->setPrice($productPrice);
                            $alert->setLastSendDate(Mage::getModel('core/date')->gmtDate());

                            $alert->setSendCount($alert->getSendCount() + 1);
                            $alert->setStatus(1);
                            $alert->save();
                        }
                    }

                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                    $this->_errors[] = $e->getMessage();
                }
            }

            if ($previousCustomer) {
                try {
                    $email->send();
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                    $this->_errors[] = $e->getMessage();
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve helper instance
     *
     * @return Amasty_Xnotif_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('amxnotif');
    }

    /**
     * @param $alert
     * @param $website
     * @param $storeId
     * @return false|Mage_Core_Model_Abstract
     */
    protected function getAlertCustomer($alert, $website, $storeId)
    {
        if (0 == $alert->getCustomerId()) {
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($website->getId());
            $customer->loadByEmail($alert->getEmail());

            if (!$customer->getId()) {
                $customer->setEmail($alert->getEmail());
                $customer->setStoreId($storeId);
                $customer->setFirstname(
                    Mage::getStoreConfig('amxnotif/general/customer_name', $storeId)
                );
                $customer->setGroupId(0);
                $customer->setId(0);
            }
        } else {
            $customer = Mage::getModel('customer/customer')->load($alert->getCustomerId());
        }

        return $customer;
    }

    /**
     * @return int
     */
    protected function getSettingMinQty()
    {
        $minQuantity = (int)Mage::getStoreConfig('amxnotif/general/min_qty');
        if ($minQuantity < 1) {
            $minQuantity = 1;
        }

        return $minQuantity;
    }

    /**
     * @param $product
     * @return bool
     */
    protected function getIsInStock($product)
    {
        $isInStock = false;
        $minQuantity = $this->getSettingMinQty();
        if ($product->isConfigurable() && $product->isInStock()) {
            $allProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
            foreach ($allProducts as $simpleProduct) {
                $quantity = $this->getQty($simpleProduct);
                $isInStock = $simpleProduct->getData('is_in_stock')
                    && $quantity >= $minQuantity;

                if ($isInStock) {
                    break;
                }
            }
        } else {
            $isInStock = $product->isSalable();
            if (!in_array($product->getTypeId(), array('bundle', 'grouped'))) {
                $quantity = $this->getQty($product);
                $isInStock = $isInStock && (int)$quantity >= (int)$minQuantity;
            }
        }

        return $isInStock;
    }

    /**
     * @param $product
     * @return mixed
     */
    protected function getQty($product)
    {
        if ($product->isConfigurable()) {
            $qty = 0;
            $allProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
            foreach ($allProducts as $simpleProduct) {
                $stockItem = $this->_getStockItem($simpleProduct);
                if ($stockItem->getData('qty') > 0) {
                    $qty += $stockItem->getData('qty');
                }
            }
        } else {
            $stockItem = $this->_getStockItem($product);
            $qty = $stockItem->getData('qty');
        }

        return $qty;
    }

    protected function dailyAdminNotification()
    {
        if (!Mage::getStoreConfig('amxnotif/general/notify_admin')) {
            return;
        }

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        /** @var Amasty_Xnotif_Model_Mysql4_Product_Collection $collection */
        $collection = Mage::getModel('amxnotif/product')
            ->getCollection()
            ->applyFilterForAdminNotification();

        $tableBlock = Mage::app()->getLayout()->createBlock('core/template')
            ->setCollection($collection)
            ->setTemplate('amasty/amxnotif/admin_email.phtml');
        $html = $tableBlock->toHtml();

        $currentDate = Mage::getModel('core/date')->date('Y-m-d');
        $emails = explode(',', Mage::getStoreConfig('amxnotif/general/email_to'));
        if (count($emails)) {
            $emails = array_map('trim', $emails);
        }

        $tpl = Mage::getModel('core/email_template');
        $tpl->setDesignConfig(array('area' => 'frontend'))
            ->sendTransactional(
                Mage::getStoreConfig('amxnotif/general/template'),
                'general',
                $emails,
                Mage::helper('amxnotif')->__('Administrator'),
                array(
                    'date' => $currentDate,
                    'html' => $html,
                    'name' => Mage::getStoreConfig('trans_email/ident_general/name')
                )
            );

        $translate->setTranslateInline(true);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    protected function _getStockItem($product)
    {
        return Mage::getModel('cataloginventory/stock_item')
            ->setStoreId($product->getStoreId())
            ->loadByProduct($product);
    }

    /**
     * @return bool
     */
    protected function isEnabledQtyLimit()
    {
        return (bool)Mage::getStoreConfig('amxnotif/general/email_limit');
    }
}
