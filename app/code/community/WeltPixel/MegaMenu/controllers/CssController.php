<?php

class WeltPixel_MegaMenu_CssController extends Mage_Core_Controller_Front_Action
{
    /**
     * Deprecated Not used anymore
     */
    public function optionsAction()
    {
        $this->getResponse()->setHeader('Content-Type', 'text/css; charset=UTF8');
        $this->loadLayout(false);
        $this->renderLayout();
    }
}
