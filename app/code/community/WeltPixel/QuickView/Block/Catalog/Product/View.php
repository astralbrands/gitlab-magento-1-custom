<?php

class WeltPixel_QuickView_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View {

    public function getSubmitUrl($product, $additional = array(), $ajax = true) {

        $packageName = Mage::getDesign()->getPackageName();

        if (($packageName != 'cleo') || !$ajax) :
            return parent::getSubmitUrl($product, $additional);
        endif;

        if (!$this->hasCustomAddToCartUrl()) {
            $this->setCustomAddToCartUrl(
                    $this->helper('weltpixel_quickview/cart')->getAddUrlForPrduct($product, $additional)
            );
        }

        return parent::getSubmitUrl($product, $additional);
    }
}
