<?php

class WeltPixel_ShadeGuide_Block_Adminhtml_Config_Form_Field_Attributes extends Mage_Core_Block_Html_Select
{
    /**
     * Prepare HTML output
     *
     * @return Mage_Core_Block_Html_Select
     */
    public function _toHtml()
    {
        $productAttrs = Mage::getResourceModel('catalog/product_attribute_collection');
        $this->addOption('', Mage::helper('shadeguide')->__('-- Please Select --'));

        foreach ($productAttrs as $attr) {
            // $applayTo = $attr->getApplyTo();
            if ($attr->getFrontendInput() == 'select' || $attr->getFrontendInput() == 'multiselect') {
                $this->addOption($attr->getAttributeCode(), $attr->getFrontendLabel());

            }
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