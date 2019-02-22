<?php 

require_once 'Amasty/Xnotif/controllers/EmailController.php';

class Born_Package_Xnotif_EmailController extends Amasty_Xnotif_EmailController
{

    public function stockAction()
    {
        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        $backUrl    = $this->getRequest()->getParam(Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED);
        $productId  = (int) $this->getRequest()->getParam('product_id');
        $guestEmail  = $this->getRequest()->getParam('guest_email');
        $parentId  = (int) $this->getRequest()->getParam('parent_id');

        if (!$backUrl) {
            $this->_redirect('/');
            return ;
        }

        if (!$product = Mage::getModel('catalog/product')->load($productId)) {
            /* @var $product Mage_Catalog_Model_Product */
            $session->addError($this->__('Not enough parameters.'));
            $this->_redirectUrl($backUrl);
            return ;
        }

        try {          
            $productName = $product->getName();

            $model = Mage::getModel('productalert/stock')
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
           
            if ($parentId){
                 $model->setParentId($parentId);
            }
            $collection = Mage::getModel('productalert/stock')
                    ->getCollection()
                    ->addWebsiteFilter(Mage::app()->getWebsite()->getId())
                    ->addFieldToFilter('product_id', $productId)
                    ->addStatusFilter(0)
                    ->setCustomerOrder();

            if($guestEmail) {
                if (!Zend_Validate::is($guestEmail, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }
                $customer = Mage::getModel('customer/customer') ;
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($guestEmail);
            
                if(!$customer->getId()){         
                    $model->setEmail($guestEmail);
                    $model->setStoreId(Mage::app()->getStore()->getId());
                    $collection->addFieldToFilter('email', $guestEmail);
                }
                else{
                    $model->setCustomerId($customer->getId());
                    $collection->addFieldToFilter('customer_id', $customer->getId());
                }
            }
            else {
                $model->setCustomerId(Mage::getSingleton('customer/session')->getId());
                $collection->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getId());
            }
        
            
            if($collection->getSize() > 0) {
                if ($productName) {
                    $session->addSuccess($this->__('Thank you! You are already subscribed to '. $productName .'.'));
                }
                else{
                    $session->addSuccess($this->__('Thank you! You are already subscribed to this product.'));
                }
                
             }
            else{
                $model->save();

                if ($productName) {
                    $session->addSuccess($this->__('You are now subscribed to ' .$productName. '.'));
                }
                else{
                    $session->addSuccess($this->__('Alert subscription has been saved.'));
                }
            }
        }
        catch (Exception $e) {
            $session->addException($e, $this->__('Unable to update the alert subscription.'));
        }
        
        $backUrlDecoded = Mage::helper('core')->urlDecodeAndEscape($backUrl);
        if(strpos($backUrlDecoded, 'quickviewblock'))
        {
            $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
            $this->getResponse()->setRedirect($refererUrl);
        }
        else{
            $this->_redirectReferer();
        }
    }

 }

 ?>