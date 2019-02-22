<?php
class Qualityunit_Pap_Model_Pap extends Mage_Core_Model_Abstract {
    protected $papSession;
    private $cacheRefreshAt = 0; // when to refresh
    private $cacheTime = 1800; // how long to store cache (s)
    public $declined = 'D';
    public $pending = 'P';
    public $approved = 'A';

    public function getSession($url = '', $username = '', $pass = '') {
        if ($this->papSession != null && $this->cacheRefreshAt < time()) {
            return $this->papSession;
        }
        $this->cacheRefreshAt = time() + $this->cacheTime;

        $config = Mage::getSingleton('pap/config');
        if ($url == '') {
            $url = $config->getAPIPath();
        }
        if ($username == '') {
            $username = $config->getAPICredential('username');
        }
        if ($pass == '') {
            $pass = $config->getAPICredential('pass');
        }

        $query = 'D=' . urlencode('{"C":"Gpf_Api_AuthService","M":"authenticate","fields":[["name","value","values","error"],' . '["username","' . $username . '",null,""],["password","' . $pass . '",null,""],' . '["roleType","M",null,""],["isFromApi","Y",null,""],["apiVersion","",null,""]]}');

        try {
            $adapter = new Varien_Http_Adapter_Curl();
            $response = $this->connectExternal($url, $query, 'POST', $adapter);
            $response = json_decode($response);
        } catch (Exception $e) {
            Mage::helper('pap')->log('Could not initiate API session: ' . $e->getMessage());
            Mage::throwException('Could not initiate API session: ' . $e->getMessage());
            return false;
        }

        if (!isset($response->success) || $response->success != 'Y') {
            if (isset($response->message)) {
                Mage::helper('pap')->log('Connection problem at ' . $url . ': ' . $response->message);
                throw Mage::exception('Qualityunit_Pap_Model', 'Connection problem at ' . $url . ': ' . $response->message);
                return false;
            }
            Mage::helper('pap')->log('Error connecting to ' . $url);
            Mage::throwException('Error connecting to ' . $url);
            return false;
        }

        $session = '';
        foreach ($response->fields as $field) {
            if ($field[0] == 'S') {
                $session = $field[1];
                break;
            }
        }
        if (empty($session)) {
            Mage::helper('pap')->log('No session found in response');
            throw Mage::exception('Qualityunit_Pap_Model', 'No session found in response');
            return false;
        }

        $this->papSession = $session;
        return $this->papSession;
    }

    public function connectExternal($url, $query, $method = Zend_Http_Client::POST, Zend_Http_Client_Adapter_Interface $httpAdapter = null) {
        if ($method == 'GET') {
            $url .= '?' . $query;
            $httpAdapter->write($method, $url);
        } else {
            $httpAdapter->write($method, $url, '1.1', array(
                    'Connection: close'
            ), $query);
        }

        try {
            $result = $httpAdapter->read();
            return Zend_Http_Response::extractBody($result);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function refundCommissions($order, $refunded = array()) {
        $config = Mage::getSingleton('pap/config');
        if (!$config->isAutoStatusChangeEnabled()) {
            Mage::helper('pap')->log('Automatic status change is not enabled.');
            return false;
        }

        Mage::helper('pap')->log('Starting refund...');
        try {
            $session = $this->getSession();
        } catch (Mage_Core_Exception $e) {
            return false;
        }

        $query = 'D=' . urlencode('{"C":"Gpf_Rpc_Server","M":"run","requests":[{"C":"Pap_Merchants_Transaction_TransactionsGrid","M":"getRows",' . '"sort_col":"dateinserted","sort_asc":false,"offset":0,"limit":900,"filters":[["t_orderid","L","' . $order->getIncrementId() . '("]],"columns":[["id"],["id"],["commission"],["totalcost"],["t_orderid"],["productid"],["dateinserted"],["name"],["rtype"],' . '["tier"],["commissionTypeName"],["rstatus"],["payoutstatus"],["firstname"],["lastname"],["userid"],["channel"],["actions"]]}],' . '"S":"' . $session . '"}');

        try {
            $adapter = new Varien_Http_Adapter_Curl();
            $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
            $response = json_decode($response);
            unset($adapter);
        } catch (Exception $e) {
            Mage::helper('pap')->log('Could not load transaction for refund: ' . $e->getMessage());
            return false;
        }

        $refundIDs = array();
        foreach ($response[0]->rows as $record) {
            // ["id","userid","commission","totalcost","t_orderid","productid","dateinserted","name","rtype","commissionTypeName","tier","firstname","lastname","rstatus","payoutstatus","channel"]
            if ($i == 1) {
                $i++;
                continue;
            }
            if (count($refunded)) {
                if (!in_array($record[5], $refunded)) {
                    continue;
                }
                $refundIDs[] = $record[0];
            }
        }

        if (empty($refundIDs)) {
            Mage::helper('pap')->log('There is nothing to refund!');
            return true;
        }

        $query = 'D=' . urlencode('{"C":"Gpf_Rpc_Server", "M":"run", "requests":[{"C":"Pap_Merchants_Transaction_TransactionsForm",' . '"M":"makeRefundChargeback", "status":"R", "merchant_note":"refunded from Magento API", "refund_multitier":"Y",' . '"ids":["' . implode('","', $ids) . '"]}], "S":"' . $session . '"}');
        try {
            Mage::helper('pap')->log('Trying to refund IDs: ' . print_r($refundIDs, true));
            $adapter = new Varien_Http_Adapter_Curl();
            $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
            $response = json_decode($response);
            unset($adapter);
        } catch (Exception $e) {
            Mage::helper('pap')->log('Problem when refunding transactions: ' . $e->getMessage());
            return false;
        }

        if (!isset($response[0]->success) || $response[0]->success != 'Y') {
            $err = '';
            if (isset($response[0]->message)) {
                $err = ': ' . $response[0]->message;
            }
            Mage::helper('pap')->log('An error occurred while refunding' . $err);
            return false;
        }
        Mage::helper('pap')->log('Refund successful');
        return true;
    }

    public function setOrderStatus($order, $status, $refunded = array()) {
        $config = Mage::getSingleton('pap/config');
        if (!$config->isAutoStatusChangeEnabled()) {
            Mage::helper('pap')->log('Automatic status change is not enabled.');
            return false;
        }

        Mage::helper('pap')->log('Changing status of order ' . $order->getIncrementId() . " to '$status'");
        try {
            $session = $this->getSession();
        } catch (Mage_Core_Exception $e) {
            return false;
        }

        $query = 'D=' . urlencode('{"C":"Gpf_Rpc_Server","M":"run","requests":[{"C":"Pap_Merchants_Transaction_TransactionsGrid","M":"getRows",' . '"sort_col":"dateinserted","sort_asc":false,"offset":0,"limit":900,"filters":[["t_orderid","L","' . $order->getIncrementId() . '("]],"columns":[["id"],["id"],["commission"],["totalcost"],["t_orderid"],["productid"],["dateinserted"],["name"],["rtype"],' . '["tier"],["commissionTypeName"],["rstatus"],["payoutstatus"],["firstname"],["lastname"],["userid"],["channel"],["actions"]]}],' . '"S":"' . $session . '"}');

        try {
            $adapter = new Varien_Http_Adapter_Curl();
            $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
            $response = json_decode($response);
            unset($adapter);
        } catch (Exception $e) {
            Mage::helper('pap')->log('Could not load transactions for status change: ' . $e->getMessage());
            return false;
        }

        $ids = array();
        $refundIDs = array();
        $approveIDs = array();
        $i = 1;
        foreach ($response[0]->rows as $record) {
            // ["id","userid","commission","totalcost","t_orderid","productid","dateinserted","name","rtype","commissionTypeName","tier","firstname","lastname","rstatus","payoutstatus","channel"]
            if ($i == 1) {
                $i++;
                continue;
            }
            if (count($refunded)) {
                if ($status == 'A') {
                    if (in_array($record[5], $refunded)) {
                        $refundIDs[] = $record[0];
                    } else {
                        $approveIDs[] = $record[0];
                    }
                    continue;
                }
            }
            $ids[] = $record[0];
        }

        if (count($refundIDs) == 0 && count($approveIDs) == 0 && count($ids) == 0) { // unprocessed transactions
            $items = $order->getAllVisibleItems();
            foreach ($items as $i => $item) {
                $productid = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($productid);

                if ($status == $this->approved) {
                    if (count($refunded) && in_array($product->getSku(), $refunded)) { // if we are refunding only specific order items
                        $this->changeStatusByOrderId($session, $order->getIncrementId() . "($i)", 'D');
                        continue;
                    }
                    $this->changeStatusByOrderId($session, $order->getIncrementId() . "($i)", 'A');
                }
                if ($status == $this->declined) {
                    if (count($refunded) && !in_array($product->getSku(), $refunded)) { // if we are refunding only specific order items
                        continue;
                    }
                    $this->changeStatusByOrderId($session, $order->getIncrementId() . "($i)", 'D');
                }
            }
            Mage::helper('pap')->log('Status of unprocessed commissions has been changed.');
            return;
        }

        try {
            Mage::helper('pap')->log('We will be changing status of IDs: ' . print_r($ids, true));
            if (!empty($refundIDs)) {
                $query = $this->getJSONRequestChangeStatus('D', $refundIDs, $session);
                try {
                    $adapter = new Varien_Http_Adapter_Curl();
                    $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
                    $response = json_decode($response);
                    unset($adapter);
                } catch (Exception $e) {
                    Mage::helper('pap')->log('Error occurred when changing status: ' . $e->getMessage());
                    //return false;
                }
            }
            if (!empty($approveIDs)) {
                $query = $this->getJSONRequestChangeStatus('A', $approveIDs, $session);
                try {
                    $adapter = new Varien_Http_Adapter_Curl();
                    $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
                    $response = json_decode($response);
                    unset($adapter);
                } catch (Exception $e) {
                    Mage::helper('pap')->log('Error occurred when changing status: ' . $e->getMessage());
                    //return false;
                }
            }

            $query = $this->getJSONRequestChangeStatus($status, $ids, $session);
            try {
                $adapter = new Varien_Http_Adapter_Curl();
                $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
                $response = json_decode($response);
                unset($adapter);
            } catch (Exception $e) {
                Mage::helper('pap')->log('Error occurred when changing status: ' . $e->getMessage());
                return false;
            }

            Mage::helper('pap')->log('Status has been changed.');
            return true;
        } catch (Exception $e) {
            Mage::helper('pap')->log('API error while status changing: ' . $e->getMessage());
            return false;
        }
    }

    private function safeString($str) {
        return urlencode($str);
    }

    private function getStatus($state) {
        if ($state === Mage_Sales_Model_Order::STATE_PENDING_PAYMENT || $state === Mage_Sales_Model_Order::STATE_NEW || $state === Mage_Sales_Model_Order::STATE_PROCESSING) {
            return $this->pending;
        }
        if ($state === Mage_Sales_Model_Order::STATE_COMPLETE) {
            return $this->approved;
        }
        return $this->declined;
    }

    public function getOrderSaleDetails($order) {
        $config = Mage::getSingleton('pap/config');

        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $couponcode = $quote->getCouponCode();

        $sales = array();
        $status = $this->getStatus($order->getState());

        if ($config->isPerProductEnabled()) { // per product tracking
            $items = $order->getAllVisibleItems();

            foreach ($items as $i => $item) {
                $productid = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($productid);

                $sales[$i] = array();
                $subtotal = ($item->getBaseRowTotal() == '') ? $item->getBasePrice() : $item->getBaseRowTotal();
                $discount = abs($item->getBaseDiscountAmount());

                $sales[$i]['totalcost'] = $subtotal - $discount;
                $sales[$i]['orderid'] = $order->getIncrementId();
                $sales[$i]['productid'] = $this->safeString($product->getSku());
                $sales[$i]['couponcode'] = $couponcode;
                $sales[$i]['status'] = $status;
                $sales[$i]['campaignid'] = $config->getCampaignID();

                for($n = 1; $n < 6; $n++) {
                    if ($config->getData($n)) {
                        $sales[$i]['data' . $n] = $this->changeExtraData($config->getData($n), $order, $item, $product);
                    }
                }
            }
        } else { // per order tracking
            $sales[0] = array();

            $subtotal = $order->getBaseSubtotal();
            $discount = abs($order->getBaseDiscountAmount());

            $sales[0]['totalcost'] = $subtotal - $discount;
            $sales[0]['orderid'] = $order->getIncrementId();
            $sales[0]['productid'] = null;
            $sales[0]['couponcode'] = $couponcode;
            $sales[0]['status'] = $status;
            $sales[0]['campaignid'] = $config->getCampaignID();

            for($n = 1; $n < 6; $n++) {
                if ($config->getData($n)) {
                    $sales[0]['data' . $n] = $this->changeExtraData($config->getData($n), $order, null, null);
                }
            }
        }

        return $sales;
    }

    public function createAffiliate($order, $onlyOrderID = false) {
        $config = Mage::getSingleton('pap/config');
        if (!$config->isCreateAffiliateEnabled()) {
            Mage::helper('pap')->log('Affiliate creation is not enabled.');
            return false;
        }

        if ($onlyOrderID) {
            $order = Mage::getModel('sales/order')->load($order);
        }

        $products = $config->getCreateAffiliateProducts();
        if (sizeof($products) > 0) {
            // conditional only
            $items = $order->getAllVisibleItems();
            $search = false;
            foreach ($items as $i => $item) {
                if (in_array($item->getProductId(), $products)) {
                    $search = true;
                    break; // end of search, we have it
                }
            }
            if (!$search) {
                return false;
            }
        }

        // create affiliate
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        Mage::helper('pap')->log('Starting affiliate creation...');
        try {
            $session = $this->getSession();
        } catch (Mage_Core_Exception $e) {
            return false;
        }

        $query = 'D=' . urlencode('{"C":"Gpf_Rpc_Server", "M":"run", "requests":[{"C":"Pap_Signup_AffiliateForm", "M":"add",' . '"fields":[["name","value"],["Id",""],["username","' . $order->getCustomerEmail() . '"],["firstname","' . $order->getCustomerFirstname() . '"],["lastname","' . $order->getCustomerLastname() . '"],["agreeWithTerms","Y"],');

        if (isset($_COOKIE['PAPVisitorId'])) {
            $query .= '["visitorId","' . $_COOKIE['PAPVisitorId'] . '"],';
        }

        $address = $customer->getPrimaryAddress('default_billing');
        if (!empty($address)) {
            $addressArray = $address->getData();
            $query .= urlencode('["data3","' . $addressArray['street'] . '"],["data4","' . $addressArray['city'] . '"],' . '["data5","' . $addressArray['region'] . '"],["data6","' . $addressArray['country_id'] . '"],' . '["data7","' . $addressArray['postcode'] . '"],["data8","' . $addressArray['telephone'] . '"]');
        }

        $query .= urlencode(']}], "S":"' . $session . '"}');
        try {
            $adapter = new Varien_Http_Adapter_Curl();
            $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
            $response = json_decode($response);
            unset($adapter);

            if (!isset($response[0]->success) || $response[0]->success != 'Y') {
                $err = '';
                if (isset($response[0]->message)) {
                    $err = ': ' . $response[0]->message;
                }
                Mage::helper('pap')->log('Error creating affiliate ' . $err);
                return false;
            } else {
                Mage::helper('pap')->log($response[0]->message);
            }
        } catch (Exception $e) {
            Mage::helper('pap')->log('Could not create an affiliate: ' . $e->getMessage());
            return false;
        }
    }

    public function registerOrderByID($orderid, $realid = true) { // called from the checkout observer
        $order = Mage::getModel('sales/order')->load($orderid);
        if ($realid) {
            $order->load($orderid);
        } else {
            $order->loadByIncrementId($orderid);
        }

        $this->registerOrder($order);
    }

    public function registerOrder($order, $visitorID = '') {
        if ($order) {
            $orderid = $order->getId();
        } else {
            Mage::helper('pap')->log('Order empty');
            return false;
        }
        Mage::helper('pap')->log("Loading details of order $orderid");

        $items = $this->getOrderSaleDetails($order);
        $this->registerSaleDetails($items, $visitorID);
    }

    public function registerSaleDetails($items, $visitorID = '') {
        $config = Mage::getSingleton('pap/config');

        foreach ($items as $i => $item) {
            Mage::helper('pap')->log('Registering sale ' . $item['orderid'] . "($i)");
            $sale = '[{"ac":"","t":"' . $item['totalcost'] . '","o":"' . $item['orderid'] . "($i)" . '","p":"' . $item['productid'] . '","s":"' . $item['status'] . '"';
            if ($item['couponcode'])
                $sale .= ',"cp":"' . $item['couponcode'] . '"';
            if ($item['data1'])
                $sale .= ',"d1":"' . $item['data1'] . '"';
            if ($item['data2'])
                $sale .= ',"d2":"' . $item['data2'] . '"';
            if ($item['data3'])
                $sale .= ',"d3":"' . $item['data3'] . '"';
            if ($item['data4'])
                $sale .= ',"d4":"' . $item['data4'] . '"';
            if ($item['data5'])
                $sale .= ',"d5":"' . $item['data5'] . '"';
            $sale .= '}]';

            $query = 'visitorId=' . $visitorID . '&accountId=' . $config->getAPICredential('account') . '&tracking=1&url=H_' . urlencode($_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']) . '&referrer=' . urlencode($_SERVER['HTTP_REFERER']) . '&getParams=' . urlencode($_SERVER['QUERY_STRING']) . '&isInIframe=false' . '&sale=' . urlencode($sale) . '&cookies=';

            try {
                $adapter = new Varien_Http_Adapter_Curl();
                $response = $this->connectExternal('http://' . $config->getInstallationPath() . '/scripts/track.php', $query, 'GET', $adapter);
            } catch (Exception $e) {
                Mage::helper('pap')->log('Error registering sale: ' . $e->getMessage());
                return false;
            }
        }
        Mage::helper('pap')->log('Sale tracking finished');
    }

    private function changeStatusByOrderId($session, $orderid, $status) {
        $config = Mage::getSingleton('pap/config');
        $query = $this->getJSONRequestChangeStatus($status, $orderid, $session, true);
        try {
            $adapter = new Varien_Http_Adapter_Curl();
            $response = $this->connectExternal($config->getAPIPath(), $query, 'POST', $adapter);
            $response = json_decode($response);
        } catch (Exception $e) {
            Mage::helper('pap')->log('Could not initiate API session: ' . $e->getMessage());
            return false;
        }
    }

    private function getJSONRequestChangeStatus($status, $ids, $session, $perOrderId = false) {
        $method = 'changeStatus';
        if ($perOrderId) {
            $fields = '"orderid":"' . $ids . '"'; // only one order ID
            $method = 'changeStatusPerOrderId';
        } else {
            $fields = '"ids":["' . implode('","', $ids) . '"]'; // array of IDs
        }
        return 'D=' . urlencode('{"C":"Gpf_Rpc_Server","M":"run","requests":[{"C":"Pap_Merchants_Transaction_TransactionsForm","M":"' . $method . '","merchant_note":"status changed automatically","status":"' . $status . '", ' . $fields . '}],"S":"' . $session . '"}');
    }

    public function changeExtraData($data, $order, $item, $product) {
        switch ($data) {
            case 'empty':
                return null;
                break;
            case 'itemName':
                return (!empty($item)) ? $this->safeString($item->getName()) : null;
                break;
            case 'itemQuantity':
                return (!empty($item)) ? $item->getQtyOrdered() : null;
                break;
            case 'itemPrice':
                if (!empty($item)) {
                    $rowtotal = $item->getBaseRowTotal();
                    if (empty($rowtotal)) {
                        return $item->getBasePrice();
                    }
                    return $rowtotal;
                }
                return null;
                break;
            case 'itemSKU':
                return (!empty($item)) ? $this->safeString($item->getSku()) : null;
                break;
            case 'itemWeight':
                return (!empty($item)) ? $item->getWeight() : null;
                break;
            case 'itemWeightAll':
                return (!empty($item)) ? $item->getRowWeight() : null;
                break;
            case 'itemCost':
                return (!empty($item)) ? $item->getCost() : null;
                break;
            case 'itemDiscount':
                return (!empty($item)) ? abs($item->getBaseDiscountAmount()) : null;
                break;
            case 'itemDiscountPercent':
                return (!empty($item)) ? $item->getDiscountPercent() : null;
                break;
            case 'itemTax':
                return (!empty($item)) ? $item->getTaxAmount() : null;
                break;
            case 'itemTaxPercent':
                return (!empty($item)) ? $item->getTaxPercent() : null;
                break;
            case 'productCategoryID':
                return (!empty($product)) ? $product->getCategoryId() : null;
                break;
            case 'productURL':
                return (!empty($product)) ? $this->safeString($product->getProductUrl(false)) : null;
                break;
            case 'storeID':
                return (!empty($order)) ? $order->getStoreId() : null;
                break;
            case 'internalOrderID':
                return (!empty($order)) ? $order->getId() : null;
                break;
            case 'customerID':
                return (!empty($order) && $order->getCustomerId()) ? $order->getCustomerId() : null;
                break;
            case 'customerEmail':
                return (!empty($order) && $order->getCustomerEmail()) ? $order->getCustomerEmail() : null;
                break;
            case 'customerName':
                $name = '';
                if (!empty($order)) {
                    $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerMiddlename() . ' ' . $order->getCustomerLastname();
                }
                return (!empty($name)) ? $name : null;
                break;
            case 'couponCode':
                return (!empty($order) && $order->getQuoteId()) ? Mage::getModel('sales/quote')->load($order->getQuoteId())->getCouponCode() : null;
                break;
            default:
                return $data;
        }
    }
}