<?php

class WeltPixel_AttributeImages_Block_Adminhtml_Attributeimages_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'weltpixel_attributeimages';
        $this->_controller = 'adminhtml_attributeimages';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label' => Mage::helper('weltpixel_attributeimages')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);
        $this->_updateButton('save', 'label', Mage::helper('weltpixel_attributeimages')->__('Sava Attribute Image'));
        
        $this->removeButton('delete');

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'edit_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
                }
            }
 
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {
        return Mage::helper('weltpixel_attributeimages')->__('Edit Attribute Image "%s"', $this->htmlEscape(Mage::registry('attributesimages')->getData('attribute_code')));
    }

}