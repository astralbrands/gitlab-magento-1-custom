<?php
class Born_BornIntegration_Model_Order_Export extends Born_BornIntegration_Model_Integration {

    protected $_orderTypes = array(
        'pur' => 'PCWEB',
        'cosb2c' => 'CSB2C',
        'cosb2b' => 'CSB2B',
	'cosb2bint' => 'CSINT'
    );

    protected $_shippingCodes = array(
        'fedex_FEDEX_GROUND' => 'FEDXG',
        'fedex_FEDEX_2_DAY' => '2D',
        'fedex_STANDARD_OVERNIGHT' => '1D',
        'usps_INT_2' => 'USPPI',
        'usps_1' => 'USPSP',
        'usps_0_FCLE' => 'USPSF',
        'usps_0_FCL' => 'USPSF',
        'usps_0_FCP' => 'USPSF',
        'usps_FCPC' => 'USPSF',
        'usps_15' => 'USPSF',
        'usps_53' => 'USPSF',
        'usps_61' => 'USPSF',
        'employee_shipping_free' => 'HC',
        'employee_shipping_paid' => 'USPSF',
	'freightcollect_freightcollect' => 'CL',
		
    );
    
    protected $_salesSite = array(
        'pur' => 'KENNP',
        'cosb2c' => 'KENNC',
        'cosb2b' => 'KENNC',
	'cosb2bint' => 'KENNC'
    );
    
    protected $_bundleSku = array(
        'pur' => '09735',
        'cosb2c' => '09736',
        'cosb2b' =>'09736',
	'cosb2bint' =>'09736'
    );
    /**
     * @param $items
     * @return array
     */
    public function getOrderItems($items) {
        $data = array();
        foreach($items as $item) {
            $data[] = array(
                'sku' => $item->getSku(),
                'qty' => $item->getQtyOrdered(),
                'original_price' => $item->getOriginalPrice(),
                'discount_amount' => $item->getDiscountAmount(),
                'net_price' => $item->getPrice()
            );
        }
        return $data;
    }
    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function exportOrder(Mage_Sales_Model_Order $order){
        $xml = $this->createOrderXml($order);
        return $xml;
    }
    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function createOrderXml(Mage_Sales_Model_Order $order){
		$storeId = $order->getStoreId();
        $salesSite = $this->_salesSite[Mage::app()->getStore($order->getStoreId())->getWebsite()->getCode()];
			if($storeId == '1'){
			    $bundleItemSku = $this->_bundleSku['pur'];
			}elseif($storeId == '2'){
                $bundleItemSku = $this->_bundleSku['cosb2c'];
			}elseif($storeId == '3'){
                $bundleItemSku = $this->_bundleSku['cosb2b'];
			}
        $orderType = $this->_orderTypes[Mage::app()->getStore($order->getStoreId())->getWebsite()->getCode()];
        $isTestMode = (boolean)Mage::getStoreConfig('bornintegration/sage_config/is_test');
        $identityPrefix = ($isTestMode) ? (string)Mage::getStoreConfig('bornintegration/sage_config/identity_prefix'): '';
        $purchaseOrderNumber = (strlen($order->getPurchaseOrderNumber())) ? $order->getPurchaseOrderNumber(): $identityPrefix.''.$order->getIncrementId();
        $totalExclTax = $order->getSubtotalInclTax() - $order->getTaxAmount();
        $totalInclTax = $order->getGrandTotal() - $order->getShippingAmount();
        $shippingAddressCode = '';
            $xmlString = '<?xml version="1.0" encoding="utf-8" ?>';
            $xmlString .= '<PARAM>';
            $xmlString .= '<GRP ID="SOH0_1">';
            $xmlString .= '<FLD NAME="SALFCY">'.$salesSite.'</FLD>';
            $xmlString .= '<FLD NAME="SOHTYP">'.$orderType.'</FLD>';
            $xmlString .= '<FLD NAME="SOHNUM">'.$identityPrefix.''.$order->getIncrementId().'</FLD>';
            $xmlString .= '<FLD NAME="BPCORD">'.$order->getCustomerId().'</FLD>';
            $xmlString .= '<FLD NAME="ORDDAT">'.date('Ymd',strtotime($order->getCreatedAtDate())).'</FLD>';
            $xmlString .= '<FLD NAME="CUSORDREF">'.$purchaseOrderNumber.'</FLD>';
            $xmlString .= '<FLD NAME="CUR">'.$order->getOrderCurrencyCode().'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH1_1">';
            $xmlString .= '<FLD NAME="BPCINV">'.$order->getCustomerId().'</FLD>';
            $xmlString .= '<FLD NAME="BPCPYR">'.$order->getCustomerId().'</FLD>';
            $xmlString .= '<FLD NAME="BPAADD">'.$shippingAddressCode.'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH2_1" >';
            $xmlString .= '<FLD NAME="STOFCY">'.$salesSite.'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH1_5">';
            $xmlString .= '<FLD NAME="ORDSTA">1</FLD>';
            $xmlString .= '<FLD NAME="DLVSTA">1</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH4_3">';
            $xmlString .= '<FLD NAME="ORDNOT">'.$order->getSubtotal().'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH3_2">';
                switch($order->getPayment()->getMethod()){                   
                    case 'creditterms':
                    $xmlString .= '<FLD NAME="PTE">NET30</FLD>';
                    break;
                    case Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS:
                    $xmlString .= '<FLD NAME="PTE">PAYPAL</FLD>';
                    break;
                    case 'ccsave':
                    $xmlString .= '<FLD NAME="PTE">CREDITCARD</FLD>';
                    break;                
                    case 'sagepaymentsprodirect':
                    $xmlString .= '<FLD NAME="PTE">CREDITCARD</FLD>';
                    break;
                    case 'authorizenet':
                    $xmlString .= '<FLD NAME="PTE">AUTHNET</FLD>';
                    break;
                    default:
                    $xmlString .= '<FLD NAME="PTE">NET30</FLD>';
                    break;
                    }   
            $xmlString .= '</GRP>';
            $scopeId = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();
                if( strpos( $order->getShippingMethod(), 'matrixrate_matrixrate_' ) !== false && $scopeId == 1) {
                $this->_shippingCodes[$order->getShippingMethod()] = Mage::app()->getWebsite($scopeId)->getConfig('bornintegration/general/x3_method');
                }	
            $methods = array();
            $methods['m1'] = Mage::app()->getWebsite($scopeId)->getConfig('bornintegration/general/x3_method');
            $methods['m2'] = $order->getShippingDescription();
				if(isset($this->_shippingCodes[$order->getShippingMethod()])){			
				$methods['m3'] = $this->_shippingCodes[$order->getShippingMethod()];
				}
            if(isset($methods['m3'])){
            $shippingMethodXml = '<GRP ID="SOH2_3">';
            $shippingMethodXml .= '<FLD NAME="MDL">'.$this->_shippingCodes[$order->getShippingMethod()].'</FLD>';
                if(strpos($order->getShippingDescription(), 'Federal Express', 0) !== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">FEDEX</FLD>';
                }elseif(strpos($order->getShippingDescription(), 'United States Postal Service', 0)!== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif(strpos($order->getShippingMethod(), 'fedex', 0)!== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">FEDEX</FLD>';
                }elseif(strpos($order->getShippingMethod(), 'usps', 0)!== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif(strpos($order->getShippingMethod(), 'tgcommerce', 0)!== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">TGC</FLD>';
                }elseif($order->getShippingMethod() == 'employee_shipping_free'){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">HC</FLD>';
                }elseif($order->getShippingMethod() == 'employee_shipping_paid'){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif($order->getShippingMethod() == 'freightcollect_freightcollect'){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">COLLECT</FLD>';
                }elseif($order->getShippingMethod() == 'freeshipping_freeshipping'){
        $shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif( strpos($order->getShippingMethod(), 'matrixrate_matrixrate_' ) !== false && $scopeId == 1) {
		$shippingMethodXml .= '<FLD NAME="BPTNUM">'.Mage::app()->getWebsite($scopeId)->getConfig('bornintegration/general/x3_carrier').'</FLD>';
		}
            $shippingMethodXml .= '</GRP>';
            }elseif(isset($methods['m2'])){
            $shippingMethodXml = '<GRP ID="SOH2_3">';	
		if(strpos($order->getShippingDescription(), 'FedEx' , 0) !== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">FEDEX</FLD>';
                    if(strpos($order->getShippingDescription(), '2 Day' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">2D</FLD>';
                    }elseif(strpos($order->getShippingDescription(), '2nd Day' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">2D</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'Standard Overnight' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">1D</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'Ground' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">FEDXG</FLD>';
                    }					
		}elseif(strpos($order->getShippingDescription(), 'USPS' , 0) !== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                    if(strpos($order->getShippingDescription(), 'International Priority Mail' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">USPPI</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'First Class International' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">USPFI</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'Ground' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">USPSP</FLD>';
                    }					
		}elseif(strpos($order->getShippingDescription(), 'Free' , 0) !== FALSE){
            $shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
            $shippingMethodXml .= '<FLD NAME="MDL">USPSP</FLD>';
        }elseif(strpos($order->getShippingDescription(), 'TradeGlobal' , 0) !== FALSE){
                $shippingMethodXml .= '<FLD NAME="MDL">TGC</FLD>';    
                }
                $shippingMethodXml .= '</GRP>';		
                }else{
                throw new Exception('No shipping method set');
                }
		if(isset($shippingMethodXml)&&!empty($shippingMethodXml)){
                $xmlString .= $shippingMethodXml;
		}else{
                throw new Exception('No shipping XML generated');
		}        
            $customerBalance = (!is_null($order->getCustomerBalanceAmount())) ? $order->getCustomerBalanceAmount(): 0;
            $xmlString .= '<TAB DIM="200" ID="SOH3_4" SIZE="4">';
            $xmlString .= '<LIN NUM="1">';
            $xmlString .= '<FLD NAME="INVDTATYP">0</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.$order->getShippingAmount().'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '<LIN NUM="2">';
            $xmlString .= '<FLD NAME="INVDTATYP">2</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.(abs($order->getDiscountAmount()) + abs($customerBalance)).'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '<LIN NUM="3">';
            $xmlString .= '<FLD NAME="INVDTATYP">3</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.abs($order->getTaxAmount()).'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '<LIN NUM="4">';
            $xmlString .= '<FLD NAME="INVDTATYP">4</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.abs($order->getFeeAmount()).'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '</TAB>';
            $xmlString .= '<TAB DIM="200" ID="SOH4_1" SIZE="'.count($order->getAllItems()).'">';
            $i = 1;
			
			
					
                foreach($order->getAllItems() as $_item){		
                    if($_item->getProductType() ==  Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $_item->getProductType() ==  Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                        if($_item->getParentItemId()){
                        $parentItem = $order->getItemById($_item->getParentItemId());
                            if(!$parentItem) {
                            $skippeditems = Mage::getModel('born_package/skippeditems')
                                            ->setOrderIncrementId($order->getIncrementId())
                                            ->setChildItemId($_item->getId())
                                            ->setChildItemSku($_item->getSku())
                                            ->setParentItemId($_item->getParentItemId());
                            $skippeditems->save();
                            continue;
                            }
                        }
                    $xmlString .= '<LIN NUM="'.$i.'">';
                        if($_item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                        $xmlString .= '<FLD NAME="ITMREF">'.$bundleItemSku.'</FLD>';
                        $xmlString .= '<FLD NAME="YBDITMNAM">'.str_replace(array('&','#','@'),'',substr($_item->getName(),0,30)).'</FLD>';
                        }else{
                        $xmlString .= '<FLD NAME="ITMREF">'.$_item->getProduct()->getSku().'</FLD>';
                        }
                    $itemName = str_replace(array('&','#','@'),'',substr($_item->getName(),0,30));
                    $xmlString .= '<FLD NAME="ITMDES">'.$itemName.'</FLD>';
                    $xmlString .= '<FLD NAME="SAU">EA</FLD>';
                    $xmlString .= '<FLD NAME="QTY">'.$_item->getQtyOrdered().'</FLD>';
                        if($_item->getParentItemId()){
                        $parentItemName = str_replace(array('&','#','@'),'',substr($order->getItemById($_item->getParentItemId())->getName(),0,30));
                        $xmlString .= '<FLD NAME="YPRNTID">'.$_item->getParentItemId().'</FLD>';
                        $xmlString .= '<FLD NAME="YBDITMNAM">'.$parentItemName.'</FLD>';
                        }
                    $xmlString .= '<FLD NAME="YMGLNITM">'.$_item->getId().'</FLD>';
                        if($_item->getParentItemId()){
                            if($order->getItemById($_item->getParentItemId())->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                            $xmlString .= '<FLD NAME="GROPRI">'.$order->getItemById($_item->getParentItemId())->getPrice().'</FLD>';
                            $xmlString .= '<FLD NAME="NETPRI">'.$order->getItemById($_item->getParentItemId())->getPrice().'</FLD>';
                        }elseif($order->getItemById($_item->getParentItemId())->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                            $xmlString .= '<FLD NAME="GROPRI">0</FLD>';
                            $xmlString .= '<FLD NAME="NETPRI">0</FLD>';
                        }else{
                            $xmlString .= '<FLD NAME="GROPRI">'.$_item->getPrice().'</FLD>';
                            $xmlString .= '<FLD NAME="NETPRI">'.$_item->getPrice().'</FLD>';
                        }
                    }else{
                    $xmlString .= '<FLD NAME="GROPRI">'.$_item->getPrice().'</FLD>';
                    $xmlString .= '<FLD NAME="NETPRI">'.$_item->getPrice().'</FLD>';
                    }
                $xmlString .= '</LIN>';
                $i++;
                }
            }
        $xmlString .= '</TAB>';
        $xmlString .= '<GRP ID="SOH4_4">';
        $xmlString .= '<FLD NAME="ORDINVNOT">'.$totalExclTax.'</FLD>';
        $xmlString .= '<FLD NAME="ORDINVATI">'.$totalInclTax.'</FLD>';
        $xmlString .= '</GRP>';
        $xmlString .= $this->processAddress($order);
        $xmlString .= '</PARAM>';
        return $xmlString;
    }
    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function processAddress(Mage_Sales_Model_Order $order){
        $resource = Mage::getSingleton('core/resource');
	$readConnection = $resource->getConnection('core_read');
    	$writeConnection = $resource->getConnection('core_write');
        $query = "SELECT * ";
	$query .= "FROM `sales_flat_order_address` as t1 ";
        $query .= "WHERE t1.parent_id = ".$order->getEntityId(); 
        $address_collection = $readConnection->fetchall($query);        
        $i=1;
            foreach($address_collection as $address_item){
                if($i === 1){
                $regionModel = Mage::getModel('directory/region')->loadByCode($address_item['region'], $address_item['country_id']);
                $regionId = $regionModel->getId();                    
                $address1 = "<GRP ID=\"ADB3_1\">";
                $address1 .= "<FLD NAME=\"BPRNAM\">".$address_item['firstname']." ".$address_item['lastname']."</FLD>";
                $address1 .= "<FLD NAME=\"BPAADD\">MAIN</FLD>";
                $address1 .= "<FLD NAME=\"CRY\">".$address_item['country_id']."</FLD>";
                $address1 .= "<FLD NAME=\"BPAADDLIG\">".$address_item['street']."</FLD>";
                $address1 .= "<FLD NAME=\"POSCOD\">".$address_item['postcode']."</FLD>";
                $address1 .= "<FLD NAME=\"CTY\">".$address_item['city']."</FLD>";
                $address1 .= "<FLD NAME=\"SAT\">".$regionId."</FLD>";
                $address1 .= "</GRP>"; 
                }elseif($i === 2){
                $regionModel = Mage::getModel('directory/region')->loadByCode($address_item['region'], $address_item['country_id']);
                $regionId = $regionModel->getId();                    
                $address2 = "<GRP ID=\"ADB2_1\">";
                $address2 .= "<FLD NAME=\"BPRNAM\">".$address_item['firstname']." ".$address_item['lastname']."</FLD>";
                $address2 .= "<FLD NAME=\"BPAADD\">SHP01</FLD>";
                $address2 .= "<FLD NAME=\"CRY\">".$address_item['country_id']."</FLD>";
                $address2 .= "<FLD NAME=\"BPAADDLIG\">".$address_item['street']."</FLD>";
                $address2 .= "<FLD NAME=\"POSCOD\">".$address_item['postcode']."</FLD>";
                $address2 .= "<FLD NAME=\"CTY\">".$address_item['city']."</FLD>";
                $address2 .= "<FLD NAME=\"SAT\">".$regionId."</FLD>";
                $address2 .= "</GRP>";       
                }
            $i++;
            }
        return $address1.$address2;
    }
    /**
     * @throws Mage_Core_Exception
     */
    public function exportOrders() {
            if($this->helper->isEnabled()) {
            $sageCodes = $this->helper->getSageCodes();
            $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('state', 'processing')
                ->addFieldToFilter('status', array('neq' => 'exported')); 
            $data = array();
                foreach($orders as $order) {
                $store = Mage::getModel('core/store')->load($order->getStoreId());
                $website = Mage::app()->getWebsite($store->getWebsiteId())->getCode();
                $data[] = array(
                    'customer_id' => ($order->getCustomerIsGuest()) ? $order->getCustomerIsGuest() : 'guest',
                    'customer_email' => $order->getCustomerEmail(),
                    'order_id' => $order->getIncrementId(),
                    'order_type' => $sageCodes[$website]['order_type'],
                    'company' => $sageCodes[$website]['company_code'],
                    'source' => $order->getOrderEntryType(),
                    'billing_address' => $order->getBillingAddressId(),
                    'shipping_address' => $order->getShippingAddressId(),
                    'items' => $this->getOrderItems($order->getAllVisibleItems()),
                    'subtotal' => $order->getSubtotal(),
                    'shipping_charge' => $order->getShippingAmount(),
                    'tax_charge' => $order->getTaxAmount(),
                    'total_paid' => $order->getTotalPaid(),
                    'total_due' => $order->getTotalDue(),
                    'payment_method' => $order->getPayment()->getMethodInstance()->getTitle()
                    );
                }
            $order->setStatus(Born_BornIntegration_Helper_Config::EXPORTED_PROCESSING, true);
            $order->save();
        }
    }
    /**
     *
     */
    public function sendOrders() {
        if($this->helper->isEnabled()) {
            
        }
    }
    /**
     * @param Mage_Sales_Model_Order $order
     * @param bool $isGuest
     * @return string
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function exportToErp(Mage_Sales_Model_Order $order, $isGuest = false){
		Mage::log(__METHOD__, false, 'Born_BornIntegration.log');
                Mage::log($order->getIncrementId(), false, 'Born_BornIntegration.log');
        $xmlString = '';
            if($order instanceof Mage_Sales_Model_Order){
            $orderType = $this->_orderTypes[Mage::app()->getStore($order->getStoreId())->getWebsite()->getCode()];
            $bundleItemSku = $this->_bundleSku[Mage::app()->getStore($order->getStoreId())->getWebsite()->getCode()];
            $salesSite = $this->_salesSite[Mage::app()->getStore($order->getStoreId())->getWebsite()->getCode()];
            $customer = ($order->getCustomerId()) ? Mage::getModel('customer/customer')->setStoreId($order->getStoreId())->load($order->getCustomerId()) : null;
            $customer_data = $customer->getData();
            $totalExclTax = $order->getSubtotalInclTax() - $order->getTaxAmount();
            $totalInclTax = $order->getGrandTotal() - $order->getShippingAmount();
            $isTestMode = (boolean)Mage::getStoreConfig('bornintegration/sage_config/is_test');
            $identityPrefix = ($isTestMode) ? (string)Mage::getStoreConfig('bornintegration/sage_config/identity_prefix'): '';
            $purchaseOrderNumber = (strlen($order->getPurchaseOrderNumber())) ? $order->getPurchaseOrderNumber(): $identityPrefix.''.$order->getIncrementId();
            $discountPercentage = 0;
                if (abs($order->getDiscountAmount()) > 0) {
                $discountPercentage = (abs($order->getDiscountAmount()) / $order->getSubtotalInclTax()) * 100;
                }
                if(!$isGuest){
                    if(isset($customer_data['default_billing'])){
                    $billingAddressId = $customer_data['default_billing'];
                    }else{
                    throw new Exception('No valid billing address');    
                    }
                    if(isset($customer_data['default_shipping'])){
                    $shippingAddressId = $customer_data['default_shipping']; 
                    }else{
                        if(isset($customer_data['default_billing'])){
                        $shippingAddressId = $customer_data['default_billing'];
                        }else{
                        throw new Exception('No valid billing address'); 
                        }                        
                    }
               
                    if(isset($shippingAddressId)&&!empty($shippingAddressId)&&isset($billingAddressId)&&!empty($billingAddressId)&&$billingAddressId === $shippingAddressId){ 
                    $billingAddressCode = 'MAIN';
                    $shippingAddressCode = 'MAIN';                        
                    }elseif(isset($shippingAddressId)&&!empty($shippingAddressId)&&isset($billingAddressId)&&!empty($billingAddressId)&&$billingAddressId != $shippingAddressId && $shippingAddressId > 0 && $billingAddressId > 0){
                    $billingAddressCode = 'MAIN';
                    $shippingAddressCode = 'SHP01';  
                    }elseif(isset($shippingAddressId)&&!empty($shippingAddressId)&&isset($billingAddressId)&&!empty($billingAddressId)&&$billingAddressId != $shippingAddressId && $shippingAddressId === 0 && $billingAddressId === 0){
                    $billingAddressCode = 'MAIN';
                    $shippingAddressCode = 'SHP01';  
                    }elseif($billingAddressId == null){
                        throw new Exception('No billing address set');
                    }elseif($shippingAddressId == null){
                        $shippingAddressCode = 'MAIN';
                    }else{
                    $billingAddressCode = ($billingAddressId > 0) ? Mage::getModel('customer/address')->load($billingAddressId)->getAddressCode(): 'MAIN';
                    $shippingAddressCode = ($shippingAddressId > 0) ? Mage::getModel('customer/address')->load($shippingAddressId)->getAddressCode(): 'SHP01';
                    }
                    
                Mage::log('billingAddressCode '.$billingAddressCode, false, 'Born_BornIntegration.log');
                Mage::log('shippingAddressCode '.$shippingAddressCode, false, 'Born_BornIntegration.log');                     
                    
                }else{
                $coreResource = Mage::getSingleton('core/resource');
                $readConnection = $coreResource->getConnection('core_read');
                $quoteAddressTable = $coreResource->getTableName('sales/quote_address');
                $readQuery = "SELECT `same_as_billing` FROM `{$quoteAddressTable}` WHERE `quote_id`='".$order->getQuoteId()."' AND `address_type`='".Mage_Sales_Model_Quote_Address::TYPE_SHIPPING."'";
                $result = (boolean)$readConnection->fetchOne($readQuery);
                $billingAddressCode = 'MAIN';
                $shippingAddressCode = ($result) ? 'MAIN': 'SHP01';
                }
            $xmlString .= '<?xml version="1.0" encoding="utf-8" ?>';
            $xmlString .= '<PARAM>';
            $xmlString .= '<GRP ID="SOH0_1">';
            $xmlString .= '<FLD NAME="SALFCY">'.$salesSite.'</FLD>';
            $xmlString .= '<FLD NAME="SOHTYP">'.$orderType.'</FLD>';
            $xmlString .= '<FLD NAME="SOHNUM">'.$identityPrefix.''.$order->getIncrementId().'</FLD>';
                if(!$customer){
                $xmlString .= '<FLD NAME="BPCORD">'.$identityPrefix.'5'.$order->getIncrementId().'</FLD>';
                }else{
                $xmlString .= '<FLD NAME="BPCORD">'.$customer->getIncrementId().'</FLD>';
                }
            $xmlString .= '<FLD NAME="ORDDAT">'.date('Ymd',strtotime($order->getCreatedAtDate())).'</FLD>';
            $xmlString .= '<FLD NAME="CUSORDREF">'.$purchaseOrderNumber.'</FLD>';
            $xmlString .= '<FLD NAME="CUR">'.$order->getOrderCurrencyCode().'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH1_1">';
                if(!$customer){
                $xmlString .= '<FLD NAME="BPCINV">'.$identityPrefix.'5'.$order->getIncrementId().'</FLD>';
                $xmlString .= '<FLD NAME="BPCPYR">'.$identityPrefix.'5'.$order->getIncrementId().'</FLD>';
                }else{
                $xmlString .= '<FLD NAME="BPCINV">'.$customer->getIncrementId().'</FLD>';
                $xmlString .= '<FLD NAME="BPCPYR">'.$customer->getIncrementId().'</FLD>';
                }
                $xmlString .= '<FLD NAME="BPAADD">'.$shippingAddressCode.'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH2_1" >';
            $xmlString .= '<FLD NAME="STOFCY">'.$salesSite.'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH1_5">';
            $xmlString .= '<FLD NAME="ORDSTA">1</FLD>';
            $xmlString .= '<FLD NAME="DLVSTA">1</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH4_3">';
            $xmlString .= '<FLD NAME="ORDNOT">'.$order->getSubtotal().'</FLD>';
            $xmlString .= '</GRP>';
            $xmlString .= '<GRP ID="SOH3_2">';
                switch($order->getPayment()->getMethod()){                   
                    case 'creditterms':
                    $xmlString .= '<FLD NAME="PTE">NET30</FLD>';
                    break;
                    case Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS:
                    $xmlString .= '<FLD NAME="PTE">PAYPAL</FLD>';
                    break;
                    case 'sagepaymentsprodirect':
                    $xmlString .= '<FLD NAME="PTE">CREDITCARD</FLD>';
                    break;
                    case 'authorizenet':
                    $xmlString .= '<FLD NAME="PTE">AUTHNET</FLD>';
                    break;
                    default:
                    $xmlString .= '<FLD NAME="PTE">NET30</FLD>';
                    break;
                    }   
            $xmlString .= '</GRP>';
            $scopeId = Mage::getModel('core/store')->load($order->getStoreId())->getWebsiteId();
                if( strpos( $order->getShippingMethod(), 'matrixrate_matrixrate_' ) !== false && $scopeId == 1) {
                $this->_shippingCodes[$order->getShippingMethod()] = Mage::app()->getWebsite($scopeId)->getConfig('bornintegration/general/x3_method');
                }	
            $methods = array();
            $methods['m1'] = Mage::app()->getWebsite($scopeId)->getConfig('bornintegration/general/x3_method');
            $methods['m2'] = $order->getShippingDescription();
				if(isset($this->_shippingCodes[$order->getShippingMethod()])){			
				$methods['m3'] = $this->_shippingCodes[$order->getShippingMethod()];
				}		
            if(isset($methods['m3'])){
            $shippingMethodXml = '<GRP ID="SOH2_3">';
            $shippingMethodXml .= '<FLD NAME="MDL">'.$this->_shippingCodes[$order->getShippingMethod()].'</FLD>';
                if(strpos($order->getShippingDescription(), 'Federal Express', 0) !== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">FEDEX</FLD>';
                }elseif(strpos($order->getShippingDescription(), 'United States Postal Service', 0)!== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif(strpos($order->getShippingMethod(), 'fedex', 0)!== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">FEDEX</FLD>';
                }elseif(strpos($order->getShippingMethod(), 'usps', 0)!== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif($order->getShippingMethod() == 'employee_shipping_free'){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">HC</FLD>';
                }elseif($order->getShippingMethod() == 'employee_shipping_paid'){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif($order->getShippingMethod() == 'freightcollect_freightcollect'){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">COLLECT</FLD>';
                }elseif($order->getShippingMethod() == 'freeshipping_freeshipping'){
                    $shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                }elseif( strpos($order->getShippingMethod(), 'matrixrate_matrixrate_' ) !== false && $scopeId == 1) {
		$shippingMethodXml .= '<FLD NAME="BPTNUM">'.Mage::app()->getWebsite($scopeId)->getConfig('bornintegration/general/x3_carrier').'</FLD>';
		}
            $shippingMethodXml .= '</GRP>';
            }elseif(isset($methods['m2'])){
            $shippingMethodXml = '<GRP ID="SOH2_3">';	
		if(strpos($order->getShippingDescription(), 'FedEx' , 0) !== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">FEDEX</FLD>';
                    if(strpos($order->getShippingDescription(), '2 Day' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">2D</FLD>';
                    }elseif(strpos($order->getShippingDescription(), '2nd Day' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">2D</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'Standard Overnight' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">1D</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'Ground' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">FEDXG</FLD>';
                    }					
		}elseif(strpos($order->getShippingDescription(), 'USPS' , 0) !== FALSE){
		$shippingMethodXml .= '<FLD NAME="BPTNUM">USPS</FLD>';
                    if(strpos($order->getShippingDescription(), 'International Priority Mail' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">USPPI</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'First Class International' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">USPFI</FLD>';
                    }elseif(strpos($order->getShippingDescription(), 'Ground' , 0) !== FALSE){
                    $shippingMethodXml .= '<FLD NAME="MDL">USPSP</FLD>';
                    }					
		}
                $shippingMethodXml .= '</GRP>';		
                }else{
                throw new Exception('No shipping method set');
                }
		if(isset($shippingMethodXml)&&!empty($shippingMethodXml)){
                $xmlString .= $shippingMethodXml;
		}else{
                throw new Exception('No shipping XML generated');
		}        
            $customerBalance = (!is_null($order->getCustomerBalanceAmount())) ? $order->getCustomerBalanceAmount(): 0;
            $xmlString .= '<TAB DIM="200" ID="SOH3_4" SIZE="4">';
            $xmlString .= '<LIN NUM="1">';
            $xmlString .= '<FLD NAME="INVDTATYP">0</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.$order->getShippingAmount().'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '<LIN NUM="2">';
            $xmlString .= '<FLD NAME="INVDTATYP">2</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.(abs($order->getDiscountAmount()) + abs($customerBalance)).'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '<LIN NUM="3">';
            $xmlString .= '<FLD NAME="INVDTATYP">3</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.abs($order->getTaxAmount()).'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '<LIN NUM="4">';
            $xmlString .= '<FLD NAME="INVDTATYP">4</FLD>';
            $xmlString .= '<FLD NAME="INVDTAAMT">'.abs($order->getFeeAmount()).'</FLD>';
            $xmlString .= '</LIN>';
            $xmlString .= '</TAB>';
            $xmlString .= '<TAB DIM="200" ID="SOH4_1" SIZE="'.count($order->getAllItems()).'">';
            $i = 1;
                foreach($order->getAllItems() as $_item){
                    if($_item->getProductType() ==  Mage_Catalog_Model_Product_Type::TYPE_SIMPLE || $_item->getProductType() ==  Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                        if($_item->getParentItemId()){
                        $parentItem = $order->getItemById($_item->getParentItemId());
                            if(!$parentItem) {
                            $skippeditems = Mage::getModel('born_package/skippeditems')
                                            ->setOrderIncrementId($order->getIncrementId())
                                            ->setChildItemId($_item->getId())
                                            ->setChildItemSku($_item->getSku())
                                            ->setParentItemId($_item->getParentItemId());
                            $skippeditems->save();
                            continue;
                            }
                        }
                    $xmlString .= '<LIN NUM="'.$i.'">';
                        if($_item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                        $xmlString .= '<FLD NAME="ITMREF">'.$bundleItemSku.'</FLD>';
                        $xmlString .= '<FLD NAME="YBDITMNAM">'.str_replace(array('&','#','@'),'',substr($_item->getName(),0,30)).'</FLD>';
                        }else{
                        $xmlString .= '<FLD NAME="ITMREF">'.$_item->getProduct()->getSku().'</FLD>';
                        }
                    $itemName = str_replace(array('&','#','@'),'',substr($_item->getName(),0,30));
                    $xmlString .= '<FLD NAME="ITMDES">'.$itemName.'</FLD>';
                    $xmlString .= '<FLD NAME="SAU">EA</FLD>';
                    $xmlString .= '<FLD NAME="QTY">'.$_item->getQtyOrdered().'</FLD>';
                        if($_item->getParentItemId()){
                        $parentItemName = str_replace(array('&','#','@'),'',substr($order->getItemById($_item->getParentItemId())->getName(),0,30));
                        $xmlString .= '<FLD NAME="YPRNTID">'.$_item->getParentItemId().'</FLD>';
                        $xmlString .= '<FLD NAME="YBDITMNAM">'.$parentItemName.'</FLD>';
                        }
                    $xmlString .= '<FLD NAME="YMGLNITM">'.$_item->getId().'</FLD>';
                        if($_item->getParentItemId()){
                            if($order->getItemById($_item->getParentItemId())->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
                            $xmlString .= '<FLD NAME="GROPRI">'.$order->getItemById($_item->getParentItemId())->getPrice().'</FLD>';
                            $xmlString .= '<FLD NAME="NETPRI">'.$order->getItemById($_item->getParentItemId())->getPrice().'</FLD>';
                        }elseif($order->getItemById($_item->getParentItemId())->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
                            $xmlString .= '<FLD NAME="GROPRI">0</FLD>';
                            $xmlString .= '<FLD NAME="NETPRI">0</FLD>';
                        }else{
                            $xmlString .= '<FLD NAME="GROPRI">'.$_item->getPrice().'</FLD>';
                            $xmlString .= '<FLD NAME="NETPRI">'.$_item->getPrice().'</FLD>';
                        }
                    }else{
                    $xmlString .= '<FLD NAME="GROPRI">'.$_item->getPrice().'</FLD>';
                    $xmlString .= '<FLD NAME="NETPRI">'.$_item->getPrice().'</FLD>';
                    }
                $xmlString .= '</LIN>';
                $i++;
                }
            }
        $xmlString .= '</TAB>';
        $xmlString .= '<GRP ID="SOH4_4">';
        $xmlString .= '<FLD NAME="ORDINVNOT">'.$totalExclTax.'</FLD>';
        $xmlString .= '<FLD NAME="ORDINVATI">'.$totalInclTax.'</FLD>';
        $xmlString .= '</GRP>';
        $xmlString .= '</PARAM>';
        }
		if(isset($customer)){
			$incrementId = $customer->getIncrementId();
			if(isset($incrementId)){
				
			}			
		}
    return $xmlString;
    } 
}