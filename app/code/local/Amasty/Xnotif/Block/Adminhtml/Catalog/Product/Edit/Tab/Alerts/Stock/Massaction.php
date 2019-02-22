<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


class Amasty_Xnotif_Block_Adminhtml_Catalog_Product_Edit_Tab_Alerts_Stock_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction
{
    public function getJavaScript()
    {
        $hideMassSelect = 'document.querySelector("#alertStock_massaction td:first-child").style.display = \'none\';';

        $script = parent::getJavaScript();
        $result = preg_replace('/^\s*var\s+/', 'window.', $script, 1);
        return $result . $hideMassSelect;
        
    }

    /**
     * set checkboxes checked for massaction work
     *
     * @return string
     */
    public function getSelectedJson()
    {
        return '1';
    }
}