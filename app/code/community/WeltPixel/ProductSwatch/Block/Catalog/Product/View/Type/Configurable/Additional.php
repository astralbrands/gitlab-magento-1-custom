<?php

class WeltPixel_ProductSwatch_Block_Catalog_Product_View_Type_Configurable_Additional extends Mage_Catalog_Block_Product_View_Type_Configurable
{

    public function getAdditionalInfo() {
        $options = array();
        $attributeDisplay = array();
         foreach ($this->getAllowProducts() as $product) {

            foreach ($this->getAllowAttributes() as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();      
                $swatchEnabled = $productAttribute->getData('enable_product_swatch');
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }
                
                //if (!isset($attributeDisplay[$productAttributeId])) {
                    $attributeDisplay[$productAttributeId] = $swatchEnabled;
                //}

                if (!in_array($attributeValue, $options[$productAttributeId])) {
                    $options[$productAttributeId][] = $attributeValue;
                }
            }
        }
        
        $images = array();
        $_helper = Mage::helper('weltpixel_productswatch');
        $swatchWidth = $_helper->getSwatchWidth();
        $swatchHeight = $_helper->getSwatchHeight();
        
        foreach ($options as $id => $values) :
            if ($attributeDisplay[$id]) :
                //images should be used
                foreach ($values as $key => $val) :
                    $images[$val] = (string) Mage::helper('weltpixel_productswatch/image')->init($val)->resize($swatchWidth, $swatchHeight);
                endforeach;
            endif;
        endforeach;
        
        return Zend_Json::encode(array(
          'displayOptions' => $attributeDisplay,
          'displayValues'  => $images
        ));
    }
}
