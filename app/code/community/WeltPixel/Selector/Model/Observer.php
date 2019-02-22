<?php

class WeltPixel_Selector_Model_Observer {

    public function controllerActionLayoutGenerateBlocksAfter($observer) {

        $_helper = Mage::helper('weltpixel_mobiledetect');
        $isMobile = false;
        if ($_helper->isMobile() || $_helper->isTablet()) {
            $isMobile = true;
        }

        $packageName = Mage::getDesign()->getPackageName();
        //load all settings in one array and get theme path
        $_settings = Mage::getStoreConfig('weltpixel_selector');

        //set header template
        $_headerName = $_settings['headeroptions']['header_style'];
        //on mobile devices version 1 is used for header all the time
        if ($isMobile) {
            $_headerName = 'header-v1.0.phtml';
        }
        $_headerTemplate = 'page/html/' . $_headerName;

        if (($header = $observer->getLayout()->getBlock('header')) && ($packageName == 'cleo') ) {
            $header->setTemplate($_headerTemplate);
        }

        //set custom search template
        $_searchDesignName = $_settings['headeroptions']['search_design'];
        //on mobile devices version 1 is used for search all the time
        if ($isMobile) {
            $_searchDesignName = 'form.mini-v1.phtml';
        }
        $_searchDesignTemplate = 'weltpixel/ajaxsearch/' . $_searchDesignName;

        if (($search = $observer->getLayout()->getBlock('top.search'))  && ($packageName == 'cleo') ) {
            $search->setTemplate($_searchDesignTemplate);
        }

        //add dynamnic css based on store view

        $cssFile = Mage::app()->getStore()->getCode();

        $head = $observer->getLayout()->getBlock('head');
        if ($head) :
            //$head->addItem('link_rel', "//fonts.googleapis.com/css?family=Raleway:300,400,500,700,600", 'rel="stylesheet" type="text/css" media="all"');
            $head->addCss('css/weltpixel/color_' . $cssFile . '.css');
        endif;

        //set other templates here

        return $this;
    }

    public function storeEdited($observer) {
        $store = $observer->getStore();
        $this->_generateCss($store);
        return $this;
    }

    public function generateDynamicCss($observer = null) {
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $this->_generateCss($store);
                }
            }
        }

        return $this;
    }

    private function _generateCss($store) {
        $io = new Varien_Io_File();
        $path = Mage::getBaseDir("skin") . DS . 'frontend' . DS . 'base' . DS . 'default' . DS . 'css' . DS . 'weltpixel' . DS;
        $name = 'color_' . $store->getCode() . '.css';
        $file = $path . DS . $name;
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);

        $cssContent = Mage::helper('selector')->getDynamicCssContent($store->getId());

        $io->streamWrite($cssContent);
        $io->close();
    }

    public function sidebarOptionsSetup($observer) {
        $layout = $observer->getEvent()->getLayout();

        $xmlUpdate = '';

        if (!Mage::getStoreConfig('weltpixel_selector/sidebaroptions/recentcompare')) :
            $xmlUpdate .= '<remove name="right.reports.product.compared" />';
        endif;

        if (!Mage::getStoreConfig('weltpixel_selector/sidebaroptions/poolenable')) :
            $xmlUpdate .= '<remove name="right.poll" />';
        endif;

        if (!Mage::getStoreConfig('weltpixel_selector/sidebaroptions/compareenable')) :
            $xmlUpdate .= '<remove name="catalog.compare.sidebar" />';
        endif;

        if (!Mage::getStoreConfig('weltpixel_selector/sidebaroptions/newslettersignup')) :
            $xmlUpdate .= '<remove name="left.newsletter" />';
        endif;

        if (!Mage::getStoreConfig('weltpixel_selector/sidebaroptions/tagsenable')) :
            $xmlUpdate .= '<remove name="tags_popular" />';
        endif;

        if (!Mage::getStoreConfig('weltpixel_selector/sidebaroptions/recentview')) :
            $xmlUpdate .= '<remove name="right.reports.product.viewed" />';
            $xmlUpdate .= '<remove name="left.reports.product.viewed" />';
        endif;



        //Product page additional info check as well
        if (!Mage::getStoreConfig('weltpixel_selector/productpageoptions/display_additionalinfo')) :
            $xmlUpdate .= '<remove name="product.attributes" />';
        endif;



        $xmlUpdate .= '<catalog_product_view>
        <reference name="root">
            <action method="setTemplate">
                <template>page/2columns-right.phtml</template>
            </action>
        </reference>
        </catalog_product_view>';

        $layout->getUpdate()->addUpdate($xmlUpdate);

        return $this;
    }

}