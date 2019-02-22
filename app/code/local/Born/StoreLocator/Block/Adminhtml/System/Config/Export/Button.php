<?php
/**
 * Store Locator
 *
 * @author BORN
 * @package Born_StoreLocator
 */
 
class Born_StoreLocator_Block_Adminhtml_System_Config_Export_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	
	/**
	 * Generate button html
	 * @return string
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('storelocator/adminhtml_fetchlatlng/export/', array('store_code'=>$this->getRequest()->getParam('store')));

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('storelocator_update')
                    ->setLabel('Export Locations')
                     ->setOnClick("window.location.href='" . $url . "'")
                    ->toHtml();

        return $html;
    }
  
}
