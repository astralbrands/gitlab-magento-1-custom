<?php
/**
 * Astal Brand
 *
 * @category    Astal Brand
 * @package     Born_Package
 * @description  Preparing the Ticket and sending the data in ticket.
 */
class Born_Package_Zendesk_BloggerController extends Mage_Core_Controller_Front_Action
{

    public function submitAction()
    {

        $_postData = $this->getRequest()->getPost();

        if ($_postData) {
            try{

                $zenTicket = Mage::getModel('born_package/zendesk_api_bloggerTicket');


                $zenHelper = Mage::Helper('born_package/zendesk_data');
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