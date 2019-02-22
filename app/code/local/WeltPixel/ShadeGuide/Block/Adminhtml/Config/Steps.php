<?php

class WeltPixel_ShadeGuide_Block_Adminhtml_Config_Steps extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    protected $_attrRenderer;

    protected $_multiselectRenderer;

    protected $_skippableRenderer;

    protected $_templateRenderer;

    /**
     * Fix for ignored "depends enabled"
     * See: https://magento.stackexchange.com/questions/15500/configuration-depends-with-front-and-backend-model
     */
    public function _toHtml()
    {
        return '<div id="' . $this->getElement()->getId(). '">' . parent::_toHtml() . '</div>';
    }

    /**
     *
     */
    public function _prepareToRender()
    {
        $this->addColumn('step_title', array(
            'label' => Mage::helper('shadeguide')->__('Step Title'),
            'style' => 'width:200px',
        ));

        $this->addColumn('product_attribute', array(
            'label' => Mage::helper('shadeguide')->__('Select Attribute'),
            'renderer' => $this->_getAttributesRenderer(),
        ));

        $this->addColumn('multiselect', array(
            'label' => Mage::helper('shadeguide')->__('Allow MultiSelect'),
            'renderer' => $this->_getMultiselectRenderer(),
        ));

        $this->addColumn('skip', array(
            'label' => Mage::helper('shadeguide')->__('Is Skippable?'),
            'renderer' => $this->_getSkippableRenderer(),
        ));

        $this->addColumn('template', array(
            'label' => Mage::helper('shadeguide')->__('Template Style'),
            'renderer' => $this->_getTemplateRenderer(),
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('shadeguide')->__('Add New Step');
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _getAttributesRenderer()
    {
        if (!$this->_attrRenderer) {
            $this->_attrRenderer = $this->getLayout()->createBlock(
                'shadeguide/adminhtml_config_form_field_attributes',
                '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_attrRenderer;
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _getSkippableRenderer()
    {
        if (!$this->_skippableRenderer) {
            $this->_skippableRenderer = $this->getLayout()->createBlock(
                'shadeguide/adminhtml_config_form_field_skippable',
                '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_skippableRenderer;
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _getMultiselectRenderer()
    {
        if (!$this->_multiselectRenderer) {
            $this->_multiselectRenderer = $this->getLayout()->createBlock(
                'shadeguide/adminhtml_config_form_field_multiselect',
                '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_multiselectRenderer;
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _getTemplateRenderer()
    {
        if (!$this->_templateRenderer) {
            $this->_templateRenderer = $this->getLayout()->createBlock(
                'shadeguide/adminhtml_config_form_field_template',
                '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_templateRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getAttributesRenderer()
                ->calcOptionHash($row->getData('product_attribute')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getMultiselectRenderer()
                ->calcOptionHash($row->getData('multiselect')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getSkippableRenderer()
                ->calcOptionHash($row->getData('skip')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_getTemplateRenderer()
                ->calcOptionHash($row->getData('template')),
            'selected="selected"'
        );
    }
}