<?php

class WeltPixel_Selector_Adminhtml_WpselectorController
    extends Mage_Adminhtml_Controller_Action
{

    public function refreshCssAction()
    {
        try {
            Mage::getModel('weltpixel_selector/observer')->generateDynamicCss();
            $this->_getSession()->addSuccess(
                Mage::helper('adminhtml')->__('The css file was successfully regenerated.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('adminhtml')->__('An error occurred while regenerating the css.')
            );
        }
        $this->_redirect('*/cache');
    }

}
