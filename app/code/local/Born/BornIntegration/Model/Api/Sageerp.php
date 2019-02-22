<?php
class Born_BornIntegration_Model_Api_Sageerp extends Varien_Object {

    const SAGE_ERP_WSDL = 'http://209.198.192.119:28880/adxwsvc/services/CAdxWebServiceXmlCC?wsdl';
    const SAGE_WSDL_PATH_CONFIG = 'bornintegration/sage_config/wsdl_url';
    const SAGE_CONNECTION_USERNAME_CONFIG = 'bornintegration/sage_config/username';
    const SAGE_CONNECTION_PASSWORD_CONFIG = 'bornintegration/sage_config/password';
    const SAGE_CONNECTION_LANG_CONFIG = 'bornintegration/sage_config/locale_code';
    const SAGE_CONNECTION_POOLALIAS_CONFIG = 'bornintegration/sage_config/pool_alias';
    const SAGE_OBJECT_PRODUCT = 'ITM';
    const SAGE_OBJECT_CUSTOMER = 'BPC';
    const SAGE_OBJECT_SALE = 'SOH';

    protected $_wsdl;
    protected $_username;
    protected $_password;
    protected $_locale;
    protected $_poolAlias;

    public function __construct() {
        parent::__construct();
        $this->_wsdl = Mage::getStoreConfig(self::SAGE_WSDL_PATH_CONFIG);
        $this->_username = Mage::getStoreConfig(self::SAGE_CONNECTION_USERNAME_CONFIG);
        $this->_password = Mage::getStoreConfig(self::SAGE_CONNECTION_PASSWORD_CONFIG);
        $this->_locale = Mage::getStoreConfig(self::SAGE_CONNECTION_LANG_CONFIG);
        $this->_poolAlias = Mage::getStoreConfig(self::SAGE_CONNECTION_POOLALIAS_CONFIG);
    }

    public function fetchResult($publicObject, $xmlInput, $operationType, $identityValue = null) {
        sleep(5);
        Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
        $soapClient = new SoapClient(self::SAGE_ERP_WSDL, array(
            'trace' => 1,
            'exception' => 0,
            'connection_timeout' => 240,
            'encoding' => 'ISO-8859-1', 
        ));
        Mage::log(__LINE__, false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
        switch ($operationType) {
            case 'save':
                try {
                $result = $soapClient->__call($operationType, array('callContext' => array('codeLang' => $this->_locale, 'codeUser' => $this->_username, 'password' => $this->_password, 'poolAlias' => $this->_poolAlias), 'publicName' => $publicObject, 'objectXml' => $xmlInput));
                } catch (Exception $ex) {
                    Mage::helper('sagelog')->saveErrorLog('SoapFault exception', $ex, '', '', false);
                }
                break;
            case 'modify':
                try {
                $result = $soapClient->__call($operationType, array('callContext' => array('codeLang' => $this->_locale, 'codeUser' => $this->_username, 'password' => $this->_password, 'poolAlias' => $this->_poolAlias), 'publicName' => $publicObject, 'objectKeys' => array(array('key' => 'BPCNUM', 'value' => $identityValue)), 'objectXml' => $xmlInput));
                } catch (Exception $ex) {
                Mage::helper('sagelog')->saveErrorLog('SoapFault exception', $ex, '', '', false);
                }
                break;
        }
        Mage::log(__LINE__, false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
        Mage::log(json_encode($result), false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
        if(!isset($result)){
        Mage::helper('sagelog')->saveErrorLog('Sage Request failed to return results.', $result, '', '', false);  
        $result = false;
        }else{
                if(is_object($result)&&isset($result->status)){
                    if(!$result->status){
                    $logObject = array();
                    $logObject['APICall'] = $publicObject;
                    $logObject['APIMethod'] = $operationType;
                    $logObject['messages'] = $result->messages;
                        if(isset($result->messages)&&!empty($result->messages)&&is_array($result->messages)){
                            foreach($result->messages AS $message){
                                if(strpos(strtolower($message), 'record already exists') !== false){
                                $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                                $order->setStatus(self::ORDER_STATUS_EXPORTED);
                                $order->addStatusToHistory(self::ORDER_STATUS_EXPORTED, 'Order pushed to X3 by CRON', false)->save();                                
                                }
                            Mage::helper('sagelog')->saveErrorLog('Sage Request failed.', $message->message, $incrementId, '', false);    
                            }
                            
                        }
                    Mage::helper('sagelog')->saveErrorLog('Sage Request failed.', $result->messages, '', '', false); 
                    
                    Mage::log('APICall: '.$publicObject, false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
                    Mage::log('Method: '.$operationType, false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
                    Mage::log('Messages: '.json_encode($result->messages), false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
                    Mage::log('Status: '.json_encode($result->status), false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
                    Mage::log('Info: '.json_encode($result->technicalInfos), false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
                    Mage::log($xmlInput, false, 'Born_BornIntegration_Model_Api_Sageerp_'.date('Ymd').'.log');
                    }
                }
        }
        return $result;
    }

}