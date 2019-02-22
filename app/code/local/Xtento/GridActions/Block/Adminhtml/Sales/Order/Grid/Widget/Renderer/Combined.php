<?php

/**
 * Product:       Xtento_GridActions (1.8.5)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2013-04-22T22:44:14+02:00
 * File:          app/code/local/Xtento/GridActions/Block/Adminhtml/Sales/Order/Grid/Widget/Renderer/Combined.php
 * Copyright:     Copyright (c) 2016 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Block_Adminhtml_Sales_Order_Grid_Widget_Renderer_Combined extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{
    public function render(Varien_Object $row)
    {
        $html = Mage::getBlockSingleton('gridactions/adminhtml_sales_order_grid_widget_renderer_carrier')->renderCombined($row, $this->getColumn());
        $html .= '&nbsp;';
        $html .= Mage::getBlockSingleton('gridactions/adminhtml_sales_order_grid_widget_renderer_trackingnumber')->renderCombined($row, $this->getColumn());

        return $html;
    }

    /*
    * Return dummy filter.
    */
    public function getFilter()
    {
        return false;
    }
}
