<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
class Amasty_Xnotif_Block_Adminhtml_Catalog_Product_Edit_Tab_Alerts_Renderer_FirstName
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    
   public function render(Varien_Object $row)
   {
       if (!$row->getEntityId()) {
             $row->setFirstname($this->__('Guest'));
       }

       return Mage::helper('core')->stripTags($row->getFirstname());
   }
}
