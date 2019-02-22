<?php

/**
 *   FOR ENTERPRISE ONLY
 */
class WeltPixel_PageCache_Model_Cookie extends Enterprise_PageCache_Model_Cookie {

    const COOKIE_DETECTION = 'MOBILE_DETECT';

    public function updateCustomerCookies() {
        parent::updateCustomerCookies();

        $_helper = Mage::helper('weltpixel_mobiledetect');
        $detectionSuffix = 'desktop';
        if ($_helper->isMobile() || $_helper->isTablet()) {
            $detectionSuffix = 'mobile';
        }
        
        setcookie(self::COOKIE_DETECTION, $detectionSuffix, 0, '/');

        return $this;
    }

}
