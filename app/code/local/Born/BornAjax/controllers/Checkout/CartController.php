<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class Born_BornAjax_Checkout_CartController extends Mage_Checkout_CartController
{
	const CONFIGURABLE_PRODUCT_IMAGE= 'checkout/cart/configurable_product_image';
	const USE_PARENT_IMAGE          = 'parent';	
	
	public function getCartQty($cart = null)
	{
		  $cart = (isset($cart) ? $cart : $this->_getCart());
		  $cartData = $cart->getQuote()->getData();
		  if ($cartData) {
		    $cartQty = $cartData['items_qty'];
		    $cartQty = intval($cartQty);
		  } else {
		    $cartQty = 0;
		  }
		  return $cartQty;
	}
  
  public function getChildProduct($item)
  {
      if ($option = $item->getOptionByCode('simple_product')) {
      	$product = $option->getProduct();
      	if(is_object($product) && $product->getId()){
      		$product = Mage::getModel('catalog/product')->load($product->getId());
      	}
          return $product;
      }
      return $item->getProduct();
  }  

  public function getProductImage($item)
  {
      $product = $this->getChildProduct($item);
      
      if (  !$product ||
            !$product->getData('thumbnail') ||
            ($product->getData('thumbnail') == 'no_selection') ||
            (Mage::getStoreConfig(self::CONFIGURABLE_PRODUCT_IMAGE) == self::USE_PARENT_IMAGE)  ) {
          $product = $item->getProduct();
      }

      // return Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getThumbnail() );
      return Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(400)-> __toString();
  }


  public function buildResponse($currentProduct, $params)
  {
		$response = array();
		$cart   = $this->_getCart();
		$quote = $cart->getQuote();
		$allVisibleItems = $quote->getAllVisibleItems();
		$items = array();
		
		if (!$allVisibleItems && count($allVisibleItems) == 0) {
			$response['cart_qty'] = 0;
			$response['items'] = $items;
			$response['status'] = "SUCCESS";
			$response['message'] ="The item has been successfully added to your cart.";			
			return $response;
		}
		
		foreach($allVisibleItems as $item)
    {
			$product = $item->getProduct();
			$visibility = $product->getVisibility();
			if ($visibility != 1 && $visibility) {
				$itemData = array(
						'id' => $item->getId(),
						'sku' => $item->getSku(),
						'name' => $item->getName(),
						'qty' => $item->getQty(),
						'img' => $this->getProductImage($item),
            'price' => $item->getPrice(),
            'original_price' => $product->getPrice() ? $product->getPrice() : $item->getPrice(),
						'url' => $item->getProductUrl(),
            'remove' => Mage::getUrl('bornajax/checkout_cart/delete',
            array('id'=>$item->getId(), Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core/url')->getEncodedUrl())),
            'edit_link' => Mage::getUrl('checkout/cart/configure', array('id'=>$item->getId()))
				);

				if ($product->getTypeId() == "configurable") {
					$helper = Mage::helper('catalog/product_configuration');
	        		$options = $helper->getConfigurableOptions($item);
	        		$itemData['options'] = $options;
				}
        array_push($items, $itemData);
		  }
		}
		$response['items'] = array_reverse($items);    
	  
	  //currency
		$currentStore = Mage::app()->getStore();
		$locale = Mage::app()->getLocale();
		$symbol = $locale->currency($currentStore->getCurrentCurrencyCode())->getSymbol();
		$response['cart_qty'] = $cart->getItemsQty();
		$response['cart_subtotal'] = $cart->getQuote()->getGrandTotal();
		$response['currency'] = $symbol;
		$response['status'] = "SUCCESS";
    if (isset($currentProduct)) {
  		$response['message'] = $this->__('<span class="item-name">%s</span> was added to your shopping cart.', Mage::helper('core')->escapeHtml($currentProduct->getName()));
    }
		$response['checkout_link'] = Mage::helper('checkout/url')->getCheckoutUrl();
		$response['cart_link'] = Mage::helper('checkout/url')->getCartUrl();

	  	return $response;
  }
  
  public function addallAction() {
    $products = $this->getRequest()->getParam('products');
  
    foreach ($products as $params) {
      $cart = Mage::getModel('checkout/cart');
      $cart->init();
      $params['isAjax'] = 1;
      $response = $this->addAction($cart, $params);
    }

    #$response['qty'] = $this->getCartQty($cart);
    $response = $this->buildResponse();
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    return;
  }
  
  public function addAction($cart = null, $params = null){
    $cart = (isset($cart) ? $cart : $this->_getCart());
    $params = (isset($params) ? $params : $this->getRequest()->getParams());
    	try{
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            /**
             * Check product availability
             */
            if (!$product) {
                throw new Exception( $this->__('Cannot add the item to shopping cart.') );
            }
            /** for custom tag product */
            if (isset($params['selected_product'])) {
                $spParams = json_decode($params['selected_product']);
                $spParams = (array)$spParams;
                $productId = $spParams['product'];
                if ($productId) {
                    $selectedProduct = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($productId);
                }
                $spData = array();
                $spData['form_key'] = $params['form_key'];
                $spData['product'] = $productId;
                $spData['qty'] = ''.$spParams['qty'].'';
                $spData['related_product'] = '';
                foreach ((array)$spParams['super_attribute'] as $key => $value)
                    $spData['super_attribute'][$key] = $value;
                $spData['productTagName'] = '';
                $cart->addProduct($selectedProduct, $spData);
            }
            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);
            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
			$response = $this->buildResponse($product, $params);
        } catch (Mage_Core_Exception $e) {
        	$response['status'] = 'ERROR';
          $response['message'] = Mage::helper('catalog')->__(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
        	$response['status']= 'ERROR';
            $response['message']= $this->__('Cannot add the item to shopping cart.');
            Mage::logException($e);
        }
        $this->loadLayout();
    $response['cart_qty'] = $cart->getItemsQty();
    $response['result_html'] = $this->getLayout()->getBlock('minicart_content')->toHtml();
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
      return $response;

  }

  public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
                $response = $this->buildResponse();
                $response['success'] = true;
                $response['message'] = $this->__('Success!');        
            } catch (Exception $e) {
                $response['success'] = false;
                $response['message'] = $this->__('Cannot remove the item.');
                Mage::logException($e);
            }
        } else {
          $response['success'] = false;
          $response['message'] = $this->__('No ID provided.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return $response;
        //$this->_redirectReferer(Mage::getUrl('*/*'));
    }
}