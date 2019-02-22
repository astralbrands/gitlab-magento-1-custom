<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/22/13
 * Time   : 2:15 PM
 * File   : ${FILE_NAME}
 * Module : ${PROJECT_NAME}
 */

//X = Exact; match on address and 9 Digit Zip Code
//Y = Yes; match on address and 5 Digit Zip Code
//A = Address matches, Zip does not
//W = 9 Digit Zip matches, address does not
//Z = 5 Digit Zip matches, address does not
//N = No; neither zip nor address match
//U = Unavailable
//R = Retry
//E = Error
//S = Service Not Supported
//“ “ = Service Not Supported
//
//International AVS Codes
//D = Match Street Address and Postal Code match for International Transaction
//M = Match Street Address and Postal Code match for International Transaction
//B = Partial Match Street Address Match for International Transaction. Postal Code not verified due to incompatible
//      formats
//P = Partial Match Postal Codes match for International Transaction but street address not verified due to incompatible
//       formats
//C = No Match Street Address and Postal Code not verified for International Transaction due to incompatible formats
//I = No Match Address Information not verified by International issuer
//G = Not Supported Non-US. Issuer does not participate

class Ebizmarts_SagePaymentsPro_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_ccCards = array();
    // @codingStandardsIgnoreStart
    public function F91B2E37D34E5DC4FFC59C324BDC1157C() 
    {
        if (Mage::getStoreConfig(
            Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_ENABLE,
            Mage::app()->getStore()->getId()
        )==0) {
            return true;
        } 
        if($_SERVER['HTTP_HOST'] == 'www.purcosmetics.com'){
        $R8409EAA6EC0CE2EA307354B2E150F8C2 = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        }elseif($_SERVER['HTTP_HOST'] == 'www.cosmedix.com'){
        $R8409EAA6EC0CE2EA307354B2E150F8C2 = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        }elseif($_SERVER['HTTP_HOST'] == 'admin.purcosmetics.com'){
        $R8409EAA6EC0CE2EA307354B2E150F8C2 = str_replace('admin.', '', $_SERVER['HTTP_HOST']);
        }elseif($_SERVER['HTTP_HOST'] == 'admin.cosmedix.com'){
        $R8409EAA6EC0CE2EA307354B2E150F8C2 = str_replace('admin.', '', $_SERVER['HTTP_HOST']);
        }
        
        $REBBCEB7D5CE9F8309DCC3226F5DAC53B =
            Mage::getStoreConfig(Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_LICENSE, Mage::app()->getStore());
        $R1A634B62E7FB6CBC3AD8309D17FDC73C =
            substr(strrev($R8409EAA6EC0CE2EA307354B2E150F8C2), 0, strlen($R8409EAA6EC0CE2EA307354B2E150F8C2));
        $R7BCAA4FB61D5AD641E1B67637D894EC1 =
            crypt($R8409EAA6EC0CE2EA307354B2E150F8C2 . 'Ebizmarts_SagePayments', $R1A634B62E7FB6CBC3AD8309D17FDC73C);
        $R835CC35CB400C713B188267E7C10C798 =
            ($R7BCAA4FB61D5AD641E1B67637D894EC1 === $REBBCEB7D5CE9F8309DCC3226F5DAC53B);
        return $R835CC35CB400C713B188267E7C10C798;
    }
    // @codingStandardsIgnoreEnd

    public function creatingAdminOrder() 
    {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        return ($controllerName == 'sales_order_create' || $controllerName == 'adminhtml_sales_order_create' ||
            $controllerName == 'sales_order_edit');
    }

    public function getCardNiceDate($string) 
    {
        $newString = $string;

        if (strlen($string) == 4) {
            $date = str_split($string, 2);
            $newString = $date[0] . '/' . '20' . $date[1];
        }

        return $newString;
    }

    public function getCcImage($cname) 
    {
        return Mage::getModel('core/design_package')
            ->getSkinUrl('sagepaymentspro/images/cc/' . str_replace(' ', '_', strtolower($cname)) . '.gif');
    }

    public function getCardLabel($value, $concatImage = true) 
    {
        if (empty($this->_ccCards)) {
            $this->_ccCards = Mage::getModel('ebizmarts_sagepaymentspro/config')->getCcTypesSagePayments();
        }

        $label = '';
        $cardLabel = $value;

        if ($concatImage) {
            $label = '<img src="' . $this->getCcImage($cardLabel) .
                '" title="' . $cardLabel . ' logo" alt="' . $cardLabel . ' logo" />  ';
        }

        $label .= $cardLabel;

        return $label;
    }
    public function getSagePaymentsConfigJson()
    {
        $conf = array();
        $conf ['global']['valid'] = (int) Mage::helper('ebizmarts_sagepaymentspro')
            ->F91B2E37D34E5DC4FFC59C324BDC1157C();
        $conf ['global']['not_valid_message'] = $this->__('This SagePayments module\'s license is NOT valid.');

        return Zend_Json::encode($conf);
    }
    public function log($msg)
    {
        if (Mage::getStoreConfig(
            Ebizmarts_SagePaymentsPro_Model_Config::CONFIG_LOG,
            Mage::app()->getStore()->getId()
        )) {
            //Mage::log($msg);
        }

    }
    public function getCvvDescription($code) 
    {
        $d = array(array('code'=>'M','description'=>'Match'),
                   array('code'=>'N','description' => 'CVV No March'),
                   array('code'=>'P', 'description' => 'Not Processed'),
                   array('code'=>'S','description'=>'Merchant Has Indicated that CVV2 Is Not Present'),
                   array('code'=>'U',
                       'description'=>'Issuer is not certified on/or has not provided Visa Encryption Keys'));
        foreach ($d as $cvv) {
            if ($cvv['code']==strtoupper($code)) {
                return $this->__($cvv['description']);
            }
        }
        return $this->__('Unknown CVV description');
    }
    public function getRiskDescription($code) 
    {
        $d = array(array('code'=>'01','description'=>'Max Sale Exeeded'),
                array('code'=>'02','description' => 'Min Sale Not Met'),
                array('code'=>'03', 'description' => '1 Day Volume Exceeded'),
                array('code'=>'04','description'=>'1 Day Usage Exceeded'),
                array('code'=>'05','description'=>'3 Day Volume Exceeded'),
                array('code'=>'06','description'=>'3 Day Usage Exceeded'),
                array('code'=>'07','description'=>'15 Day Volume Exceeded'),
                array('code'=>'08','description'=>'15 Day Usage Exceeded'),
                array('code'=>'09','description'=>'30 Day Volume Exceeded'),
                array('code'=>'10','description'=>'30 Day Usage Exceeded'),
                array('code'=>'11','description'=>'Stolen or Lost Card'),
                array('code'=>'12','description'=>'AVS Failure'));
        foreach ($d as $cvv) {
            if ($cvv['code']==strtoupper($code)) {
                return $this->__($cvv['description']);
            }
        }
        return $this->__('Unknown Risk description');
    }
    public function getResponseDescription($code) 
    {
        $d = array(array('code'=>'A','description'=>'Approved'),
                    array('code'=>'E','description' => 'Declined'));
        foreach ($d as $cvv) {
            if ($cvv['code']==strtoupper($code)) {
                return $this->__($cvv['description']);
            }
        }
        return $this->__('Unknown Risk description');

    }
    public function getAvsDescription($code)
    {
        $d = array(
            array('code' => 'X', 'description' => 'Exact; match on address and 9 Digit Zip Code'),
            array('code' => 'Y', 'description' => 'Yes; match on address and 5 Digit Zip Code'),
            array('code' => 'A', 'description' => 'Address matches, Zip does not'),
            array('code' => 'W', 'description' => '9 Digit Zip matches, address does not'),
            array('code' => 'Z', 'description' => '5 Digit Zip matches, address does not'),
            array('code' => 'N', 'description' => 'No; neither zip nor address match'),
            array('code' => 'U', 'description' => 'Unavailable'),
            array('code' => 'R', 'description' => 'Retry'),
            array('code' => 'E', 'description' => 'Error'),
            array('code' => 'S', 'description' => 'Service Not Supported'),
            array('code' => ' ', 'description' => 'Service Not Supported'),
            array('code' => 'D', 'description' =>
                'Match Street Address and Postal Code match for International Transaction'),
            array('code' => 'M', 'description' =>
                'Match Street Address and Postal Code match for International Transaction'),
            array('code' => 'B', 'description' =>
                'Partial Match Street Address Match for International Transaction. Postal Code not verified due'.
                ' to incompatible formats'),
            array('code' => 'P', 'description' =>
                'Partial Match Postal Codes match for International Transaction but street address not verified due'.
                ' to incompatible formats'),
            array('code' => 'C', 'description' =>
                'No Match Street Address and Postal Code not verified for International Transaction due'.
                ' to incompatible formats'),
            array('code' => 'I', 'description' => 'No Match Address Information not verified by International issuer'),
            array('code' => 'G', 'description' => 'Not Supported Non-US. Issuer does not participate'),
        );
        foreach ($d as $avs) {
            if ($avs['code']==strtoupper($code)) {
                return $this->__($avs['description']);
            }
        }
        return $this->__('Unknown Avs description');
    }
    public function getResponseCodeDescription($code)
    {
        $d = array(
            array('code' => '000000', 'description' => 'Server Error'),
            array('code' => '900000', 'description' => 'Order number value is in an invalid format'),
            array('code' => '900001', 'description' => 'Name value is in an invalid format or was left blank'),
            array('code' => '900002', 'description' => 'Address value is in an invalid format or was left blank'),
            array('code' => '900003', 'description' => 'City value is in an invalid format or was left blank'),
            array('code' => '900004', 'description' => 'State value is in an invalid format or was left blank'),
            array('code' => '900005', 'description' => 'Zip code value is in an invalid format or was left blank'),
            array('code' => '900006', 'description' => 'Country value is in an invalid format or was left blank'),
            array('code' => '900007', 'description' => 'Telephone value is in an invalid format or was left blank'),
            array('code' => '900008', 'description' => 'Fax value is in an invalid format or was left blank'),
            array('code' => '900009', 'description' => 'Email value is in an invalid format or was left blank'),
            array('code' => '900010', 'description' => 'Shipping address name value is in an invalid format'),
            array('code' => '900011', 'description' => 'Shipping Address value is in an invalid format'),
            array('code' => '900012', 'description' => 'Shipping city value is in an invalid format'),
            array('code' => '900013', 'description' => 'Shipping state value is in an invalid format'),
            array('code' => '900014', 'description' => 'Shipping zip code value is in an invalid format'),
            array('code' => '900015', 'description' => 'Shipping country value is in an invalid format'),
            array('code' => '900016', 'description' => 'Credit card number value is in an invalid format'),
            array('code' => '900017', 'description' => 'Expiration date value is in an invalid format'),
            array('code' => '900018', 'description' =>
                'CVV (card verification value) value is in an invalid format or was left blank (if set to required)'),
            array('code' => '900019', 'description' =>
                'Grand Total must equal > $0.00. Please check subtotal, shipping and tax values.'),
            array('code' => '900020', 'description' =>
                'Transaction Code value is in an invalid format or was left blank'),
            array('code' => '900021', 'description' =>
                'Authorization code is in an invalid format or was left blank (required for Force transactions)'),
            array('code' => '900022', 'description' =>
                'Reference value is in an invalid format or was left blank (Required for Force or Void by Reference)'),
            array('code' => '900023', 'description' =>
                'Track Data value is in an invalid format or was left blank (required for debit and retail'.
                ' transactions)'),
            array('code' => '900024', 'description' => 'Tracking number value is in an invalid format'),
            array('code' => '900025', 'description' =>
                'Customer number value is in an invalid format (used only for PCLIII transactions)'),
            array('code' => '900026', 'description' => 'Shipping company value is in an invalid format'),
            array('code' => '900027', 'description' => 'Recurring value is in an invalid format (must be = 0 or 1)'),
            array('code' => '900028', 'description' => 'Recurring value is in an invalid format'),
            array('code' => '900029', 'description' =>
                'Recurring interval value is in an invalid format (must be numeric)'),
            array('code' => '900030', 'description' =>
                'Recurring indefinite value is in an invalid format or was left blank'),
            array('code' => '900031', 'description' =>
                'Recurring times to process value is in an invalid format (must be numeric)'),
            array('code' => '900032', 'description' => 'Recurring non business days value is in an invalid format'),
            array('code' => '900033', 'description' => 'Recurring Group was left blank or group not found'),
            array('code' => '900034', 'description' =>
                'Recurring start date value is in an invalid format or was left blank'),
            array('code' => '900035', 'description' =>
                'Pin number entered is incorrect (required for Pin- debit transactions)'),
            array('code' => '901000', 'description' =>
                'General data validation error, the message will contain additional information'),
            array('code' => '910000', 'description' => 'The transaction you are trying to submit is not allowed.'),
            array('code' => '910001', 'description' => 'Visa card type transactions are not allowed.'),
            array('code' => '910002', 'description' => 'MasterCard card type transactions are not allowed.'),
            array('code' => '910003', 'description' => 'American Express card type transactions are not allowed.'),
            array('code' => '910004', 'description' => 'Discover card type transactions are not allowed.'),
            array('code' => '910005', 'description' => 'Card type transactions are not allowed.'),
            array('code' => '911911', 'description' => 'M_id or M_key incorrect'),
            array('code' => '920000', 'description' => 'Item not found'),
            array('code' => '920001', 'description' =>
                'No corresponding sale found within last 6 months, credit couldn\’t be issued.'),
            array('code' => '920002', 'description' => 'Address Verification Service failure.'),
            array('code' => '920050', 'description' => 'A debit transaction cannot be voided.'),
        );
        foreach ($d as $error) {
            if ($error['code']==strtoupper($code)) {
                return $this->__($error['description']);
            }
        }
        return $this->__('Unknown error');
    }
}