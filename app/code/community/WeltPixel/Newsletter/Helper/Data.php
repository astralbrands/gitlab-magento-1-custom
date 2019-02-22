<?php

class WeltPixel_Newsletter_Helper_Data extends Mage_Core_Helper_Abstract {

    public function isEnabled() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/enable');
    }
    
    public function getFormActionUrl() {
        return Mage::getUrl('newsletter/subscriber/new', array('_secure' => true));
    }

    /**
     * 1 - All Pages
     * 0 - Just on Home Page
     */
    public function getDisplayMode() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/display_options');
    }

    public function getDisplayBlock() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/display_block');
    }

    public function getVisitedPages() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/display_after_pages');
    }

    public function getSecondsToDisplay() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/display_after_seconds');
    }

    public function displayOnMobile() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/display_mobile');
    }
    
    public function getCloseOption() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/disable_popup');
    }

    public function getLifeTime() {
        return Mage::getStoreConfig('weltpixel_newsletter/general/popup_cookie_lifetime');
    }

//    public function isAnalyticsTrackingEnabled() {
//        return Mage::getStoreConfig('weltpixel_newsletter/general/google_analytics_tracking');
//    }
//
//    public function geNewsletterSignupSuccess() {
//        return Mage::getStoreConfig('weltpixel_newsletter/general/newsletter_signup_success');
//    }

    public function getCookieName() {
        return 'weltpixel_newsletter';
    }

    public function canShowPopup() {
        $NisAjax = !Mage::app()->getRequest()->isAjax();
        $enabled = $this->isEnabled();
        $dOption = $this->getDisplayMode();
        //check if you are on home page
        $weAreOnHomePage = (Mage::getUrl('') == Mage::getUrl('*/*/*', array('_current' => true, '_use_rewrite' => true))) ? 1 : 0;
        $displayOnMobile = $this->displayOnMobile();
        $_helper = Mage::helper('weltpixel_mobiledetect');
        $canShowOnMobile = true;
        if (!$displayOnMobile && ($_helper->isMobile() || $_helper->isTablet())) :
            $canShowOnMobile = false;
        endif;

        if ($dOption == 1) {
            return ( $enabled && $NisAjax && $canShowOnMobile );
        } else {
            //check if you are on home page
            return ( $enabled && $NisAjax && $weAreOnHomePage && $canShowOnMobile );
        }
    }

}
