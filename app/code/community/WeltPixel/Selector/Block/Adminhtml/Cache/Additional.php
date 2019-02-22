<?php

class WeltPixel_Selector_Block_Adminhtml_Cache_Additional extends Mage_Adminhtml_Block_Template
{
    public function getRefreshCssUrl()
    {
        return $this->getUrl('*/wpselector/refreshCss');
    }
}
