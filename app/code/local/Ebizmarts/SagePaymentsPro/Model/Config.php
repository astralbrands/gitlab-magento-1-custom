<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/22/13
 * Time   : 4:00 PM
 * File   : Config.php
 * Module : Ebizmarts_SagePaymentsPro
 */
class Ebizmarts_SagePaymentsPro_Model_Config extends Mage_Payment_Model_Config
{
    const INTEGRATION_DIRECT        = 1;
    const INTEGRATION_SERVER        = 2;
    const SERVER_REDIRECT           = 1;
    const SERVER_IFRAME             = 2;

    const DIRECT_ENABLE             = 'payment/sagepaymentsprodirect/active';
    const DIRECT_TITLE              = 'payment/sagepaymentsprodirect/title';
    const DIRECT_MIN_ORDER_TOTAL    = 'payment/sagepaymentsprodirect/min_order_total';
    const DIRECT_MAX_ORDER_TOTAL    = 'payment/sagepaymentsprodirect/max_order_total';
    const DIRECT_ALLOWSPACIFIC      = 'payment/sagepaymentsprodirect/allowspecific';
    const DIRECT_SPECIFICCOUNTRY    = 'payment/sagepaymentsprodirect/specificcountry';
    const DIRECT_CCTYPES            = 'payment/sagepaymentsprodirect/cctypes';
    const DIRECT_PAYMENT_ACTION     = 'payment/sagepaymentsprodirect/payment_action';

    const SERVER_ENABLE             = 'payment/sagepaymentsproserver/active';
    const SERVER_TITLE              = 'payment/sagepaymentsproserver/title';
    const SERVER_MIN_ORDER_TOTAL    = 'payment/sagepaymentsproserver/min_order_total';
    const SERVER_MAX_ORDER_TOTAL    = 'payment/sagepaymentsproserver/max_order_total';
    const SERVER_ALLOWSPACIFIC      = 'payment/sagepaymentsproserver/allowspecific';
    const SERVER_SPECIFICCOUNTRY    = 'payment/sagepaymentsproserver/specificcountry';

    const CONFIG_ENABLE             = 'payment/sagepaymentspro/active';
    const CONFIG_M_ID               = 'payment/sagepaymentspro/m_id';
    const CONFIG_MKEY               = 'payment/sagepaymentspro/m_key';
    const CONFIG_APPID_CE           = 'payment/sagepaymentspro/ApplicationIDCE';
    const CONFIG_APPID_EE           = 'payment/sagepaymentspro/ApplicationIDEE';
    const CONFIG_LANID              = 'payment/sagepaymentspro/LanguageID';
    const CONFIG_LOG                = 'payment/sagepaymentspro/log';
    const CONFIG_TOKEN_MAX          = 'payment/sagepaymentspro/max_token_card';
    const CONFIG_TOKEN              = 'payment/sagepaymentspro/token_integration';
    const CONFIG_INTEGRATION        = 'payment/sagepaymentspro/type_integration';
    const CONFIG_TITLE              = 'payment/sagepaymentspro/title';
    const CONFIG_MIN_ORDER_TOTAL    = 'payment/sagepaymentspro/min_order_total';
    const CONFIG_MAX_ORDER_TOTAL    = 'payment/sagepaymentspro/max_order_total';
    const CONFIG_ALLOWSPACIFIC      = 'payment/sagepaymentspro/allowspecific';
    const CONFIG_SPECIFICCOUNTRY    = 'payment/sagepaymentspro/specificcountry';
    const CONFIG_CCTYPES            = 'payment/sagepaymentspro/cctypes';
    const CONFIG_URL_ENVELOPE       = 'payment/sagepaymentspro/envelopeurl';
    const CONFIG_URL_OPENENVELOPE   = 'payment/sagepaymentspro/openenvelopeurl';
    const CONFIG_URL_SEVD           = 'payment/sagepaymentspro/sevdurl';
    const CONFIG_URL_RESTFULL       = 'payment/sagepaymentspro/restfulurl';
    const CONFIG_DIRECT_URL         = 'payment/sagepaymentspro/directurl';
    const CONFIG_PAYMENT_ACTION     = 'payment/sagepaymentspro/payment_action';
    const CONFIG_TOKEN_URL          = 'payment/sagepaymentspro/token_url';
    const CONFIG_LICENSE            = 'payment/sagepaymentspro/license_key';
    const CONFIG_ERROR_MSG          = 'payment/sagepaymentspro/errormsg';

    const CUSTOM_THEME              = 'payment/sagepaymentspro/custom_theme';
    const CUSTOM_THEME_DATA         = 'payment/sagepaymentspro/custom_theme_data';

    const REQUEST_TYPE_PAYMENT          = 'PAYMENT';
    const REQUEST_TYPE_AUTHORIZE        = 'AHUTHORIZE';
    const CODE_PAYMENT                  = '01';
    const CODE_AUTHORIZE                = '02';
    const CODE_RELEASE                  = '03';
    const CODE_CAPTURE                  = '11';
    const CODE_VOID                     = '04';
    const CODE_REFUND                   = '06';
    const CODE_PRIOR_AUTH_SALE          = '05';
    const RESPONSE_CODE_APPROVED        = 'A';
    const RESPONSE_CODE_REJECTED        = 'X';
    const RESPONSE_CODE_NOTAUTHED       = 'E';
    const STATUS_OK                     = 'OK';
    const TRANSACTION_TYPE_CAPTURE      = 'capture';
    const TRANSACTION_TYPE_AUTHORIZE    = 'authorize';
    const TRANSACTION_TYPE_REFUND       = 'refund';
    const TRANSACTION_TYPE_VOID         = 'void';
    const TRANSACTION_TYPE_RELEASE      = 'release';

    const NEWTOKEN                      = 'vaultcreditcardtokens';
    const HMAC_SHA1_ALGORITHM           = 'sha1';



    public function getCcTypesSagePayments()
    {
        $types = array();
        foreach (Mage::getConfig()->getNode('global/payment/sagepayments_cards/types')->asArray() as $data) {
            $types[$data['code']] = $data['name'];
        }
        return $types;
    }
    public function getAppId()
    {
        $modulesArray = (array)Mage::getConfig()->getNode('modules')->children();
        if (array_key_exists('Enterprise_Enterprise', $modulesArray)) {
            return Mage::getStoreConfig(self::CONFIG_APPID_EE);
        } else {
            return Mage::getStoreConfig(self::CONFIG_APPID_CE);
        }
    }
}