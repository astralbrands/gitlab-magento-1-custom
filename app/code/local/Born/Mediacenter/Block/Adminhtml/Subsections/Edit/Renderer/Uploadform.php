<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Renderer_uploadform
 * This class is to initialize upload admin form for media content
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Edit_Renderer_uploadform
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Initialize block
     */
    public function __construct()
    {
        $this->setTemplate('mediacenter/uplaodform.phtml');
    }

    public function getConfigJson($field)
    {
        $this->getConfig()->setParams(
            array(
                'form_key' => Mage::getSingleton('core/session')->getFormKey(),
                $field => 'text'
            )
        );
        $this->getConfig()->setFileField('Filedata');
        $this->getConfig()->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl("*/*/upload", array("param1" => "value1")));
        /*         $this->getConfig()->setFilters(array(
                    'pdf documents' => array(
                        'label' => Mage::helper('adminhtml')->__('Portable Document Format (.pdf)'),
                        'files' => array('*.pdf')
                    )
                )); */
        return Mage::helper('core')->jsonEncode($this->getConfig()->getData());
    }

    public function getConfig()
    {
        if (is_null($this->_config)) {
            $this->_config = new Varien_Object();
        }

        return $this->_config;
    }

    /**
     * Render HTML
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }


}