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
class RocketWeb_ShoppingFeeds_Model_Source_Directive_Expiration extends Varien_Object
{

    public function toHtml()
    {
        return '<div style="float:left;">'. Mage::helper('rocketshoppingfeeds')->__('No. of Days:'). '</div>'
               .'<div style="float:right;"><input type="text" name="config[#{field_name}][#{_id}][param]" value="#{param}" class="input-text validate-grater-than-zero" style="width:180px;"></div>'
               .'<p class="note" style="clear:both;"><span>' . Mage::helper('rocketshoppingfeeds')->__('If you do not provide this attribute, items will expire and be excluded from results after 30 days. You cannot use this attribute to extend the expiration period to longer than 30 days.'). '</span></p>';
    }
}
