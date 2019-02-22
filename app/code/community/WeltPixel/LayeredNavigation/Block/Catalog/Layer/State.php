<?php

class WeltPixel_LayeredNavigation_Block_Catalog_Layer_State extends Mage_Catalog_Block_Layer_State {

    
    public function __construct()
    {
        parent::__construct();
        if (!Mage::helper('weltpixel_layerednavigation')->showCurrentlyShoppingBy()) {
            $this->setTemplate('weltpixel/layerednavigation/catalog/layer/state.phtml');            
        }            
    }
        
}