<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Preorder
 */

/**
 * Class Amasty_Preorder_Block_Warning
 * @method Mage_Sales_Model_Order getOrder()
 * @method setOrder(Mage_Sales_Model_Order $order)
 */
class Amasty_Preorder_Block_Warning extends Mage_Core_Block_Template
{
    const TEMPLATE_ORDER_VIEW = 'amasty/ampreorder/warning_order_view.phtml';
    const TEMPLATE_ORDER_EMAIL = 'amasty/ampreorder/warning_order_email.phtml';

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::TEMPLATE_ORDER_VIEW);
    }

    protected function getWarningText()
    {
        $order = $this->getOrder();
        if ($order instanceof Mage_Sales_Model_Order == false) {
            return '[Please assign order parameter]';
        }

        /** @var Amasty_Preorder_Helper_Data $helper */
        $helper = Mage::helper('ampreorder');
        if (!$helper->getOrderIsPreorderFlag($order)) {
            return '';
        }

        return $helper->getOrderPreorderWarning($order->getId());
    }
}