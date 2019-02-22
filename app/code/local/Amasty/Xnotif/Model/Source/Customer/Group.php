<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


class Amasty_Xnotif_Model_Source_Customer_Group
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('customer/group_collection')
                ->loadData()->toOptionArray();

            array_unshift(
                $this->_options,
                array(
                    'value'=> '-1',
                    'label'=> Mage::helper('amxnotif')->__('ALL GROUPS')
                )
            );
        }
        return $this->_options;
    }
}