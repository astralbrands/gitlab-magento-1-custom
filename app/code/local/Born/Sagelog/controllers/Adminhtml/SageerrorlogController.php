<?php

class Born_Sagelog_Adminhtml_SageerrorlogController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        //return Mage::getSingleton('admin/session')->isAllowed('sagelog/sagelogbackend');
        return true;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('sagelog/adminhtml_logging', 'sageerrorlog'));
        $this->renderLayout();
    }

    public function viewAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $logIds = $this->getRequest()->getParam('id');
        if (!is_array($logIds)) {
            $this->_getSession()->addError($this->__('Please select the log(s).'));
        } else {
            if (!empty($logIds)) {
                try {
                    foreach ($logIds as $id) {
                        $item = Mage::getSingleton('sagelog/logging')->load($id);
                        $item->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Log(S) were deleted successfully')
					);
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
                $this->_redirectReferer();
            }
        }
    }
	public function exportCsvAction()
	{
		$fileName   = 'errorlog.csv';
		$content    = $this->getLayout()->createBlock('sagelog/adminhtml_logging_grid')
		->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
	$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
}