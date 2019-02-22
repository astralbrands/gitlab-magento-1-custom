<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Locator
 */
class Amasty_Locator_Block_Adminhtml_Location_Edit_Tabs_Map
    extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $helper = Mage::helper('amlocator');

        $model = Mage::registry('current_location');
        $helperImage = Mage::helper('amlocator/image');

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset(
            'general_form', array(
                'legend' => $helper->__('General Information')
            )
        );


        $fieldset->addField(
            'lat', 'text', array(
                'class'    => 'validate-number',
                'label'    => $helper->__('Latitude'),
                'required' => true,
                'name'     => 'lat',
            )
        );
        $fieldset->addField(
            'lng', 'text', array(
                'class'    => 'validate-number',
                'label'    => $helper->__('Longitude'),
                'required' => true,
                'name'     => 'lng',
            )
        );

        $fieldset->addField(
            'loadbycoord', 'Label', array(
                'class'    => '',
                'label'    => '',
                'after_element_html' => '
<button onclick="window.amLocatorObj.display();" style="" class="scalable" type="button">
<span><span><span>'.$helper->__('Auto Fill').'</span></span></span>
</button>
'
            )
        );

        $fieldset->addField(
            'marker', 'image', array(
                'label' => $helper->__('Custom marker'),
                'name'  => 'marker',
            )
        );

        $fieldset->addField('show_map', 'hidden', array(
                'name'               => 'Show_map',
                'after_element_html' => '<div id="map-canvas" style="margin-top: 20px; width: 515px; height: 515px; display: none"></div>'
            )
        );

        $data = array_merge(
            $model->getData(),
            array("marker" => $helperImage->getImageUrl($model->getMarker()))
        );

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $apiKey = Mage::helper('amlocator')->getApiKey();
        if ($apiKey)
        {
            $html .= '<script type="text/javascript">
                         amastyGoogleApiKey = "' . $apiKey . '";
                      </script>';
        }

        return $html;
    }
}