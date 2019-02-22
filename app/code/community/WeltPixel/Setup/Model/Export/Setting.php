<?php

class WeltPixel_Setup_Model_Export_Setting extends WeltPixel_Setup_Model_Export {

    
    public function __construct($storeId = 0) {
        $this->_entity = 'setting';
        $this->_requiredColumns = array('title', 'path', 'value');

        $this->_createCollection($storeId);       
    }

    protected function _createCollection($storeId) {
        $configOptions = $config = Mage::getConfig()
                ->loadModulesConfiguration('system.xml')
                ->getNode()
                ->asArray();
        $collection = new Varien_Data_Collection();


        $requiredSections = array('weltpixel_selector', 'weltpixel_colorsettings','weltpixel_fontsettings', 'weltpixel_layerednavigation');
        foreach ($configOptions['sections'] as $section => $sectionOptions) {
            if (in_array($section, $requiredSections)) {
                $sectionName = $sectionOptions['label'];
                foreach ($sectionOptions['groups'] as $group => $groupOptions) {
                    $groupName = $groupOptions['label'];
                    foreach ($groupOptions['fields'] as $field => $fieldOptions) {
                        $fieldName = $fieldOptions['label'];
                        $item = new Varien_Object();
                        $item->setTitle($sectionName . '/' . $groupName . '/' . $fieldName);
                        $item->setPath(implode('/', array($section, $group, $field)));
                        $item->setValue(Mage::getStoreConfig(implode('/', array($section, $group, $field)), $storeId));
                        $collection->addItem($item);
                        unset($item);
                    }
                }
            }
        }
        
        $this->setItems($collection);
    }

}