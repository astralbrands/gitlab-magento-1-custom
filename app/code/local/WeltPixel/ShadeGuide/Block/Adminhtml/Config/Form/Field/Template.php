<?php

class WeltPixel_ShadeGuide_Block_Adminhtml_Config_Form_Field_Template extends Mage_Core_Block_Html_Select
{
    /**
     * Prepare HTML output
     *
     * @return Mage_Core_Block_Html_Select
     */
    public function _toHtml()
    {
        $options = array(
            array(
                'value' => '',
                'label' => Mage::helper('shadeguide')->__('-- Please Select --')
            ),
            array(
                'value' => 'image_only',
                'label' => Mage::helper('shadeguide')->__('Image Only')
            ),
            array(
                'value' => 'image_carousel',
                'label' => Mage::helper('shadeguide')->__('Image Carousel')
            ),
            array(
                'value' => 'image_label',
                'label' => Mage::helper('shadeguide')->__('Image and Label')
            ),
            array(
                'value' => 'label_only',
                'label' => Mage::helper('shadeguide')->__('Label Only')
            ),
        );

        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        $this->setClass('medium-narrow');

        return parent::_toHtml();
    }

    /**
     * Set field name
     *
     * @param string $value
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}