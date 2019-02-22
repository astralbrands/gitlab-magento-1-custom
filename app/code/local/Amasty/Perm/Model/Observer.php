<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Model_Observer
{
    protected $_permissibleActions = array('index', 'grid', 'exportCsv', 'exportExcel');
    protected $_exportActions = array('exportCsv', 'exportExcel');
    protected $_controllerNames = array('sales_', 'orderspro_', 'adminhtml_sales_');

    public function handleAdminUserSaveAfter($observer) 
    {
        if ($user = $this->isUser($observer)) {
            $ids = $user->getSelectedCustomers();
            if (!is_null($ids)) {
                $ids = Mage::helper('adminhtml/js')->decodeGridSerializedInput($ids);
                Mage::getModel('amperm/perm')->assignCustomers($user->getId(), $ids);
            }
        }
        return $this;
    }
    
    public function handleOrderCollectionLoadBefore($observer) 
    {
        if ('amperm' == Mage::app()->getRequest()->getModuleName())
            return $this;
            
        $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
        $collection = $observer->getOrderGridCollection();
        $column = 'entity_id';
        if (!$collection) {
            $collection = $observer->getOrderInvoiceGridCollection();
            $column = 'order_id';
        }
        if (!$collection) {
            $collection = $observer->getOrderShipmentGridCollection();
            $column = 'order_id';
        }
        if (!$collection) {
            $collection = $observer->getOrderCreditmemoGridCollection();
            $column = 'order_id';
        }
        if ($collection
            && !$this->_isJoined($collection->getSelect()->getPart('from'), 'amperm_order')
            && !$this->_isJoined($collection->getSelect()->getPart('from'), 'amperm')
        ) {
            if ($uid) {
                $permissionManager = Mage::getModel('amperm/perm');
                $permissionManager->addOrdersRestriction($collection, $uid, false, $column);
            } else {
                //$collection->getSelect()->joinLeft(
                //    array('amperm' => Mage::getSingleton('core/resource')->getTableName('amperm/order')),
                //    'main_table.entity_id = amperm.oid',
                //    array('am_uid' => 'uid')
                //);
            }
        }
        
        return $this;    
    }
    
    public function handleCustomerCollectionLoadBefore($observer) 
    {
        $collection = $observer->getCollection();
        if (false !== strpos(get_class($collection), 'Customer_Collection')) {
            $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
            if (false === strpos(Mage::app()->getRequest()->getControllerName(), 'orderexport')
                && !$this->_isJoined($collection->getSelect()->getPart('from'), 'amperm')
            ) {
                if ($uid) {
                    if (!Mage::getStoreConfig('amperm/general/show_all_customers')) {
                        $permissionManager = Mage::getModel('amperm/perm');
                        $permissionManager->addCustomersRestriction($collection, $uid);
                    }
                } else {
                    //$collection->getSelect()
                    //    ->joinLeft(
                    //        array('amperm' => Mage::getSingleton('core/resource')->getTableName('amperm/perm')),
                    //        'e.entity_id = amperm.cid',
                    //        array('am_uid' => 'uid')
                    //    );
                }
            }
        }

        return $this;    
    } 
       
    public function handleCustomerSaveAfter($observer) 
    {
        $posted = array_key_exists('sales_person', Mage::app()->getRequest()->getParams());
        $uid = Mage::app()->getRequest()->getParam('sales_person');
        $user = Mage::getSingleton('admin/session')->getUser();

        if ($uid) {
            Mage::getModel('amperm/perm')->assignOneCustomer($uid, $observer->getCustomer()->getId());
        } elseif ($posted) {
            Mage::getModel('amperm/perm')->removeOneCustomer($observer->getCustomer()->getId());
        } elseif ($user
            && ('customer' == Mage::app()->getRequest()->getControllerName()
                || 'sales_order_create' == Mage::app()->getRequest()->getControllerName())
        ) {
            if (Mage::helper('amperm')->isSalesPerson($user)) { // creation of customer by dealer
                $userByCustomerId = Mage::getModel('amperm/perm')->getUserByCustomer($observer->getCustomer()->getId());
                if (!$userByCustomerId
                    || ($userByCustomerId && $userByCustomerId == $user->getId())
                ) {
                    Mage::getModel('amperm/perm')->assignOneCustomer($user->getId(), $observer->getCustomer()->getId());
                }
            }
        }
               
        return $this; 
    }
    
    public function handleOrderCreated($observer)
    {
        if (Mage::registry('amperm_processed')
        || 'addComment' == Mage::app()->getRequest()->getActionName()
        || ('sales_order_save_after' == $observer->getEvent()->getName()
            && 'Mage_Checkout' == Mage::app()->getRequest()->getControllerModule())) {
            return $this;
        }
        
        $user = null;
        
        $isGuest = true;
        $orders = $observer->getOrders(); // multishipping
        if (!$orders) { // all other situations like single checkout, google checkout, admin
            $orders = array($observer->getOrder());
        }
        if (is_object($orders[0])) { // no order if recurring profiles
            $isGuest = $orders[0]->getCustomerIsGuest();
        }
        
        if (Mage::getModel('amperm/perm')->getUserByOrder($orders[0]->getId())) {
            return $this;
        }
        
        if ($this->_isAdmin()) {
            if (Mage::getStoreConfig('amperm/general/backend_by_customer')) {
                $uid = Mage::getModel('amperm/perm')->assignOrderByCustomer($orders[0]->getCustomerId(), $orders[0]->getId());
                $user = Mage::getModel('admin/user')->load($uid);
                if (!$user || !$user->getId()) {
                    $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
                    if ($uid) {
                        Mage::getModel('amperm/perm')->assignOneOrder($uid, $orders[0]->getId());
                        $user = Mage::getSingleton('admin/session')->getUser();
                    }
                }
            } else {
                $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
                if ($uid) {
                    Mage::getModel('amperm/perm')->assignOneOrder($uid, $orders[0]->getId());
                    $user = Mage::getSingleton('admin/session')->getUser();
                } else {
                    $uid = Mage::getModel('amperm/perm')->assignOrderByCustomer($orders[0]->getCustomerId(), $orders[0]->getId());
                    $user = Mage::getModel('admin/user')->load($uid);
                }
            }
        } elseif (!$isGuest) {
            foreach ($orders as $order) {
                $uid = Mage::getModel('amperm/perm')->assignOrderByCustomer($order->getCustomerId(), $order->getId());
            }
            $user = Mage::getModel('admin/user')->load($uid);
        }

        if ((!$user || !$user->getId())
            && $uid = Mage::getStoreConfig('amperm/general/default_dealer')) {
            $user = Mage::getModel('admin/user')->load($uid);
            if ($user->getId()
                && Mage::helper('amperm')->isSalesPerson($user)) {
                Mage::getModel('amperm/perm')->assignOneOrder($uid, $orders[0]->getId());
            }
        }

        if (Mage::getSingleton('customer/session')->getSalesPerson()
            && Mage::getStoreConfig('amperm/frontend/on_checkout')) {
            $uid = Mage::getSingleton('customer/session')->getSalesPerson();
            Mage::getSingleton('customer/session')->setSalesPerson(0);
            Mage::getModel('amperm/perm')->assignOneOrder($uid, $orders[0]->getId());
        }

        // add email to queue
        if (Mage::getStoreConfig('amperm/general/send_email')
            && $user
            && $user->getId()) {
            
        	$emails = array(
        		$user->getEmail()
        	);
        	$additionalEmails = $user->getEmails();
        	if (!empty($additionalEmails)) {
        		$additionalEmails = explode(",", $additionalEmails);
        		if (is_array($additionalEmails)) {
        			foreach ($additionalEmails as $email) {
        				$emails[] = trim($email);
        			}
        		}
        	}
            $emails = implode(',', $emails);

            $orderIds = array();
            foreach ($orders as $order) {
                $orderIds[] = $order->getId();
            }
            $orderIds = implode(',', $orderIds);
            $queue = Mage::getModel('amperm/queue');
            $queue->addData(
                array(
                    'emails' => $emails,
                    'order_ids' => $orderIds
                )
            );
            $queue->save();
        }
        Mage::register('amperm_processed', true, true);
        return $this;
    }
    
    public function handleCoreCollectionAbstractLoadBefore($observer)
    {
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Customer_Model_Resource_Group_Collection) {
            $user = Mage::getSingleton('admin/session')->getUser();
            if ($user
                && Mage::helper('amperm')->isSalesPerson($user)
            ) {
                $allowedGroups = explode(',', $user->getCustomerGroupId());
                if (!in_array('0', $allowedGroups)) {
                    $collection->getSelect()
                        ->where('customer_group_id in (?)', $allowedGroups);
                }
            }
        }

        if (!Mage::helper('ambase')->isVersionLessThan(1, 4, 2))
            return;
        
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection)
        {
            $mod  = Mage::app()->getRequest()->getModuleName();
            $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
            if ($uid && 'amperm' != $mod){
                $permissionManager = Mage::getModel('amperm/perm');
                if ($collection){
                    $permissionManager->addOrdersRestriction($collection, $uid);
                }
            }
        }
    }
       
    protected function _isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin())
            return true;
        // for some reason isAdmin does not work here
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create')
            return true;
            
        return false;
    }
   
    protected function _isControllerName($place)
    {
        if ('customer' == $place)
            return true;

        $found = false;
        foreach ($this->_controllerNames as $controllerName) {
            if (Mage::app()->getRequest()->getControllerName() == $controllerName . $place) {
                $found = true;
            }
        }
        return $found;
    }

    protected function _prepareColumns(&$grid, $export = false, $place = 'order', $after = 'entity_id')
    {
        if (!$this->_isControllerName($place) ||
            !in_array(Mage::app()->getRequest()->getActionName(), $this->_permissibleActions))
            return $grid;

        $column = array(
            'header'   => Mage::helper('amperm')->__('Dealer'),
            'type'     => 'options',
            'align'    => 'center',
            'index'    => 'am_uid',
            'options'  => Mage::helper('amperm')->getSalesPersonList(true),
            'sortable' => false,
            'filter_condition_callback' => array('Amasty_Perm_Block_Adminhtml_Relation', 'dealerFilter'),
        );
        $grid->addColumnAfter($column['index'], $column, $after);

        return $grid;
    }

    public function handleCoreLayoutBlockCreateAfter($observer)
    {
        $block = $observer->getBlock();
        $hlp = Mage::helper('amperm');
        $uid = $hlp->getCurrentSalesPersonId();

        if (!$uid) {
            $gridClass = Mage::getConfig()->getBlockClassName('adminhtml/sales_order_grid');
            if ($gridClass == get_class($block)) {
                $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions));
            }
            $gridClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_grid');
            if ($gridClass == get_class($block)) {
                $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions), 'customer');
            }

        }

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit');
        if ($blockClass == get_class($block)) {
            $customer = Mage::registry('current_customer');
            if ($customer->getId()
                && Mage::getSingleton('admin/session')->isAllowed('customer/manage/login_as_customer')) {
                $url = $this->_getLoginUrl($customer);
                $block->addButton('customer_login', array(
                    'label'   => Mage::helper('amperm')->__('Log In as Customer'),
                    'onclick' => 'window.open(\'' . $url . '\', \'customer\');',
                    'class'   => 'back',
                ), 0, 1);
            }
        }
    }

    protected function _getLoginUrl($customer)
    {
        $customerId = $customer->getId();
        $key = $customer->getPasswordHash();
        $permKey = md5($customerId . $key);
        $action = Mage::getSingleton('customer/config_share')->isWebsiteScope() ? 'login' : 'index';
        return Mage::helper('adminhtml')->getUrl('adminhtml/ampermlogin/' . $action, array('customer_id' => $customerId, 'perm_key' => $permKey));
    }

    public function handleCoreBlockAbstractToHtmlAfter($observer)
    {
        $block = $observer->getBlock();
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        $hlp = Mage::helper('amperm');

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/sales_order_view_info');
        if ($blockClass == get_class($block)
            && Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/assign_order')
            && false === strpos($html, 'amperm_form')
            && Mage::getStoreConfig('amperm/general/reassign_fields')
            && $this->_isControllerName('order')) {
            $tempPos = strpos($html, '<!--Account Information-->');
            if (false !== $tempPos) {
                $pos = strpos($html, '</table>', $tempPos);
                $insert = Mage::app()->getLayout()->createBlock('amperm/adminhtml_info')->setOrderId($block->getOrder()->getId())->toHtml();
                $html = substr_replace($html, $insert, $pos-1, 0);
            }
        }

        $storeId = Mage::app()->getStore()->getId();
        $blockClass = Mage::getConfig()->getBlockClassName('customer/form_register');
        if ($blockClass == get_class($block)
            && Mage::getStoreConfig('amperm/frontend/on_registration', $storeId)
            && false === strpos($html, 'name="sales_person"')) {
            $pos = strpos($html, '<div class="buttons-set');
            $insert = Mage::app()->getLayout()->createBlock('amperm/select')->toHtml();
            $html = substr_replace($html, $insert, $pos-1, 0);
        }

        $blockClass = Mage::getConfig()->getBlockClassName('customer/form_edit');
        if ($blockClass == get_class($block)
            && Mage::getStoreConfig('amperm/frontend/in_account', $storeId)
            && false === strpos($html, 'name="sales_person"')) {
            $pos = strpos($html, '<div class="buttons-set');
            $insert = Mage::app()->getLayout()->createBlock('amperm/select')->toHtml();
            $html = substr_replace($html, $insert, $pos-1, 0);
        }

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit_tab_view');
        if ($blockClass == get_class($block)) {
            $customer = Mage::registry('current_customer');
            $dealerId = Mage::getModel('amperm/perm')->getUserByCustomer($customer->getId());
            $name = $hlp->__('Not Assigned');
            if ($dealerId) {
                $dealer = Mage::getModel('admin/user')->load($dealerId);
                $name = $dealer->getFirstname() . ' ' . $dealer->getLastname();
            }
            $pos = strpos($html, '</table>');
            $insert = '
                <tr>
                    <td><strong>' . $hlp->__('Dealer') . ':</strong></td>
                    <td>' . $name .'</td>
                </tr>
            ';
            $html = substr_replace($html, $insert, $pos-1, 0);
        }

        $user = Mage::getSingleton('admin/session')->getUser();

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit_tab_account');
        if ($user
            && !$hlp->isSalesPerson($user)
            && $blockClass == get_class($block)) {
            $pos = strpos($html, '_accountgroup_id');
            $pos = strpos($html, '</tr>', $pos);
            $dealers = $hlp->getSalesPersonList();
            $customer = Mage::registry('current_customer');
            $dealerId = Mage::getModel('amperm/perm')->getUserByCustomer($customer->getId());
            $insert = '
                <tr>
                    <td class="label"><label for="sales_person">' . $hlp->__('Dealer') . '</label></td>
                    <td class="value">
                        <select id="sales_person" name="sales_person" class=" select">
                            <option value="" ' . (!$dealerId ? 'selected="selected" ' : '') . '></option>
            ';
            foreach ($dealers as $userId => $name) {
                $insert .= '<option value="' . $userId . '" ' . ($userId == $dealerId ? 'selected="selected" ' : '') . '>' . $name . '</option>';
            }
            $insert .= '
                        </select>
                    </td>
                </tr>
            ';
            $html = substr_replace($html, $insert, $pos + 5, 0);
        }

        $blockClass = Mage::getConfig()->getBlockClassName('checkout/onepage_billing');
        if ($blockClass == get_class($block)
        && Mage::getStoreConfig('amperm/frontend/on_checkout', $storeId)) {
            $pos = strpos($html, '<div class="buttons-set"');
            $insert = Mage::app()->getLayout()->createBlock('amperm/select')->toHtml();
            $html = substr_replace($html, $insert, $pos - 1, 0);
        }

        $transport->setHtml($html);
    }

    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        $hlp = Mage::helper('amperm');
        $user = Mage::getSingleton('admin/session')->getUser();

        $massactionClass = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $customerGridClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_grid');
		if(isset($block) && null!==$block->getParentBlock()){
        $parentClass = get_class($block->getParentBlock());
		}
        if (Mage::getSingleton('admin/session')->isAllowed('customer/manage/assign_dealer')
            && $user
            && !$hlp->isSalesPerson($user)
            && $massactionClass == get_class($block)
            && $parentClass == $customerGridClass) {
            $block->addItem('assign_dealer', array(
                'label'      => $hlp->__('Assign to Dealer'),
                'url'        => Mage::helper('adminhtml')->getUrl('adminhtml/ampermassigncustomer/massAssign'),
                'additional' => array('amperm_value' => array(
                    'name'   => 'amperm_value',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $hlp->__('Dealer'),
                    'values' => $hlp->getSalesPersonList(),
                )),
            ));
        }

        $user = Mage::registry('permissions_user');

        if (!$user)
            return $this;

        if (!$user->getId())
            return $this;

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/permissions_user_edit_tabs');
        if ($blockClass == get_class($block)
            && Mage::helper('amperm')->isSalesPerson($user)) {

            if (!Mage::getStoreConfig('amperm/general/edit_no_grid')) {
                $block->addTab('customers_section', array(
                    'label'     => $hlp->__('Manage Customers'),
                    'title'     => $hlp->__('Manage Customers'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('adminhtml/perm/relation', array('_current' => true)),
                ));
            }

            $block->addTab('orders_section', array(
                'label'     => $hlp->__('Reports'),
                'title'     => $hlp->__('Reports'),
                'class'     => 'ajax',
                'url'       => $block->getUrl('adminhtml/perm/reports', array('_current' => true)),
            ));

            $block->addTab('restrictions_section', array(
                'label'     => $hlp->__('Restrictions'),
                'title'     => $hlp->__('Restrictions'),
                'content'   => $block->getLayout()->createBlock('amperm/adminhtml_restrictions')->toHtml()));

            $block->addTab('additional_information', array(
                'label'     => $hlp->__('Additional'),
                'title'     => $hlp->__('Additional'),
                'content'   => $block->getLayout()->createBlock('amperm/adminhtml_additional')->toHtml()));
        }

        return $this;
    }

    public function onCoreBlockAbstractPrepareLayoutAfter($observer)
    {
        $block = $observer->getBlock();
        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/permissions_user_edit_tabs');
        if ($blockClass == get_class($block)
            && Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $block->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $this;
    }

    /**
     * @param array  $from     array('alias' => 'table_name')
     * @param string $check    table alias
     *
     * @return bool
     */
    protected function _isJoined($from, $check)
    {
        foreach ($from as $alias => $data) {
            if ($check === $alias) {
                return true;
            }
        }

        return false;
    }

    public function onSalesQuoteSaveAfter($observer)
    {
        if (array_key_exists('sales_person', Mage::app()->getRequest()->getPost())) {
            $dealerId = Mage::app()->getRequest()->getPost('sales_person');
            Mage::getSingleton('customer/session')->setSalesPerson($dealerId);
        }
    }

    public function onControllerActionPredispatch($observer)
    {
        $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
        if ($uid) {
            $flag = '';
            if ('sales_order' == Mage::app()->getRequest()->getControllerName()
                && 'view' == Mage::app()->getRequest()->getActionName()
            ) {
                $flag = 'order';
                $paramKey = 'order_id';
            } elseif (
                'customer' == Mage::app()->getRequest()->getControllerName()
                && 'edit' == Mage::app()->getRequest()->getActionName()
            ) {
                $flag = 'customer';
                $paramKey = 'id';
            }

            if ($flag) {
                $objectId = Mage::app()->getRequest()->getParam($paramKey);

                if ($objectId) {
                    $method = 'get' . ucfirst($flag) . 'Ids';
                    $assignedObjectsIds = Mage::getModel('amperm/perm')->getResource()->$method($uid);
                    if (!in_array($objectId, $assignedObjectsIds)) {
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amperm')->__('Access Denied'));
                        $page = Mage::getSingleton('admin/session')->getUser()->getStartupPageUrl();
                        $url = Mage::helper('adminhtml')->getUrl($page);
                        Mage::app()->getResponse()
                            ->setRedirect($url)
                            ->sendResponse();
                        Mage::helper('ambase/utils')->_exit(0);
                    }
                }
            }
        }
    }

    public function sendEmail()
    {
        if (!Mage::getStoreConfig('amperm/general/send_email')){
            return;
        }

        $collection = Mage::getResourceModel('amperm/queue_collection')
            ->addFieldToFilter('processed', 0);
        if (0 < $collection->getSize()) {
            foreach ($collection as $record) {
                $to = explode(',', $record->getEmails());
                $orderIds = explode(',', $record->getOrderIds());

                foreach ($orderIds as $id) {
                    $order = Mage::getModel('sales/order')->load($id);
                    if (!$order->getId()
                    || !Mage::helper('sales')->canSendNewOrderEmail($order->getStoreId())) {
                        continue;
                    }

                    $translate = Mage::getSingleton('core/translate');
                    $translate->setTranslateInline(false);

                    $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                        ->setIsSecureMode(true);
                    $paymentBlock->getMethod()->setStore($order->getStoreId());

                    $mailTemplate = Mage::getModel('core/email_template');
                    $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $order->getStoreId()))
                        ->sendTransactional(
                            Mage::getStoreConfig('sales_email/order/template', $order->getStoreId()),
                            Mage::getStoreConfig('sales_email/order/identity', $order->getStoreId()),
                            $to,
                            null,
                            array(
                                'order'         => $order,
                                'billing'       => $order->getBillingAddress(),
                                'payment_html'  => $paymentBlock->toHtml(),
                            )
                        );

                    $translate->setTranslateInline(true);
                }
                $record->setProcessed(1);
                $record->save();
            }
        }
    }

    protected function isUser($observer)
    {
        $editor = Mage::getSingleton('admin/session')->getUser();
        if (!$editor) { // API or smth else
            return false;
        }

        $user = $observer->getDataObject();
        if ($editor->getId() == $user->getId()) { // My Account
            return false;
        }
        return $user;
    }

    public function handleAdminUserSaveBefore($observer)
    {
        if ($user = $this->isUser($observer)) {
            $customerGroupId = '';
            if ($user->getCustomerGroupId()) {
                $customerGroupId = implode(',', $user->getCustomerGroupId());
            }
            $user->setCustomerGroupId($customerGroupId);
        }
        return $this;
    }
}