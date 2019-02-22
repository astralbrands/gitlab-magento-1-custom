<?php

require_once 'Mage/CatalogSearch/controllers/ResultController.php';

class WeltPixel_LayeredNavigation_ResultController extends Mage_CatalogSearch_ResultController {

    protected function _getAjaxSearchResultNav() {
        $layout = $this->getLayout();
        $handlesToLoad = array('catalog_searchresult_weltpixel_ajax', 'catalog_searchresult_weltpixel_ajax_extra');

        /**
         * If top menu was selected then add the proper handle to use that or filter container
         */
        if (Mage::helper('weltpixel_layerednavigation')->isLayeredNavigationOnTop()) {
            $handlesToLoad[] = 'top_layered_catalogsearch_handle';
        }
        $layout->getUpdate()->load(array($handlesToLoad));
        $layout->generateXml()->generateBlocks();

        Mage::dispatchEvent(
                'controller_action_layout_generate_blocks_after', array('action' => $this, 'layout' => $layout)
        );

        $output = $layout->getOutput();
        return $output;
    }

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $this->getResponse()
                    ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                    ->setHeader('Pragma', 'no-cache')
                    ->setHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT')
                    ->setBody($this->_getAjaxSearchResultNav());
        } else {
            parent::indexAction();
        }
    }

}
