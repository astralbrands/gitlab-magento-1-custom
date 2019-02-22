<?php

require_once 'Mage/Catalog/controllers/CategoryController.php';

class WeltPixel_LayeredNavigation_CategoryController extends Mage_Catalog_CategoryController {

    protected function _getAjaxLayeredNav($category) {
        $layout = $this->getLayout();
        $handlesToLoad = array('catalog_category_weltpixel_ajax', 'catalog_category_weltpixel_ajax_extra');

        $isAnchor = $category->getIsAnchor();

        if (!$isAnchor) {
            $handlesToLoad = array('catalog_category_default_weltpixel_ajax', 'catalog_category_weltpixel_ajax_extra');
        }

        /**
         * If top menu was selected then add the proper handle to use that or filter container
         */
        if ($isAnchor && Mage::helper('weltpixel_layerednavigation')->isLayeredNavigationOnTop()) {
            $handlesToLoad[] = 'top_layered_navigation_handle';
        }
        $layout->getUpdate()->load(array($handlesToLoad));
        $layout->generateXml()->generateBlocks();

        Mage::dispatchEvent(
            'controller_action_layout_generate_blocks_after', array('action' => $this, 'layout' => $layout)
        );

        $output = $layout->getOutput();
        return $output;
    }

    public function viewAction() {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            if ($category = $this->_initCatagory()) {
                $this->getResponse()
                        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
                        ->setHeader('Pragma', 'no-cache')
                        ->setHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT')
                        ->setBody($this->_getAjaxLayeredNav($category));
            }
        } else {
            parent::viewAction();
        }
    }

}
