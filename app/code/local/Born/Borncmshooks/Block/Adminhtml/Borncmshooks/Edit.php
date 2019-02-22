<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'borncmshooks';
        $this->_controller = 'adminhtml_borncmshooks';
        
        $this->_updateButton('save', 'label', Mage::helper('borncmshooks')->__('Save Hook'));
        $this->_updateButton('delete', 'label', Mage::helper('borncmshooks')->__('Delete Hook'));
		
        if($hook_id = $this->getRequest()->getParam('id')){
           $this->_addButton('addcontent', array(
            'label'     => Mage::helper('adminhtml')->__('Hang Content'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('borncmshooks/adminhtml_borncmshooks/newrow/hookid/'. $hook_id) . '\')',
            'class'     => 'add',
        ), -100); 
        }
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('borncmshooks_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'borncmshooks_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'borncmshooks_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('borncmshooks_data') && Mage::registry('borncmshooks_data')->getId() ) {
            return Mage::helper('borncmshooks')->__("Hook for '%s'", $this->htmlEscape(Mage::registry('borncmshooks_data')->getName()));
        } else {
            return Mage::helper('borncmshooks')->__('Hook a Page');
        }
    }
}