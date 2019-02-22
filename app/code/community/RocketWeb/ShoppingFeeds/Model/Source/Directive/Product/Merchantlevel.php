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
class RocketWeb_ShoppingFeeds_Model_Source_Directive_Product_Merchantlevel extends Varien_Object
{
    /**
     * Outputs directive HTML
     * @return string
     */
    public function toHtml()
    {
        $html = '<div style="float:left;">'. Mage::helper('rocketshoppingfeeds')->__('Level:'). '</div>'
            . '<div style="float:right;">' . 
            '<select name="config[#{field_name}][#{_id}][param]" class="select" style="width:180px;">';

        $options = array(
            '1' => 'MerchantCategory',
            '2' => 'MerchantSubcategory',
            '3' => 'MerchantGroup',
            '4' => 'MerchantSubgroup'
        );
        foreach ($options as $value => $option) {
            $html .= '<option value="' . $value . '">' . $value . '</option>';
        }
        $html .= '</select></div>';
        $html .= '<p class="note" style="clear:both;"><span>Select which level of catalog category you wish to display.</span></p>';

        return $html;
    }
}
