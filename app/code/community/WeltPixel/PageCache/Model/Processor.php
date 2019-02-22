<?php

/**
 *   FOR ENTERPRISE ONLY
 * NOT USED AT THE MOMENT
 */

class WeltPixel_PageCache_Model_Processor
    extends Enterprise_PageCache_Model_Processor
{

    protected function _createRequestIds()
    {
        parent::_createRequestIds();

        $_helper = Mage::helper('weltpixel_mobiledetect');
        $detectionSuffix = 'desktop';
        if ($_helper->isMobile() || $_helper->isTablet()) {
            $detectionSuffix = 'mobile';
        }

        $this->_requestId .= '_' . $detectionSuffix;

        $this->_requestCacheId = $this->prepareCacheId($this->_requestId);

        return $this;
    }

}
