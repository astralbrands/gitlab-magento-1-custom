<?php

class WeltPixel_ProductSwatch_Model_Observer {

    public function addSwatchOptions($observer) {

        $yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();

        $fieldset = $observer->getForm()->getElement('base_fieldset');
        $fieldset->addField('enable_product_swatch', 'select', array(
            'name' => 'enable_product_swatch',
            'label' => Mage::helper('weltpixel_productswatch')->__('Enable swatch on product page'),
            'title' => Mage::helper('weltpixel_productswatch')->__('Enable swatch on product page'),
            'values' => $yesnoSource
                ), 'is_configurable');

        return $this;
    }
}