<?php

class WeltPixel_ShadeGuide_Block_Adminhtml_Config_Form_Field_Multiselect extends Mage_Core_Block_Html_Select
{
    /**
     * Prepare HTML output
     *
     * @return Mage_Core_Block_Html_Select
     */
    public function _toHtml()
    {
        $options = Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray();
        $this->addOption('', Mage::helper('shadeguide')->__('-- Please Select --'));

        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        $this->setClass('narrow');

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