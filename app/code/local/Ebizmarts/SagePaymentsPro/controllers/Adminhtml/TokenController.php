<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/9/13
 * Time   : 11:00 AM
 * File   : TokenController.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Adminhtml_TokenController extends Mage_Adminhtml_Controller_Action
{

    public function newAction()
    {
        $this->loadLayout();

        Mage::register('admin_tokenregister', $this->getRequest()->getParam('customer_id'));

        $result = Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->registerCard();
        if (!isset($result['NextURL'])) {
            $this->_getSession()->addError($this->__('Could not register token, please try again.'));
            $this->_redirectReferer();
            return;
        }

        $this->getResponse()->setBody(
            '<iframe style="width:100%;height:100%;padding:0;margin:0;border:none;" src="'.
                $result['NextURL'].'"></iframe>'
        );
    }
    public function addAction()
    {
        if ($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup_sagepay');
        }
        $this->_title($this->__('Customer'))
            ->_title($this->__('Token'))
            ->_title($this->__('New token'));

        $this->_customerid = $this->getRequest()->getParam('id');
        $this->getLayout()->setBlock('', 'ebizmarts_sagepaymentspro/adminhtml_token_add');
        $this->renderLayout();
    }

    public function saveaddAction()
    {

        if ($this->getRequest()->isPost()) { #DIRECT POST
            $post = $this->getRequest()->getPost('token');


            $post['ExpiryDate'] = str_pad($post['ExpiryMonth'], 2, '0', STR_PAD_LEFT) . substr($post['ExpiryYear'], 2);

            $rs = (array) Mage::getModel('ebizmarts_sagepaymentspro/sageMethod')->registerCard($post)->getData();

            if (empty($rs)) {
                $rs['Status'] = 'ERROR';
                $rs['StatusDetail'] = Mage::helper('ebizmarts_sagepaymentspro')
                    ->__('A server to server communication error occurred, please try again later.');
            }
            $cards = Mage::getModel('ebizmarts_sagepaymentspro/config')->getCcTypesSagePayments();
            if ($rs['result'] == 'SUCCESS') {
                $save = Mage::getModel('ebizmarts_sagepaymentspro/tokencard')
                    ->setToken($rs['guid'])
                    ->setStatus($rs['result'])
                    ->setCardType($cards[$post['CardType']])
                    ->setExpiryDate($post['ExpiryDate'])
                    ->setCustomerId($post['customer_id'])
                    ->setLastFour(substr($post['CardNumber'], -4))
                    ->setVendor(Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_MID))
                    ->save();
                return $this->getResponse()->setBody(
                    '<html>
                                                        <body>
                                                            <script type="text/javascript">
                                                                    window.parent.Control.Modal.close();
                                                            </script>
                                                        </body>
                                                    </html>'
                );
            } else {
                return $this->getResponse()->setBody(
                    '<html>
                                                        <body>
                                                            <script type="text/javascript">
                                                                    alert("'.$rs['result'].'");
                                                                    window.parent.Control.Modal.close();
                                                            </script>
                                                        </body>
                                                    </html>'
                );
            }


        }
    }
    public function massDeleteAction()
    {
        if ($this->getRequest()->isPost()) { #Mass action
            $ok = $nok = 0;
            $ids = $this->getRequest()->getPost('cards', array());
            foreach ($ids as $cardId) {
                $card = Mage::getModel('ebizmarts_sagepaymentspro/tokencard')->load($cardId);
            }
        }
        $this->_redirectReferer();
    }
    protected function _getCustomerId()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            return $customerData->getId();
        }
    }
    protected function _isAllowed()
    {
        return true;
    }
}