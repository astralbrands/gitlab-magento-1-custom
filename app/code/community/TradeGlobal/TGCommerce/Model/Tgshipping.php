<?php
/**
 *  Placeholder.  Not currently used.  We are storing shipping information in the standard rate quote storage table
 *  'sales_flat_quote_shipping_rate', which we have extended to hold our new fields
 *
 * @author      Paul Snell (paulsnell@singpost.com)
 * @category    TradeGobal
 * @package     TradeGlobal_TGCommerce
 * @copyright   Copyright (c) 2017 TradeGlobal
 */
class TradeGlobal_TGCommerce_Model_Tgshipping Extends Mage_Core_Model_Abstract {
    protected function _construct()
    {
        $this->_init('tgcommerce/tgshipping');
    }
}