<?php
class Born_BornIntegration_Model_Observer {
    const EXPORTED_CUSTOMER_CODE = 'exported';
    const ORDER_STATUS_EXPORTED = 'sage_exported';
    const ERP_RESPONSE_OK = 1;
    const ERP_RESPONSE_NOT_OK = 0;
    const COS_B2C = 29999999;
    const PUR = 19999999;
    
    public function exportOrderToSage($incrementId = NULL){
        Mage::log('BATCH START '.date('Y-m-d H:i:s'), false, 'Order_Process_Log_'.date('Ymd').'.log');
        $excludeScore       = false;    
        $status             = false;
        $synclimit          = Mage::getStoreConfig('sage_log_config/sage_sync_limit/limit_count');
        $salesPublicName    = Mage::getStoreConfig('bornintegration/sage_config/sales_public_name');
        $customerPublicName = Mage::getStoreConfig('bornintegration/sage_config/customer_public_name');
        $minScore           = Mage::getStoreConfig('signifyd_connect/advanced/hold_orders_threshold');
        $minScoreAdj        = $minScore - 1;
        $query  = "SELECT * ";
        $query .= "FROM `sales_flat_order` as t1 ";
        $query .= "JOIN `signifyd_connect_case` as t2 ";
        $query .= "ON t1.increment_id = t2.order_increment ";
        $query .= "WHERE t1.status = 'pending' ";        
        $query .= "OR t1.status = 'processing' ";
        $query .= "OR t1.status = 'bypassscore' ";
        $query .= "OR t1.status = 'special' ";
        Mage::log('Query: '.$query, false, 'Order_Process_Log_'.date('Ymd').'.log');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');        
        $collection = $readConnection->fetchall($query);
        $collection_count = count($collection);
        Mage::log('Results count: '.$collection_count, false, 'Order_Process_Log_'.date('Ymd').'.log');
            if (count($collection)) {   
            $i=1;
                foreach ($collection as $order) {
                    Mage::log('Sync Attempt: '.$order['sync_attempt'], false, 'Order_Process_Log_'.date('Ymd').'.log');
                    if(isset($order['increment_id']) && !empty($order['increment_id'])){
                        if($order['sync_attempt'] < $synclimit){
                        $incrementId = $order['increment_id']; 
                            if($order['store_id'] != 3){
                                if($order['status'] == 'bypassscore'){
                                $excludeScore = true;    
                                }
                            Mage::log('PROCESS ORDER START '.date('Y-m-d H:i:s'), false, 'Order_Process_Log_'.date('Ymd').'.log');
                                if(isset($order['score']) && !empty($order['score']) && $order['score'] > $minScoreAdj || $excludeScore == true){
                                Mage::log('Processing standard order: '.$incrementId, false, 'Order_Process_Log_'.date('Ymd').'.log');    
                                $status = $this->processOrder($incrementId);    
                                }else{
                                $orderObject = Mage::getModel('sales/order')->loadByIncrementId($order['increment_id']);
                                $orderObject->hold();
                                $orderObject->setState(Mage_Sales_Model_Order::STATE_HOLDED);
                                $orderObject->setStatus(Mage_Sales_Model_Order::STATE_HOLDED);			
                                $orderObject->addStatusHistoryComment("Signifyd: order held because of low score. ".$order['score']);
                                $orderObject->save();                                
                                Mage::log('Not processed. Order : '.$incrementId.' Signifyd score below threshold. Score: '.$order['score'].' Threshold: '.$minScore, false, 'Order_Process_Log_'.date('Ymd').'.log');    
                                }    
                            Mage::log('PROCESS ORDER STOP '.date('Y-m-d H:i:s'), false, 'Order_Process_Log_'.date('Ymd').'.log');    
                            }else{
                            Mage::log('PROCESS B2B ORDER START '.date('Y-m-d H:i:s'), false, 'Order_Process_Log_'.date('Ymd').'.log');    
                            Mage::log('Processing B2B order: '.$incrementId, false, 'Order_Process_Log_'.date('Ymd').'.log');
                            $status = $this->processB2BOrder($incrementId);    
                            Mage::log('PROCESS B2B ORDER STOP '.date('Y-m-d H:i:s'), false, 'Order_Process_Log_'.date('Ymd').'.log');
                            } 
                        $i++;
                        }                    
                    }else{
                    throw new Exception('Bad Query result');
                    }
                }
            }  
        Mage::log('BATCH STOP '.date('Y-m-d H:i:s'), false, 'Order_Process_Log_'.date('Ymd').'.log');    
        return $status;
    }
    
    public function processOrder($incrementId){
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $storeId = $order->getStoreId();
            if($storeId == '1'){
            $customerId = self::PUR;
            }else if($storeId == '2'){
            $customerId = self::COS_B2C;
            }else{
            return;
            }
        $status = FALSE;    
        $magentoCustomerId =  $order->getCustomerId();   
        $order->setCustomerId($customerId);
        $salesPublicName = Mage::getStoreConfig('bornintegration/sage_config/sales_public_name');
        $objectXML = Mage::getModel('bornintegration/order_export')->exportOrder($order);
        Mage::log($objectXML, false, "Order_".$incrementId.".log");
        $apiResponse = Mage::getModel('bornintegration/api_sageerp')->fetchResult($salesPublicName, $objectXML, 'save');
            if(is_object($apiResponse)&&!empty($apiResponse)&&$apiResponse->status == self::ERP_RESPONSE_OK){
                $order->setCustomerId($magentoCustomerId);
                if($order->getStatus() == 'processing' || $order->getStatus() == 'pending' || $order->getStatus() == 'bypassscore' || $order->getStatus() == 'special'){
                $order->setStatus(self::ORDER_STATUS_EXPORTED);
                $order->addStatusToHistory(self::ORDER_STATUS_EXPORTED, 'Order pushed to X3 by CRON', false)->save();
                $status = TRUE;
                }						
                if($order->getStatus() == 'special'){
                
                $status = TRUE;    
                }
            }else{
            $order->setCustomerId($magentoCustomerId);            
            $syncAttempt = $order->getSyncAttempt() + 1;
            $order->setSyncAttempt($syncAttempt)->save();
            Mage::helper('sagelog')->saveErrorLog('Sage Import Order Error: ', sprintf('Order ID: %d, incrementID: %d not exported due to error.', $order->getId(), $order->getIncrementId()), '', '', false);
            $status = FALSE;    
            }
        return $status;    
    }

    public function processB2BOrder($incrementId){
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $status = FALSE;
            $salesPublicName = Mage::getStoreConfig('bornintegration/sage_config/sales_public_name');
            $customerPublicName = Mage::getStoreConfig('bornintegration/sage_config/customer_public_name');    
                    if (!$order->getCustomerId()) {
                    $guestCustomerXml = Mage::getModel('bornintegration/customer_export')->buildCustomerFromOrder($order);    
                    Mage::getModel('bornintegration/api_sageerp')->fetchResult($customerPublicName, $guestCustomerXml, 'save');
                    $objectXML = Mage::getModel('bornintegration/order_export')->exportToErp($order, true);
                    $apiResponse = Mage::getModel('bornintegration/api_sageerp')->fetchResult($salesPublicName, $objectXML, 'save');
                    } else {
                    $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                    $eavSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
                    $customerAddressEntityTypeId = $eavSetup->getEntityTypeId('customer_address');
                    $addressCodeAttributeId = $eavSetup->getAttributeId($customerAddressEntityTypeId, 'address_code');
                    $this->_registerCodes($customer, $customerAddressEntityTypeId, $addressCodeAttributeId);
                        if($customer->getSageExported()){
                        $this->updateExportedCustomers($customer->getIncrementId());
                        }else{
                        $this->exportNewCustomer($customer->getIncrementId());
                        }
                    $objectXML = Mage::getModel('bornintegration/order_export')->exportToErp($order);
                    Mage::log($objectXML, false, 'OrderLog_'.$incrementId.'.log');
                    $apiResponse = Mage::getModel('bornintegration/api_sageerp')->fetchResult($salesPublicName, $objectXML, 'save');                    
                    }
                    if(is_object($apiResponse)&&!empty($apiResponse)&&$apiResponse->status == self::ERP_RESPONSE_OK){
                        if($order->getStatus() == 'processing' || $order->getStatus() == 'pending'){
                        $order->setStatus(self::ORDER_STATUS_EXPORTED);
                        $order->addStatusToHistory(self::ORDER_STATUS_EXPORTED, 'Order pushed to X3 by CRON', false)->save();	
                        }						
                        if($order->getStatus() == 'special'){
                        $order->setStatus('complete');
                        $order->addStatusToHistory('complete', 'Order re-integrated to X3 by CRON', false)->save();
                        } 
                    $status = TRUE;
                    }else{                     
                    $syncAttempt = $order->getSyncAttempt() + 1;
                    $order->setSyncAttempt($syncAttempt)->save();
                    Mage::helper('sagelog')->saveErrorLog('Sage Import Order Error: ', sprintf('Order ID: %d, incrementID: %d not exported to ERP due to error.', $order->getId(), $order->getIncrementId()), '', '', false);
                    $status = FALSE;
                    }
            
            return $status;
    }
    
    public function importOrder($incrementId = NULL){
        $status = FALSE;
	$syncattemptlimit = Mage::getStoreConfig('sage_log_config/sage_sync_limit/limit_count');
            if (is_null($incrementId) || is_object($incrementId)) {
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('sync_attempt', array('lt' => $syncattemptlimit))
		->addFieldToFilter('status', array('in' => array('pending', 'processing', 'special')));

            } else if (is_array($incrementId)) {   
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('sync_attempt', array('lt' => $syncattemptlimit))
                ->addFieldToFilter('entity_id', array('in' => $incrementId));
            } else {  
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('sync_attempt', array('lt' => $syncattemptlimit))
                ->addFieldToFilter('increment_id', array('eq' => $incrementId));
            }
            $count = $orderCollection->count();
            if ($orderCollection->count()) {
            $salesPublicName = Mage::getStoreConfig('bornintegration/sage_config/sales_public_name');
            $customerPublicName = Mage::getStoreConfig('bornintegration/sage_config/customer_public_name');
                foreach ($orderCollection as $order) {     
                    if (!$order->getCustomerId()) {
                    $guestCustomerXml = Mage::getModel('bornintegration/customer_export')->buildCustomerFromOrder($order);    
                    Mage::getModel('bornintegration/api_sageerp')->fetchResult($customerPublicName, $guestCustomerXml, 'save');
                    $objectXML = Mage::getModel('bornintegration/order_export')->exportToErp($order, true);
                    $apiResponse = Mage::getModel('bornintegration/api_sageerp')->fetchResult($salesPublicName, $objectXML, 'save');
                    } else {
                    $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                    $eavSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
                    $customerAddressEntityTypeId = $eavSetup->getEntityTypeId('customer_address');
                    $addressCodeAttributeId = $eavSetup->getAttributeId($customerAddressEntityTypeId, 'address_code');
                    $this->_registerCodes($customer, $customerAddressEntityTypeId, $addressCodeAttributeId);
                        if($customer->getSageExported()){
                        $this->updateExportedCustomers($customer->getIncrementId());
                        }else{
                        $this->exportNewCustomer($customer->getIncrementId());
                        }
                    $objectXML = Mage::getModel('bornintegration/order_export')->exportToErp($order);
                    $apiResponse = Mage::getModel('bornintegration/api_sageerp')->fetchResult($salesPublicName, $objectXML, 'save');
                    
                    }
                    if(is_object($apiResponse)&&!empty($apiResponse)&&$apiResponse->status == self::ERP_RESPONSE_OK){
                        if($order->getStatus() == 'processing' || $order->getStatus() == 'pending'){
                        $order->setStatus(self::ORDER_STATUS_EXPORTED);
                        $order->addStatusToHistory(self::ORDER_STATUS_EXPORTED, 'Order pushed to X3 by CRON-sage_order_import_process', false)->save();	
                        }						
                        if($order->getStatus() == 'special'){
                        $order->setStatus('complete');
                        $order->addStatusToHistory('complete', 'Order re-integrated to X3 by CRON-sage_order_import_process and then set back to complete', false)->save();
                        }
                    $status = TRUE;
                    }else{
                    $syncAttempt = $order->getSyncAttempt() + 1;
                    $order->setSyncAttempt($syncAttempt)->save();
                    Mage::helper('sagelog')->saveErrorLog('Sage Import Order Error: ', sprintf('Order ID: %d, incrementID: %d not exported to ERP due to error.', $order->getId(), $order->getIncrementId()), '', '', false);
                    $status = FALSE;
                    }
                }
            }
            return $status;
    }

    public function exportNewCustomer($incrementId = null){
        $customerCollection = Mage::getModel('customer/customer')
            ->getCollection()
            ->addFieldToFilter('website_id', array('in' => array(1, 2)))
            ->addAttributeToSelect('entity_id');
            if(!is_null($incrementId)){
            $customerCollection->addAttributeToFilter('increment_id', array('eq' => $incrementId));
            }else{
            $customerCollection->addFieldToFilter('sage_exported', array('eq' => 0));
            }

        $eavSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $customerAddressEntityTypeId = $eavSetup->getEntityTypeId('customer_address');
        $addressCodeAttributeId = $eavSetup->getAttributeId($customerAddressEntityTypeId, 'address_code');
        $customerIds = $customerCollection->getAllIds();
            if (is_array($customerIds) && count($customerIds) > 0){
            $customerPublicName = Mage::getStoreConfig('bornintegration/sage_config/customer_public_name');
                foreach ($customerIds as $customerId) {   
                $customer = Mage::getModel('customer/customer')->load($customerId);
                    if (!$customer->getSageExported()) {
                    $this->_registerCodes($customer, $customerAddressEntityTypeId, $addressCodeAttributeId);
                    $objectXML = Mage::getModel('bornintegration/customer_export')->exportToErp($customer);
                    $apiResponse = Mage::getModel('bornintegration/api_sageerp')->fetchResult($customerPublicName, $objectXML, 'save');
                        if(is_object($apiResponse)&&!empty($apiResponse)&&$apiResponse->status == self::ERP_RESPONSE_OK){
                        $customer->setData('sage_exported', 1)->save();
                        } else {
                        $customer->setData('sage_exported', 0)->save();
                        Mage::helper('sagelog')->saveErrorLog('Sage Export New Customer Error: ', json_encode($apiResponse), '', '', false);
                        }
                    }
                }
            }
        return $this;
    }

    public function updateExportedCustomers($incrementId = null){
	$to = date('Y-m-d H:i:s');
        $from = date('Y-m-d H:i:s', strtotime('-720 minutes', strtotime($to)));
            if(!is_null($incrementId)){
            $customerCollection = Mage::getModel('customer/customer')->getCollection()->addAttributeToFilter('increment_id', array('eq' => $incrementId));
            }else{
            $customerCollection = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('website_id', array('in' => array(1, 2)))
                ->addFieldToFilter('sage_exported', array('eq' => 1))
                ->addAttributeToSelect('entity_id');
            }
        $customerIds = $customerCollection->getAllIds();
        $eavSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $customerAddressEntityTypeId = $eavSetup->getEntityTypeId('customer_address');
        $addressCodeAttributeId = $eavSetup->getAttributeId($customerAddressEntityTypeId, 'address_code');
            if(is_array($customerIds) && count($customerIds) > 0){
            $customerPublicName = Mage::getStoreConfig('bornintegration/sage_config/customer_public_name');
                foreach ($customerIds as $customerId) {
                $customer = Mage::getModel('customer/customer')->load($customerId);
                $objectXML = Mage::getModel('bornintegration/customer_export')->exportToErp($customer);
                $apiResponse = Mage::getModel('bornintegration/api_sageerp')->fetchResult($customerPublicName, $objectXML, 'modify', $customer->getIncrementId());
                    if (is_object($apiResponse) && isset($apiResponse->status) && $apiResponse->status == self::ERP_RESPONSE_OK) {
                    $customer->setData('sage_exported', 1)->save();
                    } elseif(is_object($apiResponse) && isset($apiResponse->messages)){
                        $error_message = $apiResponse->messages[0]->message;
                        if(strpos($error_message, 'Record does not exist') !== false){
                        $status = $this->exportNewCustomer($customer->getIncrementId());     
                        }

                    }else{
                    Mage::helper('sagelog')->saveErrorLog('Sage Update Export Customers Error: ', json_encode($apiResponse), '', '', false);
                    }
                }
            }
        return $this;
    }

    public function setNewCustomerFlag(Varien_Event_Observer $observer){
	$customer = $observer->getEvent()->getCustomer();
            if (!$customer->getId()) {
            $customer->setData('sage_exported', 0);
            }
        return $this;
    }

    public function addCustomerGroupCodeField(Varien_Event_Observer $observer){
	$block = $observer->getEvent()->getBlock();
            if ($block instanceof Mage_Adminhtml_Block_Customer_Group_Edit_Form) {
            $fieldset = $block->getForm()->addFieldset('sage_customer_groups_fields', array('legend' => Mage::helper('customer')->__('Sage ERP Customer Group Settings')));
            $fieldset->addField('sage_code', 'text', array(
                'name' => 'sage_code',
                'label' => Mage::helper('customer')->__('Sage ERP Customer Group Code'),
                'title' => Mage::helper('customer')->__('Sage ERP Customer Group Code'),
                'class' => 'validate-data',
                'required' => false,
                'value' => Mage::registry('current_group')->getSageCode()
                    )
            );
            } elseif ($block instanceof Mage_Adminhtml_Block_Customer_Group_Grid) {
            $block->addColumn('sage_code', array(
                'header' => Mage::helper('customer')->__('Sage ERP Code'),
                'index' => 'sage_code',
                'width' => '200px'
                ));
            }
        return $this;
    }

    public function saveSageCustomerGroupCode(Varien_Event_Observer $observer){
	$object = $observer->getEvent()->getObject();
        $sageCode = Mage::app()->getRequest()->getParam('sage_code', null);
            if (!is_null($sageCode)) {
            $object->setSageCode($sageCode);
            }
        return $this;
    }

    protected function _registerCodes($customer, $customerAddressEntityTypeId, $addressCodeAttributeId){
	$customerId         = (int)$customer->getId();
		$coreResource       = Mage::getSingleton('core/resource');
        $readAdapter        = $coreResource->getConnection('core_read');
        $acid = (int)$addressCodeAttributeId;
            if ($addressCodeAttributeId > 0) {      
            $addressEntityVarcharTableName = $coreResource->getTableName('customer_address_entity_varchar');
            $addressEntityTableName = $coreResource->getTableName('customer/address_entity');
            $query = "SELECT `entity_id` FROM `{$addressEntityTableName}` WHERE `parent_id`='$customerId'";
            $results = $readAdapter->fetchCol($query);
                if (is_array($results) && count($results) > 0) {    
                $writestatus = $this->processAddressCode($customer,$results,$customerAddressEntityTypeId,$acid,$addressEntityVarcharTableName);
                }
        }else{
        throw new Exception('No addressCodeAttributeId provided');    
        }
        return $this;
    }
    
    private function processAddressCode($customer,$results,$customerAddressEntityTypeId,$addressCodeAttributeId,$addressEntityVarcharTableName){
	$unhandledAddresses = array();
        $addressCount = count($results);
        $status = array();
        $addresses = array();
        $billingId          = (int)$customer->getDefaultBilling();
        $shippingId         = (int)$customer->getDefaultShipping(); 
        if(count($results) > 0){
            if($addressCount > 1){
            $i = 1;
                foreach($results as $addressId){
                    if (($addressId == $billingId) || ($addressId == $shippingId)) {
                        if (($addressId == $billingId) && ($addressId == $shippingId)) {
                        $code = 'MAIN';	
                        }elseif(($addressId == $billingId) && ($addressId != $shippingId)) {
                        $code = 'MAIN';
                        }elseif(($addressId != $billingId) && ($addressId == $shippingId)) {
                        $code = 'SHP01';
                        $i++;
                        }
                    
                    $address = array();
                    $address['addressId'] = $addressId;
                    $address['customerAddressEntityTypeId'] = $customerAddressEntityTypeId;
                    $address['addressCodeAttributeId'] = $addressCodeAttributeId;
                    $address['addressEntityVarcharTableName'] = $addressEntityVarcharTableName;
                    $address['code'] = $code;
                    $addresses[] = $address;
                        }else{
                    $unhandledAddresses[] = $addressId;
                    }
                }	
            unset($addressId);
            $uac = count($unhandledAddresses);    
                    foreach($unhandledAddresses as $addressId){
                            if($uac < 10){
                            $code = 'SHP0';    
                            }elseif($uac > 9){
                            $code = 'SHP0';    
                            }elseif($uac >99){
                            $code = 'SHP';    
                            }
                    $address = array();
                    $address['addressId'] = $addressId;
                    $address['customerAddressEntityTypeId'] = $customerAddressEntityTypeId;
                    $address['addressCodeAttributeId'] = $addressCodeAttributeId;
                    $address['addressEntityVarcharTableName'] = $addressEntityVarcharTableName;
                    $address['code'] = $code.$i;
                    $addresses[] = $address;                    
                    $i++;
                    }
            }else{
                    foreach($results as $addressId){
                    $code = 'MAIN';
                    $address = array();
                    $address['addressId'] = $addressId;
                    $address['customerAddressEntityTypeId'] = $customerAddressEntityTypeId;
                    $address['addressCodeAttributeId'] = $addressCodeAttributeId;
                    $address['addressEntityVarcharTableName'] = $addressEntityVarcharTableName;
                    $address['code'] = $code;
                    $addresses[] = $address;                      
                    }		
            }
		}else{
		//Mage::log('No results found', false, 'Born_BornIntegration_Model_Observer_'.date('Ymd').'.log');    
		}
            if(isset($addresses)&&!empty($addresses)){
                $status = $this->_processAddresses($addresses);
            }
            return $status;
    }
    
    private function _processAddresses($addresses){
	    foreach($addresses AS $address){
            $status[] = $this->writeNewAddress($address['customerAddressEntityTypeId'],$address['addressId'],$address['addressCodeAttributeId'],$address['addressEntityVarcharTableName'],$address['code']);
            }
        return $status;
    }
    
    private function writeNewAddress($customerAddressEntityTypeId,$addressId,$addressCodeAttributeId,$addressEntityVarcharTableName,$code){		
	    try{
            $coreResource = Mage::getSingleton('core/resource');
            $writeAdapter = $coreResource->getConnection('core_write');
            $cid = (int)$addressId;
            $acid = (int)$addressCodeAttributeId;
            $upsert  = "INSERT INTO ".$addressEntityVarcharTableName;
            $upsert .= " (`entity_type_id`,`attribute_id`,`entity_id`,`value`)";
            $upsert .= "VALUES";
            $upsert .= " ('".$customerAddressEntityTypeId."','".$addressCodeAttributeId."','".$addressId."','".$code."')";
            $upsert .= " ON DUPLICATE KEY UPDATE";
            $upsert .= " `value`='".$code."'";   
            $results = $writeAdapter->query($upsert);
            }catch(Exception $e){
            Mage::helper('sagelog')->saveErrorLog($upsert, $e, '', '', false);
            }   
        return $results;
    }  
    
    public function makeSalable(Varien_Event_Observer $observer){
	$storeCode = Mage::app()->getStore()->getCode();
            if ($storeCode == 'cosb2b_store') {
            $product = $observer->getEvent()->getProduct();
            $inventoryItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                if ($inventoryItem->getBackorders()) {
                $observer->getEvent()->getSalable()->setIsSalable(true);
                } elseif (Mage::helper('core')->isModuleEnabled('Amasty_Preorder')) {
                    if ($inventoryItem->getBackorders() == Amasty_Preorder_Model_Rewrite_CatalogInventory_Source_Backorders::BACKORDERS_PREORDER) {
                    $observer->getEvent()->getSalable()->setIsSalable(true);
                    }
                }
            }
        return $this;
    }

    public function saveQuotePurchaseOrderNumber(Varien_Event_Observer $observer){
	$quote = $observer->getEvent()->getQuote();
        $post = Mage::app()->getFrontController()->getRequest()->getPost();
            if (isset($post['custom']['purchase_order_number'])) {
            $quote->setPurchaseOrderNumber($post['custom']['purchase_order_number']);
            }
        return $this;
    }

    public function saveOrderPurchaseNumber(Varien_Event_Observer $observer){
	$order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
            if ($quote->getPurchaseOrderNumber()) {
            $order->setPurchaseOrderNumber($quote->getPurchaseOrderNumber());
            }
        return $this;
    }

    public function updateSyncAttemptOrderEdit($order){
	$resetsync = 0;
            if ($order->getSyncAttempt() > 0) {
            $order->setSyncAttempt($resetsync);
            $order->save();
            }
    }
}