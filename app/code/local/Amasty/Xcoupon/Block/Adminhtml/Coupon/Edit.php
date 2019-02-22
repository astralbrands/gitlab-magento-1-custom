<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xcoupon
 */ 
class Amasty_Xcoupon_Block_Adminhtml_Coupon_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'amxcoupon';
        $this->_controller = 'adminhtml_coupon';
        
        $this->_removeButton('back'); 
        $this->_removeButton('reset'); 
        $this->_removeButton('delete'); 
    }

    public function getHeaderText()
    {
        return Mage::helper('amxcoupon')->__('Coupon Properties');
    }
}