<?php

class Born_BornIntegration_Model_Customer_Export {

    protected $_streetLines = 2;
    protected $_helper = null;
    protected $_guestGroups = array(
        'pur' => 'PCWEB',
        'cosb2c' => 'CSWEB',
        'cosb2b' => 'CSRTL',
        'cosb2bint' => 'CSINT'
    );
    protected $_customerCategory = array(
        'pur' => 'PCWEB',
        'cosb2c' => 'CSWEB',
        'cosb2b' => 'CSRTL',
        'cosb2bint' => 'CSINT'
    );

    public function __construct()
    {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Customer_Export_'.date('Ymd').'.log');
        $this->_streetLines = Mage::helper('customer/address')->getStreetLines();
        $this->_helper = Mage::helper('bornintegration');
    }

    public function exportToErp(Mage_Customer_Model_Customer $customer)
    {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Customer_Export_'.date('Ymd').'.log');
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $primaryBillingAddress = $customer->getPrimaryBillingAddress();
            $primaryShippingAddress = $customer->getPrimaryShippingAddress();
            $additionalAddress = $customer->getAddressesCollection();
            $xmlString = '<?xml version="1.0" encoding="utf-8" ?>';
            $xmlString .= '<PARAM>';
            $xmlString .= $this->createXmlForCustomer($customer, $additionalAddress, $primaryBillingAddress, $primaryShippingAddress);
            $xmlString .= '</PARAM>';
            return $xmlString;
        } else {
            return false;
        }
    }

    public function createXmlForCustomer(Mage_Customer_Model_Customer $customer, $additionalAddresses, $primaryBillingAddress = null, $primaryShippingAddress = null)
    {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Customer_Export_'.date('Ymd').'.log');
        $isTestMode = (boolean) Mage::getStoreConfig('bornintegration/sage_config/is_test');
        $identityPrefix = ($isTestMode) ? (string) Mage::getStoreConfig('bornintegration/sage_config/identity_prefix') : '';
        $statisticalGroupCode = $this->_helper->getStatisticalGroupCode($customer->getGroupId(), $customer->getWebsiteId());
        $statisticalCustomerGroupCode = $this->_helper->getStatisticalCustomerGroupCode($customer->getGroupId(), $customer->getWebsiteId());
        $taxRule = strlen($this->_helper->getAvaTaxTerm($customer->getGroupId())) ? $this->_helper->getAvaTaxTerm($customer->getGroupId()) : 'USA';
        $customerXmlString = '<GRP ID="BPC0_1">';
        $customerXmlString .= '<FLD NAME="BCGCOD">' . $statisticalCustomerGroupCode . '</FLD>';
        $customerXmlString .= '<FLD NAME="BPCNUM">' . $identityPrefix . $customer->getIncrementId() . '</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPRC_1">';
        $customerXmlString .= '<FLD NAME="BPRNAM">' . substr($customer->getName(), 0, 32) . '</FLD>';
        $customerXmlString .= '</GRP>';
        if ($primaryBillingAddress) {
            $customerXmlString .= '<GRP ID="BPC3_1">';
            $customerXmlString .= '<FLD NAME="BPAINV">' . $primaryBillingAddress->getAddressCode() . '</FLD>';
            $customerXmlString .= '<FLD NAME="BPAPYR">' . $primaryBillingAddress->getAddressCode() . '</FLD>';
            $customerXmlString .= '</GRP>';
        }
        $customerXmlString .= '<GRP ID="BPRC_1">';
        $customerXmlString .= '<FLD NAME="CUR">' . Mage::getStoreConfig('currency/options/base') . '</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC3_2">';
        $customerXmlString .= '<FLD NAME="VACBPR">USA</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC3_3">';
        if (Mage::app()->getWebsite($customer->getWebsiteId())->getCode() == 'cosb2b' && $customer->getCreditTerms() == 1) {
            $customerXmlString .= '<FLD NAME="PTE">NET30</FLD>';
        } else {
            $customerXmlString .= '<FLD NAME="PTE">CREDITCARD</FLD>';
        }
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC3_1">';
        $customerXmlString .= '<FLD NAME="ACCCOD">STD</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC2_6">';
        $customerXmlString .= '<FLD NAME="TSCCOD">' . $statisticalGroupCode . '</FLD>';
        $customerXmlString .= '</GRP>';
        if (count($additionalAddresses) > 0) {
            if (count($additionalAddresses) > 1) {
                $bpaaddTabString = '<TAB ID="BPC4_1" SIZE="' . count($additionalAddresses) . '">';
                $i = 1;
                $customerXmlString .= '<TAB DIM="10" ID="BPAC_1" SIZE="' . count($additionalAddresses) . '">';
                foreach ($additionalAddresses as $additionaladdress) {
                    $bpaaddTabString .= '<LIN NUM="' . $i . '">';
                    $bpaaddTabString .= '<FLD NAME="BPAADD">' . $additionaladdress->getAddressCode() . '</FLD>';
                    $bpaaddTabString .= '<FLD NAME="BPDNAM0">' . substr($additionaladdress->getName(), 0, 32) . '</FLD>';
                    $bpaaddTabString .= '</LIN>';
                    $customerXmlString .= '<LIN NUM="' . $i . '">';
                    $customerXmlString .= '<FLD NAME="CODADR">' . $additionaladdress->getAddressCode() . '</FLD>';
                    $customerXmlString .= '<FLD NAME="BPAADDFLG">2</FLD>';
                    for ($line = 1; $line <= $this->_streetLines; $line++) {
                        $addressLine = $additionaladdress->getStreet($line);
                        if (is_string($addressLine) && strlen($addressLine) > 0) {
                            $customerXmlString .= '<FLD NAME="ADDLIG' . ($line) . '">' . $addressLine . '</FLD>';
                        }
                    }
                    $customerXmlString .= '<FLD NAME="POSCOD">' . $additionaladdress->getPostcode() . '</FLD>';
                    $customerXmlString .= '<FLD NAME="CTY">' . $additionaladdress->getCity() . '</FLD>';
                    $customerXmlString .= '<FLD NAME="SAT">' . $additionaladdress->getRegionCode() . '</FLD>';
                    $customerXmlString .= '<FLD NAME="BPACRY">' . $additionaladdress->getCountryId() . '</FLD>';
                    $customerXmlString .= '<FLD NAME="WEB1">' . $customer->getEmail() . '</FLD>';
                    $customerXmlString .= '<FLD NAME="TEL1">' . $additionaladdress->getTelephone() . '</FLD>'; // telephone
                    $customerXmlString .= '<FLD NAME="TEL2">' . $additionaladdress->getFax() . '</FLD>'; // fax
                    $customerXmlString .= '</LIN>';

                    $i++;
                }
                $customerXmlString .= '</TAB>';
                $bpaaddTabString .= '</TAB>';
                $customerXmlString .= $bpaaddTabString;
            } else {
                $bpaaddTabString = '<TAB ID="BPC4_1" SIZE="2">';
                $firstAddress = $additionalAddresses->getFirstItem();
                $bpaaddTabString .= '<LIN NUM="1">';
                $bpaaddTabString .= '<FLD NAME="BPAADD">MAIN</FLD>';
                $bpaaddTabString .= '<FLD NAME="BPDNAM0">' . substr($firstAddress->getName(), 0, 32) . '</FLD>';
                $bpaaddTabString .= '</LIN>';
                $bpaaddTabString .= '<LIN NUM="2">';
                $bpaaddTabString .= '<FLD NAME="BPAADD">SHP01</FLD>';
                $bpaaddTabString .= '<FLD NAME="BPDNAM0">' . substr($firstAddress->getName(), 0, 32) . '</FLD>';
                $bpaaddTabString .= '</LIN>';
                $customerXmlString .= '<TAB DIM="10" ID="BPAC_1" SIZE="2">';
                $customerXmlString .= '<LIN NUM="1">';
                $customerXmlString .= '<FLD NAME="CODADR">MAIN</FLD>';
                $customerXmlString .= '<FLD NAME="BPAADDFLG">2</FLD>';
                for ($line = 1; $line <= $this->_streetLines; $line++) {
                    $addressLine = $firstAddress->getStreet($line);
                    if (is_string($addressLine) && strlen($addressLine) > 0) {
                        $customerXmlString .= '<FLD NAME="ADDLIG' . ($line) . '">' . $addressLine . '</FLD>';
                    }
                }
                $customerXmlString .= '<FLD NAME="POSCOD">' . $firstAddress->getPostcode() . '</FLD>';
                $customerXmlString .= '<FLD NAME="CTY">' . $firstAddress->getCity() . '</FLD>';
                $customerXmlString .= '<FLD NAME="SAT">' . $firstAddress->getRegionCode() . '</FLD>';
                $customerXmlString .= '<FLD NAME="BPACRY">' . $firstAddress->getCountryId() . '</FLD>';
                $customerXmlString .= '<FLD NAME="WEB1">' . $customer->getEmail() . '</FLD>';
                $customerXmlString .= '<FLD NAME="TEL1">' . $firstAddress->getTelephone() . '</FLD>'; // telephone
                $customerXmlString .= '<FLD NAME="TEL2">' . $firstAddress->getFax() . '</FLD>'; // fax
                $customerXmlString .= '</LIN>';
                $customerXmlString .= '<LIN NUM="2">';
                $customerXmlString .= '<FLD NAME="CODADR">SHP01</FLD>';
                $customerXmlString .= '<FLD NAME="BPAADDFLG">2</FLD>';
                for ($line = 1; $line <= $this->_streetLines; $line++) {
                    $addressLine = $firstAddress->getStreet($line);
                    if (is_string($addressLine) && strlen($addressLine) > 0) {
                        $customerXmlString .= '<FLD NAME="ADDLIG' . ($line) . '">' . $addressLine . '</FLD>';
                    }
                }
                $customerXmlString .= '<FLD NAME="POSCOD">' . $firstAddress->getPostcode() . '</FLD>';
                $customerXmlString .= '<FLD NAME="CTY">' . $firstAddress->getCity() . '</FLD>';
                $customerXmlString .= '<FLD NAME="SAT">' . $firstAddress->getRegionCode() . '</FLD>';
                $customerXmlString .= '<FLD NAME="BPACRY">' . $firstAddress->getCountryId() . '</FLD>';
                $customerXmlString .= '<FLD NAME="WEB1">' . $customer->getEmail() . '</FLD>';
                $customerXmlString .= '<FLD NAME="TEL1">' . $firstAddress->getTelephone() . '</FLD>'; // telephone
                $customerXmlString .= '<FLD NAME="TEL2">' . $firstAddress->getFax() . '</FLD>'; // fax
                $customerXmlString .= '</LIN>';
                $customerXmlString .= '</TAB>';
                $bpaaddTabString .= '</TAB>';
                $customerXmlString .= $bpaaddTabString;
            }
        }
        return $customerXmlString;
    }

    public function buildCustomerFromOrder(Mage_Sales_Model_Order $order)
    {
        //Mage::log(__METHOD__, false, 'Born_BornIntegration_Model_Customer_Export_'.date('Ymd').'.log');
        $isTestMode = (boolean) Mage::getStoreConfig('bornintegration/sage_config/is_test');
        $identityPrefix = ($isTestMode) ? (string) Mage::getStoreConfig('bornintegration/sage_config/identity_prefix') : '';
        $statisticalGroupCode = $this->_guestGroups[Mage::app()->getStore($order->getStoreId())->getWebsite()->getCode()];
        $taxRule = 'USA';
        $quoteShippingAddress = Mage::getModel('sales/quote_address')
                ->getCollection()
                ->addFieldToSelect('same_as_billing')
                ->addFieldToFilter('quote_id', array('eq' => $order->getQuoteId()))
                ->addFieldToFilter('address_type', array('eq' => Mage_Sales_Model_Quote_Address::TYPE_SHIPPING))
                ->getFirstItem();
        $customerXmlString = '<?xml version="1.0" encoding="utf-8" ?>';
        $customerXmlString .= '<PARAM>';
        $customerXmlString .= '<GRP ID="BPC0_1">';
        $customerXmlString .= '<FLD NAME="BCGCOD">' . $statisticalGroupCode . '</FLD>';
        $customerXmlString .= '<FLD NAME="BPCNUM">' . $identityPrefix . '5' . $order->getIncrementId() . '</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPRC_1">';
        $customerXmlString .= '<FLD NAME="BPRNAM">' . implode(' ', array($order->getCustomerFirstname(), $order->getCustomerLastname())) . '</FLD>';
        $customerXmlString .= '</GRP>';
        $shippingddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $customerXmlString .= '<GRP ID="BPC3_1">';
        $customerXmlString .= '<FLD NAME="BPAINV">MAIN</FLD>';
        $customerXmlString .= '<FLD NAME="BPAPYR">MAIN</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<TAB DIM="10" ID="BPC4_1" SIZE="1" >';
        $customerXmlString .= '<LIN NUM="1" >';
            if ($quoteShippingAddress->getSameAsBilling()) {
            $customerXmlString .= '<FLD NAME="BPAADD">MAIN</FLD>';
            } else {
            $customerXmlString .= '<FLD NAME="BPAADD">SHP01</FLD>';
            }
        $customerXmlString .= '</LIN>';
        $customerXmlString .= '</TAB>';
        $customerXmlString .= '<GRP ID="BPRC_1">';
        $customerXmlString .= '<FLD NAME="CUR">' . Mage::getStoreConfig('currency/options/base') . '</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC3_1">';
        $customerXmlString .= '<FLD NAME="BPCINV">' . $identityPrefix . '5' . $order->getIncrementId() . '</FLD>';
        $customerXmlString .= '<FLD NAME="BPCPYR">' . $identityPrefix . '5' . $order->getIncrementId() . '</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC3_2">';
        $customerXmlString .= '<FLD NAME="VACBPR">' . $taxRule . '</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC3_3">';
        $customerXmlString .= '<FLD NAME="PTE">CREDITCARD</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC3_1">';
        $customerXmlString .= '<FLD NAME="ACCCOD">STD</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<GRP ID="BPC2_6">';
        $customerXmlString .= '<FLD NAME="TSCCOD">' . $statisticalGroupCode . '</FLD>';
        $customerXmlString .= '</GRP>';
        $customerXmlString .= '<TAB ID="BPC4_1" SIZE="2">';
        $customerXmlString .= '<LIN NUM="1">';
        $customerXmlString .= '<FLD NAME="BPAADD">MAIN</FLD>';
        $customerXmlString .= '<FLD NAME="BPDNAM0">' . substr($order->getBillingAddress()->getName(), 0, 32) . '</FLD>';
        $customerXmlString .= '</LIN>';
            if (!empty($shippingddress)) {
            $customerXmlString .= '<LIN NUM="2">';
            $customerXmlString .= '<FLD NAME="BPAADD">SHP01</FLD>';
            $customerXmlString .= '<FLD NAME="BPDNAM0">' . substr($order->getShippingAddress()->getName(), 0, 32) . '</FLD>';
            $customerXmlString .= '</LIN>';
            }
        $customerXmlString .= '</TAB>';
        $addressLineCount = ($quoteShippingAddress->getSameAsBilling()) ? 1 : 2;
        $customerXmlString .= '<TAB DIM="10" ID="BPAC_1" SIZE="' . $addressLineCount . '">';
        $customerXmlString .= '<LIN NUM="1">';
        $customerXmlString .= '<FLD NAME="CODADR">MAIN</FLD>';
        $customerXmlString .= '<FLD NAME="BPAADDFLG">2</FLD>';
        $customerXmlString .= '<FLD NAME="ADDLIG1">' . $billingAddress->getStreet(-1) . '</FLD>';
        $customerXmlString .= '<FLD NAME="POSCOD">' . $billingAddress->getPostcode() . '</FLD>';
        $customerXmlString .= '<FLD NAME="CTY">' . $billingAddress->getCity() . '</FLD>';
        $customerXmlString .= '<FLD NAME="SAT">' . $billingAddress->getRegionCode() . '</FLD>';
        $customerXmlString .= '<FLD NAME="BPACRY">' . $billingAddress->getCountryId() . '</FLD>';
        $customerXmlString .= '<FLD NAME="WEB1">' . $order->getCustomerEmail() . '</FLD>';
        $customerXmlString .= '<FLD NAME="TEL1">' . $billingAddress->getTelephone() . '</FLD>'; // telephone
        $customerXmlString .= '<FLD NAME="TEL2">' . $billingAddress->getFax() . '</FLD>'; // fax
        $customerXmlString .= '</LIN>';
            if (!$quoteShippingAddress->getSameAsBilling()) {
            $customerXmlString .= '<LIN NUM="2">';
            $customerXmlString .= '<FLD NAME="CODADR">SHP01</FLD>';
            $customerXmlString .= '<FLD NAME="ADDLIG1">' . $shippingddress->getStreet(-1) . '</FLD>';
            $customerXmlString .= '<FLD NAME="POSCOD">' . $shippingddress->getPostcode() . '</FLD>';
            $customerXmlString .= '<FLD NAME="CTY">' . $shippingddress->getCity() . '</FLD>';
            $customerXmlString .= '<FLD NAME="SAT">' . $shippingddress->getRegionCode() . '</FLD>';
            $customerXmlString .= '<FLD NAME="BPACRY">' . $shippingddress->getCountryId() . '</FLD>';
            $customerXmlString .= '<FLD NAME="WEB1">' . $order->getCustomerEmail() . '</FLD>';
            $customerXmlString .= '<FLD NAME="TEL1">' . $shippingddress->getTelephone() . '</FLD>'; // telephone
            $customerXmlString .= '<FLD NAME="TEL2">' . $shippingddress->getFax() . '</FLD>'; // fax
            $customerXmlString .= '</LIN>';
            }
        $customerXmlString .= '</TAB>';
        $customerXmlString .= '</PARAM>';
        return $customerXmlString;
    }
}
