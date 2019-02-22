<?php

/**
 * Class Born_Mediacenter_Block_Adminhtml_Subsections_Edit
 */
class Born_Mediacenter_Block_Adminhtml_Subsections_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'mediacenter';
        $this->_controller = 'adminhtml_subsections';

        $this->_updateButton('save', 'label', Mage::helper('mediacenter')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('mediacenter')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('subsection_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'mediacenter_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'subsection_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('subsection_data') && Mage::registry('subsection_data')->getId()) {
            return Mage::helper('mediacenter')->__("Edit Subsection '%s'", $this->htmlEscape(Mage::registry('subsection_data')->getSubsectionName()));
        } else {
            return Mage::helper('mediacenter')->__('Add Subsection');
        }
    }
}