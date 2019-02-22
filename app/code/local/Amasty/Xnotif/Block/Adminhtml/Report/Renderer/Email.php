<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


class Amasty_Xnotif_Block_Adminhtml_Report_Renderer_Email extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $result = '';
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            $result =  $data;
        } else {
            $customer = $row->getData('customer_id');
            $model = Mage::getModel('customer/customer')->load($customer);
            if ($model && $model->getId()) {
                $result =  $model->getEmail();
            }
        }

        return $result;
    }
}