<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  RocketWeb
 * @package   RocketWeb_ShoppingFeeds
 * @copyright Copyright (c) 2016 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_ShoppingFeeds_Model_System_Config_Backend_Serialized_Json extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    /**
     * Changed from serialize to json_encode to solve unencoded characters
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        if (!is_array($this->getValue())) {
            $value = json_decode($this->getValue(), true);
            $this->setValue(is_array($value) ? $value : null);
        }

        return $this;
    }

    /**
     * Changed from serialize to json_encode to solve count of characters like apostrophe
     */
    protected function _beforeSave()
    {
        if (is_array($this->getValue())) {

            $value = $this->getValue();
            unset($value['__empty']);
            $value = json_encode($value);
            $value = preg_replace_callback('/\\\\u(\w{4})/', array(Mage::helper('rocketshoppingfeeds'), 'jsonUnescapedUnicodeCallback'), $value);
            $this->setValue($value);
        }

        return parent::_beforeSave();
    }

    public function filterValueAfterLoad($value)
    {
        if (!is_array($value)) {
            $value = json_decode($value, true);
            return is_array($value) ? $value : null;
        }
        return $value;
    }

    public function filterValueBeforeSave($value)
    {
        if (is_array($value)) {
            unset($value['__empty']);
            $value = json_encode($value);
            return preg_replace_callback('/\\\\u(\w{4})/', array(Mage::helper('rocketshoppingfeeds'), 'jsonUnescapedUnicodeCallback'), $value);
        }
        return $value;
    }

}
