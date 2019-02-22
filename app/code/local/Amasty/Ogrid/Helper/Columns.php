<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */
class Amasty_Ogrid_Helper_Columns extends Mage_Core_Helper_Abstract
{

    protected static $_TYPE_CONFIGURABLE = 'configurable';
    protected static $_TYPE_DEFAULT = 'default';
    protected static $_TYPE_ATTRIBUTE= 'attribute';
    protected static $_TYPE_STATIC = 'static';

    protected $_orderTableAlias = 'am_order_item';
    protected $_extrOrderColumnPrefix = 'extra_col_';
    protected $_columns = NULL;
    protected $_staticColumns = NULL;

    protected $_configurableFields = NULL;
    protected $_defaultField = NULL;

    protected static $_collectionModified  = FALSE;

    protected function _getColumns()
    {
        if (!$this->_columns) {
            $sorted = array();
            $columns = Mage::helper('amogrid')->getColumns();

            foreach ($columns as $column) {
                if (isset($column['available']) && $column['available'] == 1) {

                    $position = $column['position'];

                    while (isset($sorted[$position])) {
                        $position++;
                    }
                    $sorted[$position] = $column;

                }
            }

            ksort($sorted);

            foreach($sorted as $column)
                $this->_columns[$column['key']] = $column;
        }
        return $this->_columns;
    }

    protected function _getStaticColumns()
    {
        if (!$this->_staticColumns) {
            $columns = Mage::helper('amogrid')->getColumns();

            foreach ($columns as $column) {
                if ($column['type'] == 'static' && !empty($column['relation'])) {
                    $this->_staticColumns[$column['key']] = $column;
                }
            }
        }
        return $this->_staticColumns;
    }

    protected function _getColumn($key, $def = NULL)
    {
        $columns = $this->_getColumns();
        return isset($columns[$key]) ? $columns[$key] : $def;
    }

    protected function _isColumnAvailable($key)
    {
        $ret = FALSE;
        $column = $this->_getColumn($key);
        if ($column) {
            $ret = TRUE;
        }
        return $ret;
    }

    function prepareOrderCollectionJoins(&$collection, $orderItemsColumns = array(), $requireAttributeJoin = false)
    {
        if (self::$_collectionModified)
            return ;
        self::$_collectionModified = TRUE;

        $showShipping = $this->_isColumnAvailable('am_shipping_description');//Mage::getStoreConfig('amogrid/general/shipping');
        $showPayment = $this->_isColumnAvailable('am_method');//Mage::getStoreConfig('amogrid/general/payment');
        $showCoupon = $this->_isColumnAvailable('am_coupon_code');//Mage::getStoreConfig('amogrid/general/coupon');
        $showCustomerEmail = $this->_isColumnAvailable('am_customer_email');//Mage::getStoreConfig('amogrid/general/customer_email');
        $showWeight = $this->_isColumnAvailable('am_weight');
        $showSubtotal = $this->_isColumnAvailable('am_base_subtotal');

        $showShippingAddress = $this->_isColumnAvailable('am_shipping_address') ||
                $this->_isColumnAvailable('am_shipping_country_id') ||
                $this->_isColumnAvailable('am_shipping_region') ||
                $this->_isColumnAvailable('am_shipping_postcode') ||
                $this->_isColumnAvailable('am_shipping_street') ||
                $this->_isColumnAvailable('am_shipping_company') ||
                $this->_isColumnAvailable('am_shipping_telephone') ||
                $this->_isColumnAvailable('am_shipping_city');

        $showBillingAddress = $this->_isColumnAvailable('am_billing_address') ||
                $this->_isColumnAvailable('am_billing_country_id') ||
                $this->_isColumnAvailable('am_billing_region') ||
                $this->_isColumnAvailable('am_billing_postcode') ||
                $this->_isColumnAvailable('am_billing_street') ||
                $this->_isColumnAvailable('am_billing_company') ||
                $this->_isColumnAvailable('am_billing_telephone') ||
                $this->_isColumnAvailable('am_billing_city');

        $showTrackInfo = $this->_isColumnAvailable('am_track_number');

        $showOrderTax = $this->_isColumnAvailable('tax_amount');

        $showInvoiceDate = $this->_isColumnAvailable('am_invoice_date');
        $showInvoiceId = $this->_isColumnAvailable('am_invoice_id');

        $showShipmentDate = $this->_isColumnAvailable('am_shipment_date');
        $showShipmentId = $this->_isColumnAvailable('am_shipment_id');

        $excludeStatuses = Mage::getStoreConfig('amogrid/general/exclude');
        $excludeStatuses = !empty($excludeStatuses) ? explode(',', $excludeStatuses) : array();

        if (!empty($orderItemsColumns) || $requireAttributeJoin) {
            $collection->getSelect()->join(
                array(
                    'order_item' => $collection->getTable('sales/order_item')
                ),
                'main_table.entity_id = order_item.order_id',
                array()
            );
        }

        if ($showCoupon || $showShipping || $showCustomerEmail || $showWeight || $showSubtotal) {
            $collection->getSelect()->join(
                array(
                    'order' => $collection->getTable('sales/order')
                ),
                'main_table.entity_id = order.entity_id',
                array(
                    'order.coupon_code as am_coupon_code',
                    'order.shipping_description as am_shipping_description',
                    'order.customer_email as am_customer_email',
                    'order.weight as am_weight',
                    'order.base_subtotal as am_base_subtotal')
            );
        }

        if ($showPayment) {
            $collection->getSelect()->joinLeft(
                array(
                    'order_payment' => $collection->getTable('sales/order_payment')
                ),
                'main_table.entity_id = order_payment.parent_id',
                array('order_payment.method as am_method')
            );
        }

        if ($showShippingAddress) {
            $collection->getSelect()->joinLeft(
                array(
                    'shipping_order_address' => $collection->getTable('sales/order_address')
                ),
                'main_table.entity_id = shipping_order_address.parent_id and shipping_order_address.address_type = \'shipping\'',
                array(
                    'shipping_order_address.country_id as am_shipping_country_id',
                    'shipping_order_address.region as am_shipping_region',
                    'shipping_order_address.postcode as am_shipping_postcode',
                    'shipping_order_address.street as am_shipping_street',
                    'shipping_order_address.company as am_shipping_company',
                    'shipping_order_address.telephone as am_shipping_telephone',
                    'shipping_order_address.city as am_shipping_city',
                )
            );
        }

        if ($showBillingAddress) {
            $collection->getSelect()->joinLeft(
                array(
                    'billing_order_address' => $collection->getTable('sales/order_address')
                ),
                'main_table.entity_id = billing_order_address.parent_id and billing_order_address.address_type = \'billing\'',
                array(
                    'billing_order_address.country_id as am_billing_country_id',
                    'billing_order_address.region as am_billing_region',
                    'billing_order_address.postcode as am_billing_postcode',
                    'billing_order_address.street as am_billing_street',
                    'billing_order_address.company as am_billing_company',
                    'billing_order_address.telephone as am_billing_telephone',
                    'billing_order_address.city as am_billing_city',
                )
            );
        }

        if ($this->_isColumnAvailable('am_customer_group')) {
            $collection->getSelect()->joinLeft(
                array(
                    'customer' => $collection->getTable('customer/entity')
                ),
                'main_table.customer_id = customer.entity_id',
                array()
            );

            $collection->getSelect()->joinLeft(
                array(
                    'customer_group' => $collection->getTable('customer/customer_group')
                ),
                'ifnull(customer.group_id, 0) = customer_group.customer_group_id',
                array('customer_group.customer_group_code as am_customer_group')
            );
        }

        if ($showTrackInfo) {
            $collection->getSelect()->joinLeft(
                array(
                    'shipment_track' => $collection->getTable('sales/shipment_track')
                ),
                'main_table.entity_id = shipment_track.order_id',
                array(
                    'group_concat(distinct shipment_track.track_number) as am_track_number'
                )
            )->group('main_table.entity_id');
        }

        if (!empty($orderItemsColumns) || $requireAttributeJoin) {
            $collection->getSelect()->joinLeft(
                array(
                    $this->_orderTableAlias => $collection->getTable('amogrid/order_item')
                ),
                'order_item.item_id = ' . $this->_orderTableAlias . '.item_id',
                $orderItemsColumns
            );

            $collection->getSelect()->group('main_table.entity_id');
        }

        if ($showOrderTax) {
            $collection->getSelect()->joinLeft(
                array(
                    'order_tax' => $collection->getTable('sales/order_tax')
                ),
                'main_table.entity_id = order_tax.order_id',
                array(
                    'group_concat(order_tax.amount) as tax_amount')
            )->group('main_table.entity_id');
        }

        if ($showInvoiceDate || $showInvoiceId) {
            $collection->getSelect()->joinLeft(
                array(
                    'invoice_table' => $collection->getTable('sales/invoice')
                ),
                'main_table.entity_id = invoice_table.order_id',
                array(
                    'invoice_table.created_at as am_invoice_date',
                    'group_concat(DISTINCT invoice_table.entity_id) as am_invoice_id'
                )
            )->group('main_table.entity_id');
        }

        if ($showShipmentDate || $showShipmentId) {
            $collection->getSelect()->joinLeft(
                array(
                    'shipment_table' => $collection->getTable('sales/shipment')
                ),
                'main_table.entity_id = shipment_table.order_id',
                array(
                    'shipment_table.created_at as am_shipment_date',
                    'group_concat(DISTINCT shipment_table.entity_id) as am_shipment_id'
                )
            );
        }

        if (count($excludeStatuses) > 0) {
            $collection->getSelect()->where(
                $collection->getConnection()->quoteInto('main_table.status NOT IN (?)', $excludeStatuses)
            );
        }

//        $collection->setIsCustomerMode(TRUE);

    }

    protected function _getActivePaymentMethods()
    {
        $paymentList = array();
        foreach (Mage::getModel('payment/config')->getActiveMethods() as $method) {

            $code = $method->getId();

            if (Mage::helper('payment')->getMethodInstance($code)) {
                $paymentList[$code] = Mage::helper('payment')->getMethodInstance($code)->getConfigData('title', null);
            }
        }

        return $paymentList;
    }

    protected function _getShippingMethods()
    {
        $methods = array();
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        foreach ($carriers as $carrierCode=>$carrierModel) {
//            if (!$carrierModel->isActive()) {
//                continue;
//            }
            $carrierMethods = array();
            try {
                $carrierMethods = $carrierModel->getAllowedMethods();
            } catch (Exception $e) {

            }

            if (!$carrierMethods) {
                continue;
            }

            $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');

            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $methods[$carrierTitle.' - '.$methodTitle] = $carrierTitle.' - '.$methodTitle;
            }
        }

        return $methods;
    }

    protected function getCustomerGroupList()
    {
        $ret = array();

        foreach (Mage::getModel('customer/group')->getCollection() as $group) {
            $ret[$group->getId()] = $group->getCustomerGroupCode();

        }

        return $ret;
    }

    function getConfigurableFields()
    {
        if (!$this->_configurableFields) {

            $this->_configurableFields = array(
                'am_product_images' => array(
                    'header' => 'Images',
                    'index' => 'product_images',
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_images',
                    'width' => 80,
                    'filter' => false,
                    'sortable'  => false,
                ),
                'am_coupon_code' => array(
                    'header' => $this->__('Coupon Code'),
                    'index' => 'am_coupon_code',
                    'width' => 80,
                    'filter_index' => 'order.coupon_code'

                ),
                'am_shipping_description' => array(
                    'header' => $this->__('Shipping Method'),
                    'index' => 'am_shipping_description',
                    'width' => 80,
                    'filter_index' => 'order.shipping_description',
//                    'type'  => 'options',
//                    'options' => $this->_getShippingMethods(),

                ),
                'am_method' => array(
                    'header' => $this->__('Payment Method'),
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_payment',
                    'index' => 'am_method',
                    'width' => 80,
                    'type'  => 'options',
                    'options' => $this->_getActivePaymentMethods(),
                    'filter_index' => 'order_payment.method'
                ),
                'am_shipping_address' => array(
                    'header' => $this->__('Shipping Address'),
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_address_shipping',
                    'index' => 'am_order_item_address_id',
                    'width' => 80,
                    'sortable'  => false,
                    'filter_index' =>
                            new Zend_Db_Expr('CONCAT(shipping_order_address.country_id,
                            shipping_order_address.region,
                            shipping_order_address.city,
                            shipping_order_address.street)'),

                ),
                'am_shipping_country_id' => array(
                    'header' => $this->__('Shipping: Country'),
                    'index' => 'am_shipping_country_id',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.country_id'

                ),
                'am_shipping_region' => array(
                    'header' => $this->__('Shipping: Region'),
                    'index' => 'am_shipping_region',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.region'

                ),
                'am_shipping_city' => array(
                    'header' => $this->__('Shipping: City'),
                    'index' => 'am_shipping_city',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.city'

                ),
                'am_shipping_postcode' => array(
                    'header' => $this->__('Shipping: Postcode'),
                    'index' => 'am_shipping_postcode',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.postcode'

                ),
                'am_shipping_street' => array(
                    'header' => $this->__('Shipping: Street'),
                    'index' => 'am_shipping_street',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.street'

                ),
                'am_shipping_street' => array(
                    'header' => $this->__('Shipping: Street'),
                    'index' => 'am_shipping_street',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.street'

                ),
                'am_shipping_company' => array(
                    'header' => $this->__('Shipping: Company'),
                    'index' => 'am_shipping_company',
                    'width' => 80,
                    'filter_index' => 'shipping_order_address.company'

                ),
                'am_billing_address' => array(
                    'header' => $this->__('Billing Address'),
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_address_billing',
                    'index' => 'am_order_item_address_id',
                    'width' => 80,
                    'sortable'  => false,
                    'filter_index' =>
                            new Zend_Db_Expr('CONCAT(billing_order_address.country_id,
                            billing_order_address.region,
                            billing_order_address.city,
                            billing_order_address.street)'),
                ),
                'am_billing_country_id' => array(
                    'header' => $this->__('Billing: Country'),
                    'index' => 'am_billing_country_id',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.country_id'

                ),
                'am_billing_region' => array(
                    'header' => $this->__('Billing: Region'),
                    'index' => 'am_billing_region',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.region'

                ),
                'am_billing_city' => array(
                    'header' => $this->__('Billing: City'),
                    'index' => 'am_billing_city',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.city'

                ),
                'am_billing_postcode' => array(
                    'header' => $this->__('Billing: Postcode'),
                    'index' => 'am_billing_postcode',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.postcode'

                ),
                'am_billing_street' => array(
                    'header' => $this->__('Billing: Street'),
                    'index' => 'am_billing_street',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.street'

                ),
                'am_billing_company' => array(
                    'header' => $this->__('Billing: Company'),
                    'index' => 'am_billing_company',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.company'

                ),
                'am_billing_telephone' => array(
                    'header' => $this->__('Billing: Phone'),
                    'index' => 'am_billing_telephone',
                    'width' => 80,
                    'filter_index' => 'billing_order_address.telephone'

                ),
                'am_customer_email' => array(
                    'header' => $this->__('Customer Email'),
                    'index' => 'am_customer_email',
                    'width' => 80,
                    'filter_index' => 'order.customer_email'

                ),
                'am_weight' => array(
                    'header' => $this->__('Weight'),
                    'index' => 'am_weight',
                    'width' => 80,
                    'filter_index' => 'order.weight'
                ),
                'am_base_subtotal' => array(
                    'header' => $this->__('Subtotal'),
                    'type'  => 'currency',
                    'index' => 'am_base_subtotal',
                    'width' => 80,
                    'filter_index' => 'am_base_subtotal',
                    'currency' => 'base_currency_code',
                ),
                'am_customer_group' => array(
                    'header' => $this->__('Customer Group'),
                    'index' => 'am_customer_group',
                    'width' => 80,
                    'filter_index' => 'customer_group.customer_group_id',
                    'type'  => 'options',
                    'options' => $this->getCustomerGroupList(),
                ),
                'am_track_number' => array(
                    'header' => $this->__('Track Number'),
                    'index' => 'am_track_number',
                    'width' => 80,
                    'filter_index' => 'shipment_track.track_number'
                ),
                'tax_amount' => array(
                    'header' => Mage::helper('sales')->__('Order Tax'),
                    'index' => 'tax_amount',
                    'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_tax',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'filter_index' => 'order_tax.amount'
                ),
                'am_invoice_date' => array(
                    'header' => Mage::helper('sales')->__('Invoice Date'),
                    'index' => 'am_invoice_date',
                    'type'  => 'datetime',
                    'filter_index' => 'invoice_table.created_at'
                ),
                'am_invoice_id' => array(
                    'header' => Mage::helper('sales')->__('Invoice ID'),
                    'index' => 'am_invoice_id',
                    'type'  => 'text',
                    'filter_index' => 'invoice_table.entity_id'
                ),
                'am_shipment_date' => array(
                    'header' => Mage::helper('sales')->__('Shipment Date'),
                    'index' => 'am_shipment_date',
                    'type'  => 'datetime',
                    'filter_index' => 'shipment_table.created_at'
                ),
                'am_shipment_id' => array(
                    'header' => Mage::helper('sales')->__('Shipment ID'),
                    'index' => 'am_shipment_id',
                    'type'  => 'text',
                    'filter_index' => 'shipment_table.entity_id'
                ),
                'am_total_paid' => array(
                    'header' => Mage::helper('sales')->__('Total Paid'),
                    'index' => 'total_paid',
                    'filter_index' => 'main_table.total_paid'
                ),
            );
        }
        return $this->_configurableFields;
    }

    protected function _prepareConfigurableField(&$grid, $key)
    {
        $config = $this->getConfigurableFields();

        if (isset($config[$key])) {
            $grid->addColumn($key, $config[$key]);
        }
    }

    protected function _prepareDefaultField(&$grid, $key)
    {
        $config = $this->getDefaultFields();

        if (isset($config[$key])) {
            $grid->addColumn($key, $config[$key]);
        }
    }

    protected function _prepareAttributeField(&$grid, $column, $export = FALSE)
    {
        $key = $this->_extrOrderColumnPrefix.$column['key'];

        $grid->addColumn($key, array(
            'header' => $column['name'],
            'index' => $this->_orderTableAlias.'.'.$column['key'],
                'renderer'  => 'amogrid/adminhtml_sales_order_grid_renderer_'.($export ? 'export' : 'default'),
                //'width' => '150px',
            'filter_index' => $this->_orderTableAlias.'.'.$column['key'],
            'amogrid_require_join' => true
        ));
    }

    function prepareGrid(&$grid, $export = FALSE)
    {

        $columns = $this->_getColumns();
        $after = NULL;

        if ($columns) {
            foreach ($columns as $key => $column) {
                switch ($column['type']) {
                    case "configurable":
                        $this->_prepareConfigurableField($grid, $key);
                        break;
                    case "default":
                        $this->_prepareDefaultField($grid, $key);
                        break;
                    case "attribute":
                        $this->_prepareAttributeField($grid, $column, $export);
                        break;
                }

                $after = $key;
            }
        }
    }

    function getDefaultFields()
    {
        if (!$this->_defaultField) {
            $this->_defaultField = array(
                'am_real_order_id' => array(
                    'header'=> Mage::helper('sales')->__('Order #'),
                    'width' => '80px',
                    'type'  => 'text',
                    'index' => 'increment_id',
                    'filter_index' => 'main_table.increment_id'
                ),
                'am_created_at' => array(
                    'header' => Mage::helper('sales')->__('Purchased On'),
                    'index' => 'created_at',
                    'type' => 'datetime',
                    'width' => '100px',
                    'filter_index' => 'main_table.created_at'
                ),
                'am_billing_name' => array(
                    'header' => Mage::helper('sales')->__('Bill to Name'),
                    'index' => 'billing_name',
                    'filter_index' => 'main_table.billing_name'
                ),
                'am_shipping_name' => array(
                    'header' => Mage::helper('sales')->__('Ship to Name'),
                    'index' => 'shipping_name',
                    'filter_index' => 'main_table.shipping_name'
                ),
                'am_base_grand_total' => array(
                    'header' => Mage::helper('sales')->__('G.T. (Base)'),
                    'index' => 'base_grand_total',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
                    'filter_index' => 'main_table.base_grand_total'
                ),
                'am_grand_total' => array(
                    'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                    'index' => 'grand_total',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
                    'filter_index' => 'main_table.grand_total'
                ),
                'am_status' => array(
                    'header' => Mage::helper('sales')->__('Status'),
                    'index' => 'status',
                    'type'  => 'options',
                    'width' => '70px',
                    'filter_index' => 'main_table.status',
                    'options' => array_merge(array(NULL => ""), Mage::getSingleton('sales/order_config')->getStatuses())
                )
            );

            if (!Mage::app()->isSingleStoreMode()) {
                $this->_defaultField['am_store_id'] = array(
                    'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                    'index'     => 'store_id',
                    'type'      => 'store',
                    'store_view'=> true,
                    'display_deleted' => true,
                    'filter_index' => 'main_table.store_id'
                );
            }

            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
                $this->_defaultField['am_action'] = array(
                        'header'    => Mage::helper('sales')->__('Action'),
                        'width'     => '50px',
                        'type'      => 'action',
                        'getter'     => 'getId',
                        'actions'   => array(
                            array(
                                'caption' => Mage::helper('sales')->__('View'),
                                'url'     => array('base'=>'*/sales_order/view'),
                                'field'   => 'order_id'
                            )
                        ),
                        'filter'    => false,
                        'sortable'  => false,
                        'index'     => 'stores',
                        'is_system' => true,
                );
            }
        }
        return $this->_defaultField;
    }

    public function removeColumns($grid)
    {
        $this->_removeDefaultColumns($grid);
        $this->_removeStaticColumns($grid);
    }

    protected function _removeStaticColumns($grid)
    {
        $staticColumns = $this->_getStaticColumns();

        if (is_array($staticColumns)) {
            foreach ($staticColumns as $key => $column) {
                $available = isset($column['available']) && $column['available'] == 1;
                if (!$available) {

                    $this->_removeColumn($grid, $column['relation']);
                }
            }
        }
    }

    protected function _removeDefaultColumns($grid)
    {
        $mainTableColumns = array(
            'real_order_id', 'store_id',
            'created_at', 'billing_name', 'shipping_name', 'base_grand_total',
            'grand_total', 'status', 'action'
        );

        $columns = $grid->getColumns();

        foreach ($columns as $column) {

            $columnId = $column->getId();
            if (in_array($columnId, $mainTableColumns)) {
                $this->_removeColumn($grid, $columnId);
            }
        }
    }

    protected function _removeColumn($grid, $columnId)
    {
        if (method_exists($grid, 'removeColumn'))
            $grid->removeColumn($columnId);
        else
        $grid->addColumn($columnId, array(
            'header_css_class' => 'am_hidden',
            'column_css_class' => 'am_hidden',
            'filter'    => false,
            'sortable'  => false,
        ));
    }

    protected function _getColumnKey($column)
    {
        $key = $column['key'];

        switch ($column['type']) {
            case self::$_TYPE_ATTRIBUTE:
                $key = $this->_extrOrderColumnPrefix.$column['key'];
                break;

            case self::$_TYPE_STATIC:
                $key = $column['relation'];
                break;
        };
        return $key;
    }

    function reorder($grid)
    {
        if (method_exists($grid, 'addColumnsOrder')) {
           $grid->sortColumnsByOrder();

            $columns = $this->_getColumns();
            $after = null;
            if ($columns) {
                foreach ($columns as $column) {
                    $key = $this->_getColumnKey($column);

                    $gridColumn = $grid->getColumn($key);

                    if ($gridColumn) {
                        $grid->addColumnsOrder($key, $after);//->sortColumnsByOrder();
                        $after = $key;
                    }
                }
            }

            $grid->sortColumnsByOrder();
        } else {
            //SOME TO DO
        }
    }

    function restyle($grid)
    {
        $columns = $this->_getColumns();
        if ($columns) {
            foreach ($columns as $column) {
                $key = $this->_getColumnKey($column);

                $gridColumn = $grid->getColumn($key);

                if (!empty($column['width']) && $gridColumn) {
                    $gridColumn->setData('width', $column['width']);
                }
            }
        }
    }
}
