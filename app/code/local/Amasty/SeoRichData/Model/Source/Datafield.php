<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */
class Amasty_SeoRichData_Model_Source_Datafield extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => 'price',
            'label' => Mage::helper('amseorichdata')->__('Price')
        );
        $options[] = array(
            'value' => 'stock_status',
            'label' => Mage::helper('amseorichdata')->__('Stock Status')
        );
        $options[] = array(
            'value' => 'rating',
            'label' => Mage::helper('amseorichdata')->__('Rating')
        );
        $options[] = array(
            'value' => 'custom',
            'label' => Mage::helper('amseorichdata')->__('Custom')
        );
        return $options;
    }
}