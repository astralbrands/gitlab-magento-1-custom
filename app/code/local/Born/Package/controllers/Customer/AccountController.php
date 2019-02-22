<?php 

require_once 'Mage/Customer/controllers/AccountController.php';

class Born_Package_Customer_AccountController extends Mage_Customer_AccountController
{
  /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
    	$_brandName = Mage::getStoreConfig('customer/create_account/brand_name', Mage::app()->getStore()->getStoreId());

    	$_successStoreName = $_brandName ? $_brandName : Mage::app()->getStore()->getFrontendName();

        $this->_getSession()->addSuccess(
            $this->__('Thank you for registering with %s.', $_successStoreName)
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType =  $this->_getHelper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        $successUrl = $this->_getUrl('*/*/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }

        //Disable registration page on B2B site
        if (Mage::app()->getWebsite()->getCode() == 'cosb2b') { 
            $_storeId = Mage::app()->getStore()->getStoreId();
            $_professoinalLoginUrlKey = Mage::getStoreConfig('customer/startup/professional_signup_url_key',$_storeId);
            $this->_redirect($_professoinalLoginUrlKey);
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
}

?>