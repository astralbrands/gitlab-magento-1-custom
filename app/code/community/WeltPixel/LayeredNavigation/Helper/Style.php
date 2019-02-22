<?php

/**
 * Adding dynamic css, js files -> for options set in backend
 */
class WeltPixel_LayeredNavigation_Helper_Style extends Mage_Core_Helper_Data {

    public function addMobileStyle($headBlock) {
        $_helper = Mage::helper('weltpixel_layerednavigation');
            $treshold = $_helper->getTresholdValue() . 'px';
            $headBlock->addCss('css/weltpixel/layerednavigation-mobile.css', 'media="only screen and (max-width: '. $treshold .')"');            
    }

}
