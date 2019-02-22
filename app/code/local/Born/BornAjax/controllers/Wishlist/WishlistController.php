<?php

require_once(Mage::getBaseDir('app').'/code/core/Mage/Wishlist/controllers/IndexController.php');

class Born_BornAjax_Wishlist_WishlistController extends Mage_Wishlist_IndexController
{
    
    protected $_loggedIn = false;

    public function preDispatch() {
        parent::preDispatch();
        // Override re routing behavior in parent
        $action = $this->getRequest()->getActionName();
        $flag = 'no-dispatch';

        if ($this->getFlag($action, $flag))
        {
          unset($this->_flags[$action][$flag]); // Remove the flag to unset it
          $this->getResponse()->clearHeader('Location'); // Remove Location header for redirect
          $this->getResponse()->setHttpResponseCode(200); // Set HTTP Response Code to OK
        }

        $this->_loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        return;
    }

    //  Adding ajax method to Mage_Wishlist_IndexController (IndexController.php)
    public function ajaxAction()
    {
        $result = array();
        
        if (!$this->_loggedIn) {
            $result['message'] = "<div class='login'><div></div><ul><li><a class='ajax-signup' href='/customer/account/create'>Sign Up</a></li><li><a class='ajax-login' href='/customer/account/login'>Login</a></li><li><a class='facebook' href='#'>Facebook Connect</a></li></ul></div>";
            $this->getResponse()->setHeader('HTTP/1.0','401',true);
            if($this->getRequest()->isXmlHttpRequest()){
                $this->getResponse()->setBody(Zend_Json::encode($result));
            }else{
                $this->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
            }
            return;
        }

        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
//            return $this->norouteAction();
            $result['message'] = "no wishlist found";
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int) $this->getRequest()->getParam('product');

        if (!$productId) {
//            $this->_redirect('*/');
            $result['message'] = "no product provided";
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
//            $this->_redirect('*/');
            $result['message'] = "cannot specify product.";
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }

        // $typeId = $product->getTypeId();
        // if ($typeId == 'configurable' && !$this->getRequest()->getPost('super_attribute')) {
        //     $result['message'] = "Please select product option.";
        //     $this->getResponse()->setBody(Zend_Json::encode($result));
        //     return;
        // }

        try {
            $requestParams = $this->getRequest()->getParams();

            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            if($buyRequest->getRemove() == 1){
                $isDeleted = $wishlist->deleteItem($product);
            }
            else{
                $result = $wishlist->addNewItem($product, $buyRequest);
            }


            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist'  => $wishlist,
                    'product'   => $product,
                    'item'      => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

//            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.', $product->getName(), Mage::helper('core')->escapeUrl($referer));
//            $session->addSuccess($message);

            if($isDeleted){
                $result['message'] = $this->__('%1$s has been removed.', $product->getName());
            }elseif(!$isDeleted){
                $result['message'] = $this->__('%1$s not found in wishlist.', $product->getName());
            }
            elseif(is_null($isDeleted)){
                $result['message'] = $this->__('<p>%1$s has been added to your wishlist.</p><p>Click <a href="%2$s">here</a> to continue shopping<br/>Click <a href="/wishlist">here</a> to view your wishlist</p>', $product->getName(), Mage::helper('core')->escapeUrl($referer));
            }
            $result['status'] = 'SUCCESS';
            if($this->getRequest()->isXmlHttpRequest()){
                $this->getResponse()->setBody(Zend_Json::encode($result));
            }else{
                $this->getResponse()->setRedirect(Mage::getUrl('wishlist'));
            }
            
        }
        catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }

//        $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
        return;

    }

    /* *
      * Chen L
      * Adding retrieve method to Mage_Wishlist_IndexController (IndexController.php) to get the list of wishlist items
    */
    public function retrieveAction() {
        $result = array();

        if (!$this->_loggedIn) {
            $result['message'] = "<div class='login'>" .
                                    "<div></div>" .
                                    "<ul>" .
                                        "<li><a class='ajax-signup' href='/customer/account/create'>Sign Up</a></li>" .
                                        "<li><a class='ajax-login' href='/customer/account/login'>Login</a></li>" .
                                        "<li>". Mage::app()->getLayout()->createBlock('inchoo_socialconnect/facebook_button')->toHtml() ."</li>" .
                                    "</ul>" .
                                "</div>";
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }
//to see if we can get the wishlist
        $wishlist = $this->_getWishlist();//wishlist object too complicated for FE

        if (!$wishlist) {
            $result['message'] = "no wishlist found";
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }else{
            $result['message'] = "success";
            $items = array(); 
            $customer = Mage::getSingleton('customer/session')->getCustomer();
             if($customer->getId())
            {
                 $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer, true);
                 $wishListItemCollection = $wishlist->getItemCollection();
                 foreach ($wishListItemCollection as $wishitem)
                 {
                       $product = Mage::getModel('catalog/product')->load($wishitem->getProductId());
                        if ($product->getId()){

                            $imgSrc = Mage::helper('catalog/image')->init($product, 'small_image')->resize(200, 200);
                            //echo $imgSrc; 
                        }
                        // put the info into an array!
                        $itemData = array(
                              'id' => $wishitem->getId(),
                              'name' => $wishitem->getName(),
                              //'qty' => $wishitem->getQty(),
                              'img' => (string)$imgSrc,
                              'price' => $wishitem->getPrice(),
                              'url' => $wishitem->getProductUrl()
                            );

                        array_push($items, $itemData);
                } 
             }
            //print_r($wishlist); 
            $result['items'] = $items;    
            $this->getResponse()->setBody(Zend_Json::encode($result));
            // print_r(Zend_Json::encode($result)); 
            return;
        }

    } 

}