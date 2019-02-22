<?php

class WeltPixel_Selector_Block_Instagram extends Mage_Core_Block_Template {

    public function getConfig()
    {
        $_helper = Mage::helper('weltpixel_selector');

        return array(
            'client_id' => $_helper->getInstagramClientId(),
            'access_token' => $_helper->getInstagramAccessToken(),
        );
    }

}