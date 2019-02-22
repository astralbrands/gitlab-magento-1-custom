<?php

class WeltPixel_Custom_Model_Observer
{
    public function processPreDispatch(Varien_Event_Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        // Check to see if $action is a CMS controller
        if (
            $action instanceof Mage_Cms_IndexController ||
            $action instanceof Mage_Cms_PageController ||
            $action instanceof WeltPixel_ShadeGuide_FoundationfinderController
        ) {
            $cache = Mage::app()->getCacheInstance();
            $cache->banUse('full_page');
        }
    }
}