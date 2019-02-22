<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Settings_Import
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(
        Varien_Data_Form_Element_Abstract $element
    ) {
        $this->setElement($element);

        $onclick = 'var inputCaller = this;';

        $importTypes = array(
            'location',
        );

        foreach ($importTypes as $type) {
            $startUrl = $this->getUrl(
                'adminhtml/amlocator_import/start', array(
                    'type' => $type,
                    'storeid' => $this->_getActiveStoreId()
                )
            );


            $processUrl = $this->getUrl(
                'adminhtml/amlocator_import/process', array(
                    'type' => $type,
                    'storeid' => $this->_getActiveStoreId()
                )
            );

            $commitUrl = $this->getUrl(
                'adminhtml/amlocator_import/commit', array(
                    'type' => $type,
                    'storeid' => $this->_getActiveStoreId()
                )
            );

            $onclick
                .=
                'window.setTimeout(function(){ amLocatorImportObj.run(\'' . $startUrl
                . '\', \'' . $processUrl . '\', \'' . $commitUrl
                . '\', inputCaller);}, 100); ';
        }

        $import = Mage::getSingleton('amlocator/import');
        $importAvailable = $import->filesAvailable();

        $element->setComment(
            $importAvailable
                ? ''
                : $this->__('Required files:') . ' ' . implode(
                    ', ', $import->getRequiredFiles()
                )
        );

        $comment = $element->getComment();

        $element->setComment(
            $comment . '<span id="amlocator_import_text">CSV file format example: <link>http://amasty.com/examples/stores.csv</link></span>'
        );

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setId('amasty_locator_import_button')
            ->setLabel($this->__('Add Stores'))
            ->setOnClick($onclick)
            ->setDisabled(!$importAvailable)
            ->toHtml();

        return $html;
    }

    protected function _getActiveStoreId(){
        if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) // store level
        {
            $storeId = Mage::getModel('core/store')->load($code)->getId();
        }
        elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) // website level
        {
            $website_id = Mage::getModel('core/website')->load($code)->getId();
            $storeId = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
        }
        else // default level
        {
            $storeId = 0;
        }
        return $storeId;
    }
}
