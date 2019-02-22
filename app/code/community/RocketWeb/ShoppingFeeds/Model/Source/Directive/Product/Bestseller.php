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
class RocketWeb_ShoppingFeeds_Model_Source_Directive_Product_Bestseller extends Varien_Object
{
    /**
     * Outputs directive HTML
     * @return string
     */
    public function toHtml()
    {
        $helper = Mage::helper('rocketshoppingfeeds');
        $html = '<div style="float:left;">'. $helper->__('Period:'). '</div>'
            . '<div style="float:right;">' . 
            '<select name="config[#{field_name}][#{_id}][param]" class="select" style="width:180px;">';
        $options = array(
            Mage_Sales_Model_Resource_Report_Bestsellers::AGGREGATION_DAILY     => 'Daily',
            Mage_Sales_Model_Resource_Report_Bestsellers::AGGREGATION_MONTHLY   => 'Monthly',
            Mage_Sales_Model_Resource_Report_Bestsellers::AGGREGATION_YEARLY    => 'Yearly',
        );
        foreach ($options as $value => $label) {
            $html .= '<option value="' . $value . '">' . $helper->__($label) . '</option>';
        }
        $html .= '</select></div>';
        $html .= '<p class="note" style="clear:both;"><span>' . 
            $helper->__('Coma separated bestseller SKUs list') . '</span></p>';
        return $html;
    }
}
