<?php

/**
 * Class Born_Mediacenter_IndexController
 */
class Born_Mediacenter_IndexController extends Mage_Core_Controller_Front_Action
{
    public function IndexAction()
    {

        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Mediacenter"));
        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
        $breadcrumbs->addCrumb("home", array(
            "label" => $this->__("Home Page"),
            "title" => $this->__("Home Page"),
            "link" => Mage::getBaseUrl()
        ));

        $breadcrumbs->addCrumb("mediacenter", array(
            "label" => $this->__("Mediacenter"),
            "title" => $this->__("Mediacenter")
        ));

        $this->renderLayout();

    }

    public function downloadAction()
    {
        $filename = $this->getRequest()->getParam('file_name');
        $filepath = Mage::getBaseDir('media') . '/mediacenter/' . $filename;

        if ($filename) {
            try {
                if (!is_file($filepath) || !is_readable($filepath)) {
                    throw new Exception ();
                }
                $this->_prepareDownloadResponse($filename, array('type' => 'filename', 'value' => $filepath));

            } catch (Exception $e) {
                echo "File Missing";
            }
        } else {
            $this->_getSession()->addError($filepath . ' not found');
            return;
        }
    }

    public function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}