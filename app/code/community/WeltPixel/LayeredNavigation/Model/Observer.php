<?php

class WeltPixel_LayeredNavigation_Model_Observer {

    public function navigationPositioning($observer) {

        $action = $observer->getEvent()->getAction();
        $layout = $observer->getEvent()->getLayout();

        $allowedUpdates = array('catalog', 'catalogsearch');
        $moduleName = $action->getRequest()->getModuleName();

        if (in_array($moduleName, $allowedUpdates)) {
            $xmlUpdate = Mage::helper('weltpixel_layerednavigation/positioning')->getPositioningXmlUpdate();
            $layout->getUpdate()->addUpdate($xmlUpdate);
        }

        return $this;
    }

    public function addSwatchOptions($observer) {

        $yesnoSource = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
        $filterOptions = Mage::getModel('weltpixel_layerednavigation/adminhtml_system_config_source_navigation_attributeoptions')->toOptionArray();

        $fieldset = $observer->getForm()->getElement('base_fieldset');
        $fieldset->addField('enable_layered_swatch', 'select', array(
            'name' => 'enable_layered_swatch',
            'label' => Mage::helper('weltpixel_layerednavigation')->__('Enable swatch in layered navigation'),
            'title' => Mage::helper('weltpixel_layerednavigation')->__('Enable swatch in layered navigation'),
            'values' => $yesnoSource
                ), 'is_configurable');

        $fieldset->addField('layered_filter_option', 'select', array(
            'name' => 'layered_filter_option',
            'note' => 'This overwrites the setting from Weltpixel/Layerednavigation/Show all other filters as',
            'label' => Mage::helper('weltpixel_layerednavigation')->__('Filter type in layered navigation'),
            'title' => Mage::helper('weltpixel_layerednavigation')->__('Filter type in layered navigation'),
            'values' => $filterOptions
                ), 'enable_layered_swatch');


        return $this;
    }

}