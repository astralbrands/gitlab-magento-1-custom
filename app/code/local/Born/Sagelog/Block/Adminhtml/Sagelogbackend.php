<?php  

class Born_Sagelog_Block_Adminhtml_Sagelogbackend extends Mage_Adminhtml_Block_Template {
	
	public $errorType;
	public $errorMessage;
	protected function _construct()
	{
		$id = $this->getRequest()->getParam('id');
		$errorlog = Mage::getModel('sagelog/logging')->load($id);
		$this->errorType = $errorlog->getErrorType();
        $this->errorMessage = $errorlog->getErrorMessage();
	}
	public function getErrorType()
	{
		return $this->errorType;
	}
	public function getErrorMessage()
	{
        return $this->errorMessage;
	}
}