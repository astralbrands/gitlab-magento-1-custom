<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/4/13
 * Time   : 11:29 AM
 * File   : Formpost.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Block_Checkout_Formpost extends Mage_Core_Block_Template
{
    protected function _getApi()
    {
        return Mage::getModel('ebizmarts_sagepaymentspro/api_sage');
    }
    protected  function _getSidParam()
    {
        $coreSession =  Mage::getSingleton('core/session');
        $sessionIdQueryString = $coreSession->getSessionIdQueryParam() . '='. $coreSession->getSessionId();

        return $sessionIdQueryString;
    }
    protected function _toHtml()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['token'])) {
            $token = $params['token'];
        } else {
            $token = 0;
        }
        $callbackUrl = Mage::getModel('core/url')->getUrl('sgusa/server/callback'). '?' . $this->_getSidParam();

        $api = $this->_getApi();
        $data = $api->getCheckoutUrl($token, $callbackUrl);
        $order = Mage::getModel('sales/order')->loadByIncrementId(
            Mage::getSingleton('checkout/session')
            ->getLastRealOrderId()
        );
        $order->setStatus('sagepayments_pending');
        $order->save();

        $postUrl = Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_URL_SEVD);
        $form = new Varien_Data_Form;
        $form->setAction($postUrl)
            ->setId('sagepaymentsform')
            ->setName('sagepaymentsform')
            ->setMethod('POST')
            ->setUseContainer(true);
        $url = Mage::getModel('core/url')->getUrl('sgusa/server/return'). '?' . $this->_getSidParam();
        $form->addField('request', 'hidden', array('name'=>'request', 'value' => $data));
        $form->addField('redirect_url', 'hidden', array('name'=>'redirect_url', 'value' => $url));
        $form->addField('consumer_initiated', 'hidden', array('name'=>'consumer_initiated', 'value' => 'true'));

        $html = '<html><head><title>SagePayments FORM</title></head><body>';
        $html.= '<code>' . $this->__('Redirecting to SagePayments site...') .'</code>';
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("sagepaymentsform").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}