<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */

/**
 * Custom renderer
 */
class RocketWeb_ShoppingFeeds_Block_Adminhtml_Feed_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_feed';
        $this->_blockGroup = 'rocketshoppingfeeds';
        $this->_headerText = Mage::helper('rocketshoppingfeeds')->__('New Feed');
        $this->_mode = 'new';

        parent::__construct();
        $this->_removeButton('save');
        $this->_removeButton('reset');

        $this->_addButton('next', array(
            'label'     => Mage::helper('adminhtml')->__('Continue'),
            'onclick'   => "editForm.submit();",
            'class'     => 'save',
        ), -1, 0, 'footer');
    }
}