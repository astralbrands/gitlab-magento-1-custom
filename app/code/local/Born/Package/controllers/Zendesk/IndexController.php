<?php 

class Born_Package_Zendesk_IndexController extends Mage_Core_Controller_Front_Action
{

	public function submitAction()
	{

		$_postData = $this->getRequest()->getPost();

		if ($_postData) {
			try{

				$zenTicket = Mage::getModel('born_package/zendesk_api_ticket');

				$zenHelper = Mage::Helper('born_package/zendesk_data');

				if ($_postData['sign_up_for_our_newsletter'] && isset($_postData['sign_up_for_our_newsletter']) && $_postData['email_address']) {
					$_newsletterHelper = Mage::helper('born_package/newsletter_data');

					$_newsletterHelper->subscribe($_postData['email_address']);

					unset($_postData['sign_up_for_our_newsletter']);
				}
				
				$_postFiles = $_FILES;

				foreach ($_postFiles as $_file) {
					if (isset($_file) && !$_file['error']) {
						$_uploader = new Varien_File_Uploader($_file);
						$_uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'pdf'));
						$_uploader->setAllowRenameFiles(true);
						$_uploader->setFilesDispersion(false);

						$_path = Mage::getBaseDir('media') . DS.'signup'.DS;

						$_uploadedFile = $_uploader->save($_path, $_file['name']);

						$_uploadRespond = $zenTicket->upload($_uploadedFile);
					}
				}

				$_formData = $zenTicket->prepareTicketData($_postData);

				$result = $zenTicket->create($_formData);

				Mage::getSingleton('core/session')->addSuccess('Your application has been submitted!');

				if ($_redirectkey = Mage::getStoreConfig('zendesk/general/cms_redirect')) {
					$_redirectUrl = Mage::getUrl($_redirectkey);
					Mage::app()->getFrontController()->getResponse()->setRedirect($_redirectUrl);
				}
				else{
					$this->_redirectReferer();
				}
			}catch(Exception $e) {
				Mage::getSingleton('core/session')->addError('Unable to submit your request. Please, try again later');
				$this->_redirectReferer();
			}
		}else{
			Mage::getSingleton('core/session')->addError('No form data found. Please try again.');
			$this->_redirectReferer();
		}
	}
}


?>