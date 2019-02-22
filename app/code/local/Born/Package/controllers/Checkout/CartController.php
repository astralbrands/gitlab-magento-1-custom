<?php 

require_once 'Mage/Checkout/controllers/CartController.php';

class Born_Package_Checkout_CartController extends Mage_Checkout_CartController
{
 /**
     * Minicart delete action
     */
    public function ajaxDeleteAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int) $this->getRequest()->getParam('id');
        $result = array();
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                $result['subtotal'] = $this->_getCart()->getQuote()->getSubtotal();
                $result['subtotal'] = number_format($result['subtotal'], 2);

                if ($promoMesage = Mage::helper('born_package/checkout_cart')->getPromoMessage($result['subtotal'])) {
                    $result['promo-message'] = $promoMesage;
                }

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('minicart_content')->toHtml();

                $result['success'] = 1;
                $result['message'] = $this->__('Item was removed successfully.');
                Mage::dispatchEvent('ajax_cart_remove_item_success', array('id' => $id));
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not remove the item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}

?>