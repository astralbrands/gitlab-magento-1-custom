<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
class Amasty_Xnotif_Block_Product_View_Type_Grouped extends Amasty_Xnotif_Block_Product_View_Type_Grouped_Pure
{
    protected function _toHtml()
    {
        if(strpos($this->getTemplate(), "availability") === false){
            $this->setTemplate('amasty/amxnotif/product/view/type/grouped.phtml');
        }
        return parent::_toHtml();
    }
}
