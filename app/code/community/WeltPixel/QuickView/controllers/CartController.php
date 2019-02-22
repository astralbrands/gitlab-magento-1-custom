<?php

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';

class WeltPixel_QuickView_CartController extends Mage_Checkout_CartController {

    public function updateItemOptionsAction() {

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return parent::updateItemOptionsAction();
        }

        $message = '';
        $cart = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                                array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                //Mage::throwException($this->__('Quote item is not found.'));
                return;
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                //Mage::throwException($item);
                return;
            }
            if ($item->getHasError()) {
                //Mage::throwException($item->getMessage());
                return;
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete', array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$cart->getQuote()->getHasError()) {
                $message = $this->__('<span><strong>%s</strong> was added to your shopping cart.</span><br /><p><a class="simple-button" href="%s">Continue Shopping</a><span> or </span><a class="button" href="%s">Checkout</a></p>', Mage::helper('core')->escapeHtml($item->getProduct()->getName()), 'javascript:weltpixel.lightbox.close()', Mage::helper('checkout/url')->getCheckoutUrl()
                );
            }
        } catch (Mage_Core_Exception $e) {
            $message = Mage::helper('core')->escapeHtml($e->getMessage());
        } catch (Exception $e) {
            $message = $this->__('Cannot update the item.');
            Mage::logException($e);
        }


        $this->loadLayout();
        $body = array(
            'message' => $message,
            'blocks' => array()
        );
        if ($this->getLayout()->getBlock('cart_sidebar')) {
            $body['blocks']['cart_sidebar'] = array(
                'class' => Mage::helper('weltpixel_quickview')->isMageEnterprise() ? 'top-cart' : 'block-cart',
                'content' => preg_replace('/\/uenc\/[^\/]*/', '', $this->getLayout()->getBlock('cart_sidebar')->toHtml()),
            );
        }
        if ($this->getLayout()->getBlock('quick_access')) {
            $body['blocks']['quick_access'] = array(
                'id' => 'quick-access',
                'content' => preg_replace('/\/uenc\/[^\/]*/', '', $this->getLayout()->getBlock('quick_access')->toHtml()),
            );
        }

        $this->getResponse()
                ->setHeader('Content-Type', 'application/json', true)
                ->setBody(Mage::helper('core')->jsonEncode($body));
    }

}
