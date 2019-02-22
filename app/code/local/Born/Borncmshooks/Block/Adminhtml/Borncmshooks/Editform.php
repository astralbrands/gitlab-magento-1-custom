<?php

class Born_Borncmshooks_Block_Adminhtml_Borncmshooks_Editform extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'borncmshooks';
        $this->_controller = 'adminhtml_borncmshooks';
        
		$this->_removeButton('back');
        $this->_removeButton('delete');
        $this->_removeButton('save');
        $this->_removeButton('reset');
        if($this->getRequest()->getParam('hookid')){
            $this->_addButton('backtohook', array(
                'label'     => Mage::helper('borncmshooks')->__('Back'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('borncmshooks/adminhtml_borncmshooks/edit/id/' . $this->getRequest()->getParam('hookid'))  . '\')',
                'class'     => 'back',
            ), -97);  
        }
        
        
        
        $this->_addButton('deleterow', array(
            'label'     => Mage::helper('borncmshooks')->__('Delete Row'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('borncmshooks/adminhtml_borncmshooks/deleterow/id/' . $this->getRequest()->getParam('id')) . '\')',
            'class'     => 'delete',
        ), -98);

        $this->_addButton('saveandcontinuetofield', array(
            'label'     => Mage::helper('borncmshooks')->__('Save Row'),
            'onclick'   => 'saveRowAndContinueToHookEdit()',
            'class'     => 'save',
        ), -99);

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('borncmshooks')->__('Save And Continue Edit'),
            'onclick'   => 'saveRowAndContinueEdit()',
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

            function saveRowAndContinueEdit(){
                        if(document.getElementById('row_form').selectedIndex == 0){
                            alert('Select A Form');
                        }else{
                          editForm.submit($('edit_form').action+'backeditrow/edit');  
                        }
            }

            function saveRowAndContinueToHookEdit(){
                        if(document.getElementById('row_form').selectedIndex == 0){
                            alert('Select A Form');
                        }else{
                          editForm.submit($('edit_form').action+'backtohookfromrow/edit');  
                        }
            }
        ";
    }

    public function getHeaderText()
    {
        if($this->getRequest()->getParam('id')){
            $row = Mage::getModel('borncmshooks/rows')->load($this->getRequest()->getParam('id'));
        }
        
        if(isset($row)) {
            return Mage::helper('borncmshooks')->__("Edit Row '%s'", $this->htmlEscape($row->getName()));
        } else {
            return Mage::helper('borncmshooks')->__('Add Row');
        }
    }
}