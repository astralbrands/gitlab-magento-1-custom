<?php 

class Born_Package_Block_Contacts_Professional extends Mage_Core_Block_Template
{

	public function getSubmitAction()
	{
		return Mage::getUrl('professional_form/index/submit');
	}	
}
?>
