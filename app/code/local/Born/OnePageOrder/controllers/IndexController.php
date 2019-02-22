<?php
class Born_OnePageOrder_IndexController extends Mage_Core_Controller_Front_Action
{
    public function saveAction()
    {
        $params = $this->getRequest()->getParams();


        $backUrl = Mage::getUrl('checkout/cart/index');


        $buyRequests = $this->_prepareCartItems($params);

        $cart = Mage::getSingleton('checkout/cart');
        try{
            $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
            $processed = 0;
            $countItemsAddedToCart = 0;

        foreach($buyRequests as $_buyRequest){
            
            if (isset($_buyRequest['qty'])) {
                $_buyRequest['qty'] = $filter->filter($_buyRequest['qty']);
            }
            if($_buyRequest['qty'] <= 0){
                continue;
            }

            $countItemsAddedToCart += $_buyRequest['qty'];

            $product = $this->_initProduct($_buyRequest['product']);
            $cart->addProduct($product, $_buyRequest);

            $this->_getSession()->setCartWasUpdated(true);
            $processed++;
        }
        if (!$cart->getQuote()->getHasError() && $processed > 0) {
                $message = $this->__($countItemsAddedToCart . ' item(s) have been added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                $this->_getSession()->addSuccess($message);
            }
        $cart->save();

        if ($countItemsAddedToCart == 0) {
            $message = $this->__('No items have been added to cart, please select items first before adding to cart.');

            $this->_getSession()->addError($message);
        }
        }catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
        }

        if ($params['action'] == 'add') {
            $this->_redirectReferer();
        }else{
            $this->getResponse()->setRedirect($backUrl);
        }

    }    
    
   protected function _prepareCartItems($requestParams = array())
    {
        $buyrequest = array();
        $watchArray = array();
        if(is_array($requestParams) && count($requestParams) > 0)
        {
            foreach($requestParams as $key=>$value)
            {
                if($key == 'super_attribute')
                {
                    foreach($value as $configurableProductId => $child)
                    {
                        foreach($child as $sizeAttributeId => $selectedProducts){
                            $i = count($buyrequest) + 1;
                            foreach($selectedProducts as $optionId => $qty){
                                $buyrequest[$i]['product'] = $configurableProductId;
                                $buyrequest[$i]['qty'] = $qty;
                                $buyrequest[$i]['super_attribute'][$sizeAttributeId] = $optionId;
                                $i++;
                            }
                        }
                        $i++;
                    }
                }elseif($key == 'bundle_option'){
                    $i=count($buyrequest) + 1;
                    foreach($value as $bundleProductId => $child){
                        $buyrequest[$i]['product'] = $bundleProductId;
                        $buyrequest[$i]['bundle_option'] = array();

                        $bundleContainsItems = false;
                        $totalBundleQty = null;
                        $defaultBundleItemQty = 1;

                        foreach($child as $optionId => $selections){

                            foreach($selections as $selectionId => $qty){
                                if(is_null($totalBundleQty) && $qty)
                                {
                                    $totalBundleQty = $qty;
                                }
                                $buyrequest[$i]['bundle_option'][$optionId] = $selectionId;
                                $buyrequest[$i]['bundle_option_qty'][$optionId] = $defaultBundleItemQty;

                                if($qty > 0){
                                    $bundleContainsItems = true;
                                }
                            }
                        }

                        if($bundleContainsItems && $totalBundleQty){
                            $buyrequest[$i]['qty'] = $totalBundleQty;
                        }
                        $i++;
                    }

                }elseif($key == 'simple'){
                    $i=count($buyrequest) + 1;
                    foreach($value as $productId => $qty){
                        $buyrequest[$i]['product'] = $productId;
                        $buyrequest[$i]['qty'] = $qty;
                        $i++;
                    }
                    
                }
            }
        }
        return $buyrequest;
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    protected function _initProduct($productId = null)
    {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }
	public function ajaxSubscribeAction()
	{
		
		$session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */
        $productId  = (int) $this->getRequest()->getParam('product_id');
        $guestEmail  = $this->getRequest()->getParam('email');
         

        if (!$product = Mage::getModel('catalog/product')->load($productId)) {
            /* @var $product Mage_Catalog_Model_Product */
            $this->getResponse()->setBody($this->__('Not enough parameters.'));
            return ;
        }

        try {          
            $model = Mage::getModel('productalert/stock')
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
           
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
                $this->getResponse()->setBody($this->__('Thank you! You are already subscribed to this product.'));
				return;
             }
            else{
                $model->save();
                $this->getResponse()->setBody($this->__('Alert subscription has been saved.'));
				return;
            }
        }
        catch (Exception $e) {
			echo $e->getMessage();
			exit;
            $this->getResponse()->setBody($this->__('Unable to update the alert subscription.'));
			return;
        }
	}
}

